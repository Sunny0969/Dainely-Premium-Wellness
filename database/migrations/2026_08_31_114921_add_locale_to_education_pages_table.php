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
            $table->string('locale', 5)->default('en')->after('id');
            $table->dropUnique(['slug']);
            $table->unique(['slug', 'locale']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('education_pages', function (Blueprint $table) {
            $table->dropUnique(['slug', 'locale']);
            $table->unique('slug');
            $table->dropColumn('locale');
        });
    }
};
