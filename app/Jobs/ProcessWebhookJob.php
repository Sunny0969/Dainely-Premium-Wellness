<?php

namespace App\Jobs;

use App\Models\Supabase\WebhookLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $webhookLogId
    ) {}

    public function handle(): void
    {
        $log = WebhookLog::find($this->webhookLogId);

        if (!$log) {
            Log::error("ProcessWebhookJob: WebhookLog #{$this->webhookLogId} not found.");
            return;
        }

        Log::info("ProcessWebhookJob: Processing webhook #{$log->id}", [
            'source'     => $log->source,
            'event_type' => $log->event_type,
        ]);

        try {
            // Process based on source
            switch ($log->source) {
                case 'judge':
                    $this->processJudgeWebhook($log->payload);
                    break;
                case 'video-ai':
                    $this->processVideoAiWebhook($log->payload);
                    break;
                case 'wallpass':
                    $this->processWallpassWebhook($log->payload);
                    break;
                default:
                    Log::warning("ProcessWebhookJob: Unhandled webhook source '{$log->source}'");
                    break;
            }

            $log->markProcessed();
        } catch (\Throwable $e) {
            Log::error("ProcessWebhookJob: Failed to process webhook #{$log->id}", [
                'error' => $e->getMessage(),
            ]);

            $log->markFailedWithRetry($e->getMessage());
            throw $e;
        }
    }

    protected function processJudgeWebhook(array $payload): void
    {
        Log::info('ProcessWebhookJob: Handling Judge.me webhook', $payload);
        // Custom implementation details for Judge.me reviews syncing can go here
    }

    protected function processVideoAiWebhook(array $payload): void
    {
        Log::info('ProcessWebhookJob: Handling Video AI webhook', $payload);
        // Custom implementation details for Video AI video content sync
    }

    protected function processWallpassWebhook(array $payload): void
    {
        Log::info('ProcessWebhookJob: Handling Wallpass webhook', $payload);
        // Custom implementation details for Wallpass user membership/auth updates
    }
}
