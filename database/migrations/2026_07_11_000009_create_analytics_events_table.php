<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('supabase')->create('analytics_events', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->nullable()->index();
            $table->string('visitor_id')->nullable()->index();
            $table->string('event_name'); // product_view, add_to_cart, begin_checkout, purchase, etc.
            $table->json('payload')->nullable();
            $table->string('locale', 5)->index();
            $table->text('url')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('ip_address')->nullable();
            $table->boolean('processed_ga4')->default(false);
            $table->boolean('processed_meta')->default(false);
            $table->text('ga4_error')->nullable();
            $table->text('meta_error')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('supabase')->dropIfExists('analytics_events');
    }
};
