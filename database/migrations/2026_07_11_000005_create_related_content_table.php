<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('supabase')->create('related_content', function (Blueprint $table) {
            $table->id();
            $table->string('source_type')->index();
            $table->unsignedBigInteger('source_id')->index();
            $table->string('related_type')->index();
            $table->unsignedBigInteger('related_id')->index();
            $table->string('relation_type')->default('recommendation'); // recommendation, article, cross-sell
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('supabase')->dropIfExists('related_content');
    }
};
