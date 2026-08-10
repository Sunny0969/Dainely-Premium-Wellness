<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // about, contact, privacy, faq, etc.
            $table->string('type')->default('static'); // static, education, legal
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('page_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('title');
            $table->string('slug');
            $table->longText('content')->nullable(); // HTML/markdown content
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->timestamps();

            $table->unique(['page_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_translations');
        Schema::dropIfExists('pages');
    }
};
