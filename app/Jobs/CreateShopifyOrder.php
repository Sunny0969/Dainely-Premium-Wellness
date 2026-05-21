<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\ShopifyService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CreateShopifyOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60; // seconds between retries

    public function __construct(protected Order $order) {}

    public function handle(ShopifyService $shopify): void
    {
        // Don't re-submit if already has Shopify order
        if ($this->order->shopify_order_id) {
            Log::info('Shopify order already exists for order: ' . $this->order->order_number);
            return;
        }

        $this->order->load('items');

        $shopifyOrder = $shopify->createOrder([
            'id'                   => $this->order->id,
            'order_number'         => $this->order->order_number,
            'customer_email'       => $this->order->customer_email,
            'customer_first_name'  => $this->order->customer_first_name,
            'customer_last_name'   => $this->order->customer_last_name,
            'customer_phone'       => $this->order->customer_phone,
            'shipping_address1'    => $this->order->shipping_address1,
            'shipping_address2'    => $this->order->shipping_address2,
            'shipping_city'        => $this->order->shipping_city,
            'shipping_state'       => $this->order->shipping_state,
            'shipping_zip'         => $this->order->shipping_zip,
            'shipping_country'     => $this->order->shipping_country,
            'total_usd'            => $this->order->total_usd,
            'square_payment_id'    => $this->order->square_payment_id,
            'locale'               => $this->order->locale,
            'discount_code'        => $this->order->discount_code,
            'items'                => $this->order->items->map(fn($item) => [
                'product_name'    => $item->product_name,
                'sku'             => $item->sku,
                'quantity'        => $item->quantity,
                'unit_price_usd'  => $item->unit_price_usd,
            ])->toArray(),
        ]);

        if ($shopifyOrder) {
            $this->order->update([
                'shopify_order_id'     => (string)$shopifyOrder['id'],
                'shopify_order_number' => $shopifyOrder['order_number'] ?? null,
            ]);
            Log::info('Shopify order created successfully', [
                'dainely_order' => $this->order->order_number,
                'shopify_order' => $shopifyOrder['id'],
            ]);
        } else {
            Log::error('Failed to create Shopify order for: ' . $this->order->order_number);
            $this->fail('Shopify order creation returned null');
        }
    }
}
