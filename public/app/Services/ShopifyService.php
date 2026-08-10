<?php
namespace App\Services;

use App\Support\ProductSlugResolver;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShopifyService
{
    public const CATALOG_CACHE_KEY = 'shopify_products_catalog_v1';

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

    /**
     * Token for Admin API writes (orders): env/storage token, else client_credentials grant.
     */
    protected function resolveEffectiveAccessToken(): string
    {
        if ($this->token !== '') {
            return $this->token;
        }

        $auth = $this->requestAccessTokenViaClientCredentials();
        if ($auth['success'] && ! empty($auth['token'])) {
            return (string) $auth['token'];
        }

        return '';
    }

    public function canSyncOrders(): bool
    {
        if (! config('shopify.sync_orders', true)) {
            return false;
        }

        return $this->resolveEffectiveAccessToken() !== '';
    }

    protected function headers(): array
    {
        return $this->headersForToken($this->token);
    }

    protected function headersForToken(string $token): array
    {
        return [
            'X-Shopify-Access-Token' => $token,
            'Content-Type'           => 'application/json',
        ];
    }

    protected function httpClient(array $extraHeaders = [], int $timeoutSeconds = 12): \Illuminate\Http\Client\PendingRequest
    {
        $client = Http::timeout(max(3, $timeoutSeconds))
            ->connectTimeout(5)
            ->acceptJson()
            ->withHeaders(array_merge(['User-Agent' => 'Dainely-Wellness/1.0'], $extraHeaders));

        if (! config('shopify.verify_ssl', true)) {
            $client = $client->withoutVerifying();
        }

        return $client;
    }

    /**
     * Drop catalog + header caches (and optional single-product handle cache).
     */
    public function forgetCatalogCaches(?string $handle = null): void
    {
        Cache::forget(self::CATALOG_CACHE_KEY);
        Cache::forget('header_shopify_products_v2');
        Cache::forget('featured_shopify_product_v1');

        if (is_string($handle) && trim($handle) !== '') {
            Cache::forget('shopify_product_handle_'.md5(trim($handle)));
        }
    }

    /**
     * Execute a Shopify Admin GraphQL query/mutation.
     *
     * @return array{success: bool, data: ?array, error: ?string}
     */
    public function graphql(string $query, array $variables = []): array
    {
        $token = $this->resolveEffectiveAccessToken();
        if ($token === '') {
            return ['success' => false, 'data' => null, 'error' => 'No Shopify Admin API token available.'];
        }

        try {
            $response = $this->httpClient($this->headersForToken($token), 25)
                ->post($this->apiBase . '/graphql.json', [
                    'query'     => $query,
                    'variables' => $variables,
                ]);

            if (! $response->successful()) {
                return [
                    'success' => false,
                    'data'    => null,
                    'error'   => 'GraphQL HTTP ' . $response->status() . ': ' . $response->body(),
                ];
            }

            $body = $response->json();
            if (! empty($body['errors'])) {
                $message = collect($body['errors'])->pluck('message')->implode('; ');

                return ['success' => false, 'data' => null, 'error' => $message];
            }

            return ['success' => true, 'data' => $body['data'] ?? null, 'error' => null];
        } catch (\Throwable $e) {
            return ['success' => false, 'data' => null, 'error' => $e->getMessage()];
        }
    }

    /**
     * Find or create a Shopify customer by email.
     */
    public function findOrCreateCustomer(array $data): ?int
    {
        $email = trim((string) ($data['email'] ?? ''));
        if ($email === '') {
            return null;
        }

        $token = $this->resolveEffectiveAccessToken();
        if ($token === '') {
            return null;
        }

        try {
            $search = $this->httpClient($this->headersForToken($token))
                ->get($this->apiBase . '/customers/search.json', [
                    'query' => 'email:' . $email,
                    'limit' => 1,
                ]);

            if ($search->successful()) {
                $existing = $search->json('customers.0.id');
                if ($existing) {
                    return (int) $existing;
                }
            }

            $country = strtoupper((string) ($data['country'] ?? 'US'));
            $phone   = $this->sanitizePhoneForShopify($data['phone'] ?? null, $country);

            $customerPayload = [
                'customer' => [
                    'first_name' => $data['first_name'] ?? '',
                    'last_name'  => $data['last_name'] ?? '',
                    'email'      => $email,
                    'tags'       => 'dainely-website',
                    'addresses'  => [[
                        'first_name' => $data['first_name'] ?? '',
                        'last_name'  => $data['last_name'] ?? '',
                        'address1'   => $data['address1'] ?? '',
                        'address2'   => $data['address2'] ?? '',
                        'city'       => $data['city'] ?? '',
                        'province'   => $data['state'] ?? '',
                        'zip'        => $data['zip'] ?? '',
                        'country'    => $country,
                        'phone'      => $phone ?? '',
                        'default'    => true,
                    ]],
                ],
            ];

            if ($phone !== null) {
                $customerPayload['customer']['phone'] = $phone;
            }

            $create = $this->httpClient($this->headersForToken($token))
                ->post($this->apiBase . '/customers.json', $customerPayload);

            if ($create->successful()) {
                return (int) ($create->json('customer.id') ?? 0) ?: null;
            }

            Log::warning('Shopify customer create failed', ['body' => $create->body(), 'email' => $email]);
        } catch (\Throwable $e) {
            Log::warning('Shopify findOrCreateCustomer exception: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Create a product in Shopify.
     */
    public function createProduct(array $data): ?array
    {
        $token = $this->resolveEffectiveAccessToken();
        if ($token === '') {
            Log::warning('Shopify createProduct skipped — no Admin API token', ['title' => $data['title'] ?? '']);

            return null;
        }

        try {
            $response = $this->httpClient($this->headersForToken($token))
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
     * Exchange Client ID + Secret for shpat_ via client_credentials grant.
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
        $ttl = (int) config('storefront.shopify_cache_ttl', config('shopify.product_cache_ttl', 900));
        $localFirst = filter_var(config('shopify.storefront_local_first', true), FILTER_VALIDATE_BOOLEAN);

        // One shared catalog cache — home/header/listing all reuse it.
        // Prefer webhook-warmed local data; hit Shopify only on cold start.
        $cached = Cache::get(self::CATALOG_CACHE_KEY);
        if ((! is_array($cached) || ($cached['success'] ?? false) !== true) && $localFirst) {
            $cached = app(LocalShopifyCatalog::class)->catalogPayload();
        }

        if (! is_array($cached) || ($cached['success'] ?? false) !== true) {
            $cached = $this->fetchProductsLive(50);
            if (($cached['success'] ?? false) === true) {
                Cache::put(self::CATALOG_CACHE_KEY, $cached, $ttl);
                if ($localFirst && ! empty($cached['products']) && is_array($cached['products'])) {
                    app(LocalShopifyCatalog::class)->seedFromLiveCatalog($cached['products']);
                }
            }
        }

        if (($cached['success'] ?? false) !== true) {
            return is_array($cached) ? $cached : $this->fetchProductsLive($limit);
        }

        $products = is_array($cached['products'] ?? null) ? $cached['products'] : [];

        if ($limit > 50 && ! $localFirst) {
            return $this->fetchProductsLive($limit);
        }

        if ($limit < count($products)) {
            $cached['products'] = array_slice($products, 0, $limit);
        }

        return $cached;
    }

    /**
     * Uncached Shopify catalog fetch.
     *
     * @return array{success: bool, products: array, error: ?string, status?: int, source: ?string}
     */
    protected function fetchProductsLive(int $limit): array
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
     * Paginate the full Shopify catalog for Phase 2 bulk sync into Supabase.
     *
     * @return array{success: bool, products: array<int, array>, error: ?string, source: ?string}
     */
    public function fetchAllProducts(int $pageSize = 250): array
    {
        $pageSize = max(1, min($pageSize, 250));

        if ($this->hasAdminAccessToken()) {
            return $this->fetchAllProductsFromAdminApi($pageSize, $this->token);
        }

        $auth = $this->requestAccessTokenViaClientCredentials();
        if ($auth['success'] && ! empty($auth['token'])) {
            $admin = $this->fetchAllProductsFromAdminApi($pageSize, $auth['token']);
            if ($admin['success']) {
                $admin['source'] = 'client_credentials';

                return $admin;
            }
        }

        if (filter_var(config('shopify.use_storefront_catalog', true), FILTER_VALIDATE_BOOLEAN)) {
            return $this->fetchAllProductsFromStorefront($pageSize);
        }

        return [
            'success'  => false,
            'products' => [],
            'error'    => $auth['error'] ?? 'Could not load full Shopify catalog.',
            'source'   => null,
        ];
    }

    /**
     * @return array{success: bool, products: array<int, array>, error: ?string, source: string}
     */
    protected function fetchAllProductsFromAdminApi(int $pageSize, string $accessToken): array
    {
        $all = [];
        $sinceId = 0;

        try {
            do {
                $query = ['limit' => $pageSize];
                if ($sinceId > 0) {
                    $query['since_id'] = $sinceId;
                }

                $response = $this->httpClient([
                    'X-Shopify-Access-Token' => $accessToken,
                    'Content-Type'           => 'application/json',
                ])->get($this->apiBase . '/products.json', $query);

                if (! $response->successful()) {
                    $message = $response->json('errors') ?? $response->body();
                    if (is_array($message)) {
                        $message = json_encode($message);
                    }

                    return [
                        'success'  => false,
                        'products' => $all,
                        'error'    => trim((string) $message) ?: 'Shopify Admin API pagination failed.',
                        'source'   => 'admin',
                    ];
                }

                $batch = $response->json()['products'] ?? [];
                if ($batch === []) {
                    break;
                }

                foreach ($batch as $product) {
                    $all[] = $product;
                    $sinceId = max($sinceId, (int) ($product['id'] ?? 0));
                }
            } while (count($batch) >= $pageSize);

            return [
                'success'  => true,
                'products' => $all,
                'error'    => null,
                'source'   => 'admin',
            ];
        } catch (\Exception $e) {
            Log::error('Shopify fetchAllProductsFromAdminApi: ' . $e->getMessage());

            return [
                'success'  => false,
                'products' => $all,
                'error'    => $e->getMessage(),
                'source'   => 'admin',
            ];
        }
    }

    /**
     * @return array{success: bool, products: array<int, array>, error: ?string, source: string}
     */
    protected function fetchAllProductsFromStorefront(int $pageSize): array
    {
        $all = [];
        $page = 1;

        try {
            do {
                $response = $this->httpClient()
                    ->get("https://{$this->domain}/products.json", [
                        'limit' => $pageSize,
                        'page'  => $page,
                    ]);

                if (! $response->successful()) {
                    return [
                        'success'  => false,
                        'products' => $all,
                        'error'    => 'Storefront catalog unavailable (HTTP ' . $response->status() . ').',
                        'source'   => 'storefront',
                    ];
                }

                $batch = $response->json()['products'] ?? [];
                if ($batch === []) {
                    break;
                }

                foreach ($batch as $product) {
                    $all[] = $product;
                }

                $page++;
            } while (count($batch) >= $pageSize && $page <= 40);

            return [
                'success'  => true,
                'products' => $all,
                'error'    => null,
                'source'   => 'storefront',
            ];
        } catch (\Exception $e) {
            return [
                'success'  => false,
                'products' => $all,
                'error'    => $e->getMessage(),
                'source'   => 'storefront',
            ];
        }
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
     * @param  bool  $fresh  When true, bypass cache (cart/checkout stock & price).
     * @return array{success: bool, product: ?array, error: ?string}
     */
    public function fetchProductByHandle(string $handle, ?string $accessToken = null, bool $fresh = false): array
    {
        $handle = trim($handle);
        if ($handle === '') {
            return ['success' => false, 'product' => null, 'error' => 'Handle is required.'];
        }

        $cacheKey = 'shopify_product_handle_'.md5($handle);
        $ttl      = (int) config('storefront.shopify_cache_ttl', config('shopify.product_cache_ttl', 900));
        $localFirst = filter_var(config('shopify.storefront_local_first', true), FILTER_VALIDATE_BOOLEAN);

        if (! $fresh) {
            $cached = Cache::get($cacheKey);
            if (is_array($cached) && ($cached['success'] ?? false) && ! empty($cached['product'])) {
                return $cached;
            }

            if ($localFirst) {
                $local = app(LocalShopifyCatalog::class)->productByHandle($handle);
                if (is_array($local) && ! empty($local['handle'])) {
                    $result = [
                        'success' => true,
                        'product' => $local,
                        'error'   => null,
                        'source'  => 'local_webhook_sync',
                    ];
                    Cache::put($cacheKey, $result, $ttl);

                    return $result;
                }
            }
        }

        $result = $this->fetchProductByHandleLive($handle, $accessToken);

        if ($result['success'] ?? false) {
            Cache::put($cacheKey, $result, $ttl);
            if ($localFirst && ! $fresh && is_array($result['product'] ?? null)) {
                app(LocalShopifyCatalog::class)->rememberWebhookProduct($result['product']);
            }
        }

        return $result;
    }

    /**
     * Uncached Shopify product fetch by handle.
     *
     * @return array{success: bool, product: ?array, error: ?string}
     */
    protected function fetchProductByHandleLive(string $handle, ?string $accessToken = null): array
    {
        $token = $accessToken ?? $this->token;

        if ($token === '') {
            $auth = $this->requestAccessTokenViaClientCredentials();
            if ($auth['success'] && ! empty($auth['token'])) {
                $token = $auth['token'];
            } else {
                return $this->fetchProductByHandleFromStorefront($handle);
            }
        }

        try {
            $response = $this->httpClient([
                'X-Shopify-Access-Token' => $token,
                'Content-Type'           => 'application/json',
            ])->get($this->apiBase . '/products.json', ['handle' => $handle]);

            if ($response->successful()) {
                $product = $response->json()['products'][0] ?? null;

                if ($product !== null) {
                    return [
                        'success' => true,
                        'product' => $product,
                        'error'   => null,
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::error('Shopify fetchProductByHandle exception: ' . $e->getMessage());
        }

        return $this->fetchProductByHandleFromStorefront($handle);
    }

    /**
     * @return array{success: bool, product: ?array, error: ?string}
     */
    protected function fetchProductByHandleFromStorefront(string $handle): array
    {
        try {
            $response = $this->httpClient()
                ->get("https://{$this->domain}/products/{$handle}.json");

            if ($response->successful()) {
                $product = $response->json()['product'] ?? null;

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
            return [
                'success' => false,
                'product' => null,
                'error'   => $e->getMessage(),
            ];
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

        $active = array_filter($products, fn (array $product) => ($product['status'] ?? 'active') === 'active');

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
                'variants'       => $product['variants'] ?? [],
                'variant_count'  => count($product['variants'] ?? []),
                'updated_at'     => $product['updated_at'] ?? null,
                'url'            => $handle ? "{$storeUrl}/products/{$handle}" : $storeUrl,
            ];
        }, $active));
    }

    /**
     * Map a raw Shopify product for sidebar / CTA blocks.
     */
    public function mapProductForCta(array $product): object
    {
        $variant = $product['variants'][0] ?? [];
        $image = $product['image']['src'] ?? ($product['images'][0]['src'] ?? null);

        return (object) [
            'title'             => $product['title'] ?? 'Product',
            'handle'            => $product['handle'] ?? '',
            'main_image'        => $image ?? '',
            'price_usd'         => (float) ($variant['price'] ?? 0),
            'compare_price_usd' => ! empty($variant['compare_at_price']) ? (float) $variant['compare_at_price'] : null,
        ];
    }

    /**
     * First active Shopify product for featured CTAs.
     */
    public function featuredProduct(): ?object
    {
        $result = $this->fetchProducts(12);
        if (! $result['success'] || empty($result['products'])) {
            return null;
        }

        foreach ($result['products'] as $product) {
            if (($product['status'] ?? 'active') === 'active') {
                return $this->mapProductForCta($product);
            }
        }

        return null;
    }

    /**
     * Build a Shopify order from website checkout data (after Square payment).
     */
    /**
     * @return array{success: bool, order: ?array, error: ?string}
     */
    public function createOrderFromCheckout(array $checkout): array
    {
        if (! config('shopify.sync_orders', true)) {
            Log::info('Shopify order sync disabled', ['ref' => $checkout['order_number'] ?? '']);

            return ['success' => false, 'order' => null, 'error' => 'Shopify sync disabled in config.'];
        }

        $cartItems = $checkout['items'] ?? [];
        if ($cartItems === [] && ! empty($checkout['cart'])) {
            $legacyCart = $checkout['cart'];
            $cartItems  = [[
                'title'        => $legacyCart['title'] ?? 'Dainely Product',
                'price'        => $legacyCart['price'] ?? 0,
                'quantity'     => (int) ($checkout['qty'] ?? ($legacyCart['quantity'] ?? 1)),
                'option_value' => $legacyCart['option_value'] ?? null,
                'option_label' => $legacyCart['option_label'] ?? null,
                'variant_id'   => $legacyCart['variant_id'] ?? null,
                'sku'          => $legacyCart['sku'] ?? '',
                'product_id'   => $legacyCart['product_id'] ?? null,
            ]];
        }

        $subtotalUsd = 0.0;
        $shopifyItems = [];

        foreach ($cartItems as $cartItem) {
            $qty       = max(1, (int) ($cartItem['quantity'] ?? 1));
            $unitPrice = (float) ($cartItem['price'] ?? 0);
            $subtotalUsd += round($unitPrice * $qty, 2);

            $productName = (string) ($cartItem['title'] ?? 'Dainely Product');
            if (! empty($cartItem['option_label']) && ! empty($cartItem['option_value'])) {
                $productName .= ' — ' . $cartItem['option_value'];
            } elseif (! empty($cartItem['option_value'])) {
                $productName .= ' — ' . $cartItem['option_value'];
            }

            $itemCart = array_merge($cartItem, [
                'title'      => $cartItem['title'] ?? 'Product',
                'variant_id' => $cartItem['variant_id'] ?? null,
            ]);

            $shopifyItems[] = [
                'product_name'   => $productName,
                'quantity'       => $qty,
                'unit_price_usd' => $unitPrice,
                'sku'            => $cartItem['sku'] ?? '',
                'variant_id'     => $this->resolveCartVariantId($itemCart),
            ];
        }

        $shippingMethod = $checkout['shipping_method'] ?? 'standard';
        $shippingUsd    = (float) ($checkout['shipping_usd'] ?? $this->estimateShippingUsd($subtotalUsd, $shippingMethod));
        $taxUsd         = round((float) ($checkout['tax_usd'] ?? 0), 2);
        $discountUsd    = round((float) ($checkout['discount_usd'] ?? 0), 2);
        $totalUsd       = round((float) ($checkout['total_usd'] ?? ($subtotalUsd + $shippingUsd + $taxUsd - $discountUsd)), 2);

        $presentation = $this->resolveCustomerPresentation(
            $checkout,
            $subtotalUsd,
            $shippingUsd,
            $taxUsd,
            $totalUsd,
            $discountUsd,
        );

        $chargeCurrency = strtoupper((string) ($checkout['charge_currency'] ?? 'USD'));
        foreach ($shopifyItems as $index => $item) {
            $unitUsd = (float) ($item['unit_price_usd'] ?? 0);
            $shopifyItems[$index]['unit_price'] = $presentation['currency'] === $chargeCurrency
                ? $unitUsd
                : round($unitUsd * max((float) $presentation['exchange_rate'], 0.0001), 2);
        }

        $presentedTaxLines = $this->presentTaxLines($checkout['tax_lines'] ?? [], $presentation, $chargeCurrency);

        $noteAttributes = [
            ['name' => 'dainely_order_number', 'value' => (string) ($checkout['order_number'] ?? '')],
            ['name' => 'square_payment_id', 'value' => (string) ($checkout['square_payment_id'] ?? '')],
            ['name' => 'order_source', 'value' => 'dainely-website'],
            ['name' => 'shipping_method', 'value' => $shippingMethod],
            ['name' => 'checkout_locale', 'value' => (string) ($checkout['locale'] ?? 'en')],
            ['name' => 'charge_currency', 'value' => (string) ($checkout['charge_currency'] ?? 'USD')],
            ['name' => 'display_currency', 'value' => $presentation['currency']],
            ['name' => 'display_total', 'value' => (string) $presentation['total']],
            ['name' => 'settlement_total_usd', 'value' => (string) $presentation['total_usd']],
            ['name' => 'exchange_rate', 'value' => (string) $presentation['exchange_rate']],
            ['name' => 'tax_usd', 'value' => (string) $taxUsd],
        ];

        $result = $this->createOrder([
            'order_number'         => $checkout['order_number'] ?? '',
            'customer_email'       => $checkout['email'] ?? '',
            'customer_first_name'  => $checkout['first_name'] ?? '',
            'customer_last_name'   => $checkout['last_name'] ?? '',
            'customer_phone'       => $checkout['phone'] ?? '',
            'shipping_address1'    => $checkout['address1'] ?? '',
            'shipping_address2'    => $checkout['address2'] ?? '',
            'shipping_city'        => $checkout['city'] ?? '',
            'shipping_state'       => $checkout['state'] ?? '',
            'shipping_zip'         => $checkout['zip'] ?? '',
            'shipping_country'     => $checkout['country'] ?? 'US',
            'subtotal'             => $presentation['subtotal'],
            'shipping'             => $presentation['shipping'],
            'tax'                  => $presentation['tax'],
            'tax_lines'            => $presentedTaxLines,
            'shipping_method'      => $shippingMethod,
            'total'                => $presentation['total'],
            'discount_amount'      => $presentation['discount'],
            'discount_code'        => $checkout['discount_code'] ?? null,
            'square_payment_id'    => $checkout['square_payment_id'] ?? '',
            'locale'               => $checkout['locale'] ?? 'en',
            'currency'             => $presentation['currency'],
            'exchange_rate'        => $presentation['exchange_rate'],
            'subtotal_usd'         => $subtotalUsd,
            'shipping_usd'         => $shippingUsd,
            'tax_usd'              => $taxUsd,
            'total_usd'            => $totalUsd,
            'note_attributes'      => $noteAttributes,
            'items'                => $shopifyItems,
        ]);

        if (! $result['success']) {
            Log::warning('Shopify createOrderFromCheckout failed', [
                'ref'   => $checkout['order_number'] ?? '',
                'error' => $result['error'] ?? 'unknown',
            ]);
        }

        return $result;
    }

    /**
     * Accept only positive numeric Shopify variant IDs (reject "0", handles, etc.).
     */
    public function normalizeVariantId(mixed $variantId): ?int
    {
        if ($variantId === null || $variantId === '') {
            return null;
        }

        if (! is_numeric($variantId)) {
            return null;
        }

        $id = (int) $variantId;

        return $id > 0 ? $id : null;
    }

    /**
     * Resolve a Shopify variant ID from cart session data (direct ID or product lookup).
     *
     * @param  array<string, mixed>  $cart
     */
    public function resolveCartVariantId(array $cart): ?int
    {
        $existing = $this->normalizeVariantId($cart['variant_id'] ?? null);
        if ($existing !== null) {
            return $existing;
        }

        $fromOption = $this->normalizeVariantId($cart['option_value'] ?? null);
        if ($fromOption !== null) {
            return $fromOption;
        }

        $productId = trim((string) ($cart['product_id'] ?? ''));
        $product   = null;

        if ($productId === '' || $productId === 'dainely-belt') {
            $handle = ProductSlugResolver::resolveHandle('dainely-belt');
            $result = $this->fetchProductByHandle($handle, null, false);
            $product = $result['product'] ?? null;
        } elseif (ctype_digit($productId)) {
            $result = $this->fetchProductById($productId);
            $product = $result['product'] ?? null;
        } else {
            $handle = ProductSlugResolver::resolveHandle($productId);
            $result = $this->fetchProductByHandle($handle, null, false);
            $product = $result['product'] ?? null;
        }

        if (! is_array($product) || empty($product['variants'])) {
            return null;
        }

        $variants = $product['variants'];

        if (! empty($cart['option_label'])) {
            foreach ($variants as $variant) {
                if (strcasecmp((string) ($variant['title'] ?? ''), (string) $cart['option_label']) === 0) {
                    return $this->normalizeVariantId($variant['id'] ?? null);
                }
            }
        }

        return $this->normalizeVariantId($variants[0]['id'] ?? null);
    }

    public function estimateShippingUsd(float $subtotal, string $method = 'standard'): float
    {
        $threshold = app(CurrencyService::class)->freeShippingThresholdUsd();
        $ttl = max(60, (int) config('shopify.shipping_cache_ttl', 1800));
        $cacheKey = 'shipping_rate_'.md5(json_encode([
            round($subtotal, 2),
            $method,
            $threshold,
        ]));

        return (float) Cache::remember($cacheKey, $ttl, function () use ($subtotal, $method, $threshold) {
            if ($subtotal >= $threshold) {
                return 0.0;
            }

            return $method === 'express' ? 24.99 : 9.99;
        });
    }

    /**
     * Validate an active Shopify discount/coupon code and compute the discount amount (USD).
     *
     * Uses Admin GraphQL codeDiscountNodeByCode so only live Shopify discounts apply.
     *
     * @return array{
     *   valid: bool,
     *   discount_usd: float,
     *   code: ?string,
     *   type: ?string,
     *   value: float|null,
     *   message: ?string,
     *   error: ?string,
     *   free_shipping?: bool
     * }
     */
    public function validateDiscountCode(string $code, float $subtotalUsd): array
    {
        $code = strtoupper(trim($code));
        $subtotalUsd = round(max(0, $subtotalUsd), 2);

        if ($code === '') {
            return $this->discountResult(false, 0, null, null, null, null, 'empty');
        }

        if ($subtotalUsd <= 0) {
            return $this->discountResult(false, 0, $code, null, null, __('checkout.invalid_discount'), 'empty_cart');
        }

        $cacheKey = 'shopify_discount_code_' . md5($code);
        $ttl = (int) config('shopify.discount_cache_ttl', 300);
        $rule = Cache::get($cacheKey);

        if (! is_array($rule) || empty($rule['code'])) {
            $lookup = $this->fetchDiscountCodeFromShopify($code);
            if (! ($lookup['success'] ?? false) || empty($lookup['rule'])) {
                return $this->discountResult(
                    false,
                    0,
                    $code,
                    null,
                    null,
                    __('checkout.invalid_discount'),
                    $lookup['error'] ?? 'not_found'
                );
            }
            $rule = $lookup['rule'];
            Cache::put($cacheKey, $rule, now()->addSeconds($ttl));
        }

        if (! ($rule['active'] ?? false)) {
            return $this->discountResult(false, 0, $code, null, null, __('checkout.discount_inactive'), 'inactive');
        }

        $startsAt = $rule['starts_at'] ?? null;
        if (is_string($startsAt) && $startsAt !== '' && strtotime($startsAt) > time()) {
            return $this->discountResult(false, 0, $code, null, null, __('checkout.discount_inactive'), 'not_started');
        }

        $endsAt = $rule['ends_at'] ?? null;
        if (is_string($endsAt) && $endsAt !== '' && strtotime($endsAt) < time()) {
            return $this->discountResult(false, 0, $code, null, null, __('checkout.discount_expired'), 'expired');
        }

        $usageLimit = $rule['usage_limit'] ?? null;
        $usageCount = (int) ($rule['usage_count'] ?? 0);
        if ($usageLimit !== null && $usageCount >= (int) $usageLimit) {
            return $this->discountResult(false, 0, $code, null, null, __('checkout.discount_usage_limit'), 'usage_limit');
        }

        $minimum = $rule['minimum_subtotal'] ?? null;
        if ($minimum !== null && $subtotalUsd < (float) $minimum) {
            return $this->discountResult(
                false,
                0,
                $code,
                $rule['type'] ?? null,
                $rule['value'] ?? null,
                __('checkout.discount_minimum', ['amount' => number_format((float) $minimum, 2)]),
                'minimum'
            );
        }

        $type = (string) ($rule['type'] ?? '');
        $value = (float) ($rule['value'] ?? 0);
        $discountUsd = 0.0;
        $freeShipping = false;

        if ($type === 'percentage') {
            $discountUsd = round($subtotalUsd * ($value / 100), 2);
        } elseif ($type === 'fixed_amount') {
            $discountUsd = round(min($value, $subtotalUsd), 2);
        } elseif ($type === 'free_shipping') {
            $freeShipping = true;
            $discountUsd = 0.0;
        } else {
            return $this->discountResult(false, 0, $code, null, null, __('checkout.invalid_discount'), 'unsupported');
        }

        $discountUsd = min($discountUsd, $subtotalUsd);

        if (! $freeShipping && $discountUsd <= 0) {
            return $this->discountResult(false, 0, $code, $type, $value, __('checkout.invalid_discount'), 'zero');
        }

        $result = $this->discountResult(
            true,
            $discountUsd,
            (string) ($rule['code'] ?? $code),
            $type,
            $value,
            __('checkout.discount_applied'),
            null
        );
        $result['free_shipping'] = $freeShipping;

        return $result;
    }

    /**
     * @return array{success: bool, rule: ?array<string, mixed>, error: ?string}
     */
    protected function fetchDiscountCodeFromShopify(string $code): array
    {
        $simpleQuery = <<<'GQL'
query DiscountByCode($code: String!) {
  codeDiscountNodeByCode(code: $code) {
    codeDiscount {
      __typename
      ... on DiscountCodeBasic {
        title
        status
        startsAt
        endsAt
        usageLimit
        asyncUsageCount
        customerGets {
          value {
            ... on DiscountPercentage { percentage }
            ... on DiscountAmount { amount { amount currencyCode } appliesOnEachItem }
          }
        }
        minimumRequirement {
          ... on DiscountMinimumSubtotal {
            greaterThanOrEqualToSubtotal { amount currencyCode }
          }
        }
        codes(first: 1) {
          nodes { code }
        }
      }
      ... on DiscountCodeFreeShipping {
        title
        status
        startsAt
        endsAt
        usageLimit
        asyncUsageCount
        minimumRequirement {
          ... on DiscountMinimumSubtotal {
            greaterThanOrEqualToSubtotal { amount currencyCode }
          }
        }
        codes(first: 1) {
          nodes { code }
        }
      }
    }
  }
}
GQL;

        $response = $this->graphql($simpleQuery, ['code' => $code]);

        if (! ($response['success'] ?? false)) {
            // Fallback to legacy REST price rules lookup for older Shopify setups.
            $rest = $this->fetchDiscountCodeFromRest($code);
            if ($rest['success'] ?? false) {
                return $rest;
            }

            Log::warning('Shopify discount GraphQL lookup failed', [
                'code'  => $code,
                'error' => $response['error'] ?? 'unknown',
            ]);

            return ['success' => false, 'rule' => null, 'error' => $response['error'] ?? 'lookup_failed'];
        }

        $discount = $response['data']['codeDiscountNodeByCode']['codeDiscount'] ?? null;
        if (! is_array($discount)) {
            $rest = $this->fetchDiscountCodeFromRest($code);
            if ($rest['success'] ?? false) {
                return $rest;
            }

            return ['success' => false, 'rule' => null, 'error' => 'not_found'];
        }

        $typename = (string) ($discount['__typename'] ?? '');
        $resolvedCode = strtoupper((string) ($discount['codes']['nodes'][0]['code'] ?? $code));
        $status = strtoupper((string) ($discount['status'] ?? ''));
        $active = $status === 'ACTIVE';

        $type = null;
        $value = null;

        if ($typename === 'DiscountCodeBasic') {
            $pct = $discount['customerGets']['value']['percentage'] ?? null;
            $amt = $discount['customerGets']['value']['amount']['amount'] ?? null;
            if ($pct !== null) {
                $type = 'percentage';
                // Shopify GraphQL returns 0–1 (e.g. 0.15 = 15%).
                $pctFloat = (float) $pct;
                $value = $pctFloat <= 1
                    ? round($pctFloat * 100, 4)
                    : round($pctFloat, 4);
            } elseif ($amt !== null) {
                $type = 'fixed_amount';
                $value = round((float) $amt, 2);
            }
        } elseif ($typename === 'DiscountCodeFreeShipping') {
            $type = 'free_shipping';
            $value = 0.0;
        }

        if ($type === null) {
            return ['success' => false, 'rule' => null, 'error' => 'unsupported'];
        }

        $minimum = null;
        $minRaw = $discount['minimumRequirement']['greaterThanOrEqualToSubtotal']['amount'] ?? null;
        if ($minRaw !== null) {
            $minimum = round((float) $minRaw, 2);
        }

        return [
            'success' => true,
            'rule' => [
                'code'              => $resolvedCode,
                'type'              => $type,
                'value'             => $value,
                'active'            => $active,
                'starts_at'         => $discount['startsAt'] ?? null,
                'ends_at'           => $discount['endsAt'] ?? null,
                'usage_limit'       => $discount['usageLimit'] ?? null,
                'usage_count'       => (int) ($discount['asyncUsageCount'] ?? 0),
                'minimum_subtotal'  => $minimum,
                'typename'          => $typename,
            ],
            'error' => null,
        ];
    }

    /**
     * Legacy REST fallback: /discount_codes/lookup.json + price_rules.
     *
     * @return array{success: bool, rule: ?array<string, mixed>, error: ?string}
     */
    protected function fetchDiscountCodeFromRest(string $code): array
    {
        $token = $this->resolveEffectiveAccessToken();
        if ($token === '') {
            return ['success' => false, 'rule' => null, 'error' => 'no_token'];
        }

        try {
            $lookup = $this->httpClient($this->headersForToken($token))
                ->get($this->apiBase . '/discount_codes/lookup.json', ['code' => $code]);

            if (! $lookup->successful()) {
                return ['success' => false, 'rule' => null, 'error' => 'not_found'];
            }

            $discountCode = $lookup->json('discount_code');
            if (! is_array($discountCode) || empty($discountCode['price_rule_id'])) {
                return ['success' => false, 'rule' => null, 'error' => 'not_found'];
            }

            $priceRuleId = $discountCode['price_rule_id'];
            $ruleRes = $this->httpClient($this->headersForToken($token))
                ->get($this->apiBase . '/price_rules/' . $priceRuleId . '.json');

            if (! $ruleRes->successful()) {
                return ['success' => false, 'rule' => null, 'error' => 'price_rule_missing'];
            }

            $priceRule = $ruleRes->json('price_rule');
            if (! is_array($priceRule)) {
                return ['success' => false, 'rule' => null, 'error' => 'price_rule_missing'];
            }

            $valueType = (string) ($priceRule['value_type'] ?? '');
            $rawValue = abs((float) ($priceRule['value'] ?? 0));
            $targetType = (string) ($priceRule['target_type'] ?? 'line_item');

            if ($targetType === 'shipping_line') {
                $type = 'free_shipping';
                $value = 0.0;
            } elseif ($valueType === 'percentage') {
                $type = 'percentage';
                $value = $rawValue;
            } elseif ($valueType === 'fixed_amount') {
                $type = 'fixed_amount';
                $value = $rawValue;
            } else {
                return ['success' => false, 'rule' => null, 'error' => 'unsupported'];
            }

            $minimum = null;
            if (isset($priceRule['prerequisite_subtotal_range']['greater_than_or_equal_to'])) {
                $minimum = round((float) $priceRule['prerequisite_subtotal_range']['greater_than_or_equal_to'], 2);
            }

            $startsAt = $priceRule['starts_at'] ?? null;
            $endsAt = $priceRule['ends_at'] ?? null;
            $now = time();
            $active = true;
            if (is_string($startsAt) && $startsAt !== '' && strtotime($startsAt) > $now) {
                $active = false;
            }
            if (is_string($endsAt) && $endsAt !== '' && strtotime($endsAt) < $now) {
                $active = false;
            }

            return [
                'success' => true,
                'rule' => [
                    'code'             => strtoupper((string) ($discountCode['code'] ?? $code)),
                    'type'             => $type,
                    'value'            => $value,
                    'active'           => $active,
                    'starts_at'        => $startsAt,
                    'ends_at'          => $endsAt,
                    'usage_limit'      => $priceRule['usage_limit'] ?? null,
                    'usage_count'      => (int) ($discountCode['usage_count'] ?? 0),
                    'minimum_subtotal' => $minimum,
                    'typename'         => 'PriceRule',
                ],
                'error' => null,
            ];
        } catch (\Throwable $e) {
            Log::warning('Shopify discount REST lookup failed', [
                'code'  => $code,
                'error' => $e->getMessage(),
            ]);

            return ['success' => false, 'rule' => null, 'error' => $e->getMessage()];
        }
    }

    /**
     * @return array{
     *   valid: bool,
     *   discount_usd: float,
     *   code: ?string,
     *   type: ?string,
     *   value: float|null,
     *   message: ?string,
     *   error: ?string,
     *   free_shipping: bool
     * }
     */
    protected function discountResult(
        bool $valid,
        float $discountUsd,
        ?string $code,
        ?string $type,
        ?float $value,
        ?string $message,
        ?string $error,
    ): array {
        return [
            'valid'          => $valid,
            'discount_usd'   => round(max(0, $discountUsd), 2),
            'code'           => $code,
            'type'           => $type,
            'value'          => $value,
            'message'        => $message,
            'error'          => $error,
            'free_shipping'  => false,
        ];
    }

    /**
     * Create an order in Shopify Admin (Orders page).
     *
     * @return array{success: bool, order: ?array, error: ?string}
     */
    public function createOrder(array $data): array
    {
        $token = $this->resolveEffectiveAccessToken();
        if ($token === '') {
            $message = 'Shopify Admin API token unavailable. Set SHOPIFY_ADMIN_ACCESS_TOKEN or valid CLIENT_ID/SECRET.';
            Log::warning('Shopify createOrder skipped — no Admin API token', [
                'ref' => $data['order_number'] ?? '',
            ]);

            return ['success' => false, 'order' => null, 'error' => $message];
        }

        $country = strtoupper((string) ($data['shipping_country'] ?? 'US'));
        $phone   = $this->sanitizePhoneForShopify($data['customer_phone'] ?? null, $country);

        $address = [
            'first_name' => $data['customer_first_name'],
            'last_name'  => $data['customer_last_name'],
            'address1'   => $data['shipping_address1'],
            'address2'   => $data['shipping_address2'] ?? '',
            'city'       => $data['shipping_city'],
            'province'   => $data['shipping_state'] ?? '',
            'zip'        => $data['shipping_zip'],
            'country'    => $country,
        ];

        if ($phone !== null) {
            $address['phone'] = $phone;
        }

        $noteAttributes = $data['note_attributes'] ?? [
            ['name' => 'dainely_order_number', 'value' => $data['order_number'] ?? ''],
            ['name' => 'square_payment_id', 'value' => $data['square_payment_id'] ?? ''],
        ];

        $items = $data['items'] ?? [];
        $customerId = $this->findOrCreateCustomer([
            'email'      => $data['customer_email'] ?? '',
            'first_name' => $data['customer_first_name'] ?? '',
            'last_name'  => $data['customer_last_name'] ?? '',
            'phone'      => $data['customer_phone'] ?? null,
            'address1'   => $data['shipping_address1'] ?? '',
            'address2'   => $data['shipping_address2'] ?? '',
            'city'       => $data['shipping_city'] ?? '',
            'state'      => $data['shipping_state'] ?? '',
            'zip'        => $data['shipping_zip'] ?? '',
            'country'    => $data['shipping_country'] ?? 'US',
        ]);

        $payload = $this->buildOrderPayload($data, $items, $address, $phone, $noteAttributes, false);

        if ($customerId !== null) {
            $payload['order']['customer'] = ['id' => $customerId];
        }

        $result = $this->submitOrderPayload($token, $payload, $data['order_number'] ?? '');

        if ($result['success']) {
            return $result;
        }

        $errorBody = $result['error'] ?? '';

        if ($this->shopifyErrorMentions($errorBody, 'phone') && $phone !== null) {
            Log::info('Shopify createOrder retrying without phone', ['ref' => $data['order_number'] ?? '']);
            $payload = $this->buildOrderPayload($data, $items, $address, null, $noteAttributes, false);
            $result  = $this->submitOrderPayload($token, $payload, $data['order_number'] ?? '');
            if ($result['success']) {
                return $result;
            }
            $errorBody = $result['error'] ?? $errorBody;
        }

        if ($this->shopifyErrorMentions($errorBody, 'line_items') || $this->shopifyErrorMentions($errorBody, 'variant')) {
            Log::info('Shopify createOrder retrying with custom line items', ['ref' => $data['order_number'] ?? '']);
            $payload = $this->buildOrderPayload($data, $items, $address, null, $noteAttributes, true);
            $result  = $this->submitOrderPayload($token, $payload, $data['order_number'] ?? '');
            if ($result['success']) {
                return $result;
            }
            $errorBody = $result['error'] ?? $errorBody;
        }

        return ['success' => false, 'order' => null, 'error' => $errorBody];
    }

    /**
     * @param  list<array<string, mixed>>  $items
     * @param  array<string, mixed>  $address
     * @param  list<array<string, string>>  $noteAttributes
     * @return array{order: array<string, mixed>}
     */
    protected function buildOrderPayload(
        array $data,
        array $items,
        array $address,
        ?string $phone,
        array $noteAttributes,
        bool $customLineItemsOnly,
    ): array {
        $tags = array_merge(
            config('shopify.order_tags', ['DainelyLab_Order', 'Dainely_Order', 'Square_Checkout', 'Laravel_Order']),
            ['dainely-website', $data['locale'] ?? 'en']
        );

        $payload = [
            'order' => [
                'line_items'               => $customLineItemsOnly
                    ? $this->buildOrderLineItemsCustom($items)
                    : $this->buildOrderLineItems($items),
                'email'                    => $data['customer_email'],
                'financial_status'         => 'paid',
                'fulfillment_status'       => null,
                'inventory_behaviour'      => 'decrement_obeying_policy',
                'tags'                     => implode(',', array_unique($tags)),
                'note'                     => 'Website order ' . ($data['order_number'] ?? '') . ' · Paid via Square · Tax via Shopify',
                'note_attributes'          => $noteAttributes,
                'shipping_address'         => $address,
                'billing_address'          => $address,
                'send_receipt'             => true,
                'send_fulfillment_receipt' => true,
            ],
        ];

        $currency = strtoupper((string) ($data['currency'] ?? config('shopify.shop_currency', 'USD')));
        $shopCurrency = strtoupper((string) config('shopify.shop_currency', 'USD'));
        if ($currency !== $shopCurrency) {
            $payload['order']['currency'] = $currency;
        }

        if ($phone !== null) {
            $payload['order']['phone'] = $phone;
            $payload['order']['shipping_address']['phone'] = $phone;
            $payload['order']['billing_address']['phone'] = $phone;
        }

        $shippingAmount = (float) ($data['shipping'] ?? $data['shipping_usd'] ?? 0);
        if ($shippingAmount > 0) {
            $method = $data['shipping_method'] ?? 'standard';
            $payload['order']['shipping_lines'] = [[
                'title' => $method === 'express' ? 'Express Shipping' : 'Standard Shipping',
                'price' => number_format($shippingAmount, 2, '.', ''),
                'code'  => $method,
            ]];
        }

        $totalAmount = (float) ($data['total'] ?? $data['total_usd'] ?? 0);
        $taxAmount   = (float) ($data['tax'] ?? $data['tax_usd'] ?? 0);

        if ($taxAmount > 0) {
            $payload['order']['total_tax'] = number_format($taxAmount, 2, '.', '');
            $taxLines = $data['tax_lines'] ?? [];
            if ($taxLines !== []) {
                $payload['order']['tax_lines'] = array_values(array_map(function (array $line) {
                    return [
                        'title' => (string) ($line['title'] ?? 'Tax'),
                        'price' => number_format((float) ($line['price'] ?? $line['price_usd'] ?? 0), 2, '.', ''),
                        'rate'  => (float) ($line['rate'] ?? 0),
                    ];
                }, $taxLines));
            }
        }

        if ($totalAmount > 0) {
            $payload['order']['transactions'] = [[
                'kind'          => 'sale',
                'status'        => 'success',
                'amount'        => number_format($totalAmount, 2, '.', ''),
                'gateway'       => 'Square',
                'authorization' => $data['square_payment_id'] ?? '',
            ]];
        }

        $discountAmount = (float) ($data['discount_amount'] ?? $data['discount_usd'] ?? 0);
        if (! empty($data['discount_code']) && $discountAmount > 0) {
            $payload['order']['discount_codes'] = [[
                'code'   => $data['discount_code'],
                'amount' => number_format($discountAmount, 2, '.', ''),
                'type'   => 'fixed_amount',
            ]];
        }

        return $payload;
    }

    /**
     * @return array{success: bool, order: ?array, error: ?string}
     */
    protected function submitOrderPayload(string $token, array $payload, string $orderRef): array
    {
        try {
            $response = $this->httpClient($this->headersForToken($token), 25)
                ->post($this->apiBase . '/orders.json', $payload);

            if ($response->successful()) {
                $order = $response->json()['order'] ?? null;
                if ($order) {
                    Log::info('Shopify createOrder success', [
                        'ref'   => $orderRef,
                        'name'  => $order['name'] ?? null,
                        'id'    => $order['id'] ?? null,
                        'email' => $payload['order']['email'] ?? '',
                    ]);
                }

                return ['success' => true, 'order' => $order, 'error' => null];
            }

            $error = $this->formatShopifyErrorResponse($response->status(), $response->body());
            Log::error('Shopify createOrder failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
                'ref'    => $orderRef,
            ]);

            return ['success' => false, 'order' => null, 'error' => $error];
        } catch (\Exception $e) {
            Log::error('Shopify createOrder exception: ' . $e->getMessage(), ['ref' => $orderRef]);

            return ['success' => false, 'order' => null, 'error' => $e->getMessage()];
        }
    }

    protected function formatShopifyErrorResponse(int $status, string $body): string
    {
        $decoded = json_decode($body, true);
        if (is_array($decoded) && isset($decoded['errors'])) {
            $errors = $decoded['errors'];
            if (is_string($errors)) {
                return "HTTP {$status}: {$errors}";
            }
            if (is_array($errors)) {
                return 'HTTP ' . $status . ': ' . json_encode($errors);
            }
        }

        return trim($body) !== '' ? "HTTP {$status}: {$body}" : "HTTP {$status}: Shopify order request failed.";
    }

    protected function shopifyErrorMentions(string $error, string $needle): bool
    {
        return stripos($error, $needle) !== false;
    }

    /**
     * Shopify rejects malformed phone numbers — normalize to E.164 or omit.
     */
    protected function sanitizePhoneForShopify(?string $phone, string $countryCode = 'US'): ?string
    {
        if ($phone === null) {
            return null;
        }

        $phone = trim($phone);
        if ($phone === '') {
            return null;
        }

        if (preg_match('/^\+\d{10,15}$/', $phone)) {
            return $phone;
        }

        $digits = preg_replace('/\D+/', '', $phone) ?? '';
        $country = strtoupper($countryCode);

        if (in_array($country, ['US', 'CA'], true) && strlen($digits) === 10) {
            return '+1' . $digits;
        }

        if (strlen($digits) === 11 && str_starts_with($digits, '1')) {
            return '+' . $digits;
        }

        if (in_array($country, ['FR', 'DE'], true) && strlen($digits) >= 9 && strlen($digits) <= 12) {
            $prefix = $country === 'FR' ? '+33' : '+49';
            $local  = ltrim($digits, '0');

            return $prefix . $local;
        }

        return null;
    }

    /**
     * @param  list<array<string, mixed>>  $items
     * @return list<array<string, mixed>>
     */
    protected function buildOrderLineItems(array $items): array
    {
        return array_values(array_map(function (array $item) {
            $variantId = $this->normalizeVariantId($item['variant_id'] ?? null);

            if ($variantId !== null) {
                $line = [
                    'variant_id' => $variantId,
                    'quantity'   => (int) ($item['quantity'] ?? 1),
                ];
                $unitPrice = $item['unit_price'] ?? $item['unit_price_usd'] ?? null;
                if ($unitPrice !== null) {
                    $line['price'] = number_format((float) $unitPrice, 2, '.', '');
                }

                return $line;
            }

            return $this->customLineItemPayload($item);
        }, $items));
    }

    /**
     * @param  list<array<string, mixed>>  $items
     * @return list<array<string, mixed>>
     */
    protected function buildOrderLineItemsCustom(array $items): array
    {
        return array_values(array_map(fn (array $item) => $this->customLineItemPayload($item), $items));
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    protected function customLineItemPayload(array $item): array
    {
        $price = (float) ($item['unit_price'] ?? $item['unit_price_usd'] ?? 0);

        return [
            'title'             => (string) ($item['product_name'] ?? 'Product'),
            'quantity'          => (int) ($item['quantity'] ?? 1),
            'price'             => number_format($price, 2, '.', ''),
            'sku'               => (string) ($item['sku'] ?? ''),
            'requires_shipping' => true,
        ];
    }

    /**
     * Customer-facing amounts for Shopify receipts (EUR on /fr /de) while Square settles in USD.
     *
     * @return array{
     *   currency: string,
     *   subtotal: float,
     *   shipping: float,
     *   tax: float,
     *   total: float,
     *   discount: float,
     *   total_usd: float,
     *   exchange_rate: float
     * }
     */
    protected function resolveCustomerPresentation(
        array $checkout,
        float $subtotalUsd,
        float $shippingUsd,
        float $taxUsd,
        float $totalUsd,
        float $discountUsd,
    ): array {
        $chargeCurrency = strtoupper((string) ($checkout['charge_currency'] ?? config('shopify.shop_currency', 'USD')));
        $displayCurrency = strtoupper((string) ($checkout['display_currency'] ?? $chargeCurrency));
        $rate = (float) ($checkout['exchange_rate'] ?? 1);
        $convert = static fn (float $usd): float => $displayCurrency === $chargeCurrency || $rate <= 0
            ? round($usd, 2)
            : round($usd * $rate, 2);

        $hasExplicitDisplay = isset($checkout['display_subtotal'], $checkout['display_total']);

        return [
            'currency'      => $displayCurrency,
            'subtotal'      => $hasExplicitDisplay ? round((float) $checkout['display_subtotal'], 2) : $convert($subtotalUsd),
            'shipping'      => $hasExplicitDisplay
                ? round((float) ($checkout['display_shipping'] ?? $convert($shippingUsd)), 2)
                : $convert($shippingUsd),
            'tax'           => $hasExplicitDisplay
                ? round((float) ($checkout['display_tax'] ?? $convert($taxUsd)), 2)
                : $convert($taxUsd),
            'total'         => $hasExplicitDisplay ? round((float) $checkout['display_total'], 2) : $convert($totalUsd),
            'discount'      => $hasExplicitDisplay
                ? round((float) ($checkout['display_discount'] ?? $convert($discountUsd)), 2)
                : $convert($discountUsd),
            'total_usd'     => $totalUsd,
            'exchange_rate' => $rate > 0 ? $rate : 1.0,
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $taxLines
     * @return list<array{title: string, rate: float, price: float}>
     */
    protected function presentTaxLines(array $taxLines, array $presentation, string $chargeCurrency): array
    {
        if ($taxLines === []) {
            return [];
        }

        $rate = (float) ($presentation['exchange_rate'] ?? 1);
        $useDisplay = ($presentation['currency'] ?? $chargeCurrency) !== $chargeCurrency;

        return array_values(array_map(function (array $line) use ($useDisplay, $rate) {
            $priceUsd = (float) ($line['price_usd'] ?? 0);

            return [
                'title' => (string) ($line['title'] ?? 'Tax'),
                'rate'  => (float) ($line['rate'] ?? 0),
                'price' => $useDisplay ? round($priceUsd * max($rate, 0.0001), 2) : $priceUsd,
            ];
        }, $taxLines));
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
