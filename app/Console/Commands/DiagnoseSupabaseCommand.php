<?php

namespace App\Console\Commands;

use App\Support\SupabaseDb;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DiagnoseSupabaseCommand extends Command
{
    protected $signature = 'supabase:diagnose';

    protected $description = 'Diagnose Supabase (pgsql) connectivity for admin / Phase 2 CMS';

    public function handle(): int
    {
        $rows = [
            ['FEATURES_SUPABASE enabled', SupabaseDb::enabled() ? 'yes' : 'no'],
            ['pdo_pgsql loaded', SupabaseDb::driverLoaded() ? 'yes' : 'NO — enable on host'],
            ['DB_SUPABASE_HOST', (string) config('database.connections.supabase.host')],
            ['DB_SUPABASE_PORT', (string) config('database.connections.supabase.port')],
            ['DB_SUPABASE_USERNAME', (string) config('database.connections.supabase.username')],
            ['DB_SUPABASE_DATABASE', (string) config('database.connections.supabase.database')],
            ['DB_SUPABASE_SSLMODE', (string) config('database.connections.supabase.sslmode')],
            ['DB_SUPABASE_TIMEOUT', (string) env('DB_SUPABASE_TIMEOUT', '15')],
            ['Password set', config('database.connections.supabase.password') ? 'yes' : 'NO'],
        ];

        SupabaseDb::reset();
        $t = microtime(true);
        $ok = SupabaseDb::ping();
        $elapsed = round(microtime(true) - $t, 2);

        $rows[] = ['Ping', $ok ? "OK ({$elapsed}s)" : 'FAILED'];
        $rows[] = ['Reason', $ok ? '—' : SupabaseDb::failureReason()];

        if ($ok) {
            try {
                $count = DB::connection('supabase')->table('dainely_products')->count();
                $rows[] = ['dainely_products count', (string) $count];
            } catch (\Throwable $e) {
                $rows[] = ['dainely_products count', 'error: '.$e->getMessage()];
            }
        }

        $this->table(['Check', 'Result'], $rows);

        if (! $ok) {
            $this->error('Supabase offline — fix the Reason above, then: php artisan config:clear');

            return self::FAILURE;
        }

        $this->info('Supabase OK');

        return self::SUCCESS;
    }
}
