<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::connection('supabase')->hasTable('ai_schema_cache')) {
            return;
        }

        Schema::connection('supabase')->create('ai_schema_cache', function (Blueprint $table) {
            $table->id();
            $table->string('cacheable_type');
            $table->unsignedBigInteger('cacheable_id');
            $table->string('locale', 5);
            $table->json('schema_data');
            $table->string('schema_version')->default('1.0');
            $table->timestamp('generated_at')->useCurrent();
            $table->timestamps();

            $table->unique(['cacheable_type', 'cacheable_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::connection('supabase')->dropIfExists('ai_schema_cache');
    }
};
