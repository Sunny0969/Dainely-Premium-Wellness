<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supabase\LandingPage;
use App\Models\Supabase\Product;
use App\Models\Supabase\ProductBundle;
use App\Support\SupabaseDb;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

abstract class AdminController extends Controller
{
    protected const CATALOG_CACHE_TTL = 300; // 5 minutes

    protected const PING_CACHE_TTL = 600; // 10 minutes

    protected const METRICS_CACHE_TTL = 90; // 90 seconds

    /**
     * Flash a clear offline banner when Supabase is not reachable.
     *
     * Successful pings are cached so Admin pages do not re-probe the remote
     * DB (and pay SSL/TCP latency) on every click.
     */
    protected function flashIfSupabaseOffline(string $managerLabel): bool
    {
        if (! SupabaseDb::enabled()) {
            session()->flash(
                'error',
                '⚠️ Supabase is disabled (FEATURES_SUPABASE=false). '.$managerLabel.' offline.'
            );

            return false;
        }

        if (! SupabaseDb::driverLoaded()) {
            session()->flash(
                'error',
                '⚠️ PHP extension pdo_pgsql is not installed/enabled on this server. '
                .'Ask hosting to enable pgsql for PHP '.PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION.'. '
                .$managerLabel.' offline.'
            );

            return false;
        }

        if (Cache::get('admin.supabase_ping_ok') === true) {
            // Still apply pinned pooler IP so queries don't hit a flaky hostname.
            $pinnedHost = Cache::get('supabase.resolved_host');
            $pinnedPort = Cache::get('supabase.resolved_port');
            if (is_string($pinnedHost) && $pinnedHost !== '' && is_numeric($pinnedPort)) {
                config([
                    'database.connections.supabase.host' => $pinnedHost,
                    'database.connections.supabase.port' => (int) $pinnedPort,
                ]);
            }

            // Stale "online" while tcp_fail is set → empty CMS data. Force retry.
            if (Cache::get('supabase.tcp_fail') === true) {
                Cache::forget('admin.supabase_ping_ok');
            } else {
                return true;
            }
        }

        // After a failed connect, skip probing briefly so every Admin click does
        // not wait on a hung TCP handshake — but allow ?retry_supabase=1 to force.
        if (Cache::get('admin.supabase_ping_fail') === true && ! request()->boolean('retry_supabase')) {
            session()->flash(
                'error',
                '⚠️ Supabase database temporarily unreachable. '.$managerLabel.' offline. '
                .(SupabaseDb::failureReason())
                .' Reload with ?retry_supabase=1 to try again.'
            );

            return false;
        }

        Cache::forget('supabase.tcp_fail');
        Cache::forget('supabase.tcp_ok');
        Cache::forget('admin.supabase_ping_fail');
        $this->forgetAdminCatalogCaches();

        $ok = SupabaseDb::ping();

        if ($ok) {
            Cache::put('admin.supabase_ping_ok', true, now()->addSeconds(self::PING_CACHE_TTL));
            Cache::forget('admin.supabase_ping_fail');
            Cache::forget('supabase.tcp_fail');

            return true;
        }

        Cache::forget('admin.supabase_ping_ok');
        Cache::put('admin.supabase_ping_fail', true, now()->addSeconds(30));

        session()->flash(
            'error',
            '⚠️ Supabase database connection failed. '.$managerLabel.' offline. '.SupabaseDb::failureReason()
        );

        return false;
    }

    /**
     * Lightweight product list for admin dropdowns / indexes (cached).
     *
     * @return Collection<int, Product>
     */
    protected function cachedProductsForSelect(array $columns = ['id', 'title', 'handle', 'shopify_product_id']): Collection
    {
        $key = 'admin.catalog.products.'.md5(implode(',', $columns));
        $cached = Cache::get($key);
        if ($cached instanceof Collection) {
            return $cached;
        }

        $rows = SupabaseDb::run(
            fn () => Product::query()->orderBy('title')->get($columns),
            collect()
        );

        // Never cache empty results — they often mean a failed/offline fetch.
        if (SupabaseDb::available() && $rows->isNotEmpty()) {
            Cache::put($key, $rows, self::CATALOG_CACHE_TTL);
        }

        return $rows;
    }

    /**
     * @return Collection<int, LandingPage>
     */
    protected function cachedLandingsForSelect(array $columns = ['id', 'title', 'slug', 'locale']): Collection
    {
        $key = 'admin.catalog.landings.'.md5(implode(',', $columns));
        $cached = Cache::get($key);
        if ($cached instanceof Collection) {
            return $cached;
        }

        $rows = SupabaseDb::run(
            fn () => LandingPage::query()->orderBy('title')->get($columns),
            collect()
        );

        if (SupabaseDb::available() && $rows->isNotEmpty()) {
            Cache::put($key, $rows, self::CATALOG_CACHE_TTL);
        }

        return $rows;
    }

    /**
     * @return Collection<int, ProductBundle>
     */
    protected function cachedBundlesForSelect(array $columns = ['id', 'title', 'locale']): Collection
    {
        $key = 'admin.catalog.bundles.'.md5(implode(',', $columns));
        $cached = Cache::get($key);
        if ($cached instanceof Collection) {
            return $cached;
        }

        $rows = SupabaseDb::run(
            fn () => ProductBundle::query()->orderBy('title')->get($columns),
            collect()
        );

        if (SupabaseDb::available() && $rows->isNotEmpty()) {
            Cache::put($key, $rows, self::CATALOG_CACHE_TTL);
        }

        return $rows;
    }

    protected function forgetAdminCatalogCaches(): void
    {
        Cache::forget('admin.dashboard.metrics');
        // Forget known select cache keys (common column sets used by Admin).
        foreach ([
            'admin.catalog.products.'.md5(implode(',', ['id', 'title', 'handle', 'shopify_product_id'])),
            'admin.catalog.products.'.md5(implode(',', ['id', 'title', 'handle', 'sku', 'price', 'status', 'featured_image'])),
            'admin.catalog.landings.'.md5(implode(',', ['id', 'title', 'slug', 'locale'])),
            'admin.catalog.bundles.'.md5(implode(',', ['id', 'title', 'locale'])),
        ] as $key) {
            Cache::forget($key);
        }
    }
}
