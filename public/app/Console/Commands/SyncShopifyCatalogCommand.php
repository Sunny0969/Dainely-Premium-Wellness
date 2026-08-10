<?php

namespace App\Console\Commands;

use App\Jobs\SyncProductJob;
use App\Models\Supabase\Product;
use App\Models\Supabase\ProductContent;
use App\Models\Supabase\WebhookLog;
use App\Services\ShopifyService;
use App\Support\SupabaseDb;
use Illuminate\Console\Command;
use Throwable;

/**
 * Pull the full Shopify catalog into Supabase (Phase 2 bulk product sync).
 */
class SyncShopifyCatalogCommand extends Command
{
    protected $signature = 'shopify:sync-catalog
                            {--limit=0 : Max products to sync (0 = all)}
                            {--dry-run : Fetch and list only, do not write to Supabase}';

    protected $description = 'Bulk-sync all Shopify products into dainely_products + product_content';

    public function handle(ShopifyService $shopify): int
    {
        if (! $this->option('dry-run') && ! SupabaseDb::available()) {
            $this->error('Supabase unavailable — enable pdo_pgsql and FEATURES_SUPABASE=true');

            return self::FAILURE;
        }

        $this->info('Fetching Shopify catalog…');
        $result = $shopify->fetchAllProducts(250);

        if (! ($result['success'] ?? false)) {
            $this->error('Shopify fetch failed: ' . ($result['error'] ?? 'unknown'));

            return self::FAILURE;
        }

        $products = $result['products'] ?? [];
        $source = $result['source'] ?? 'unknown';
        $limit = (int) $this->option('limit');
        if ($limit > 0) {
            $products = array_slice($products, 0, $limit);
        }

        $this->info(sprintf(
            'Found %d product(s) via %s%s',
            count($products),
            $source,
            $this->option('dry-run') ? ' [dry-run]' : ''
        ));

        if ($products === []) {
            $this->warn('No products returned from Shopify.');

            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            foreach ($products as $p) {
                $this->line(sprintf(
                    '  - %s (%s)',
                    $p['handle'] ?? 'no-handle',
                    $p['title'] ?? 'untitled'
                ));
            }

            return self::SUCCESS;
        }

        $ok = 0;
        $failed = 0;
        $bar = $this->output->createProgressBar(count($products));
        $bar->start();

        foreach ($products as $product) {
            try {
                $payload = $this->normalizePayload($product);

                $log = WebhookLog::create([
                    'source'     => 'shopify',
                    'event_type' => 'products/bulk_sync',
                    'payload'    => $payload,
                    'status'     => 'pending',
                ]);

                SyncProductJob::dispatchSync($payload, 'products/update', $log->id);
                $log->refresh();

                if ($log->status === 'processed') {
                    $ok++;
                } else {
                    $failed++;
                    $this->newLine();
                    $this->warn('Failed: ' . ($payload['handle'] ?? $payload['id'] ?? '?') . ' — ' . ($log->error ?? 'unknown'));
                }
            } catch (Throwable $e) {
                $failed++;
                $this->newLine();
                $this->warn('Exception: ' . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $totalProducts = Product::query()->count();
        $totalContent = ProductContent::query()->count();

        $this->table(
            ['Metric', 'Value'],
            [
                ['Synced OK', (string) $ok],
                ['Failed', (string) $failed],
                ['dainely_products total', (string) $totalProducts],
                ['product_content total', (string) $totalContent],
            ]
        );

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * Ensure Admin + storefront product shapes work with SyncProductJob.
     *
     * @param  array<string, mixed>  $product
     * @return array<string, mixed>
     */
    protected function normalizePayload(array $product): array
    {
        if (empty($product['images']) && ! empty($product['image']) && is_array($product['image'])) {
            $product['images'] = [$product['image']];
        }

        if (empty($product['variants']) || ! is_array($product['variants'])) {
            $product['variants'] = [[
                'price' => $product['price'] ?? null,
                'sku' => null,
                'inventory_quantity' => 0,
            ]];
        }

        return $product;
    }
}
