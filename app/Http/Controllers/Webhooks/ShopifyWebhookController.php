<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Jobs\SyncProductJob;
use App\Models\Order;
use App\Models\Supabase\WebhookLog;
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
                $log->update([
                    'status'       => 'processed',
                    'processed_at' => now(),
                ]);
            } catch (\Throwable $e) {
                Log::error('Shopify webhook processing failed', [
                    'topic' => $topic,
                    'error' => $e->getMessage(),
                ]);
                $log->update([
                    'status' => 'failed',
                    'error'  => $e->getMessage(),
                ]);
            }
        }

        return response()->json(['status' => 'accepted'], 200);
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
