<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\ShopifyService;
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

        match ($topic) {
            'orders/fulfilled' => $this->handleOrderFulfilled($payload),
            'orders/cancelled' => $this->handleOrderCancelled($payload),
            'refunds/create'   => $this->handleRefund($payload),
            default            => Log::info("Shopify webhook: unhandled topic {$topic}"),
        };

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
