<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('code', 3)->unique(); // USD, EUR, GBP
            $table->string('name', 100);
            $table->string('symbol', 10);
            $table->decimal('exchange_rate', 12, 6)->default(1.000000); // rate from USD
            $table->boolean('is_active')->default(true);
            $table->timestamp('rates_updated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
