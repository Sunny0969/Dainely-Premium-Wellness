<?php

namespace App\Services;

use App\Models\Supabase\ProductBundle;
use App\Support\SupabaseDb;
use Illuminate\Support\Collection;

/**
 * Phase 2 §9 — hydrate bundles for display (components + totals).
 */
class BundleDisplayService
{
    /**
     * @return array{
     *   bundle: ProductBundle,
     *   components: list<array{title:string,image:?string,quantity:int,unit_price:float,line_total:float,handle:?string}>,
     *   total: float,
     *   currency: string,
     *   add_url: string
     * }|null
     */
    public function present(?int $bundleId, string $locale): ?array
    {
        if (! $bundleId || ! SupabaseDb::available()) {
            return null;
        }

        /** @var ProductBundle|null $bundle */
        $bundle = SupabaseDb::run(
            fn () => ProductBundle::with(['items.product'])
                ->where('id', $bundleId)
                ->first(),
            null
        );

        if (! $bundle) {
            return null;
        }

        $components = [];
        $total = 0.0;

        foreach ($bundle->items as $item) {
            $product = $item->product;
            if (! $product) {
                continue;
            }

            $qty = max(1, (int) $item->quantity);
            $unit = (float) ($product->price ?? 0);
            $line = $unit * $qty;
            $total += $line;

            $components[] = [
                'title' => method_exists($product, 'getTranslatedTitle')
                    ? $product->getTranslatedTitle($locale)
                    : (string) $product->title,
                'image' => $product->featured_image,
                'quantity' => $qty,
                'unit_price' => $unit,
                'line_total' => $line,
                'handle' => $product->handle,
                'variant_id' => $product->variant_id,
            ];
        }

        return [
            'bundle' => $bundle,
            'components' => $components,
            'total' => $total,
            'currency' => (string) config('shopify.shop_currency', 'USD'),
            'add_url' => route('bundle.add', ['locale' => $locale, 'bundleId' => $bundle->id]),
        ];
    }

    /**
     * Resolve bundle id from block content (plain id or JSON).
     */
    public function resolveIdFromContent(?string $content, ?int $fallbackId = null): ?int
    {
        $raw = trim((string) $content);
        if ($raw === '') {
            return $fallbackId ?: null;
        }

        if (is_numeric($raw)) {
            return (int) $raw;
        }

        $decoded = json_decode($raw, true);
        if (is_array($decoded) && ! empty($decoded['bundle_id'])) {
            return (int) $decoded['bundle_id'];
        }

        return $fallbackId ?: null;
    }

    /**
     * @param  Collection<int, object>  $blocks
     * @return array<int, array>
     */
    public function mapForBlocks(Collection $blocks, string $locale, ?int $pageBundleId = null): array
    {
        $map = [];

        foreach ($blocks as $block) {
            if (($block->block_type ?? '') !== 'bundle') {
                continue;
            }

            $id = $this->resolveIdFromContent($block->content ?? null, $pageBundleId);
            if (! $id) {
                continue;
            }

            $presented = $this->present($id, $locale);
            if ($presented) {
                $map[(int) $block->id] = $presented;
            }
        }

        // Also expose page-level offer bundle (for CTA pages without a block)
        if ($pageBundleId && ! isset($map['page'])) {
            $pagePresent = $this->present($pageBundleId, $locale);
            if ($pagePresent) {
                $map['page'] = $pagePresent;
            }
        }

        return $map;
    }
}
