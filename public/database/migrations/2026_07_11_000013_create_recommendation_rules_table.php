<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::connection('supabase')->hasTable('recommendation_rules')) {
            return;
        }

        Schema::connection('supabase')->create('recommendation_rules', function (Blueprint $table) {
            $table->id();
            $table->string('rule_type');
            $table->morphs('source_item');
            $table->morphs('recommended_item');
            $table->float('score')->default(1.0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('supabase')->dropIfExists('recommendation_rules');
    }
};
