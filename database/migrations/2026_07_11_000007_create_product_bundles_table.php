<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('supabase')->create('product_bundles', function (Blueprint $table) {
            $table->id();
            $table->string('shopify_product_id')->unique()->index();
            $table->string('handle')->unique();
            $table->decimal('price_usd', 10, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('supabase')->dropIfExists('product_bundles');
    }
};
