<?php

namespace App\Console\Commands;

use App\Models\Supabase\LandingPage;
use App\Models\Supabase\PageBlock;
use App\Models\Supabase\Product;
use App\Models\Supabase\ProductBundle;
use App\Models\Supabase\ProductBundleItem;
use App\Services\BundleDisplayService;
use App\Support\SupabaseDb;
use Illuminate\Console\Command;

class VerifyBundlesCommand extends Command
{
    protected $signature = 'bundles:verify {--seed : Create demo bundle + attach bundle block to landing}';

    protected $description = 'Verify Phase 2 §9 bundles: expand-to-cart + landing bundle block display';

    public function handle(BundleDisplayService $display): int
    {
        if (! SupabaseDb::available()) {
            $this->error('Supabase unavailable');

            return self::FAILURE;
        }

        if ($this->option('seed')) {
            $this->seed();
        }

        $bundle = ProductBundle::with('items.product')
            ->where('title', 'Daily Relief System')
            ->orWhere('bundle_shopify_product_id', 'like', '%demo-daily-relief%')
            ->orderByDesc('id')
            ->first();

        $bladeOk = is_file(resource_path('views/components/blocks/bundle.blade.php'));
        $presented = $bundle ? $display->present((int) $bundle->id, 'en') : null;

        $landing = LandingPage::where('slug', 'dainely-belt-for-walking')->where('locale', 'en')->first();
        $hasBundleBlock = $landing
            ? $landing->pageBlocks()->where('block_type', 'bundle')->exists()
            : false;

        $this->table(['Check', 'Result'], [
            ['bundle.blade.php', $bladeOk ? 'OK' : 'MISSING'],
            ['Demo bundle', $bundle ? "#{$bundle->id} ({$bundle->items->count()} items)" : 'none — use --seed'],
            ['Presented total', $presented ? number_format($presented['total'], 2) : '—'],
            ['Components', $presented ? (string) count($presented['components']) : '0'],
            ['Add URL', $presented['add_url'] ?? '—'],
            ['Landing bundle block', $hasBundleBlock ? 'yes' : 'no'],
            ['Landing URL', $landing ? '/en/'.$landing->slug : '—'],
        ]);

        if (! $bladeOk || ! $presented || count($presented['components']) < 1) {
            $this->warn('§9 incomplete. Run: php artisan bundles:verify --seed');

            return self::FAILURE;
        }

        $this->info('§9 VERIFY PASSED');
        $this->line('Open landing: /en/'.($landing->slug ?? 'dainely-belt-for-walking'));
        $this->line('Or POST add:   '.$presented['add_url']);

        return self::SUCCESS;
    }

    protected function seed(): void
    {
        $products = Product::orderBy('id')->limit(3)->get();
        if ($products->count() < 2) {
            $this->error('Need at least 2 products in dainely_products (run shopify:sync-catalog)');

            return;
        }

        $bundle = ProductBundle::updateOrCreate(
            [
                'bundle_shopify_product_id' => 'gid://shopify/Product/demo-daily-relief',
                'locale' => 'en',
            ],
            [
                'title' => 'Daily Relief System',
                'description' => 'Demo bundle: belt + recovery companion products.',
            ]
        );

        ProductBundleItem::where('bundle_id', $bundle->id)->delete();

        foreach ($products->take(2) as $i => $product) {
            ProductBundleItem::create([
                'bundle_id' => $bundle->id,
                'product_id' => $product->id,
                'quantity' => $i === 0 ? 1 : 1,
            ]);
        }

        $landing = LandingPage::updateOrCreate(
            ['slug' => 'dainely-belt-for-walking', 'locale' => 'en'],
            [
                'title' => 'Dainely Belt for Walking',
                'meta_title' => 'Dainely Belt for Walking',
                'published' => true,
                'bundle_id' => $bundle->id,
                'shopify_product_id' => null,
                'cta_label' => 'Get the Daily Relief System',
            ]
        );

        PageBlock::updateOrCreate(
            [
                'blockable_type' => LandingPage::class,
                'blockable_id' => $landing->id,
                'locale' => 'en',
                'block_type' => 'bundle',
            ],
            [
                'title' => 'Daily Relief System',
                'content' => (string) $bundle->id,
                'sort_order' => 6,
                'visible' => true,
            ]
        );

        $this->info("Seeded bundle #{$bundle->id} with {$products->take(2)->count()} components; landing #{$landing->id}");
    }
}
