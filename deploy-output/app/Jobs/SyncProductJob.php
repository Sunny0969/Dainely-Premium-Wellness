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
use Throwable;

class SyncProductJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected int $webhookLogId,
        protected string $topic,
        protected array $payload
    ) {}

    public function handle(): void
    {
        Log::info('SyncProductJob started processing', [
            'webhook_log_id' => $this->webhookLogId,
            'topic' => $this->topic,
        ]);

        if (! SupabaseDb::available()) {
            Log::warning('SyncProductJob skipped: Supabase DB unavailable');
            return;
        }

        $log = WebhookLog::find($this->webhookLogId);

        try {
            if ($this->topic === 'products/delete') {
                $shopifyProductId = $this->normalizeShopifyId($this->payload['id'] ?? null);
                if ($shopifyProductId) {
                    $product = Product::where('shopify_product_id', $shopifyProductId)
                        ->orWhere('shopify_product_id', (string) ($this->payload['id'] ?? ''))
                        ->first();

                    if ($product) {
                        $this->forgetSchemaCache($product);
                        $product->delete();
                    }

                    Log::info('Product deleted from Supabase', ['shopify_product_id' => $shopifyProductId]);
                }
            } else {
                $rawId = $this->payload['id'] ?? null;
                $shopifyProductId = $this->normalizeShopifyId($rawId);
                if (!$shopifyProductId) {
                    throw new \Exception('Missing product ID in Shopify payload.');
                }

                $firstVariant = $this->payload['variants'][0] ?? [];
                $variantId = $firstVariant['admin_graphql_api_id']
                    ?? (isset($firstVariant['id']) ? 'gid://shopify/ProductVariant/' . $firstVariant['id'] : null);
                $sku = $firstVariant['sku'] ?? null;
                $price = $firstVariant['price'] ?? null;
                $compareAtPrice = $firstVariant['compare_at_price'] ?? null;
                $inventory = $firstVariant['inventory_quantity'] ?? 0;

                $featuredImage = $this->payload['image']['src']
                    ?? $this->payload['images'][0]['src']
                    ?? null;

                $product = Product::updateOrCreate(
                    ['shopify_product_id' => $shopifyProductId],
                    [
                        'variant_id'       => $variantId ? (string) $variantId : null,
                        'sku'              => $sku,
                        'handle'           => $this->payload['handle'] ?? '',
                        'title'            => $this->payload['title'] ?? '',
                        'status'           => $this->payload['status'] ?? 'active',
                        'price'            => $price,
                        'compare_at_price' => $compareAtPrice,
                        'inventory'        => (int) $inventory,
                        'featured_image'   => $featuredImage,
                        'synced_at'        => now(),
                    ]
                );

                // Ensure locale rows exist for multilingual content (CMS fills later)
                foreach (['en', 'fr', 'de'] as $locale) {
                    ProductContent::firstOrCreate(
                        ['product_id' => $product->id, 'locale' => $locale],
                        [
                            'seo_title' => $product->title,
                            'overview'  => isset($this->payload['body_html'])
                                ? strip_tags((string) $this->payload['body_html'])
                                : null,
                        ]
                    );
                }

                $this->forgetSchemaCache($product);

                Log::info('Product upserted to Supabase', [
                    'shopify_product_id' => $shopifyProductId,
                    'title' => $this->payload['title'] ?? '',
                ]);
            }

            if ($log) {
                $log->update([
                    'status' => 'processed',
                    'processed_at' => now(),
                ]);
            }
        } catch (Throwable $e) {
            Log::error('SyncProductJob failed', [
                'webhook_log_id' => $this->webhookLogId,
                'message' => $e->getMessage(),
            ]);

            if ($log) {
                $log->update([
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                ]);
            }

            throw $e;
        }
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

    protected function forgetSchemaCache(Product $product): void
    {
        foreach (['en', 'fr', 'de'] as $locale) {
            Cache::forget("product_{$product->id}_{$locale}");
            Cache::forget("json_ld_product_{$product->id}_{$locale}");
        }

        AiSchemaCache::where('cacheable_type', Product::class)
            ->where('cacheable_id', $product->id)
            ->delete();
    }
}
