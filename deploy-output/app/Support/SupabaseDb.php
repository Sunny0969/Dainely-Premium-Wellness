<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Guards Phase 2 Supabase (pgsql) usage on hosts without pdo_pgsql.
 */
class SupabaseDb
{
    protected static ?bool $available = null;

    public static function enabled(): bool
    {
        return filter_var(config('supabase.enabled', true), FILTER_VALIDATE_BOOLEAN);
    }

    public static function driverLoaded(): bool
    {
        return extension_loaded('pdo_pgsql');
    }

    public static function available(): bool
    {
        if (static::$available !== null) {
            return static::$available;
        }

        if (! static::enabled()) {
            return static::$available = false;
        }

        if (! static::driverLoaded()) {
            return static::$available = false;
        }

        return static::$available = true;
    }

    /**
     * Run a Supabase-backed callback, or return $fallback on missing driver / query errors.
     *
     * @template T
     * @param  callable():T  $callback
     * @param  T  $fallback
     * @return T
     */
    public static function run(callable $callback, mixed $fallback = null): mixed
    {
        if (! static::available()) {
            return $fallback;
        }

        try {
            return $callback();
        } catch (Throwable $e) {
            // Missing driver / DNS / auth — never take down the storefront.
            if (static::isConnectionFailure($e)) {
                static::$available = false;
                Log::warning('Supabase unavailable; continuing without CMS overlay', [
                    'error' => $e->getMessage(),
                ]);

                return $fallback;
            }

            throw $e;
        }
    }

    public static function isConnectionFailure(Throwable $e): bool
    {
        $message = strtolower($e->getMessage());

        return str_contains($message, 'could not find driver')
            || str_contains($message, 'pdo_pgsql')
            || str_contains($message, 'pgsql')
            || str_contains($message, 'connection refused')
            || str_contains($message, 'timeout')
            || str_contains($message, 'ssl connection');
    }

    /** @internal testing */
    public static function reset(): void
    {
        static::$available = null;
    }
}
