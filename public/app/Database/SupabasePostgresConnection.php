<?php

namespace App\Database;

use DateTimeInterface;
use Illuminate\Database\PostgresConnection;

/**
 * PostgreSQL-safe bindings for Supabase / pooler connections.
 *
 * Laravel's base Connection casts booleans to 0/1 for PDO. With
 * PDO::ATTR_EMULATE_PREPARES (required for transaction pooler port 6543),
 * that becomes a literal integer in SQL and Postgres rejects:
 *   ERROR: operator does not exist: boolean = integer
 */
class SupabasePostgresConnection extends PostgresConnection
{
    /**
     * @param  array<int|string, mixed>  $bindings
     * @return array<int|string, mixed>
     */
    public function prepareBindings(array $bindings)
    {
        $grammar = $this->getQueryGrammar();

        foreach ($bindings as $key => $value) {
            if ($value instanceof DateTimeInterface) {
                $bindings[$key] = $value->format($grammar->getDateFormat());
            } elseif (is_bool($value)) {
                $bindings[$key] = $value ? 'true' : 'false';
            }
        }

        return $bindings;
    }
}
