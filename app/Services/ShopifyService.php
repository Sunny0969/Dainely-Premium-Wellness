<?php
namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShopifyService
{
    protected string $domain;
    protected string $token;
    protected string $apiVersion;
    protected string $apiBase;

    public function __construct()
    {
        $this->domain     = config('shopify.store_domain', 'dmede-usa.myshopify.com');
        $this->token      = $this->resolveAccessToken();
        $this->apiVersion = config('shopify.api_version', '2024-01');
        $this->apiBase    = "https://{$this->domain}/admin/api/{$this->apiVersion}";
    }

    /**
     * Admin API token: shpat_ (or shpua_) — never the client secret shpss_.
     */
    protected function resolveAccessToken(): string
    {
        $fromEnv = trim((string) config('shopify.access_token', ''));

        if ($this->isValidAdminToken($fromEnv)) {
            return $fromEnv;
        }

        $path = storage_path('app/shopify_access_token');
        if (is_readable($path)) {
            $stored = trim((string) file_get_contents($path));
            if ($this->isValidAdminToken($stored)) {
                return $stored;
            }
        }

        return '';
    }

    protected function isValidAdminToken(string $token): bool
    {
        if ($token === '') {
            return false;
        }

        return ! str_starts_with($token, 'shpss_');
    }

    public function hasAdminAccessToken(): bool
    {
        return $this->token !== '';
    }

    protected function headers(): array
    {
        return [
            'X-Shopify-Access-Token' => $this->token,
            'Content-Type'           => 'application/json',
        ];
    }

    protected function httpClient(array $extraHeaders = []): \Illuminate\Http\Client\PendingRequest
    {
        $client = Http::timeout(30)
            ->acceptJson()
            ->withHeaders(array_merge(['User-Agent' => 'Dainely-Wellness/1.0'], $extraHeaders));

        if (! config('shopify.verify_ssl', true)) {
            $client = $client->withoutVerifying();
        }

        return $client;
    }

    /**
     * Create a product in Shopify.
     */
    public function createProduct(array $data): ?array
    {
        if (empty($this->token)) {
            Log::info('Shopify: no token — mock product', ['title' => $data['title']]);
            return ['id' => rand(1000000000, 9999999999), 'title' => $data['title']];
        }

        try {
            $response = $this->httpClient($this->headers())
                ->post($this->apiBase . '/products.json', ['product' => $data]);

            if ($response->successful()) {
                return $response->json()['product'] ?? null;
            }
            Log::error('Shopify createProduct failed', ['body' => $response->body()]);
            return null;
        } catch (\Exception $e) {
            Log::error('Shopify createProduct exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Fetch products from Shopify Admin API with structured success/error payload.
     *
     * @return array{success: bool, products: array, error: ?string, status: int}
     */
    /**
     * Step 1: Exchange Client ID + Secret for shpat_ via client_credentials grant.
     *
     * @return array{success: bool, token: ?string, error: ?string}
     */
    public function requestAccessTokenViaClientCredentials(): array
    {
        $clientId = config('shopify.client_id');
        $clientSecret = config('shopify.client_secret');

        if (empty($clientId) || empty($clientSecret)) {
            return [
                'success' => false,
                'token'   => null,
                'error'   => 'SHOPIFY_CLIENT_ID and SHOPIFY_CLIENT_SECRET are required in .env.',
            ];
        }

        $cacheKey = 'shopify_client_credentials_' . md5($this->domain . $clientId);
        $cached = Cache::get($cacheKey);
        if (is_string($cached) && $cached !== '') {
            return ['success' => true, 'token' => $cached, 'error' => null];
        }

        try {
            $response = $this->httpClient()
                ->asForm()
                ->post("https://{$this->domain}/admin/oauth/access_token", [
                    'grant_type'    => 'client_credentials',
                    'client_id'     => $clientId,
                    'client_secret' => $clientSecret,
                ]);

            if (! $response->successful()) {
                Log::error('Shopify client_credentials auth failed', ['body' => $response->body()]);

                return [
                    'success' => false,
                    'token'   => null,
                    'error'   => 'Failed to authenticate with Shopify: ' . $response->body(),
                ];
            }

            $token = $response->json('access_token');
            if (! is_string($token) || $token === '') {
                return [
                    'success' => false,
                    'token'   => null,
                    'error'   => 'Shopify did not return an access_token.',
                ];
            }

            Cache::put($cacheKey, $token, now()->addMinutes(55));

            return ['success' => true, 'token' => $token, 'error' => null];
        } catch (\Exception $e) {
            Log::error('Shopify client_credentials exception: ' . $e->getMessage());

            return ['success' => false, 'token' => null, 'error' => $e->getMessage()];
        }
    }

    public function fetchProducts(int $limit = 50): array
    {
        $limit = max(1, min($limit, 250));

        if ($this->hasAdminAccessToken()) {
            return $this->fetchProductsFromAdminApi($limit, $this->token);
        }

        $auth = $this->requestAccessTokenViaClientCredentials();
        if ($auth['success'] && ! empty($auth['token'])) {
            $admin = $this->fetchProductsFromAdminApi($limit, $auth['token']);
            if ($admin['success']) {
                $admin['source'] = 'client_credentials';

                return $admin;
            }
        }

        $authError = $auth['error'] ?? null;
        $storefrontError = null;

        if (filter_var(config('shopify.use_storefront_catalog', true), FILTER_VALIDATE_BOOLEAN)) {
            $storefront = $this->fetchProductsFromStorefront($limit);
            if ($storefront['success']) {
                return $storefront;
            }
            $storefrontError = $storefront['error'] ?? null;
        }

        return [
            'success'  => false,
            'products' => [],
            'error'    => trim(($authError ? "Auth: {$authError}. " : '') . ($storefrontError ? "Storefront: {$storefrontError}" : ''))
                ?: 'Could not load products from Shopify.',
            'status'   => 503,
            'source'   => null,
        ];
    }

    /**
     * @return array{success: bool, products: array, error: ?string, status: int, source?: string}
     */
    public function fetchProductsFromAdminApi(int $limit, ?string $accessToken = null): array
    {
        $token = $accessToken ?? $this->token;
        if ($token === '') {
            return [
                'success'  => false,
                'products' => [],
                'error'    => 'No access token available.',
                'status'   => 503,
                'source'   => 'admin',
            ];
        }

        try {
            $response = $this->httpClient([
                'X-Shopify-Access-Token' => $token,
                'Content-Type'           => 'application/json',
            ])->get($this->apiBase . '/products.json', ['limit' => $limit]);

            if ($response->successful()) {
                return [
                    'success'  => true,
                    'products' => $response->json()['products'] ?? [],
                    'error'    => null,
                    'status'   => 200,
                    'source'   => 'admin',
                ];
            }

            $message = $response->json('errors') ?? $response->body();
            if (is_array($message)) {
                $message = json_encode($message);
            }

            Log::error('Shopify Admin API fetchProducts failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return [
                'success'  => false,
                'products' => [],
                'error'    => trim((string) $message) ?: 'Shopify Admin API request failed.',
                'status'   => $response->status(),
                'source'   => 'admin',
            ];
        } catch (\Exception $e) {
            Log::error('Shopify Admin API fetchProducts exception: ' . $e->getMessage());

            return [
                'success'  => false,
                'products' => [],
                'error'    => $e->getMessage(),
                'status'   => 500,
                'source'   => 'admin',
            ];
        }
    }

    /**
     * Public storefront catalog (no token). Client ID/Secret not required for read-only display.
     *
     * @return array{success: bool, products: array, error: ?string, status: int, source?: string}
     */
    protected function fetchProductsFromStorefront(int $limit): array
    {
        try {
            $response = $this->httpClient()
                ->get("https://{$this->domain}/products.json", ['limit' => $limit]);

            if ($response->successful()) {
                return [
                    'success'  => true,
                    'products' => $response->json()['products'] ?? [],
                    'error'    => null,
                    'status'   => 200,
                    'source'   => 'storefront',
                ];
            }

            return [
                'success'  => false,
                'products' => [],
                'error'    => 'Storefront catalog unavailable (HTTP ' . $response->status() . ').',
                'status'   => $response->status(),
                'source'   => 'storefront',
            ];
        } catch (\Exception $e) {
            return [
                'success'  => false,
                'products' => [],
                'error'    => $e->getMessage(),
                'status'   => 500,
                'source'   => 'storefront',
            ];
        }
    }

    /**
     * Fetch a single product by Shopify ID from Admin API.
     *
     * @return array{success: bool, product: ?array, error: ?string}
     */
    public function fetchProductById(int|string $id, ?string $accessToken = null): array
    {
        $token = $accessToken ?? $this->token;

        if ($token === '') {
            // Try client_credentials if no token
            $auth = $this->requestAccessTokenViaClientCredentials();
            if ($auth['success'] && !empty($auth['token'])) {
                $token = $auth['token'];
            } else {
                return ['success' => false, 'product' => null, 'error' => 'No access token available.'];
            }
        }

        try {
            $response = $this->httpClient([
                'X-Shopify-Access-Token' => $token,
                'Content-Type'           => 'application/json',
            ])->get($this->apiBase . "/products/{$id}.json");

            if ($response->successful()) {
                return [
                    'success' => true,
                    'product' => $response->json()['product'] ?? null,
                    'error'   => null,
                ];
            }

            return [
                'success' => false,
                'product' => null,
                'error'   => 'Product not found (HTTP ' . $response->status() . ').',
            ];
        } catch (\Exception $e) {
            Log::error('Shopify fetchProductById exception: ' . $e->getMessage());
            return ['success' => false, 'product' => null, 'error' => $e->getMessage()];
        }
    }

    /**
     * Fetch a single product by Shopify handle from Admin API.
     *
     * @return array{success: bool, product: ?array, error: ?string}
     */
    public function fetchProductByHandle(string $handle, ?string $accessToken = null): array
    {
        $handle = trim($handle);
        if ($handle === '') {
            return ['success' => false, 'product' => null, 'error' => 'Handle is required.'];
        }

        $token = $accessToken ?? $this->token;

        if ($token === '') {
            $auth = $this->requestAccessTokenViaClientCredentials();
            if ($auth['success'] && ! empty($auth['token'])) {
                $token = $auth['token'];
            } else {
                return ['success' => false, 'product' => null, 'error' => 'No access token available.'];
            }
        }

        try {
            $response = $this->httpClient([
                'X-Shopify-Access-Token' => $token,
                'Content-Type'           => 'application/json',
            ])->get($this->apiBase . '/products.json', ['handle' => $handle]);

            if ($response->successful()) {
                $product = $response->json()['products'][0] ?? null;

                return [
                    'success' => $product !== null,
                    'product' => $product,
                    'error'   => $product === null ? 'Product not found.' : null,
                ];
            }

            return [
                'success' => false,
                'product' => null,
                'error'   => 'Product not found (HTTP ' . $response->status() . ').',
            ];
        } catch (\Exception $e) {
            Log::error('Shopify fetchProductByHandle exception: ' . $e->getMessage());

            return ['success' => false, 'product' => null, 'error' => $e->getMessage()];
        }
    }

    /**
     * List all products (simple array; empty on failure).
     */
    public function getProducts(int $limit = 50): array
    {
        $result = $this->fetchProducts($limit);

        return $result['success'] ? $result['products'] : [];
    }

    /**
     * Normalize Shopify API products for frontend cards / slider.
     *
     * @param  array<int, array<string, mixed>>  $products
     * @return array<int, array<string, mixed>>
     */
    public function mapProductsForDisplay(array $products): array
    {
        $storeUrl = 'https://' . $this->domain;

        return array_values(array_map(function (array $product) use ($storeUrl) {
            $variant = $product['variants'][0] ?? [];
            $image = $product['image']['src']
                ?? ($product['images'][0]['src'] ?? null);
            $handle = $product['handle'] ?? null;

            return [
                'id'             => $product['id'] ?? null,
                'title'          => $product['title'] ?? 'Untitled',
                'handle'         => $handle,
                'status'         => $product['status'] ?? 'active',
                'image'          => $image,
                'price'          => $variant['price'] ?? null,
                'compare_at'     => $variant['compare_at_price'] ?? null,
                'variant_count'  => count($product['variants'] ?? []),
                'updated_at'     => $product['updated_at'] ?? null,
                'url'            => $handle ? "{$storeUrl}/products/{$handle}" : $storeUrl,
            ];
        }, $products));
    }

    /**
     * Create an order in Shopify (called from queue job).
     */
    public function createOrder(array $data): ?array
    {
        if (empty($this->token)) {
            Log::info('Shopify: no token — mock order', ['ref' => $data['order_number'] ?? '']);
            return ['id' => rand(1000000, 9999999), 'order_number' => 'MOCK-' . rand(1000,9999)];
        }

        $lineItems = array_map(fn($item) => [
            'title'      => $item['product_name'],
            'quantity'   => $item['quantity'],
            'price'      => number_format($item['unit_price_usd'], 2, '.', ''),
            'sku'        => $item['sku'] ?? '',
            'requires_shipping' => true,
        ], $data['items']);

        $payload = [
            'order' => [
                'line_items'       => $lineItems,
                'email'            => $data['customer_email'],
                'financial_status' => 'paid',
                'tags'             => 'dainely-platform,' . ($data['locale'] ?? 'en'),
                'note'             => 'Dainely Order: ' . ($data['order_number'] ?? ''),
                'note_attributes'  => [
                    ['name' => 'dainely_order_number', 'value' => $data['order_number'] ?? ''],
                    ['name' => 'square_payment_id',    'value' => $data['square_payment_id'] ?? ''],
                ],
                'shipping_address' => [
                    'first_name' => $data['customer_first_name'],
                    'last_name'  => $data['customer_last_name'],
                    'address1'   => $data['shipping_address1'],
                    'address2'   => $data['shipping_address2'] ?? '',
                    'city'       => $data['shipping_city'],
                    'province'   => $data['shipping_state'] ?? '',
                    'zip'        => $data['shipping_zip'],
                    'country'    => $data['shipping_country'],
                    'phone'      => $data['customer_phone'] ?? '',
                ],
                'send_receipt'             => true,
                'send_fulfillment_receipt' => true,
            ],
        ];

        if (!empty($data['discount_code'])) {
            $payload['order']['discount_codes'] = [[
                'code'   => $data['discount_code'],
                'amount' => number_format($data['discount_amount'] ?? 0, 2),
                'type'   => 'fixed_amount',
            ]];
        }

        try {
            $response = $this->httpClient($this->headers())
                ->post($this->apiBase . '/orders.json', $payload);

            if ($response->successful()) {
                return $response->json()['order'] ?? null;
            }
            Log::error('Shopify createOrder failed', ['status' => $response->status(), 'body' => $response->body()]);
            return null;
        } catch (\Exception $e) {
            Log::error('Shopify createOrder exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Validate Shopify webhook HMAC.
     */
    public function validateWebhookSignature(string $body, string $hmacHeader): bool
    {
        $secret = config('shopify.webhook_secret', '');
        if (empty($secret)) return true;
        return hash_equals(
            base64_encode(hash_hmac('sha256', $body, $secret, true)),
            $hmacHeader
        );
    }
}
