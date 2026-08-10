<?php

namespace App\Jobs;

use App\Models\Supabase\WebhookLog;
use App\Support\SupabaseDb;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Phase 2 §8 — Retry failed webhook logs with exponential backoff.
 */
class RetryFailedWebhooksJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $batchSize;

    public function __construct(int $batchSize = 10)
    {
        $this->batchSize = $batchSize;
    }

    public function handle(): void
    {
        if (! SupabaseDb::available()) {
            Log::warning('RetryFailedWebhooksJob: Supabase unavailable.');

            return;
        }

        $retryable = WebhookLog::retryable()
            ->orderBy('created_at')
            ->limit($this->batchSize)
            ->get();

        if ($retryable->isEmpty()) {
            Log::debug('RetryFailedWebhooksJob: No retryable webhook logs found.');

            return;
        }

        Log::info("RetryFailedWebhooksJob: Retrying {$retryable->count()} webhook(s).");

        foreach ($retryable as $log) {
            $this->retryWebhook($log);
        }
    }

    /**
     * Force-retry one webhook log (admin Retry button).
     */
    public function retryOne(WebhookLog $log): void
    {
        $log->update([
            'status' => 'failed',
            'attempts' => 0,
            'next_retry_at' => now(),
            'error' => null,
            'processed_at' => null,
        ]);

        $log->refresh();
        $this->retryWebhook($log);
    }

    public function retryWebhook(WebhookLog $log): void
    {
        $topic = (string) $log->event_type;
        $payload = is_array($log->payload) ? $log->payload : [];

        Log::info("Retrying webhook #{$log->id}", [
            'source' => $log->source,
            'topic' => $topic,
            'attempt' => ($log->attempts ?? 0) + 1,
        ]);

        try {
            if (in_array($log->source, ['judge', 'video-ai', 'wallpass'], true)) {
                $job = new ProcessWebhookJob($log->id);
                $job->handle(app(\App\Services\ReviewService::class));

                return;
            }

            if ($log->source === 'shopify' && str_starts_with($topic, 'products/')) {
                $syncJob = new SyncProductJob($payload, $topic, $log->id);
                $syncJob->handle();

                return;
            }

            if ($log->source === 'shopify') {
                $this->handleNonProductTopic($topic, $payload);
                $log->markProcessed();

                return;
            }

            if ($log->source === 'square') {
                $this->handleSquareTopic($topic, $payload);
                $log->markProcessed();

                return;
            }

            Log::warning("RetryFailedWebhooksJob: Unhandled source '{$log->source}' for #{$log->id}");
            $log->markFailedWithRetry("Unhandled webhook source: {$log->source}");
        } catch (\Throwable $e) {
            Log::warning("Webhook retry failed for #{$log->id}", [
                'topic' => $topic,
                'error' => $e->getMessage(),
            ]);

            $log->markFailedWithRetry($e->getMessage());
        }
    }

    protected function handleNonProductTopic(string $topic, array $payload): void
    {
        match ($topic) {
            'orders/fulfilled' => $this->handleOrderFulfilled($payload),
            'orders/cancelled' => $this->handleOrderCancelled($payload),
            'refunds/create' => $this->handleRefund($payload),
            default => Log::info("RetryFailedWebhooksJob: Skipping unhandled topic {$topic}"),
        };
    }

    protected function handleSquareTopic(string $topic, array $payload): void
    {
        $paymentId = data_get($payload, 'data.object.payment.id')
            ?? data_get($payload, 'data.object.refund.payment_id');
        $status = data_get($payload, 'data.object.payment.status');

        if (! $paymentId) {
            return;
        }

        $order = \App\Models\Order::where('square_payment_id', $paymentId)->first();
        if (! $order) {
            return;
        }

        if ($topic === 'payment.updated') {
            if ($status === 'COMPLETED' && $order->status === 'pending') {
                $order->update(['status' => 'paid']);
            } elseif ($status === 'FAILED') {
                $order->update(['status' => 'cancelled']);
            }
        }

        if ($topic === 'refund.created') {
            $order->update(['status' => 'refunded']);
        }
    }

    protected function handleOrderFulfilled(array $payload): void
    {
        $shopifyId = (string) ($payload['id'] ?? '');
        if ($shopifyId === '') {
            return;
        }
        \App\Models\Order::where('shopify_order_id', $shopifyId)->first()?->update(['status' => 'fulfilled']);
    }

    protected function handleOrderCancelled(array $payload): void
    {
        $shopifyId = (string) ($payload['id'] ?? '');
        if ($shopifyId === '') {
            return;
        }
        \App\Models\Order::where('shopify_order_id', $shopifyId)->first()?->update(['status' => 'cancelled']);
    }

    protected function handleRefund(array $payload): void
    {
        $shopifyOrderId = (string) ($payload['order_id'] ?? '');
        if ($shopifyOrderId === '') {
            return;
        }
        \App\Models\Order::where('shopify_order_id', $shopifyOrderId)->first()?->update(['status' => 'refunded']);
    }
}
