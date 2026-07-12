<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('supabase')->create('recommendation_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('trigger_type')->index(); // cart_has_product, visitor_viewed_product, etc.
            $table->string('trigger_value')->nullable();
            $table->foreignId('recommended_product_id')->constrained('products')->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('supabase')->dropIfExists('recommendation_rules');
    }
};
