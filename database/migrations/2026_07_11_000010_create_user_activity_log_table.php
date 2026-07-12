<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::connection('supabase')->hasTable('user_activity_log')) {
            return;
        }

        Schema::connection('supabase')->create('user_activity_log', function (Blueprint $table) {
            $table->id();
            $table->string('visitor_id');
            $table->string('user_id')->nullable();
            $table->string('event_type');
            $table->morphs('item');
            $table->json('context')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::connection('supabase')->dropIfExists('user_activity_log');
    }
};
