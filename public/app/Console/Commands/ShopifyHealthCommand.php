<?php

namespace App\Console\Commands;

use App\Services\ShopifyService;
use Illuminate\Console\Command;

class ShopifyHealthCommand extends Command
{
    protected $signature = 'shopify:health {--test-order : Create a test order in Shopify (sandbox/dev only)}';

    protected $description = 'Verify Shopify Admin API auth and order-sync readiness';

    public function handle(ShopifyService $shopify): int
    {
        $this->info('Shopify store: ' . config('shopify.store_domain'));
        $this->info('Sync orders: ' . (config('shopify.sync_orders') ? 'enabled' : 'disabled'));

        if (! $shopify->canSyncOrders()) {
            $this->error('Cannot sync orders — no valid Admin API token.');
            $this->line('Set SHOPIFY_ADMIN_ACCESS_TOKEN (shpat_...) or SHOPIFY_CLIENT_ID + SHOPIFY_CLIENT_SECRET in .env');

            return self::FAILURE;
        }

        $this->info('Admin API token: OK');

        $products = $shopify->fetchProducts(3);
        if ($products['success']) {
            $this->info('Product API: OK (' . count($products['products']) . ' products sampled)');
        } else {
            $this->warn('Product API failed: ' . ($products['error'] ?? 'unknown'));
        }

        $tax = app(\App\Services\ShopifyTaxService::class);
        $taxResult = $tax->estimate(
            [['title' => 'Test', 'price' => 64.0, 'quantity' => 1, 'variant_id' => null]],
            ['first_name' => 'Test', 'last_name' => 'User', 'address1' => '123 Main', 'city' => 'New York', 'state' => 'NY', 'zip' => '10001', 'country' => 'US'],
            'standard'
        );

        if ($taxResult['success']) {
            $this->info('Shopify Tax estimate: OK ($' . number_format($taxResult['tax_usd'], 2) . ')');
        } else {
            $this->warn('Shopify Tax estimate failed: ' . ($taxResult['error'] ?? 'unknown'));
            $this->line('  → Enable write_draft_orders scope on DMEDE custom app, or set SHOPIFY_TAX_FALLBACK=true for local dev.');
        }

        if (! $this->option('test-order')) {
            $this->line('Run with --test-order to push a $0.01 test order to Shopify Admin.');

            return self::SUCCESS;
        }

        if (app()->environment('production')) {
            $this->error('Refusing --test-order in production.');

            return self::FAILURE;
        }

        $result = $shopify->createOrder([
            'order_number'        => 'TEST-' . strtoupper(substr(md5((string) microtime(true)), 0, 8)),
            'customer_email'      => 'shopify-health@dainelylab.com',
            'customer_first_name' => 'Health',
            'customer_last_name'  => 'Check',
            'customer_phone'      => null,
            'shipping_address1'   => '123 Test Street',
            'shipping_city'       => 'New York',
            'shipping_state'      => 'NY',
            'shipping_zip'        => '10001',
            'shipping_country'    => 'US',
            'shipping_usd'        => 0,
            'total_usd'           => 0.01,
            'shipping_method'     => 'standard',
            'square_payment_id'   => 'health-check',
            'locale'              => 'en',
            'items'               => [[
                'product_name'   => 'Dainely Sync Health Check',
                'quantity'       => 1,
                'unit_price_usd' => 0.01,
                'sku'            => 'HEALTH-CHECK',
                'variant_id'     => null,
            ]],
        ]);

        if ($result['success']) {
            $order = $result['order'];
            $this->info('Test order created: ' . ($order['name'] ?? 'unknown') . ' (ID ' . ($order['id'] ?? '?') . ')');

            return self::SUCCESS;
        }

        $this->error('Test order failed: ' . ($result['error'] ?? 'unknown'));

        return self::FAILURE;
    }
}
