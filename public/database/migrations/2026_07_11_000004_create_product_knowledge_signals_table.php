<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::connection('supabase')->hasTable('product_knowledge_signals')) {
            return;
        }

        Schema::connection('supabase')->create('product_knowledge_signals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('dainely_products')->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('speaker_type')->default('expert');
            $table->text('question');
            $table->text('answer');
            $table->json('keywords')->nullable();
            $table->string('source')->nullable();
            $table->float('confidence')->default(1.0);
            $table->boolean('approved')->default(false);
            $table->string('embedding_id')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::connection('supabase')->dropIfExists('product_knowledge_signals');
    }
};
