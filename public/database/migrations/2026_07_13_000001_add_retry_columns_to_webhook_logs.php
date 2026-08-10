<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'supabase';

    public function up(): void
    {
        Schema::connection('supabase')->table('webhook_logs', function (Blueprint $table) {
            $table->unsignedSmallInteger('attempts')->default(0)->after('error');
            $table->timestamp('next_retry_at')->nullable()->after('attempts');
        });
    }

    public function down(): void
    {
        Schema::connection('supabase')->table('webhook_logs', function (Blueprint $table) {
            $table->dropColumn(['attempts', 'next_retry_at']);
        });
    }
};
