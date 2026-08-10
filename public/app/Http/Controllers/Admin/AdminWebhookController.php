<?php

namespace App\Http\Controllers\Admin;

use App\Jobs\RetryFailedWebhooksJob;
use App\Models\Supabase\WebhookLog;
use App\Support\SupabaseDb;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class AdminWebhookController extends AdminController
{
    public function index(Request $request)
    {
        $this->flashIfSupabaseOffline('Webhook logs manager');

        $status = (string) $request->query('status', '');
        $source = (string) $request->query('source', '');
        $q = trim((string) $request->query('q', ''));

        $logs = SupabaseDb::run(function () use ($status, $source, $q) {
            $query = WebhookLog::query()->orderByDesc('created_at');

            if ($status !== '' && in_array($status, ['pending', 'processed', 'failed', 'dead'], true)) {
                $query->where('status', $status);
            }

            if ($source !== '') {
                $query->where('source', $source);
            }

            if ($q !== '') {
                $query->where(function ($inner) use ($q) {
                    $inner->where('event_type', 'ilike', '%'.$q.'%')
                        ->orWhere('error', 'ilike', '%'.$q.'%');
                    if (ctype_digit($q)) {
                        $inner->orWhere('id', (int) $q);
                    }
                });
            }

            return $query->paginate(20)->withQueryString();
        }, new LengthAwarePaginator([], 0, 20));

        $stats = SupabaseDb::run(function () {
            $row = DB::connection('supabase')->selectOne("
                SELECT
                    COUNT(*)::int AS total,
                    COUNT(*) FILTER (WHERE status = 'pending')::int AS pending,
                    COUNT(*) FILTER (WHERE status = 'processed')::int AS processed,
                    COUNT(*) FILTER (WHERE status = 'failed')::int AS failed,
                    COUNT(*) FILTER (WHERE status = 'dead')::int AS dead
                FROM webhook_logs
            ");

            return [
                'total' => (int) ($row->total ?? 0),
                'pending' => (int) ($row->pending ?? 0),
                'processed' => (int) ($row->processed ?? 0),
                'failed' => (int) ($row->failed ?? 0),
                'dead' => (int) ($row->dead ?? 0),
            ];
        }, [
            'total' => 0,
            'pending' => 0,
            'processed' => 0,
            'failed' => 0,
            'dead' => 0,
        ]);

        $sources = SupabaseDb::run(
            fn () => WebhookLog::query()->select('source')->distinct()->orderBy('source')->pluck('source'),
            collect()
        );

        return view('admin.webhooks.index', compact('logs', 'stats', 'sources', 'status', 'source', 'q'));
    }

    public function retry(int $id)
    {
        if (! SupabaseDb::available()) {
            return back()->with('error', 'Database offline. Cannot retry webhook.');
        }

        return SupabaseDb::run(function () use ($id) {
            $log = WebhookLog::findOrFail($id);

            if (! in_array($log->status, ['failed', 'dead', 'pending'], true)) {
                return back()->with('error', "Webhook #{$id} is already {$log->status}.");
            }

            $job = new RetryFailedWebhooksJob(1);
            $job->retryOne($log);

            $log->refresh();

            if ($log->status === 'processed') {
                return back()->with('success', "Webhook #{$id} processed successfully. Catalog/order state updated.");
            }

            return back()->with(
                'error',
                "Webhook #{$id} still {$log->status}".($log->error ? ': '.$log->error : '.')
            );
        }, fn () => back()->with('error', 'Database operation failed.'));
    }

    /**
     * Process stuck pending webhooks (e.g. queue worker was down).
     */
    public function processPending()
    {
        if (! SupabaseDb::available()) {
            return back()->with('error', 'Database offline.');
        }

        return SupabaseDb::run(function () {
            $pending = WebhookLog::query()
                ->where('status', 'pending')
                ->where('created_at', '<=', now()->subMinutes(1))
                ->orderBy('created_at')
                ->limit(25)
                ->get();

            if ($pending->isEmpty()) {
                return back()->with('success', 'No stuck pending webhooks to process.');
            }

            $job = new RetryFailedWebhooksJob(25);
            $ok = 0;
            $fail = 0;

            foreach ($pending as $log) {
                $job->retryOne($log);
                $log->refresh();
                if ($log->status === 'processed') {
                    $ok++;
                } else {
                    $fail++;
                }
            }

            return back()->with(
                'success',
                "Processed stuck pending webhooks: {$ok} ok, {$fail} still failing."
            );
        }, fn () => back()->with('error', 'Could not process pending webhooks.'));
    }
}
