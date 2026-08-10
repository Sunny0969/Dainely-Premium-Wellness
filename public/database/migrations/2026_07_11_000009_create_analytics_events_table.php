<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::connection('supabase')->hasTable('analytics_events')) {
            return;
        }

        Schema::connection('supabase')->create('analytics_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_name');
            $table->json('event_data');
            $table->string('session_id')->nullable();
            $table->string('user_id')->nullable();
            $table->timestamp('occurred_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('supabase')->dropIfExists('analytics_events');
    }
};
