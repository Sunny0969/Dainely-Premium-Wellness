<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('supabase')->create('ai_schema_cache', function (Blueprint $table) {
            $table->id();
            $table->string('schemaable_type')->index();
            $table->unsignedBigInteger('schemaable_id')->index();
            $table->string('locale', 5)->index();
            $table->string('schema_type'); // Product, FAQPage, BreadcrumbList, WebPage, etc.
            $table->json('schema_json');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->unique(['schemaable_type', 'schemaable_id', 'locale', 'schema_type'], 'schema_cache_unique');
        });
    }

    public function down(): void
    {
        Schema::connection('supabase')->dropIfExists('ai_schema_cache');
    }
};
