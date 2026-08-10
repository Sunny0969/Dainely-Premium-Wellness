<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\SquareService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SquareWebhookController extends Controller
{
    public function __construct(protected SquareService $square) {}

    public function handle(Request $request)
    {
        $rawBody  = $request->getContent();
        $sigHeader = $request->header('x-square-hmacsha256-signature', '');
        $webhookUrl = route('webhooks.square');

        // Validate HMAC signature
        if (!$this->square->validateWebhookSignature($rawBody, $sigHeader, $webhookUrl)) {
            Log::warning('Square webhook: invalid signature');
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $payload  = $request->json()->all();
        $eventType = $payload['type'] ?? '';

        Log::info('Square webhook received', ['type' => $eventType]);

        $log = null;
        if (\App\Support\SupabaseDb::available()) {
            try {
                $log = \App\Models\Supabase\WebhookLog::create([
                    'source' => 'square',
                    'event_type' => $eventType !== '' ? $eventType : 'square/unknown',
                    'payload' => $payload,
                    'status' => 'pending',
                    'attempts' => 0,
                ]);
            } catch (\Throwable $e) {
                Log::warning('Square webhook: could not persist log', ['error' => $e->getMessage()]);
            }
        }

        try {
            match ($eventType) {
                'payment.updated' => $this->handlePaymentUpdated($payload),
                'refund.created'  => $this->handleRefundCreated($payload),
                default           => Log::info("Square webhook: unhandled event {$eventType}"),
            };
            $log?->markProcessed();
        } catch (\Throwable $e) {
            Log::error('Square webhook processing failed', ['error' => $e->getMessage()]);
            $log?->markFailedWithRetry($e->getMessage());
        }

        return response()->json(['received' => true]);
    }

    protected function handlePaymentUpdated(array $payload): void
    {
        $paymentId = $payload['data']['object']['payment']['id'] ?? null;
        $status    = $payload['data']['object']['payment']['status'] ?? null;

        if (!$paymentId) return;

        $order = Order::where('square_payment_id', $paymentId)->first();
        if (!$order) return;

        if ($status === 'COMPLETED' && $order->status === 'pending') {
            $order->update(['status' => 'paid']);
        } elseif ($status === 'FAILED') {
            $order->update(['status' => 'cancelled']);
        }
    }

    protected function handleRefundCreated(array $payload): void
    {
        $paymentId = $payload['data']['object']['refund']['payment_id'] ?? null;
        if (!$paymentId) return;

        $order = Order::where('square_payment_id', $paymentId)->first();
        $order?->update(['status' => 'refunded']);
    }
}
