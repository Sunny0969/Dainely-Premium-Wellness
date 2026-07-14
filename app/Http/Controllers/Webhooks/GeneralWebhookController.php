<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\Supabase\WebhookLog;
use App\Jobs\ProcessWebhookJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GeneralWebhookController extends Controller
{
    /**
     * Handle Judge.me webhook requests.
     */
    public function judge(Request $request)
    {
        return $this->logAndQueueWebhook($request, 'judge', 'reviews/update');
    }

    /**
     * Handle Video AI webhook requests.
     */
    public function videoAi(Request $request)
    {
        return $this->logAndQueueWebhook($request, 'video-ai', 'videos/processed');
    }

    /**
     * Handle Wallpass webhook requests.
     */
    public function wallpass(Request $request)
    {
        return $this->logAndQueueWebhook($request, 'wallpass', 'user/access');
    }

    /**
     * Log the incoming request and dispatch the queue processor job.
     */
    protected function logAndQueueWebhook(Request $request, string $source, string $eventType)
    {
        $payload = $request->all();

        Log::info("GeneralWebhookController: Received {$source} webhook", [
            'event' => $eventType,
        ]);

        $log = WebhookLog::create([
            'source'     => $source,
            'event_type' => $eventType,
            'payload'    => $payload,
            'status'     => 'pending',
            'attempts'   => 0,
        ]);

        ProcessWebhookJob::dispatch($log->id);

        return response()->json([
            'status' => 'accepted',
            'log_id' => $log->id,
        ], 200);
    }
}
