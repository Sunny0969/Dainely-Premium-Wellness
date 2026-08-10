<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::connection('supabase')->hasTable('page_blocks')) {
            return;
        }

        Schema::connection('supabase')->create('page_blocks', function (Blueprint $table) {
            $table->id();
            $table->morphs('blockable');
            $table->string('locale', 5);
            $table->string('block_type');
            $table->string('title')->nullable();
            $table->text('content')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('visible')->default(true);
            $table->timestamps();

            $table->index(['blockable_type', 'blockable_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::connection('supabase')->dropIfExists('page_blocks');
    }
};
