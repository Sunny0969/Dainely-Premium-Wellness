<?php

namespace App\Http\Controllers;

use App\Support\SupabaseDb;
use Illuminate\Http\JsonResponse;

class HealthController extends Controller
{
    /**
     * Ops health — no secrets. Used to verify pdo_pgsql after hosting changes.
     */
    public function supabase(): JsonResponse
    {
        SupabaseDb::reset();
        $driver = SupabaseDb::driverLoaded();
        $ok = $driver && SupabaseDb::ping();

        return response()->json([
            'php_version' => PHP_VERSION,
            'sapi' => PHP_SAPI,
            'pdo_pgsql' => $driver,
            'pgsql' => extension_loaded('pgsql'),
            'supabase_enabled' => SupabaseDb::enabled(),
            'supabase_ok' => $ok,
            'offline_reason' => $ok ? null : SupabaseDb::failureReason(),
        ]);
    }
}
