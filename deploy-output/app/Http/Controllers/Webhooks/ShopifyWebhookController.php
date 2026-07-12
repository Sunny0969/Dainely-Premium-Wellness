<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Supabase\WebhookLog;
use App\Jobs\SyncProductJob;
use App\Services\ShopifyService;
use App\Support\SupabaseDb;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ShopifyWebhookController extends Controller
{
    public function __construct(protected ShopifyService $shopify) {}

    public function handle(Request $request)
    {
        $rawBody   = $request->getContent();
        $hmacHeader = $request->header('x-shopify-hmac-sha256', '');

        if (!$this->shopify->validateWebhookSignature($rawBody, $hmacHeader)) {
            Log::warning('Shopify webhook: invalid signature');
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $topic   = $request->header('x-shopify-topic', '');
        $payload = $request->json()->all();

        Log::info('Shopify webhook received', ['topic' => $topic]);

        if (! SupabaseDb::available()) {
            Log::warning('Shopify webhook: Supabase DB unavailable; acknowledging without sync', [
                'topic' => $topic,
                'driver' => extension_loaded('pdo_pgsql') ? 'pdo_pgsql' : 'missing',
            ]);

            // Still handle local MySQL order status updates when possible
            if (! str_starts_with($topic, 'products/')) {
                try {
                    match ($topic) {
                        'orders/fulfilled' => $this->handleOrderFulfilled($payload),
                        'orders/cancelled' => $this->handleOrderCancelled($payload),
                        'refunds/create'   => $this->handleRefund($payload),
                        default            => null,
                    };
                } catch (\Throwable $e) {
                    Log::error('Shopify webhook (no Supabase) processing failed', [
                        'topic' => $topic,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            return response()->json(['received' => true, 'supabase' => false]);
        }

        // 1. Log webhook to database as pending
        $log = WebhookLog::create([
            'source'     => 'shopify',
            'event_type' => $topic,
            'payload'    => $payload,
            'status'     => 'pending',
        ]);

        // 2. Dispatch product sync job or handle other topics
        if (str_starts_with($topic, 'products/')) {
            SyncProductJob::dispatch($log->id, $topic, $payload);
        } else {
            try {
                match ($topic) {
                    'orders/fulfilled' => $this->handleOrderFulfilled($payload),
                    'orders/cancelled' => $this->handleOrderCancelled($payload),
                    'refunds/create'   => $this->handleRefund($payload),
                    default            => Log::info("Shopify webhook: unhandled topic {$topic}"),
                };

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

        return response()->json(['received' => true]);
    }

    protected function handleOrderFulfilled(array $payload): void
    {
        $shopifyId = (string)($payload['id'] ?? '');
        if (!$shopifyId) return;

        $order = Order::where('shopify_order_id', $shopifyId)->first();
        $order?->update(['status' => 'fulfilled']);
    }

    protected function handleOrderCancelled(array $payload): void
    {
        $shopifyId = (string)($payload['id'] ?? '');
        $order     = Order::where('shopify_order_id', $shopifyId)->first();
        $order?->update(['status' => 'cancelled']);
    }

    protected function handleRefund(array $payload): void
    {
        $shopifyOrderId = (string)($payload['order_id'] ?? '');
        $order          = Order::where('shopify_order_id', $shopifyOrderId)->first();
        $order?->update(['status' => 'refunded']);
    }
}

