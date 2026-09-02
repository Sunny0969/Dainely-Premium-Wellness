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
        Schema::create('education_pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            
            // Hero
            $table->string('hero_title')->nullable();
            $table->text('hero_description')->nullable();
            $table->string('hero_image')->nullable();
            $table->string('author_image')->nullable();
            $table->string('author_name')->nullable();
            $table->string('author_role')->nullable();
            $table->string('read_time')->nullable();
            
            // Figures (JSON array of {value, label})
            $table->json('figures')->nullable();
            
            // Root Causes
            $table->string('root_causes_title')->nullable();
            $table->json('root_causes')->nullable();
            
            // Treatments
            $table->string('treatments_title')->nullable();
            $table->text('treatments_description')->nullable();
            $table->json('treatments')->nullable();
            
            // Flexible Content Blocks
            $table->json('content_blocks')->nullable();
            
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('education_pages');
    }
};
