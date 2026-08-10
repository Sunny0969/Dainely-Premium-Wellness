<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessWebhookJob;
use App\Models\Supabase\WebhookLog;
use App\Support\SupabaseDb;
use App\Support\WebhookSignature;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Phase 2 §12 — secure stub endpoints for future integrations.
 */
class GeneralWebhookController extends Controller
{
    public function judge(Request $request): JsonResponse
    {
        return $this->logAndQueueWebhook($request, 'judge', $this->eventType($request, 'review/updated'));
    }

    public function videoAi(Request $request): JsonResponse
    {
        return $this->logAndQueueWebhook($request, 'video-ai', $this->eventType($request, 'videos/processed'));
    }

    public function wallpass(Request $request): JsonResponse
    {
        return $this->logAndQueueWebhook($request, 'wallpass', $this->eventType($request, 'user/access'));
    }

    protected function logAndQueueWebhook(Request $request, string $source, string $eventType): JsonResponse
    {
        $validation = WebhookSignature::validate($request, $source);
        if ($validation !== true) {
            Log::warning("GeneralWebhookController: {$source} signature rejected", [
                'reason' => $validation,
            ]);

            return response()->json(['status' => 'unauthorized', 'error' => $validation], 401);
        }

        $payload = $request->all();
        if ($payload === [] && $request->getContent() !== '') {
            $decoded = json_decode($request->getContent(), true);
            if (is_array($decoded)) {
                $payload = $decoded;
            }
        }

        Log::info("GeneralWebhookController: Received {$source} webhook", [
            'event' => $eventType,
            'bytes' => strlen($request->getContent() ?: ''),
            'signed' => $request->headers->has('X-Webhook-Signature')
                || $request->headers->has('X-Hub-Signature-256')
                || $request->headers->has('X-Judgeme-Hmac-Sha256'),
        ]);

        $logId = null;

        if (SupabaseDb::available()) {
            try {
                $log = WebhookLog::create([
                    'source' => $source,
                    'event_type' => $eventType,
                    'payload' => $payload,
                    'status' => 'pending',
                    'attempts' => 0,
                ]);
                $logId = $log->id;
            } catch (\Throwable $e) {
                Log::error("GeneralWebhookController: failed to persist {$source} webhook", [
                    'error' => $e->getMessage(),
                ]);
            }
        } else {
            Log::warning("GeneralWebhookController: Supabase unavailable — {$source} payload logged only");
        }

        if ($logId) {
            ProcessWebhookJob::dispatch($logId);
        } else {
            ProcessWebhookJob::dispatch(null, $source, $eventType, $payload);
        }

        return response()->json([
            'status' => 'accepted',
            'log_id' => $logId,
        ], 200);
    }

    protected function eventType(Request $request, string $fallback): string
    {
        return (string) (
            $request->input('key')
            ?? $request->input('event')
            ?? $request->input('topic')
            ?? $request->header('X-Webhook-Event')
            ?? $fallback
        );
    }
}
