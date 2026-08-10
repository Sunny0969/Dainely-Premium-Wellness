<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 2 §7.3 — optional parent hierarchy for landing page breadcrumbs.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::connection('supabase')->hasTable('landing_pages')) {
            return;
        }

        if (! Schema::connection('supabase')->hasColumn('landing_pages', 'parent_id')) {
            Schema::connection('supabase')->table('landing_pages', function (Blueprint $table) {
                $table->unsignedBigInteger('parent_id')->nullable()->after('id')->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::connection('supabase')->hasColumn('landing_pages', 'parent_id')) {
            Schema::connection('supabase')->table('landing_pages', function (Blueprint $table) {
                $table->dropColumn('parent_id');
            });
        }
    }
};
