<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('supabase')->create('page_blocks', function (Blueprint $table) {
            $table->id();
            $table->string('blockable_type')->index();
            $table->unsignedBigInteger('blockable_id')->index();
            $table->string('type'); // hero, benefits, video, testimonials, faqs, cta, comparison, bundle
            $table->json('content'); // JSON payload of block settings
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('supabase')->dropIfExists('page_blocks');
    }
};
