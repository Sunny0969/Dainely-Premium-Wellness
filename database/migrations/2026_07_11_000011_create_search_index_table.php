<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('supabase')->create('search_index', function (Blueprint $table) {
            $table->id();
            $table->string('searchable_type')->index();
            $table->unsignedBigInteger('searchable_id')->index();
            $table->string('locale', 5)->index();
            $table->string('title');
            $table->text('content');
            $table->timestamps();

            $table->unique(['searchable_type', 'searchable_id', 'locale']);
        });

        // Add search_vector column and GIN index using raw SQL
        DB::connection('supabase')->statement('ALTER TABLE search_index ADD COLUMN search_vector tsvector');
        DB::connection('supabase')->statement('CREATE INDEX search_index_vector_idx ON search_index USING GIN(search_vector)');
    }

    public function down(): void
    {
        Schema::connection('supabase')->dropIfExists('search_index');
    }
};
