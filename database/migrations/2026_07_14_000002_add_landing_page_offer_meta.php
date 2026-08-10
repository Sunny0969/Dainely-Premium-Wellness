<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 2 §8.4 — link landing pages to Shopify product or bundle for CTA checkout.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::connection('supabase')->hasTable('landing_pages')) {
            return;
        }

        Schema::connection('supabase')->table('landing_pages', function (Blueprint $table) {
            if (! Schema::connection('supabase')->hasColumn('landing_pages', 'shopify_product_id')) {
                $table->string('shopify_product_id')->nullable()->after('canonical_url');
            }
            if (! Schema::connection('supabase')->hasColumn('landing_pages', 'bundle_id')) {
                $table->unsignedBigInteger('bundle_id')->nullable()->after('shopify_product_id')->index();
            }
            if (! Schema::connection('supabase')->hasColumn('landing_pages', 'cta_label')) {
                $table->string('cta_label')->nullable()->after('bundle_id');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::connection('supabase')->hasTable('landing_pages')) {
            return;
        }

        Schema::connection('supabase')->table('landing_pages', function (Blueprint $table) {
            foreach (['shopify_product_id', 'bundle_id', 'cta_label'] as $col) {
                if (Schema::connection('supabase')->hasColumn('landing_pages', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
