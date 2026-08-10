<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::connection('supabase')->hasTable('faqs')) {
            return;
        }

        Schema::connection('supabase')->create('faqs', function (Blueprint $table) {
            $table->id();
            $table->morphs('faqable');
            $table->string('locale', 5);
            $table->text('question');
            $table->text('answer');
            $table->integer('sort_order')->default(0);
            $table->boolean('approved')->default(true);
            $table->timestamps();

            $table->index(['faqable_type', 'faqable_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::connection('supabase')->dropIfExists('faqs');
    }
};
