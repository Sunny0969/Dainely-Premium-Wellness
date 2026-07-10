<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class OrderPersistenceService
{
    /**
     * Persist a paid order locally (DB when available, JSON file fallback).
     *
     * @param  array<string, mixed>  $payload
     */
    public function savePaidOrder(string $orderRef, array $payload): void
    {
        if ($this->ordersTableAvailable()) {
            try {
                $this->saveToDatabase($orderRef, $payload);

                return;
            } catch (\Throwable $e) {
                Log::error('Order DB save failed — using file fallback', [
                    'ref'   => $orderRef,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->saveToFile($orderRef, $payload);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function loadPendingSyncPayload(string $orderRef): ?array
    {
        if ($this->ordersTableAvailable()) {
            try {
                $order = Order::where('order_number', $orderRef)->first();
                if ($order && is_array($order->meta['shopify_sync']['payload'] ?? null)) {
                    return $order->meta['shopify_sync']['payload'];
                }
            } catch (\Throwable $e) {
                Log::debug('Order load from DB failed — trying file fallback', [
                    'ref'   => $orderRef,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $path = "pending-orders/{$orderRef}.json";
        if (Storage::disk('local')->exists($path)) {
            $data = json_decode(Storage::disk('local')->get($path), true);

            return is_array($data) ? $data : null;
        }

        return null;
    }

    public function markShopifySynced(string $orderRef, array $shopifyOrder): void
    {
        if (! $this->ordersTableAvailable()) {
            Storage::disk('local')->delete("pending-orders/{$orderRef}.json");

            return;
        }

        $order = Order::where('order_number', $orderRef)->first();
        if (! $order) {
            return;
        }

        $meta = $order->meta ?? [];
        $meta['shopify_sync'] = array_merge($meta['shopify_sync'] ?? [], [
            'status'  => 'synced',
            'synced_at' => now()->toIso8601String(),
        ]);

        $order->update([
            'status'               => 'paid',
            'shopify_order_id'     => (string) ($shopifyOrder['id'] ?? ''),
            'shopify_order_number' => (string) ($shopifyOrder['order_number'] ?? ($shopifyOrder['name'] ?? '')),
            'meta'                 => $meta,
        ]);
    }

    public function markShopifySyncFailed(string $orderRef, string $error): void
    {
        if (! $this->ordersTableAvailable()) {
            return;
        }

        $order = Order::where('order_number', $orderRef)->first();
        if (! $order) {
            return;
        }

        $meta = $order->meta ?? [];
        $attempts = (int) ($meta['shopify_sync']['attempts'] ?? 0) + 1;
        $meta['shopify_sync'] = array_merge($meta['shopify_sync'] ?? [], [
            'status'        => 'pending',
            'last_error'    => $error,
            'attempts'      => $attempts,
            'last_attempt'  => now()->toIso8601String(),
        ]);

        $order->update(['status' => 'paid_shopify_pending', 'meta' => $meta]);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function saveToDatabase(string $orderRef, array $payload): void
    {
        $totals = $payload['totals'] ?? [];
        $customer = $payload['customer'] ?? [];

        $order = Order::create([
            'order_number'         => $orderRef,
            'status'               => ($payload['shopify_synced'] ?? false) ? 'paid' : 'paid_shopify_pending',
            'locale'               => $payload['locale'] ?? 'en',
            'currency'             => $totals['currency_code'] ?? 'USD',
            'exchange_rate'        => $totals['exchange_rate'] ?? 1,
            'subtotal_usd'         => $totals['subtotal_usd'] ?? 0,
            'discount_amount_usd'  => $totals['discount_usd'] ?? 0,
            'shipping_usd'         => $totals['shipping_usd'] ?? 0,
            'tax_usd'              => $totals['tax_usd'] ?? 0,
            'total_usd'            => $totals['total_usd'] ?? 0,
            'customer_email'       => $customer['email'] ?? '',
            'customer_first_name'  => $customer['first_name'] ?? '',
            'customer_last_name'   => $customer['last_name'] ?? '',
            'customer_phone'       => $customer['phone'] ?? null,
            'shipping_address1'    => $customer['address1'] ?? '',
            'shipping_address2'    => $customer['address2'] ?? null,
            'shipping_city'        => $customer['city'] ?? '',
            'shipping_state'       => $customer['state'] ?? null,
            'shipping_zip'         => $customer['zip'] ?? '',
            'shipping_country'     => $customer['country'] ?? 'US',
            'square_payment_id'    => $payload['square_payment_id'] ?? null,
            'shopify_order_id'     => $payload['shopify_order_id'] ?? null,
            'shopify_order_number' => $payload['shopify_order_name'] ?? null,
            'discount_code'        => $payload['discount_code'] ?? null,
            'meta'                 => [
                'presentation' => [
                    'currency_code'   => $totals['currency_code'] ?? 'USD',
                    'currency_symbol' => $totals['currency_symbol'] ?? '$',
                    'subtotal'        => $totals['subtotal'] ?? null,
                    'shipping'        => $totals['shipping'] ?? null,
                    'tax'             => $totals['tax'] ?? null,
                    'total'           => $totals['total'] ?? null,
                    'discount'        => $totals['discount'] ?? null,
                ],
                'shopify_sync' => [
                    'status'  => ($payload['shopify_synced'] ?? false) ? 'synced' : 'pending',
                    'payload' => $payload['shopify_payload'] ?? null,
                ],
            ],
        ]);

        foreach ($payload['items'] ?? [] as $item) {
            $qty = max(1, (int) ($item['quantity'] ?? 1));
            $unit = (float) ($item['price'] ?? 0);

            OrderItem::create([
                'order_id'        => $order->id,
                'product_id'      => is_numeric($item['product_id'] ?? null) ? (int) $item['product_id'] : null,
                'product_name'    => $item['title'] ?? 'Product',
                'sku'             => $item['sku'] ?? null,
                'quantity'        => $qty,
                'unit_price_usd'  => $unit,
                'total_price_usd' => round($unit * $qty, 2),
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function saveToFile(string $orderRef, array $payload): void
    {
        Storage::disk('local')->put(
            "pending-orders/{$orderRef}.json",
            json_encode($payload, JSON_PRETTY_PRINT)
        );
    }

    protected function ordersTableAvailable(): bool
    {
        static $available = null;

        if ($available !== null) {
            return $available;
        }

        try {
            $available = Schema::hasTable('orders');
        } catch (\Throwable $e) {
            Log::debug('Order persistence: database unavailable', [
                'error' => $e->getMessage(),
            ]);
            $available = false;
        }

        return $available;
    }
}
