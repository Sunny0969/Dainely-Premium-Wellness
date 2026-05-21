<?php
namespace App\Console\Commands;

use App\Models\Product;
use App\Services\ShopifyService;
use Illuminate\Console\Command;

class SyncProductsToShopify extends Command
{
    protected $signature   = 'shopify:sync-products {--force : Force re-sync even if product already has Shopify ID}';
    protected $description = 'Create/sync Dainely products to the Shopify DMEDE-USA store';

    public function handle(ShopifyService $shopify): int
    {
        $products = Product::with('translations')->active()->get();

        $this->info("Syncing {$products->count()} products to Shopify (dmede-usa.myshopify.com)...");

        foreach ($products as $product) {
            $translation = $product->translations->firstWhere('locale', 'en');
            if (!$translation) {
                $this->warn("  ⚠ Skipping #{$product->id} — no English translation");
                continue;
            }

            if ($product->shopify_product_id && !$this->option('force')) {
                $this->line("  ⏩ Skipping \"{$translation->name}\" — already synced (ID: {$product->shopify_product_id})");
                continue;
            }

            $this->line("  → Syncing: {$translation->name}");

            $gallery = $product->gallery_images ?? [];
            if (is_string($gallery)) $gallery = json_decode($gallery, true) ?? [];

            $imageObjects = array_map(fn($src, $i) => [
                'src'      => url($src),
                'position' => $i + 1,
                'alt'      => $translation->name,
            ], $gallery, array_keys($gallery));

            $shopifyData = [
                'title'        => $translation->name,
                'body_html'    => $translation->description ?? '<p>' . $translation->short_description . '</p>',
                'vendor'       => 'Dainely',
                'product_type' => ucfirst($product->type),
                'tags'         => 'dainely,wellness,back-pain,sciatica',
                'status'       => 'active',
                'variants'     => $this->buildVariants($product),
                'images'       => array_values(array_filter($imageObjects)),
            ];

            $result = $shopify->createProduct($shopifyData);

            if ($result && isset($result['id'])) {
                $product->update(['shopify_product_id' => (string)$result['id']]);
                $this->info("    ✅ Created Shopify product ID: {$result['id']} for \"{$translation->name}\"");
            } else {
                $this->error("    ❌ Failed to create Shopify product for \"{$translation->name}\"");
            }
        }

        $this->info('Shopify product sync complete.');
        return Command::SUCCESS;
    }

    protected function buildVariants(Product $product): array
    {
        $meta  = $product->meta;
        if (is_string($meta)) $meta = json_decode($meta, true) ?? [];
        $sizes = $meta['sizes'] ?? ['One Size'];

        return array_map(fn($size) => [
            'option1'              => $size,
            'price'                => number_format($product->price_usd, 2, '.', ''),
            'compare_at_price'     => $product->compare_price_usd
                ? number_format($product->compare_price_usd, 2, '.', '')
                : null,
            'sku'                  => $product->sku . '-' . str_replace('/', '-', $size),
            'inventory_management' => 'shopify',
            'inventory_quantity'   => 999,
            'fulfillment_service'  => 'manual',
            'requires_shipping'    => true,
            'weight'               => 0.5,
            'weight_unit'          => 'kg',
        ], $sizes);
    }
}
