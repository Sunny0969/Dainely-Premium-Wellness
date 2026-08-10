<?php

namespace App\Database;

use Illuminate\Database\Connectors\PostgresConnector as BasePostgresConnector;

/**
 * Adds libpq connect_timeout to the DSN so remote Supabase/pooler
 * fails fast instead of hanging until PHP max_execution_time (30s).
 *
 * PDO::ATTR_TIMEOUT alone is unreliable for pgsql connect on Windows.
 */
class PostgresConnector extends BasePostgresConnector
{
    /**
     * @param  array<string, mixed>  $config
     */
    protected function getDsn(array $config): string
    {
        $dsn = parent::getDsn($config);

        $seconds = (int) ($config['connect_timeout'] ?? $config['timeout'] ?? 5);
        $seconds = max(1, min(20, $seconds));

        if (! str_contains($dsn, 'connect_timeout=')) {
            $dsn .= ";connect_timeout={$seconds}";
        }

        return $dsn;
    }
}
