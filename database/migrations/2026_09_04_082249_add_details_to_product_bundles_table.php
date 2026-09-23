<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection('supabase')->table('product_bundles', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('id');
            $table->jsonb('images')->nullable()->after('description');
            $table->decimal('price', 10, 2)->nullable()->after('images');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('supabase')->table('product_bundles', function (Blueprint $table) {
            $table->dropColumn(['slug', 'images', 'price']);
        });
    }
};
