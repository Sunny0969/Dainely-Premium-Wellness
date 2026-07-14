<?php

namespace App\Jobs;

use App\Models\Supabase\WebhookLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Phase 2 §8 — Retry failed webhook logs with exponential backoff.
 *
 * This job is designed to be scheduled (e.g. every 5 minutes via
 * Laravel Scheduler) and will pick up any webhook_logs that:
 *   - Have status = 'failed'
 *   - Have attempts < MAX_ATTEMPTS
 *   - Have next_retry_at <= now()  (backoff window elapsed)
 *
 * For product-related webhooks it re-dispatches SyncProductJob.
 * For non-product webhooks it re-invokes the ShopifyWebhookController
 * handler methods inline.
 */
class RetryFailedWebhooksJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Maximum number of webhook logs to retry per run.
     */
    protected int $batchSize;

    public function __construct(int $batchSize = 10)
    {
        $this->batchSize = $batchSize;
    }

    public function handle(): void
    {
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

    protected function retryWebhook(WebhookLog $log): void
    {
        $topic   = $log->event_type;
        $payload = $log->payload ?? [];

        Log::info("Retrying webhook #{$log->id}", [
            'topic'    => $topic,
            'attempt'  => ($log->attempts ?? 0) + 1,
        ]);

        try {
            if (str_starts_with($topic, 'products/')) {
                // Re-dispatch product sync job synchronously for retry tracking
                $syncJob = new SyncProductJob($payload, $topic, $log->id);
                $syncJob->handle();
            } else {
                // Re-process non-product topics inline
                $this->handleNonProductTopic($topic, $payload);
                $log->markProcessed();
            }
        } catch (\Throwable $e) {
            Log::warning("Webhook retry failed for #{$log->id}", [
                'topic' => $topic,
                'error' => $e->getMessage(),
            ]);

            $log->markFailedWithRetry($e->getMessage());
        }
    }

    /**
     * Mirror of ShopifyWebhookController::handleNonProductTopics()
     * for inline re-processing.
     */
    protected function handleNonProductTopic(string $topic, array $payload): void
    {
        match ($topic) {
            'orders/fulfilled' => $this->handleOrderFulfilled($payload),
            'orders/cancelled' => $this->handleOrderCancelled($payload),
            'refunds/create'   => $this->handleRefund($payload),
            default            => Log::info("RetryFailedWebhooksJob: Skipping unhandled topic {$topic}"),
        };
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
