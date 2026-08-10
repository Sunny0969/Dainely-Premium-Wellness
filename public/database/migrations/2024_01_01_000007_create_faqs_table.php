<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->string('category')->nullable(); // general, shipping, product, etc.
            $table->string('scope')->default('global'); // global, product, page
            $table->unsignedBigInteger('scope_id')->nullable(); // product_id or page_id
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('faq_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faq_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->text('question');
            $table->text('answer');
            $table->timestamps();

            $table->unique(['faq_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faq_translations');
        Schema::dropIfExists('faqs');
    }
};
