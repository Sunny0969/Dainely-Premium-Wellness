<?php

namespace App\Console\Commands;

use App\Models\Supabase\LandingPage;
use App\Models\Supabase\PageBlock;
use App\Models\Supabase\Product;
use App\Services\LandingOfferService;
use App\Support\SupabaseDb;
use Illuminate\Console\Command;

class VerifyLandingBlocksCommand extends Command
{
    protected $signature = 'landing:verify {--seed : Create/update a demo published landing page with offer + blocks}';

    protected $description = 'Verify Phase 2 §8.3/§8.4 landing blocks + offer checkout wiring';

    public function handle(LandingOfferService $offers): int
    {
        if (! SupabaseDb::available()) {
            $this->error('Supabase unavailable');

            return self::FAILURE;
        }

        $blockFiles = [
            'benefits', 'how-it-works', 'testimonials', 'faqs', 'cta', 'video', 'comparison',
        ];
        $missing = [];
        foreach ($blockFiles as $type) {
            if (! is_file(resource_path("views/components/blocks/{$type}.blade.php"))) {
                $missing[] = $type;
            }
        }

        if ($this->option('seed')) {
            $this->seedDemo();
        }

        $page = LandingPage::query()
            ->where('slug', 'dainely-belt-for-walking')
            ->where('locale', 'en')
            ->first();

        $offer = $page ? $offers->resolve($page, 'en') : null;

        $this->table(['Check', 'Result'], [
            ['Block blades present', $missing === [] ? 'OK (7)' : 'MISSING: '.implode(',', $missing)],
            ['Demo landing exists', $page ? "yes #{$page->id}" : 'no — run with --seed'],
            ['Published', $page ? ($page->published ? 'yes' : 'no') : '—'],
            ['shopify_product_id', $page->shopify_product_id ?? '—'],
            ['bundle_id', (string) ($page->bundle_id ?? '—')],
            ['Offer type', $offer['type'] ?? '—'],
            ['Checkout URL', $offer['checkout_url'] ?? '—'],
            ['Block count', $page ? (string) $page->pageBlocks()->count() : '0'],
        ]);

        if ($missing !== []) {
            return self::FAILURE;
        }

        if (! $page || ! $offer || ! in_array($offer['type'], ['product', 'bundle'], true)) {
            $this->warn('Offer not fully linked. Run: php artisan landing:verify --seed');

            return self::FAILURE;
        }

        $this->info('§8.3+8.4 VERIFY PASSED');
        $this->line('Open: /en/'.$page->slug);
        $this->line('CTA:  '.$offer['checkout_url']);

        return self::SUCCESS;
    }

    protected function seedDemo(): void
    {
        $product = Product::where('handle', 'dainely-comfort-belt')->first()
            ?? Product::orderBy('id')->first();

        $page = LandingPage::updateOrCreate(
            ['slug' => 'dainely-belt-for-walking', 'locale' => 'en'],
            [
                'title' => 'Dainely Belt for Walking',
                'meta_title' => 'Dainely Belt for Walking | Comfort in Motion',
                'meta_description' => 'Landing page demo for Phase 2 flexible blocks and checkout CTA.',
                'published' => true,
                'shopify_product_id' => $product?->shopify_product_id ?: $product?->handle,
                'bundle_id' => null,
                'cta_label' => 'Buy Dainely Belt — Checkout',
            ]
        );

        $defs = [
            ['block_type' => 'benefits', 'title' => 'Why walkers love it', 'content' => '<ul><li>Support without stiffness</li><li>All-day comfort</li><li>Discreet under clothes</li></ul>', 'sort_order' => 1],
            ['block_type' => 'how-it-works', 'title' => 'How it works', 'content' => '<p>Gentle decompression + core support while you walk.</p>', 'sort_order' => 2],
            ['block_type' => 'video', 'title' => 'See it in motion', 'content' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'sort_order' => 3],
            ['block_type' => 'comparison', 'title' => 'Compare options', 'content' => '<table><tr><th>Feature</th><th>Generic brace</th><th>Dainely</th></tr><tr><td>Decompression</td><td>No</td><td>Yes</td></tr><tr><td>All-day wear</td><td>Limited</td><td>Designed for it</td></tr></table>', 'sort_order' => 4],
            ['block_type' => 'cta', 'title' => 'Ready to move better?', 'content' => '<p>Free shipping over $29.99. 30-day guarantee.</p>', 'sort_order' => 5],
        ];

        foreach ($defs as $def) {
            PageBlock::updateOrCreate(
                [
                    'blockable_type' => LandingPage::class,
                    'blockable_id' => $page->id,
                    'locale' => 'en',
                    'block_type' => $def['block_type'],
                ],
                [
                    'title' => $def['title'],
                    'content' => $def['content'],
                    'sort_order' => $def['sort_order'],
                    'visible' => true,
                ]
            );
        }

        $this->info("Seeded landing #{$page->id} /en/{$page->slug}");
    }
}
