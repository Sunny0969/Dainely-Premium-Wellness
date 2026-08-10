<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::connection('supabase')->hasTable('search_index')) {
            return;
        }

        Schema::connection('supabase')->create('search_index', function (Blueprint $table) {
            $table->id();
            $table->morphs('searchable');
            $table->string('locale', 5);
            $table->string('title');
            $table->text('body_plain');
            $table->text('keywords')->nullable();
            $table->timestamps();

            $table->index('locale');
        });

        DB::connection('supabase')->statement('ALTER TABLE search_index ADD COLUMN tsv tsvector');
        DB::connection('supabase')->statement('CREATE INDEX search_idx ON search_index USING GIN (tsv)');
    }

    public function down(): void
    {
        Schema::connection('supabase')->dropIfExists('search_index');
    }
};
