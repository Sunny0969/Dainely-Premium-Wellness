<?php

namespace App\Jobs;

use App\Models\Supabase\WebhookLog;
use App\Services\ReviewService;
use App\Support\SupabaseDb;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Phase 2 §12 — generic processor for integration webhooks.
 */
class ProcessWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public ?int $webhookLogId = null,
        public ?string $source = null,
        public ?string $eventType = null,
        public array $payload = [],
    ) {}

    public static function fromPayload(string $source, string $eventType, array $payload): self
    {
        return new self(null, $source, $eventType, $payload);
    }

    public function handle(ReviewService $reviews): void
    {
        $log = null;
        $source = $this->source;
        $payload = $this->payload;

        if ($this->webhookLogId && SupabaseDb::available()) {
            $log = WebhookLog::find($this->webhookLogId);
            if (! $log) {
                Log::error("ProcessWebhookJob: WebhookLog #{$this->webhookLogId} not found.");

                return;
            }
            $source = $log->source;
            $payload = is_array($log->payload) ? $log->payload : [];
        }

        if (! $source) {
            Log::error('ProcessWebhookJob: missing source');

            return;
        }

        Log::info("ProcessWebhookJob: Processing webhook", [
            'log_id' => $this->webhookLogId,
            'source' => $source,
            'event_type' => $log?->event_type ?? $this->eventType,
        ]);

        try {
            match ($source) {
                'judge' => $this->processJudgeWebhook($payload, $reviews),
                'video-ai' => $this->processVideoAiWebhook($payload),
                'wallpass' => $this->processWallpassWebhook($payload),
                default => Log::warning("ProcessWebhookJob: Unhandled webhook source '{$source}'"),
            };

            $log?->markProcessed();
        } catch (\Throwable $e) {
            Log::error('ProcessWebhookJob: Failed to process webhook', [
                'log_id' => $this->webhookLogId,
                'error' => $e->getMessage(),
            ]);

            if ($log) {
                $log->markFailedWithRetry($e->getMessage());
            }

            throw $e;
        }
    }

    protected function processJudgeWebhook(array $payload, ReviewService $reviews): void
    {
        Log::info('ProcessWebhookJob: Handling Judge.me webhook', [
            'keys' => array_keys($payload),
        ]);

        // Bust all product review caches so storefront picks up new reviews.
        foreach (ReviewService::canonicalHandlesForWarmup() as $handle) {
            Cache::forget('judgeme_reviews_'.$handle);
        }
        Cache::forget('judgeme_universal_media_reviews');
        Cache::forget('judgeme_shop_totals');

        // Best-effort warm for the product mentioned in the payload.
        $handle = data_get($payload, 'review.product_handle')
            ?? data_get($payload, 'product_handle')
            ?? data_get($payload, 'review.product.handle');

        if (is_string($handle) && $handle !== '') {
            $reviews->warmCacheForHandle($handle);
        }
    }

    protected function processVideoAiWebhook(array $payload): void
    {
        Log::info('ProcessWebhookJob: Handling Video AI webhook', [
            'keys' => array_keys($payload),
        ]);
    }

    protected function processWallpassWebhook(array $payload): void
    {
        Log::info('ProcessWebhookJob: Handling Wallpass webhook', [
            'keys' => array_keys($payload),
        ]);
    }
}
