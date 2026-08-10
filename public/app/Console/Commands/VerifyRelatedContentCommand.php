<?php

namespace App\Console\Commands;

use App\Models\Supabase\Product;
use App\Models\Supabase\RelatedContent;
use App\Services\RelatedContentResolver;
use App\Support\SupabaseDb;
use Illuminate\Console\Command;

/**
 * Seed demo knowledge-graph links and verify Phase 2 §7 resolver.
 */
class VerifyRelatedContentCommand extends Command
{
    protected $signature = 'related:verify {--seed : Create demo product→education/blog links}';

    protected $description = 'Verify Content Relationship Engine (§7) end-to-end';

    public function handle(RelatedContentResolver $resolver): int
    {
        if (! SupabaseDb::available()) {
            $this->error('Supabase unavailable');

            return self::FAILURE;
        }

        $product = Product::query()->where('handle', 'dainely-comfort-belt')->first()
            ?? Product::query()->orderBy('id')->first();

        if (! $product) {
            $this->error('No products in dainely_products — run shopify:sync-catalog first');

            return self::FAILURE;
        }

        if ($this->option('seed')) {
            $this->seedForProduct((int) $product->id);
        }

        $links = $resolver->for('product', (int) $product->id, 'en');

        $this->table(
            ['Check', 'Result'],
            [
                ['Product', "{$product->handle} #{$product->id}"],
                ['related_content rows (source=product)', (string) RelatedContent::where('source_type', 'product')->where('source_id', $product->id)->count()],
                ['Resolved links (en)', (string) $links->count()],
            ]
        );

        foreach ($links as $link) {
            $this->line("  → [{$link['type_label']}] {$link['title']}");
            $this->line("     {$link['url']}");
        }

        $ok = $links->isNotEmpty();
        if ($ok) {
            $this->info('§7 VERIFY PASSED — open product page and confirm Related Resources section.');
            $this->line('Admin: /dainely-admin-panel/related');
            $this->line('Page:  /en/products/' . $product->handle);

            return self::SUCCESS;
        }

        $this->warn('No links resolved. Run: php artisan related:verify --seed');

        return self::FAILURE;
    }

    protected function seedForProduct(int $productId): void
    {
        $pairs = [
            ['related_type' => 'education', 'related_id' => 1, 'display_order' => 1],
            ['related_type' => 'education', 'related_id' => 2, 'display_order' => 2],
            ['related_type' => 'blog', 'related_id' => 1, 'display_order' => 3],
            ['related_type' => 'blog', 'related_id' => 4, 'display_order' => 4],
        ];

        foreach ($pairs as $pair) {
            RelatedContent::firstOrCreate(
                [
                    'source_type' => 'product',
                    'source_id' => $productId,
                    'related_type' => $pair['related_type'],
                    'related_id' => $pair['related_id'],
                ],
                ['display_order' => $pair['display_order']]
            );
        }

        $this->info("Seeded demo relations for product #{$productId}");
    }
}
