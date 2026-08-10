<?php

namespace App\Console\Commands;

use App\Jobs\SyncProductJob;
use App\Models\Supabase\Product;
use App\Models\Supabase\ProductContent;
use App\Models\Supabase\WebhookLog;
use App\Support\SupabaseDb;
use Illuminate\Console\Command;

/**
 * Smoke-test Phase 2 §4 product synchronisation against Supabase.
 */
class SyncProductSmokeTest extends Command
{
    protected $signature = 'shopify:sync-smoke
                            {--delete : Also exercise products/delete after upsert}';

    protected $description = 'Upsert a sample Shopify product payload into Supabase (Phase 2 sync verify)';

    public function handle(): int
    {
        if (! SupabaseDb::available()) {
            $this->error('Supabase unavailable — enable pdo_pgsql and FEATURES_SUPABASE=true');
            return self::FAILURE;
        }

        $payload = [
            'id' => 8067824845014,
            'title' => 'Dainely Comfort Belt',
            'handle' => 'dainely-comfort-belt',
            'status' => 'active',
            'body_html' => '<p>Phase 2 sync smoke test</p>',
            'image' => ['src' => 'https://cdn.shopify.com/s/files/1/smoke-test.jpg'],
            'images' => [['src' => 'https://cdn.shopify.com/s/files/1/smoke-test.jpg']],
            'variants' => [[
                'id' => 111222333,
                'admin_graphql_api_id' => 'gid://shopify/ProductVariant/111222333',
                'sku' => 'DAINELY-BELT',
                'price' => '249.00',
                'compare_at_price' => '299.00',
                'inventory_quantity' => 25,
            ]],
        ];

        $log = WebhookLog::create([
            'source'     => 'shopify',
            'event_type' => 'products/update',
            'payload'    => $payload,
            'status'     => 'pending',
        ]);

        SyncProductJob::dispatchSync($payload, 'products/update', $log->id);

        $log->refresh();
        $product = Product::where('handle', 'dainely-comfort-belt')->first();
        $contentCount = $product
            ? ProductContent::where('product_id', $product->id)->count()
            : 0;

        $this->table(
            ['Check', 'Result'],
            [
                ['webhook_logs.status', $log->status],
                ['dainely_products.handle', $product?->handle ?? 'MISSING'],
                ['dainely_products.price', $product?->price ?? 'MISSING'],
                ['product_content locales', (string) $contentCount],
                ['synced_at', (string) ($product?->synced_at ?? '')],
            ]
        );

        if ($log->status !== 'processed' || ! $product || $contentCount < 3) {
            $this->error('Sync smoke FAILED');
            if ($log->error) {
                $this->line($log->error);
            }
            return self::FAILURE;
        }

        $this->info('Sync smoke PASSED — check Supabase Table Editor');

        if ($this->option('delete')) {
            $delLog = WebhookLog::create([
                'source'     => 'shopify',
                'event_type' => 'products/delete',
                'payload'    => ['id' => $payload['id']],
                'status'     => 'pending',
            ]);
            SyncProductJob::dispatchSync(['id' => $payload['id']], 'products/delete', $delLog->id);
            $this->warn('Delete option ran — product removed again.');
        }

        return self::SUCCESS;
    }
}
