<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::connection('supabase')->hasTable('related_content')) {
            return;
        }

        Schema::connection('supabase')->create('related_content', function (Blueprint $table) {
            $table->id();
            $table->string('source_type');
            $table->unsignedBigInteger('source_id');
            $table->string('related_type');
            $table->unsignedBigInteger('related_id');
            $table->integer('display_order')->default(0);
            $table->timestamps();

            $table->index(['source_type', 'source_id']);
            $table->index(['related_type', 'related_id']);
        });
    }

    public function down(): void
    {
        Schema::connection('supabase')->dropIfExists('related_content');
    }
};
