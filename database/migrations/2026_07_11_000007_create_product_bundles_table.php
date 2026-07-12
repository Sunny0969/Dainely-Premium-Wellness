<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::connection('supabase')->hasTable('product_bundles')) {
            return;
        }

        Schema::connection('supabase')->create('product_bundles', function (Blueprint $table) {
            $table->id();
            $table->string('bundle_shopify_product_id');
            $table->string('locale', 5);
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['bundle_shopify_product_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::connection('supabase')->dropIfExists('product_bundles');
    }
};
