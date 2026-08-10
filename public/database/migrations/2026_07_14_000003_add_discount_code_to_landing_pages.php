<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::connection('supabase')->hasTable('landing_pages')) {
            return;
        }

        Schema::connection('supabase')->table('landing_pages', function (Blueprint $table) {
            if (! Schema::connection('supabase')->hasColumn('landing_pages', 'discount_code')) {
                $table->string('discount_code')->nullable()->after('cta_label');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::connection('supabase')->hasTable('landing_pages')) {
            return;
        }

        if (Schema::connection('supabase')->hasColumn('landing_pages', 'discount_code')) {
            Schema::connection('supabase')->table('landing_pages', function (Blueprint $table) {
                $table->dropColumn('discount_code');
            });
        }
    }
};
