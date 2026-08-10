<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Make Phase 2 (+ co-located) tables private to the Supabase API.
 *
 * Clears Advisor: rls_disabled_in_public / sensitive_columns_exposed
 * Laravel PDO (postgres / table owner) keeps working — we do NOT FORCE RLS.
 */
return new class extends Migration
{
    protected string $conn = 'supabase';

    /** @var list<string> */
    protected array $tables = [
        'dainely_products',
        'product_content',
        'faqs',
        'landing_pages',
        'page_blocks',
        'product_knowledge_signals',
        'related_content',
        'ai_schema_cache',
        'product_bundles',
        'product_bundle_items',
        'analytics_events',
        'user_activity_log',
        'search_index',
        'webhook_logs',
        'recommendation_rules',
        'users',
        'password_reset_tokens',
        'personal_access_tokens',
        'failed_jobs',
        'jobs',
        'job_batches',
        'cache',
        'cache_locks',
        'sessions',
        'languages',
        'currencies',
        'products',
        'product_translations',
        'pages',
        'blog_categories',
        'blog_posts',
        'blog_post_translations',
        'faq_translations',
        'orders',
        'order_items',
        'discount_codes',
        'testimonials',
    ];

    public function up(): void
    {
        if (! extension_loaded('pdo_pgsql')) {
            return;
        }

        foreach ($this->tables as $table) {
            try {
                if (! Schema::connection($this->conn)->hasTable($table)) {
                    continue;
                }

                $ident = $this->quoteIdent($table);

                DB::connection($this->conn)->unprepared(
                    "ALTER TABLE public.{$ident} ENABLE ROW LEVEL SECURITY"
                );

                DB::connection($this->conn)->unprepared(
                    "REVOKE ALL ON TABLE public.{$ident} FROM PUBLIC"
                );

                // PostgREST roles (ignore if role missing on non-Supabase hosts)
                foreach (['anon', 'authenticated'] as $role) {
                    try {
                        DB::connection($this->conn)->unprepared(
                            "REVOKE ALL ON TABLE public.{$ident} FROM {$role}"
                        );
                    } catch (\Throwable $e) {
                        Log::debug("RLS migrate: revoke {$role} on {$table}: ".$e->getMessage());
                    }
                }
            } catch (\Throwable $e) {
                Log::warning("RLS migrate failed for {$table}: ".$e->getMessage());
            }
        }
    }

    public function down(): void
    {
        if (! extension_loaded('pdo_pgsql')) {
            return;
        }

        foreach ($this->tables as $table) {
            try {
                if (! Schema::connection($this->conn)->hasTable($table)) {
                    continue;
                }
                $ident = $this->quoteIdent($table);
                DB::connection($this->conn)->unprepared(
                    "ALTER TABLE public.{$ident} DISABLE ROW LEVEL SECURITY"
                );
            } catch (\Throwable $e) {
                Log::warning("RLS migrate down failed for {$table}: ".$e->getMessage());
            }
        }
    }

    protected function quoteIdent(string $ident): string
    {
        return '"'.str_replace('"', '""', $ident).'"';
    }
};
