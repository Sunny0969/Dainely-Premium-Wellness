<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('supabase')->create('webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->string('source')->index(); // shopify, square, judge, video-ai, wallpass
            $table->string('event_type')->index();
            $table->json('payload');
            $table->string('status')->default('pending')->index(); // pending, processed, failed
            $table->text('error_message')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('supabase')->dropIfExists('webhook_logs');
    }
};
