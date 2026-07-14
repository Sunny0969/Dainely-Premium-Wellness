<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supabase\WebhookLog;
use App\Jobs\RetryFailedWebhooksJob;
use App\Support\SupabaseDb;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class AdminWebhookController extends Controller
{
    public function index()
    {
        if (!SupabaseDb::available()) {
            session()->flash('error', '⚠️ Supabase database connection failed. Webhook logs manager offline.');
        }

        $logs = SupabaseDb::run(
            fn () => WebhookLog::orderBy('created_at', 'desc')->paginate(20),
            new LengthAwarePaginator([], 0, 20)
        );

        return view('admin.webhooks.index', compact('logs'));
    }

    public function retry(int $id)
    {
        if (!SupabaseDb::available()) {
            return back()->with('error', 'Database offline. Cannot retry webhook.');
        }

        return SupabaseDb::run(function () use ($id) {
            $log = WebhookLog::findOrFail($id);
            $log->update([
                'status'        => 'failed',
                'attempts'      => 0,
                'next_retry_at' => now(),
                'error'         => null,
            ]);

            try {
                $job = new RetryFailedWebhooksJob(100);
                $job->handle();

                $log->refresh();
                if ($log->status === 'processed') {
                    return back()->with('success', "Webhook #{$id} retried and processed successfully!");
                }
                return back()->with('error', "Webhook #{$id} retried but failed again: " . $log->error);
            } catch (\Throwable $e) {
                return back()->with('error', "Failed executing webhook retry: " . $e->getMessage());
            }
        }, back()->with('error', 'Database operation failed.'));
    }
}
