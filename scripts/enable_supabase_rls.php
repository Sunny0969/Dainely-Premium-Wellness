<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$tables = [
    'dainely_products', 'product_content', 'faqs', 'landing_pages', 'page_blocks',
    'product_knowledge_signals', 'related_content', 'ai_schema_cache', 'product_bundles',
    'product_bundle_items', 'analytics_events', 'user_activity_log', 'search_index',
    'webhook_logs', 'recommendation_rules', 'page_translations', 'users', 'password_reset_tokens',
    'personal_access_tokens', 'orders', 'order_items',
];

$conn = 'supabase';
$ok = 0;
$skip = 0;
$fail = 0;

foreach ($tables as $table) {
    try {
        if (! Schema::connection($conn)->hasTable($table)) {
            echo "SKIP  {$table} (missing)\n";
            $skip++;
            continue;
        }

        $ident = '"'.str_replace('"', '""', $table).'"';
        DB::connection($conn)->unprepared("ALTER TABLE public.{$ident} ENABLE ROW LEVEL SECURITY");

        try {
            DB::connection($conn)->unprepared("REVOKE ALL ON TABLE public.{$ident} FROM anon");
        } catch (Throwable) {
        }
        try {
            DB::connection($conn)->unprepared("REVOKE ALL ON TABLE public.{$ident} FROM authenticated");
        } catch (Throwable) {
        }

        $row = DB::connection($conn)->selectOne(
            "select c.relrowsecurity as rls
             from pg_class c
             join pg_namespace n on n.oid = c.relnamespace
             where n.nspname = 'public' and c.relname = ?",
            [$table]
        );

        echo 'OK    '.$table.' rls='.(! empty($row?->rls) ? 'on' : 'off')."\n";
        $ok++;
    } catch (Throwable $e) {
        echo 'FAIL  '.$table.' — '.strtok($e->getMessage(), "\n")."\n";
        $fail++;
    }
}

echo "\nDone. ok={$ok} skip={$skip} fail={$fail}\n";
echo "Advisor warnings should clear after Supabase rescans (can take a few minutes).\n";
echo "Laravel continues via postgres/PDO (RLS not FORCEd for table owner).\n";
exit($fail > 0 ? 1 : 0);
