<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('supabase')->create('user_activity_log', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->index();
            $table->string('visitor_id')->nullable()->index();
            $table->string('action'); // page_view, click, search, scroll, etc.
            $table->json('details')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('supabase')->dropIfExists('user_activity_log');
    }
};
