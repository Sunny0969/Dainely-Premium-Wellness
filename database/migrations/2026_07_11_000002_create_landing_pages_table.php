<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::connection('supabase')->hasTable('landing_pages')) {
            return;
        }

        Schema::connection('supabase')->create('landing_pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug');
            $table->string('locale', 5);
            $table->string('title');
            $table->text('meta_description')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('canonical_url')->nullable();
            $table->boolean('published')->default(false);
            $table->timestamps();

            $table->unique(['slug', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::connection('supabase')->dropIfExists('landing_pages');
    }
};
