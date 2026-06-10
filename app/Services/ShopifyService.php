<?php
namespace App\Services;

use App\Support\ProductSlugResolver;
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

        $cacheKey = 'shopify_product_handle_'.md5($handle);
        $ttl      = (int) config('shopify.product_cache_ttl', 900);

        $cached = Cache::get($cacheKey);
        if (is_array($cached) && ($cached['success'] ?? false) && ! empty($cached['product'])) {
            return $cached;
        }

        $result = $this->fetchProductByHandleLive($handle, $accessToken);

        if ($result['success'] ?? false) {
            Cache::put($cacheKey, $result, $ttl);
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
    public function createOrderFromCheckout(array $checkout): ?array
    {
        if (! config('shopify.sync_orders', true)) {
            Log::info('Shopify order sync disabled', ['ref' => $checkout['order_number'] ?? '']);

            return null;
        }

        $cart     = $checkout['cart'] ?? [];
        $shippingMethod = $checkout['shipping_method'] ?? 'standard';
        
        $items = [];
        $subtotal = 0.0;
        
        $noteAttributes = [
            ['name' => 'dainely_order_number', 'value' => (string) ($checkout['order_number'] ?? '')],
            ['name' => 'square_payment_id', 'value' => (string) ($checkout['square_payment_id'] ?? '')],
            ['name' => 'order_source', 'value' => 'dainely-website'],
            ['name' => 'shipping_method', 'value' => $shippingMethod],
        ];

        // Check if $cart is a sequential array (list of items)
        if (isset($cart[0]) && is_array($cart[0])) {
            foreach ($cart as $item) {
                $itemQty   = (int) ($item['quantity'] ?? 1);
                $itemPrice = (float) ($item['price'] ?? 0);
                $subtotal += round($itemPrice * $itemQty, 2);

                $productName = (string) ($item['title'] ?? 'Dainely Product');
                if (! empty($item['option_value'])) {
                    $productName .= ' — ' . $item['option_value'];
                }

                $variantId = $this->resolveCartVariantId($item);

                $items[] = [
                    'product_name'   => $productName,
                    'quantity'       => $itemQty,
                    'unit_price_usd' => $itemPrice,
                    'sku'            => $item['sku'] ?? '',
                    'variant_id'     => $variantId,
                ];

                if (! empty($item['option_label']) && ! empty($item['option_value'])) {
                    $noteAttributes[] = ['name' => (string) $item['option_label'], 'value' => (string) $item['option_value']];
                }
            }
        } else {
            // Fallback for single item
            $qty      = (int) ($checkout['qty'] ?? ($cart['quantity'] ?? 1));
            $unitPrice = (float) ($cart['price'] ?? 0);
            $subtotal = round($unitPrice * $qty, 2);

            $productName = (string) ($cart['title'] ?? 'Dainely Product');
            if (! empty($cart['option_value'])) {
                $productName .= ' — ' . $cart['option_value'];
            }

            $variantId = $this->resolveCartVariantId($cart);

            $items[] = [
                'product_name'   => $productName,
                'quantity'       => $qty,
                'unit_price_usd' => $unitPrice,
                'sku'            => $cart['sku'] ?? '',
                'variant_id'     => $variantId,
            ];

            if (! empty($cart['option_label']) && ! empty($cart['option_value'])) {
                $noteAttributes[] = ['name' => (string) $cart['option_label'], 'value' => (string) $cart['option_value']];
            }
        }

        $subtotal = round($subtotal, 2);
        $shippingUsd = (float) ($checkout['shipping_usd'] ?? $this->estimateShippingUsd($subtotal, $shippingMethod));
        $totalUsd = round((float) ($checkout['total_usd'] ?? ($subtotal + $shippingUsd)), 2);
        $discountUsd = max(0, round($subtotal + $shippingUsd - $totalUsd, 2));

        return $this->createOrder([
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
            'subtotal_usd'         => $subtotal,
            'shipping_usd'         => $shippingUsd,
            'shipping_method'      => $shippingMethod,
            'total_usd'            => $totalUsd,
            'discount_amount'      => $discountUsd,
            'discount_code'        => $checkout['discount_code'] ?? null,
            'square_payment_id'    => $checkout['square_payment_id'] ?? '',
            'locale'               => $checkout['locale'] ?? 'en',
            'note_attributes'      => $noteAttributes,
            'items'                => $items,
        ]);
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
        if ($productId === '' || $productId === 'dainely-belt') {
            $handle = ProductSlugResolver::resolveHandle('dainely-belt');
        } elseif (ctype_digit($productId)) {
            return null;
        } else {
            $handle = ProductSlugResolver::resolveHandle($productId);
        }

        $result = $this->fetchProductByHandle($handle);
        if (! $result['success'] || empty($result['product']['variants'])) {
            return null;
        }

        $variants = $result['product']['variants'];

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
        if ($subtotal >= 75) {
            return 0.0;
        }

        return $method === 'express' ? 24.99 : 9.99;
    }

    /**
     * Create an order in Shopify Admin (Orders page).
     */
    public function createOrder(array $data): ?array
    {
        $token = $this->resolveEffectiveAccessToken();
        if ($token === '') {
            Log::warning('Shopify createOrder skipped — no Admin API token', [
                'ref' => $data['order_number'] ?? '',
            ]);

            return null;
        }

        $phone = $this->sanitizePhoneForShopify($data['customer_phone'] ?? null);

        $address = [
            'first_name' => $data['customer_first_name'],
            'last_name'  => $data['customer_last_name'],
            'address1'   => $data['shipping_address1'],
            'address2'   => $data['shipping_address2'] ?? '',
            'city'       => $data['shipping_city'],
            'province'   => $data['shipping_state'] ?? '',
            'zip'        => $data['shipping_zip'],
            'country'    => $data['shipping_country'],
        ];

        if ($phone !== null) {
            $address['phone'] = $phone;
        }

        $noteAttributes = $data['note_attributes'] ?? [
            ['name' => 'dainely_order_number', 'value' => $data['order_number'] ?? ''],
            ['name' => 'square_payment_id', 'value' => $data['square_payment_id'] ?? ''],
        ];

        $payload = [
            'order' => [
                'line_items'               => $this->buildOrderLineItems($data['items'] ?? []),
                'email'                    => $data['customer_email'],
                'financial_status'         => 'paid',
                'fulfillment_status'       => null,
                'inventory_behaviour'      => 'decrement_obeying_policy',
                'tags'                     => 'dainely-website,dainely-platform,' . ($data['locale'] ?? 'en'),
                'note'                     => 'Website order ' . ($data['order_number'] ?? '') . ' · Paid via Square',
                'note_attributes'          => $noteAttributes,
                'shipping_address'         => $address,
                'billing_address'          => $address,
                'send_receipt'             => true,
                'send_fulfillment_receipt' => true,
            ],
        ];

        if ($phone !== null) {
            $payload['order']['phone'] = $phone;
        }

        $shippingUsd = (float) ($data['shipping_usd'] ?? 0);
        if ($shippingUsd > 0) {
            $method = $data['shipping_method'] ?? 'standard';
            $payload['order']['shipping_lines'] = [[
                'title' => $method === 'express' ? 'Express Shipping' : 'Standard Shipping',
                'price' => number_format($shippingUsd, 2, '.', ''),
                'code'  => $method,
            ]];
        }

        $totalUsd = (float) ($data['total_usd'] ?? 0);
        if ($totalUsd > 0) {
            $payload['order']['transactions'] = [[
                'kind'          => 'sale',
                'status'        => 'success',
                'amount'        => number_format($totalUsd, 2, '.', ''),
                'gateway'       => 'Square',
                'authorization' => $data['square_payment_id'] ?? '',
            ]];
        }

        if (! empty($data['discount_code']) && (float) ($data['discount_amount'] ?? 0) > 0) {
            $payload['order']['discount_codes'] = [[
                'code'   => $data['discount_code'],
                'amount' => number_format((float) $data['discount_amount'], 2, '.', ''),
                'type'   => 'fixed_amount',
            ]];
        }

        try {
            $response = $this->httpClient($this->headersForToken($token))
                ->post($this->apiBase . '/orders.json', $payload);

            if ($response->successful()) {
                $order = $response->json()['order'] ?? null;
                if ($order) {
                    Log::info('Shopify createOrder success', [
                        'ref'    => $data['order_number'] ?? '',
                        'name'   => $order['name'] ?? null,
                        'email'  => $data['customer_email'] ?? '',
                    ]);
                }

                return $order;
            }

            Log::error('Shopify createOrder failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
                'ref'    => $data['order_number'] ?? '',
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Shopify createOrder exception: ' . $e->getMessage(), [
                'ref' => $data['order_number'] ?? '',
            ]);

            return null;
        }
    }

    /**
     * Shopify rejects malformed phone numbers — omit rather than fail the order.
     */
    protected function sanitizePhoneForShopify(?string $phone): ?string
    {
        if ($phone === null) {
            return null;
        }

        $phone = trim($phone);
        if ($phone === '') {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phone) ?? '';
        if (strlen($digits) < 10 || strlen($digits) > 15) {
            return null;
        }

        return $phone;
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
                return [
                    'variant_id' => $variantId,
                    'quantity'   => (int) ($item['quantity'] ?? 1),
                ];
            }

            $price = (float) ($item['unit_price_usd'] ?? 0);

            return [
                'title'             => (string) ($item['product_name'] ?? 'Product'),
                'quantity'          => (int) ($item['quantity'] ?? 1),
                'price'             => number_format($price, 2, '.', ''),
                'sku'               => (string) ($item['sku'] ?? ''),
                'requires_shipping' => true,
            ];
        }, $items));
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
