<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop existing tables on supabase connection to avoid name conflicts
        DB::connection('supabase')->statement('DROP TABLE IF EXISTS faq_translations CASCADE');
        DB::connection('supabase')->statement('DROP TABLE IF EXISTS faqs CASCADE');

        Schema::connection('supabase')->create('faqs', function (Blueprint $table) {
            $table->id();
            $table->string('faqable_type')->nullable()->index();
            $table->unsignedBigInteger('faqable_id')->nullable()->index();
            $table->string('category')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::connection('supabase')->create('faq_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faq_id')->constrained('faqs')->cascadeOnDelete();
            $table->string('locale', 5)->index();
            $table->text('question');
            $table->text('answer');
            $table->timestamps();

            $table->unique(['faq_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::connection('supabase')->dropIfExists('faq_translations');
        Schema::connection('supabase')->dropIfExists('faqs');
    }
};
