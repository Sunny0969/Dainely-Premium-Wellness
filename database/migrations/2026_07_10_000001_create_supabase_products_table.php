<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop Phase 1 products table with CASCADE to remove dependent foreign keys
        DB::connection('supabase')->statement('DROP TABLE IF EXISTS products CASCADE');

        Schema::connection('supabase')->create('products', function (Blueprint $table) {
            $table->id();
            $table->string('shopify_product_id')->unique();
            $table->string('variant_id')->nullable();
            $table->string('sku')->nullable();
            $table->string('handle')->unique();
            $table->string('title');
            $table->string('status')->default('active');
            $table->decimal('price', 10, 2)->nullable();
            $table->decimal('compare_at_price', 10, 2)->nullable();
            $table->integer('inventory')->nullable();
            $table->string('featured_image')->nullable();
            $table->timestamp('synced_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('supabase')->dropIfExists('products');
    }
};
