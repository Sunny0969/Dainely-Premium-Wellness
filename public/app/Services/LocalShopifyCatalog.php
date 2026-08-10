<?php

namespace App\Services;

use App\Models\Supabase\Product;
use App\Support\ProductVisibility;
use App\Support\StorefrontCache;
use App\Support\SupabaseDb;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Webhook-warmed local Shopify catalog.
 * Storefront reads from cache/DB; live Shopify API is fallback / checkout-fresh only.
 */
class LocalShopifyCatalog
{
    public const WEBHOOK_PAYLOAD_PREFIX = 'shopify_webhook_product_';

    /**
     * Persist full Shopify product JSON from webhook and rebuild browse cache.
     *
     * @param  array<string, mixed>  $payload
     */
    public function rememberWebhookProduct(array $payload): void
    {
        $product = $this->normalizeProduct($payload);
        $handle = trim((string) ($product['handle'] ?? ''));
        if ($handle === '') {
            return;
        }

        Cache::put($this->payloadKey($handle), $product, now()->addDays(45));

        if (ProductVisibility::isHandleHidden($handle)) {
            Cache::forget('shopify_product_handle_'.md5($handle));
            $this->rebuildCatalogCache();

            return;
        }

        $this->putHandleCache($handle, $product);
        $this->rebuildCatalogCache();

        Log::info('LocalShopifyCatalog warmed from webhook', ['handle' => $handle]);
    }

    public function forgetWebhookProduct(?string $handle): void
    {
        $handle = trim((string) $handle);
        if ($handle === '') {
            return;
        }

        Cache::forget($this->payloadKey($handle));
        Cache::forget('shopify_product_handle_'.md5($handle));
        $this->rebuildCatalogCache();
    }

    /**
     * Seed durable payloads + catalog after a live Shopify fetch (cold start).
     *
     * @param  array<int, array<string, mixed>>  $products
     */
    public function seedFromLiveCatalog(array $products): void
    {
        $normalized = [];

        foreach ($products as $product) {
            if (! is_array($product)) {
                continue;
            }

            $item = $this->normalizeProduct($product);
            $handle = trim((string) ($item['handle'] ?? ''));
            if ($handle === '' || ProductVisibility::isHandleHidden($handle)) {
                continue;
            }

            Cache::put($this->payloadKey($handle), $item, now()->addDays(45));
            $this->putHandleCache($handle, $item);
            $normalized[] = $item;
        }

        if ($normalized === []) {
            return;
        }

        Cache::put(ShopifyService::CATALOG_CACHE_KEY, [
            'success'  => true,
            'products' => $normalized,
            'error'    => null,
            'source'   => 'live_seed',
        ], StorefrontCache::shopifyTtlSeconds());

        Cache::forget('header_shopify_products_v2');
        Cache::forget('featured_shopify_product_v1');
    }

    /**
     * @return array{success: bool, products: array, error: ?string, source: string}|null
     */
    public function catalogPayload(): ?array
    {
        $cached = Cache::get(ShopifyService::CATALOG_CACHE_KEY);
        if (is_array($cached) && ($cached['success'] ?? false) === true && ! empty($cached['products'])) {
            return $cached;
        }

        return $this->rebuildCatalogCache();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function productByHandle(string $handle): ?array
    {
        $handle = trim($handle);
        if ($handle === '' || ProductVisibility::isHandleHidden($handle)) {
            return null;
        }

        $fromWebhook = Cache::get($this->payloadKey($handle));
        if (is_array($fromWebhook) && ! empty($fromWebhook['handle'])) {
            return $fromWebhook;
        }

        $catalog = Cache::get(ShopifyService::CATALOG_CACHE_KEY);
        if (is_array($catalog) && ! empty($catalog['products'])) {
            foreach ($catalog['products'] as $product) {
                if (! is_array($product)) {
                    continue;
                }
                if (strcasecmp((string) ($product['handle'] ?? ''), $handle) === 0) {
                    return $product;
                }
            }
        }

        return $this->productFromDatabase($handle);
    }

    /**
     * Rebuild shared catalog cache from Supabase rows + durable webhook payloads.
     *
     * @return array{success: bool, products: array, error: ?string, source: string}|null
     */
    public function rebuildCatalogCache(): ?array
    {
        $products = [];

        if (SupabaseDb::available()) {
            try {
                $rows = Product::query()
                    ->where('status', '!=', ProductVisibility::STATUS_UNPUBLISHED)
                    ->orderBy('title')
                    ->limit(250)
                    ->get();

                foreach ($rows as $row) {
                    $handle = trim((string) $row->handle);
                    if ($handle === '') {
                        continue;
                    }

                    $fromWebhook = Cache::get($this->payloadKey($handle));
                    $products[] = is_array($fromWebhook)
                        ? $fromWebhook
                        : $this->rowToShopifyShape($row);
                }
            } catch (\Throwable $e) {
                Log::warning('LocalShopifyCatalog rebuild DB failed', ['error' => $e->getMessage()]);
            }
        }

        if ($products === []) {
            return null;
        }

        $payload = [
            'success'  => true,
            'products' => $products,
            'error'    => null,
            'source'   => 'local_webhook_sync',
        ];

        Cache::put(
            ShopifyService::CATALOG_CACHE_KEY,
            $payload,
            StorefrontCache::shopifyTtlSeconds()
        );

        Cache::forget('header_shopify_products_v2');
        Cache::forget('featured_shopify_product_v1');

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function normalizeProduct(array $payload): array
    {
        if (empty($payload['images']) && ! empty($payload['image']) && is_array($payload['image'])) {
            $payload['images'] = [$payload['image']];
        }

        if (empty($payload['variants']) || ! is_array($payload['variants'])) {
            $payload['variants'] = [[
                'id'                  => null,
                'price'               => $payload['price'] ?? '0.00',
                'compare_at_price'    => null,
                'sku'                 => null,
                'inventory_quantity'  => 0,
                'title'               => 'Default Title',
            ]];
        }

        return $payload;
    }

    protected function putHandleCache(string $handle, array $product): void
    {
        Cache::put('shopify_product_handle_'.md5($handle), [
            'success' => true,
            'product' => $product,
            'error'   => null,
            'source'  => 'webhook',
        ], StorefrontCache::shopifyTtlSeconds());
    }

    protected function payloadKey(string $handle): string
    {
        return self::WEBHOOK_PAYLOAD_PREFIX.md5(strtolower(trim($handle)));
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function productFromDatabase(string $handle): ?array
    {
        if (! SupabaseDb::available()) {
            return null;
        }

        try {
            $row = Product::query()
                ->where('handle', $handle)
                ->where('status', '!=', ProductVisibility::STATUS_UNPUBLISHED)
                ->first();

            return $row ? $this->rowToShopifyShape($row) : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Minimal Admin-API-like shape when durable webhook JSON is missing.
     *
     * @return array<string, mixed>
     */
    protected function rowToShopifyShape(Product $row): array
    {
        $variantNumericId = null;
        $variantId = (string) ($row->variant_id ?? '');
        if (preg_match('/ProductVariant\/(\d+)/', $variantId, $m)) {
            $variantNumericId = (int) $m[1];
        } elseif (ctype_digit($variantId)) {
            $variantNumericId = (int) $variantId;
        }

        $shopifyNumericId = null;
        $spid = (string) ($row->shopify_product_id ?? '');
        if (preg_match('/Product\/(\d+)/', $spid, $m)) {
            $shopifyNumericId = (int) $m[1];
        } elseif (ctype_digit($spid)) {
            $shopifyNumericId = (int) $spid;
        }

        $imageSrc = $row->featured_image;
        $images = $imageSrc ? [['src' => $imageSrc]] : [];

        return [
            'id'         => $shopifyNumericId,
            'title'      => (string) $row->title,
            'handle'     => (string) $row->handle,
            'status'     => (string) ($row->status ?: 'active'),
            'body_html'  => '',
            'image'      => $imageSrc ? ['src' => $imageSrc] : null,
            'images'     => $images,
            'variants'   => [[
                'id'                   => $variantNumericId,
                'admin_graphql_api_id' => $variantId !== '' ? $variantId : null,
                'price'                => $row->price !== null ? (string) $row->price : '0.00',
                'compare_at_price'     => $row->compare_at_price !== null ? (string) $row->compare_at_price : null,
                'sku'                  => $row->sku,
                'inventory_quantity'   => (int) ($row->inventory ?? 0),
                'title'                => 'Default Title',
            ]],
            'updated_at' => optional($row->synced_at)?->toIso8601String(),
        ];
    }
}
