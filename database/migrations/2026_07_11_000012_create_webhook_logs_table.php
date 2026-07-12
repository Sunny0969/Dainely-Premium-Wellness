<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::connection('supabase')->hasTable('webhook_logs')) {
            return;
        }

        Schema::connection('supabase')->create('webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->string('source');
            $table->string('event_type');
            $table->json('payload');
            $table->string('status')->default('pending');
            $table->text('error')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('supabase')->dropIfExists('webhook_logs');
    }
};
