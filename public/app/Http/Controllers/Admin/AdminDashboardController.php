<?php

namespace App\Http\Controllers\Admin;

use App\Models\Supabase\UserActivityLog;
use App\Support\SupabaseDb;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends AdminController
{
    public function index()
    {
        $online = $this->flashIfSupabaseOffline('CMS Dashboard');

        $metrics = Cache::get('admin.dashboard.metrics');

        // Ignore stale all-zero metrics (cached while Supabase was unreachable).
        if (is_array($metrics) && (int) ($metrics['products_count'] ?? 0) === 0) {
            $metrics = null;
            Cache::forget('admin.dashboard.metrics');
        }

        if (! is_array($metrics)) {
            $fetched = false;
            $metrics = SupabaseDb::run(function () use (&$fetched) {
                // One round-trip for all counts (remote Supabase latency dominates).
                $row = DB::connection('supabase')->selectOne("
                    SELECT
                        (SELECT COUNT(*) FROM dainely_products) AS products_count,
                        (SELECT COUNT(*) FROM landing_pages) AS landings_count,
                        (SELECT COUNT(*) FROM product_bundles) AS bundles_count,
                        (SELECT COUNT(*) FROM webhook_logs WHERE status = 'pending') AS webhooks_pending,
                        (SELECT COUNT(*) FROM webhook_logs WHERE status = 'failed') AS webhooks_failed,
                        (SELECT COUNT(*) FROM webhook_logs WHERE status = 'dead') AS webhooks_dead
                ");

                $fetched = true;

                return [
                    'products_count' => (int) ($row->products_count ?? 0),
                    'landings_count' => (int) ($row->landings_count ?? 0),
                    'bundles_count' => (int) ($row->bundles_count ?? 0),
                    'webhooks_pending' => (int) ($row->webhooks_pending ?? 0),
                    'webhooks_failed' => (int) ($row->webhooks_failed ?? 0),
                    'webhooks_dead' => (int) ($row->webhooks_dead ?? 0),
                    'recent_activities' => UserActivityLog::query()
                        ->orderByDesc('created_at')
                        ->limit(10)
                        ->get(),
                ];
            }, [
                'products_count' => 0,
                'landings_count' => 0,
                'bundles_count' => 0,
                'webhooks_pending' => 0,
                'webhooks_failed' => 0,
                'webhooks_dead' => 0,
                'recent_activities' => collect(),
            ]);

            if ($fetched && $online && SupabaseDb::available()) {
                Cache::put('admin.dashboard.metrics', $metrics, self::METRICS_CACHE_TTL);
            }
        }

        return view('admin.dashboard', compact('metrics'));
    }
}
