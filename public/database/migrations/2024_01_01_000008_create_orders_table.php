<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->string('status')->default('pending'); // pending, paid, fulfilled, cancelled, refunded
            $table->string('locale', 5)->default('en');
            $table->string('currency', 3)->default('USD');
            $table->decimal('exchange_rate', 12, 6)->default(1.000000);
            $table->decimal('subtotal_usd', 10, 2)->default(0);
            $table->decimal('discount_amount_usd', 10, 2)->default(0);
            $table->decimal('shipping_usd', 10, 2)->default(0);
            $table->decimal('tax_usd', 10, 2)->default(0);
            $table->decimal('total_usd', 10, 2)->default(0);
            $table->string('customer_email');
            $table->string('customer_first_name');
            $table->string('customer_last_name');
            $table->string('customer_phone')->nullable();
            $table->string('shipping_address1');
            $table->string('shipping_address2')->nullable();
            $table->string('shipping_city');
            $table->string('shipping_state')->nullable();
            $table->string('shipping_zip');
            $table->string('shipping_country', 2);
            $table->string('square_payment_id')->nullable()->index();
            $table->string('shopify_order_id')->nullable()->index();
            $table->string('shopify_order_number')->nullable();
            $table->string('discount_code')->nullable();
            $table->boolean('gdpr_consent')->default(false);
            $table->timestamp('gdpr_consented_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('product_name');
            $table->string('sku')->nullable();
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price_usd', 10, 2);
            $table->decimal('total_price_usd', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
