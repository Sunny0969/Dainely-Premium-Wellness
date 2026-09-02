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
        Schema::table('education_pages', function (Blueprint $table) {
            $table->json('related_products')->nullable()->after('layout_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('education_pages', function (Blueprint $table) {
            $table->dropColumn('related_products');
        });
    }
};
