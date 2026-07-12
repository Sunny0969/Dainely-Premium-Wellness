<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Drop leftover pre-Phase-2 columns so live tables match the spec exactly.
 * Products remain "dainely_products" (client DB already owns "products").
 */
return new class extends Migration
{
    protected string $conn = 'supabase';

    public function up(): void
    {
        $this->dropLegacy('related_content', ['relation_type']);

        $this->dropLegacy('analytics_events', [
            'visitor_id',
            'payload',
            'locale',
            'url',
            'user_agent',
            'ip_address',
            'processed_ga4',
            'processed_meta',
            'ga4_error',
            'meta_error',
        ]);

        // Ensure required Phase 2 columns exist / are non-null friendly
        if (Schema::connection($this->conn)->hasTable('analytics_events')) {
            Schema::connection($this->conn)->table('analytics_events', function (Blueprint $table) {
                if (! Schema::connection($this->conn)->hasColumn('analytics_events', 'event_data')) {
                    $table->json('event_data')->nullable();
                }
                if (! Schema::connection($this->conn)->hasColumn('analytics_events', 'user_id')) {
                    $table->string('user_id')->nullable();
                }
                if (! Schema::connection($this->conn)->hasColumn('analytics_events', 'occurred_at')) {
                    $table->timestamp('occurred_at')->useCurrent();
                }
            });
        }

        $this->dropLegacy('user_activity_log', [
            'session_id',
            'action',
            'details',
            'ip_address',
            'user_agent',
            'updated_at',
        ]);

        $this->dropLegacy('search_index', ['content', 'search_vector']);

        // Drop obsolete translation table if still present
        Schema::connection($this->conn)->dropIfExists('faq_translations');

        // Ensure GIN index exists on search_index.tsv
        if (Schema::connection($this->conn)->hasTable('search_index')
            && Schema::connection($this->conn)->hasColumn('search_index', 'tsv')) {
            DB::connection($this->conn)->statement('CREATE INDEX IF NOT EXISTS search_idx ON search_index USING GIN (tsv)');
        }

        // Drop duplicate GIN on old column name if present
        DB::connection($this->conn)->statement('DROP INDEX IF EXISTS search_index_vector_idx');
    }

    public function down(): void
    {
        // One-way cleanup toward Phase 2.
    }

    /**
     * @param  list<string>  $columns
     */
    protected function dropLegacy(string $table, array $columns): void
    {
        if (! Schema::connection($this->conn)->hasTable($table)) {
            return;
        }

        $existing = array_values(array_filter(
            $columns,
            fn (string $col) => Schema::connection($this->conn)->hasColumn($table, $col)
        ));

        if ($existing === []) {
            return;
        }

        Schema::connection($this->conn)->table($table, function (Blueprint $blueprint) use ($existing) {
            $blueprint->dropColumn($existing);
        });
    }
};
