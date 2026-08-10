<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Jobs\SyncProductJob;
use App\Models\Order;
use App\Models\Supabase\WebhookLog;
use App\Services\LocalShopifyCatalog;
use App\Support\SupabaseDb;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Phase 2 §4.1 — Shopify webhook listener.
 * HMAC is verified by middleware `webhook.shopify`.
 */
class ShopifyWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $topic = (string) $request->header('X-Shopify-Topic', '');
        $payload = $request->all();

        Log::info('Shopify webhook received', ['topic' => $topic]);

        // Always warm/forget local catalog on product topics (even if Supabase is down).
        $this->warmLocalCatalog($topic, $payload);

        if (! SupabaseDb::available()) {
            Log::warning('Shopify webhook: Supabase unavailable; acknowledging without DB sync', [
                'topic' => $topic,
            ]);

            $this->handleNonProductTopics($topic, $payload);

            return response()->json(['status' => 'accepted', 'supabase' => false], 200);
        }

        $log = WebhookLog::create([
            'source'     => 'shopify',
            'event_type' => $topic,
            'payload'    => $payload,
            'status'     => 'pending',
        ]);

        if (str_starts_with($topic, 'products/')) {
            SyncProductJob::dispatch($payload, $topic, $log->id);
        } else {
            try {
                $this->handleNonProductTopics($topic, $payload);
                $log->markProcessed();
            } catch (\Throwable $e) {
                Log::error('Shopify webhook processing failed', [
                    'topic' => $topic,
                    'error' => $e->getMessage(),
                ]);
                $log->markFailedWithRetry($e->getMessage());
            }
        }

        return response()->json(['status' => 'accepted'], 200);
    }

    /**
     * Update storefront cache immediately from webhook JSON (no Shopify API round-trip).
     *
     * @param  array<string, mixed>  $payload
     */
    protected function warmLocalCatalog(string $topic, array $payload): void
    {
        if (! str_starts_with($topic, 'products/')) {
            return;
        }

        try {
            $catalog = app(LocalShopifyCatalog::class);

            if ($topic === 'products/delete') {
                if (! empty($payload['handle'])) {
                    $catalog->forgetWebhookProduct((string) $payload['handle']);
                }

                return;
            }

            $catalog->rememberWebhookProduct($payload);
        } catch (\Throwable $e) {
            Log::warning('Shopify webhook: local catalog warm failed', [
                'topic' => $topic,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function handleNonProductTopics(string $topic, array $payload): void
    {
        match ($topic) {
            'orders/fulfilled' => $this->handleOrderFulfilled($payload),
            'orders/cancelled' => $this->handleOrderCancelled($payload),
            'refunds/create'   => $this->handleRefund($payload),
            default            => Log::info("Shopify webhook: unhandled topic {$topic}"),
        };
    }

    protected function handleOrderFulfilled(array $payload): void
    {
        $shopifyId = (string) ($payload['id'] ?? '');
        if (! $shopifyId) {
            return;
        }

        Order::where('shopify_order_id', $shopifyId)->first()?->update(['status' => 'fulfilled']);
    }

    protected function handleOrderCancelled(array $payload): void
    {
        $shopifyId = (string) ($payload['id'] ?? '');
        if (! $shopifyId) {
            return;
        }

        Order::where('shopify_order_id', $shopifyId)->first()?->update(['status' => 'cancelled']);
    }

    protected function handleRefund(array $payload): void
    {
        $shopifyOrderId = (string) ($payload['order_id'] ?? '');
        if (! $shopifyOrderId) {
            return;
        }

        Order::where('shopify_order_id', $shopifyOrderId)->first()?->update(['status' => 'refunded']);
    }
}
