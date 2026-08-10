<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Shared Supabase may already have a client "products" table — skip to avoid conflict
        if (Schema::hasTable('products')) {
            return;
        }

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique();
            $table->string('shopify_product_id')->nullable()->index(); // DMEDE Shopify product ID
            $table->decimal('price_usd', 10, 2); // base price in USD
            $table->decimal('compare_price_usd', 10, 2)->nullable(); // strikethrough price
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->string('type')->default('simple'); // simple, bundle, subscription
            $table->integer('sort_order')->default(0);
            $table->string('main_image')->nullable();
            $table->json('gallery_images')->nullable();
            $table->json('video_urls')->nullable();
            $table->json('meta')->nullable(); // flexible extra data
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
