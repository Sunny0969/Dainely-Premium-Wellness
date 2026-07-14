<?php

namespace App\Jobs;

use App\Models\Supabase\AiSchemaCache;
use App\Models\Supabase\Product;
use App\Models\Supabase\ProductContent;
use App\Models\Supabase\WebhookLog;
use App\Support\SupabaseDb;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/**
 * Phase 2 §4.2 — Upsert Shopify product into Supabase + seed locale content.
 */
class SyncProductJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public array $payload,
        public string $topic = 'products/update',
        public ?int $webhookLogId = null,
    ) {}

    public function handle(): void
    {
        Log::info('SyncProductJob started', [
            'topic' => $this->topic,
            'webhook_log_id' => $this->webhookLogId,
            'shopify_id' => $this->payload['id'] ?? null,
        ]);

        if (! SupabaseDb::available()) {
            $this->failLog('Supabase DB unavailable (pdo_pgsql / FEATURES_SUPABASE)');
            return;
        }

        $log = $this->webhookLogId ? WebhookLog::find($this->webhookLogId) : null;

        try {
            if ($this->topic === 'products/delete') {
                $this->deleteProduct();
            } else {
                $this->upsertProduct();
            }

            $log?->markProcessed();
        } catch (Throwable $e) {
            Log::error('SyncProductJob failed', [
                'topic' => $this->topic,
                'message' => $e->getMessage(),
            ]);

            if ($log) {
                $log->markFailedWithRetry($e->getMessage());
            }

            throw $e;
        }
    }

    protected function upsertProduct(): void
    {
        $p = $this->payload;
        $shopifyProductId = $this->normalizeShopifyId($p['id'] ?? null);

        if (! $shopifyProductId) {
            throw new \InvalidArgumentException('Missing product ID in Shopify payload.');
        }

        $variant = $p['variants'][0] ?? [];
        $handle = trim((string) ($p['handle'] ?? ''));
        if ($handle === '') {
            $handle = Str::slug((string) ($p['title'] ?? '')) ?: ('product-' . preg_replace('/\D+/', '', (string) ($p['id'] ?? '0')));
        }

        $variantId = $variant['admin_graphql_api_id']
            ?? (isset($variant['id']) ? 'gid://shopify/ProductVariant/' . $variant['id'] : null);

        $featuredImage = $p['images'][0]['src']
            ?? $p['image']['src']
            ?? null;

        // Match by Shopify GID, raw id, or existing handle (bulk sync vs smoke-test rows)
        $rawId = (string) ($p['id'] ?? '');
        $product = Product::query()
            ->where(function ($q) use ($shopifyProductId, $rawId) {
                $q->where('shopify_product_id', $shopifyProductId);
                if ($rawId !== '' && $rawId !== $shopifyProductId) {
                    $q->orWhere('shopify_product_id', $rawId);
                }
            })
            ->first();

        if (! $product && $handle !== '') {
            $product = Product::query()->where('handle', $handle)->first();
        }

        $attributes = [
            'shopify_product_id' => $shopifyProductId,
            'title'              => (string) ($p['title'] ?? ''),
            'handle'             => $handle,
            'status'             => (string) ($p['status'] ?? 'active'),
            'price'              => $variant['price'] ?? null,
            'compare_at_price'   => $variant['compare_at_price'] ?? null,
            'inventory'          => (int) ($variant['inventory_quantity'] ?? 0),
            'featured_image'     => $featuredImage,
            'variant_id'         => $variantId ? (string) $variantId : null,
            'sku'                => $variant['sku'] ?? null,
            'synced_at'          => now(),
        ];

        if ($product) {
            $product->update($attributes);
        } else {
            $product = Product::create($attributes);
        }

        foreach (['en', 'fr', 'de'] as $locale) {
            ProductContent::firstOrCreate(
                ['product_id' => $product->id, 'locale' => $locale],
                [
                    'seo_title' => $product->title,
                    'overview'  => isset($p['body_html'])
                        ? strip_tags((string) $p['body_html'])
                        : null,
                ]
            );
        }

        $this->invalidateCaches($product);

        Log::info('Product upserted to Supabase', [
            'id' => $product->id,
            'shopify_product_id' => $shopifyProductId,
            'handle' => $product->handle,
        ]);
    }

    protected function deleteProduct(): void
    {
        $shopifyProductId = $this->normalizeShopifyId($this->payload['id'] ?? null);
        $rawId = (string) ($this->payload['id'] ?? '');

        $product = null;
        if ($shopifyProductId || $rawId !== '') {
            $product = Product::query()
                ->where(function ($q) use ($shopifyProductId, $rawId) {
                    if ($shopifyProductId) {
                        $q->where('shopify_product_id', $shopifyProductId);
                    }
                    if ($rawId !== '' && $rawId !== $shopifyProductId) {
                        $q->orWhere('shopify_product_id', $rawId);
                    }
                })
                ->first();
        }

        if ($product) {
            $this->invalidateCaches($product);
            $product->delete();
            Log::info('Product deleted from Supabase', [
                'shopify_product_id' => $shopifyProductId,
            ]);
        }
    }

    protected function invalidateCaches(Product $product): void
    {
        foreach (['en', 'fr', 'de'] as $locale) {
            Cache::forget("product_{$product->id}_{$locale}");
            Cache::forget('product_tmp_' . md5((string) $product->handle) . "_{$locale}");
            Cache::forget("json_ld_product_{$product->id}_{$locale}");
        }

        // Docs use cacheable_type 'product'; we also store FQCN
        AiSchemaCache::query()
            ->where('cacheable_id', $product->id)
            ->where(function ($q) {
                $q->where('cacheable_type', 'product')
                    ->orWhere('cacheable_type', Product::class);
            })
            ->delete();
    }

    protected function normalizeShopifyId(mixed $id): ?string
    {
        if ($id === null || $id === '') {
            return null;
        }

        $id = (string) $id;
        if (str_starts_with($id, 'gid://')) {
            return $id;
        }

        return 'gid://shopify/Product/' . $id;
    }

    protected function failLog(string $message): void
    {
        Log::warning('SyncProductJob skipped', ['reason' => $message]);

        if ($this->webhookLogId) {
            WebhookLog::where('id', $this->webhookLogId)->update([
                'status' => 'failed',
                'error'  => $message,
            ]);
        }
    }
}
