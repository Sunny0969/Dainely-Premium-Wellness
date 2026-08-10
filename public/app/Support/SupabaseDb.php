<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Guards Phase 2 Supabase (pgsql) usage on hosts without pdo_pgsql.
 */
class SupabaseDb
{
    protected static ?bool $available = null;

    protected static ?string $lastError = null;

    public static function enabled(): bool
    {
        return filter_var(config('supabase.enabled', true), FILTER_VALIDATE_BOOLEAN);
    }

    public static function driverLoaded(): bool
    {
        return extension_loaded('pdo_pgsql');
    }

    public static function lastError(): ?string
    {
        return static::$lastError;
    }

    /**
     * Human-readable reason when Supabase is offline (for admin flash messages).
     */
    public static function failureReason(): string
    {
        if (! static::enabled()) {
            return 'Supabase is disabled (FEATURES_SUPABASE / SUPABASE_DB_ENABLED=false).';
        }

        if (! static::driverLoaded()) {
            return 'PHP extension pdo_pgsql is not installed/enabled on this server. Ask hosting to enable pgsql for PHP '.PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION.'.';
        }

        if (static::$lastError) {
            return static::$lastError;
        }

        return 'Supabase connection failed. Check DB_SUPABASE_* in .env (use pooler host + project username postgres.<project-ref>).';
    }

    public static function available(): bool
    {
        if (static::$available !== null) {
            return static::$available;
        }

        if (! static::enabled()) {
            static::$lastError = 'Supabase disabled in config.';

            return static::$available = false;
        }

        if (! static::driverLoaded()) {
            static::$lastError = 'pdo_pgsql missing.';

            return static::$available = false;
        }

        // Driver present — treat as available until a real query/ping fails.
        return static::$available = true;
    }

    /**
     * Probe the database (SELECT 1). Updates available/lastError.
     * Purges the connection so a prior failed attempt does not stick in the pool.
     */
    public static function ping(): bool
    {
        if (! static::enabled()) {
            static::$available = false;
            static::$lastError = 'Supabase disabled in config.';

            return false;
        }

        if (! static::driverLoaded()) {
            static::$available = false;
            static::$lastError = 'pdo_pgsql missing on this PHP host.';

            return false;
        }

        // Fast TCP probe before PDO — on Windows libpq can ignore connect_timeout
        // and hang until PHP max_execution_time (FatalError).
        if (! static::tcpReachable()) {
            static::$available = false;
            static::$lastError = 'Connection timed out reaching Supabase. Use the pooler host (aws-0-….pooler.supabase.com), check VPN/firewall for outbound TCP 5432/6543, and keep DB_SUPABASE_TIMEOUT low (e.g. 5).';
            Log::warning('Supabase TCP probe failed', [
                'host' => config('database.connections.supabase.host'),
                'port' => config('database.connections.supabase.port'),
            ]);

            return false;
        }

        try {
            DB::connection('supabase')->select('select 1 as ok');
            static::$available = true;
            static::$lastError = null;

            return true;
        } catch (Throwable $e) {
            static::$available = false;
            static::$lastError = static::summarizeError($e);
            Log::warning('Supabase ping failed', ['error' => $e->getMessage()]);

            try {
                DB::purge('supabase');
            } catch (Throwable) {
                // ignore
            }

            return false;
        }
    }

    /**
     * Cheap host:port check with a hard socket timeout (seconds).
     * Resolves DNS and tries each IPv4 — some networks fail on the hostname
     * but succeed on a specific pooler IP.
     */
    protected static function tcpReachable(): bool
    {
        if (\Illuminate\Support\Facades\Cache::get('supabase.tcp_fail') === true) {
            return false;
        }

        // Re-apply a recently working IP/port (config resets every request).
        $pinnedHost = \Illuminate\Support\Facades\Cache::get('supabase.resolved_host');
        $pinnedPort = \Illuminate\Support\Facades\Cache::get('supabase.resolved_port');
        if (is_string($pinnedHost) && $pinnedHost !== '' && is_numeric($pinnedPort)) {
            static::pinConnectionHost($pinnedHost, (int) $pinnedPort);
            if (\Illuminate\Support\Facades\Cache::get('supabase.tcp_ok') === true) {
                return true;
            }
        }

        // Do NOT trust tcp_ok without a pinned host — PDO would hit the flaky
        // hostname and return empty admin data while looking "online".
        if (\Illuminate\Support\Facades\Cache::get('supabase.tcp_ok') === true
            && (! is_string($pinnedHost) || $pinnedHost === '')) {
            \Illuminate\Support\Facades\Cache::forget('supabase.tcp_ok');
        }

        $host = (string) config('database.connections.supabase.host', '');
        $configuredPort = (int) config('database.connections.supabase.port', 6543);
        // Pooler from overseas often needs 3–5s; keep enough headroom per attempt.
        $timeout = max(4, min(8, (int) config('database.connections.supabase.connect_timeout', 15)));

        if ($host === '' || $configuredPort < 1) {
            return false;
        }

        // Prefer transaction pooler (6543); fall back to session (5432).
        $ports = array_values(array_unique([$configuredPort, $configuredPort === 6543 ? 5432 : 6543]));

        $ips = [];
        if (! filter_var($host, FILTER_VALIDATE_IP)) {
            $ips = @gethostbynamel($host) ?: [];
        }

        // Prefer pinned IP, then resolved IPs, then hostname last (hostname often hangs
        // on flaky DNS routes while a specific pooler IP still works).
        $targets = [];
        if (is_string($pinnedHost) && $pinnedHost !== '') {
            $targets[] = $pinnedHost;
        }
        foreach ($ips as $ip) {
            $targets[] = $ip;
        }
        // Only fall back to hostname when DNS gave nothing (or host is already an IP).
        if ($ips === []) {
            $targets[] = $host;
        }
        $targets = array_values(array_unique(array_filter($targets)));

        foreach ($targets as $target) {
            foreach ($ports as $port) {
                $errno = 0;
                $errstr = '';
                $fp = @fsockopen($target, $port, $errno, $errstr, $timeout);
                if (is_resource($fp) || $fp instanceof \Socket) {
                    fclose($fp);
                    static::pinConnectionHost($target, $port);
                    \Illuminate\Support\Facades\Cache::put('supabase.tcp_ok', true, now()->addSeconds(45));
                    \Illuminate\Support\Facades\Cache::put('supabase.resolved_host', $target, now()->addMinutes(10));
                    \Illuminate\Support\Facades\Cache::put('supabase.resolved_port', $port, now()->addMinutes(10));

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Point the supabase PDO connection at a reachable host/port for this request.
     */
    protected static function pinConnectionHost(string $host, int $port): void
    {
        $currentHost = (string) config('database.connections.supabase.host');
        $currentPort = (int) config('database.connections.supabase.port');
        if ($currentHost === $host && $currentPort === $port) {
            return;
        }

        config([
            'database.connections.supabase.host' => $host,
            'database.connections.supabase.port' => $port,
        ]);

        try {
            \Illuminate\Support\Facades\DB::purge('supabase');
        } catch (Throwable) {
            // ignore
        }
    }

    /**
     * Run a Supabase-backed callback, or return $fallback on missing driver / query errors.
     *
     * Pass redirect/back()->with(...) as a Closure so session flashes are NOT set
     * on every successful call (PHP evaluates arguments eagerly).
     *
     * @template T
     * @param  callable():T  $callback
     * @param  T|callable():T  $fallback
     * @return T
     */
    public static function run(callable $callback, mixed $fallback = null): mixed
    {
        $resolveFallback = static function () use ($fallback) {
            return $fallback instanceof \Closure ? $fallback() : $fallback;
        };

        if (! static::available()) {
            return $resolveFallback();
        }

        // Soft gate: do not open PDO when the host is unreachable (short TCP probe).
        if (! static::tcpReachable()) {
            static::$available = false;
            static::$lastError = 'Supabase host unreachable (TCP probe failed).';
            static::rememberOutage();

            return $resolveFallback();
        }

        try {
            return $callback();
        } catch (Throwable $e) {
            // Missing driver / DNS / auth — never take down the storefront.
            if (static::isConnectionFailure($e)) {
                static::$available = false;
                static::$lastError = static::summarizeError($e);
                Log::warning('Supabase unavailable; continuing without CMS overlay', [
                    'error' => $e->getMessage(),
                ]);
                static::rememberOutage();

                try {
                    DB::purge('supabase');
                } catch (Throwable) {
                    // ignore
                }

                return $resolveFallback();
            }

            throw $e;
        }
    }

    /**
     * Mark a short outage window and drop "admin online" cache so CMS retries.
     */
    protected static function rememberOutage(): void
    {
        \Illuminate\Support\Facades\Cache::put('supabase.tcp_fail', true, now()->addSeconds(20));
        \Illuminate\Support\Facades\Cache::forget('supabase.tcp_ok');
        \Illuminate\Support\Facades\Cache::forget('admin.supabase_ping_ok');
        \Illuminate\Support\Facades\Cache::forget('admin.dashboard.metrics');
    }

    public static function isConnectionFailure(Throwable $e): bool
    {
        $message = strtolower($e->getMessage());

        // Do NOT match bare "pgsql" — SQL / schema errors can mention it and are not outages.
        return str_contains($message, 'could not find driver')
            || str_contains($message, 'pdo_pgsql')
            || str_contains($message, 'connection refused')
            || str_contains($message, 'operation timed out')
            || str_contains($message, 'connection timed out')
            || str_contains($message, 'timed out')
            || str_contains($message, 'timeout expired')
            || str_contains($message, 'ssl connection has been closed')
            || str_contains($message, 'password authentication failed')
            || str_contains($message, 'could not connect')
            || str_contains($message, 'no route to host')
            || str_contains($message, 'name or service not known')
            || str_contains($message, 'server closed the connection')
            || str_contains($message, 'remaining connection slots')
            || str_contains($message, 'too many connections')
            || str_contains($message, 'sqlstate[08006]')
            || str_contains($message, 'sqlstate[08001]');
    }

    protected static function summarizeError(Throwable $e): string
    {
        $message = $e->getMessage();
        $lower = strtolower($message);

        if (str_contains($lower, 'timeout') || str_contains($lower, 'timed out')) {
            return 'Connection timed out reaching Supabase. Use the pooler host (aws-0-….pooler.supabase.com), raise DB_SUPABASE_TIMEOUT (e.g. 15), and ensure the host allows outbound TCP 5432/6543.';
        }

        if (str_contains($lower, 'password authentication failed')) {
            return 'Supabase password/username rejected. For the pooler, username must be postgres.<project-ref>.';
        }

        if (str_contains($lower, 'could not find driver') || str_contains($lower, 'pdo_pgsql')) {
            return 'PHP pdo_pgsql driver missing on this server.';
        }

        // Never echo raw credentials; keep message short.
        return 'Supabase DB error: '.strtok($message, "\n");
    }

    /** @internal testing */
    public static function reset(): void
    {
        static::$available = null;
        static::$lastError = null;
    }
}
