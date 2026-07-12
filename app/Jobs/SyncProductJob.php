<?php

namespace App\Jobs;

use App\Models\Supabase\Product;
use App\Models\Supabase\WebhookLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class SyncProductJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected int $webhookLogId,
        protected string $topic,
        protected array $payload
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('SyncProductJob started processing', [
            'webhook_log_id' => $this->webhookLogId,
            'topic' => $this->topic
        ]);

        $log = WebhookLog::find($this->webhookLogId);

        try {
            if ($this->topic === 'products/delete') {
                $shopifyProductId = (string)($this->payload['id'] ?? '');
                if ($shopifyProductId) {
                    Product::where('shopify_product_id', $shopifyProductId)->delete();
                    Log::info('Product deleted from Supabase', ['shopify_product_id' => $shopifyProductId]);
                }
            } else {
                // products/create or products/update
                $shopifyProductId = (string)($this->payload['id'] ?? '');
                if (!$shopifyProductId) {
                    throw new \Exception('Missing product ID in Shopify payload.');
                }

                $firstVariant = $this->payload['variants'][0] ?? [];
                $variantId = $firstVariant['id'] ?? null;
                $sku = $firstVariant['sku'] ?? null;
                $price = $firstVariant['price'] ?? '0.00';
                $compareAtPrice = $firstVariant['compare_at_price'] ?? null;
                $inventory = $firstVariant['inventory_quantity'] ?? 0;

                $featuredImage = $this->payload['image']['src']
                    ?? $this->payload['images'][0]['src']
                    ?? null;

                Product::updateOrCreate(
                    ['shopify_product_id' => $shopifyProductId],
                    [
                        'variant_id'        => $variantId ? (string)$variantId : null,
                        'sku'               => $sku,
                        'handle'            => $this->payload['handle'] ?? '',
                        'title'             => $this->payload['title'] ?? '',
                        'status'            => $this->payload['status'] ?? 'active',
                        'price'             => $price,
                        'compare_at_price'  => $compareAtPrice,
                        'inventory'         => (int)$inventory,
                        'featured_image'    => $featuredImage,
                        'synced_at'         => now(),
                    ]
                );

                Log::info('Product upserted to Supabase', [
                    'shopify_product_id' => $shopifyProductId,
                    'title' => $this->payload['title'] ?? ''
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
                'message' => $e->getMessage()
            ]);

            if ($log) {
                $log->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                ]);
            }

            throw $e;
        }
    }
}
