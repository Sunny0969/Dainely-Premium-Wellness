<?php

namespace App\Support;

use App\Models\Supabase\Product;
use Illuminate\Support\Facades\Log;

/**
 * Verify cart stock against webhook-synced local DB (no live Shopify API).
 */
class CartInventory
{
    /**
     * @param  list<array<string, mixed>>  $items
     * @return array{ok: bool, message: ?string, shortages: list<array{title: string, available: int, requested: int}>}
     */
    public static function validate(array $items): array
    {
        if ($items === [] || ! SupabaseDb::available()) {
            return ['ok' => true, 'message' => null, 'shortages' => []];
        }

        try {
            return SupabaseDb::run(function () use ($items) {
                $shortages = [];

                foreach ($items as $item) {
                    $requested = max(1, (int) ($item['quantity'] ?? 1));
                    $product = self::findProduct($item);

                    if (! $product) {
                        continue;
                    }

                    if (strtolower((string) $product->status) === ProductVisibility::STATUS_UNPUBLISHED) {
                        $shortages[] = [
                            'title'     => (string) ($item['title'] ?? $product->title),
                            'available' => 0,
                            'requested' => $requested,
                        ];
                        continue;
                    }

                    // Only enforce when inventory has been synced from Shopify webhooks.
                    if ($product->synced_at === null) {
                        continue;
                    }

                    $available = (int) ($product->inventory ?? 0);
                    if ($available < $requested) {
                        $shortages[] = [
                            'title'     => (string) ($item['title'] ?? $product->title),
                            'available' => max(0, $available),
                            'requested' => $requested,
                        ];
                    }
                }

                if ($shortages === []) {
                    return ['ok' => true, 'message' => null, 'shortages' => []];
                }

                $first = $shortages[0];

                return [
                    'ok'        => false,
                    'message'   => __('checkout.insufficient_stock', [
                        'title'     => $first['title'],
                        'available' => $first['available'],
                    ]),
                    'shortages' => $shortages,
                ];
            }, ['ok' => true, 'message' => null, 'shortages' => []]);
        } catch (\Throwable $e) {
            Log::warning('CartInventory validate skipped', ['error' => $e->getMessage()]);

            return ['ok' => true, 'message' => null, 'shortages' => []];
        }
    }

    /**
     * @param  array<string, mixed>  $item
     */
    protected static function findProduct(array $item): ?Product
    {
        $variantId = trim((string) ($item['variant_id'] ?? ''));
        if ($variantId !== '') {
            $numeric = preg_replace('/\D+/', '', $variantId) ?: '';
            $byVariant = Product::query()
                ->where(function ($q) use ($variantId, $numeric) {
                    $q->where('variant_id', $variantId);
                    if ($numeric !== '') {
                        $q->orWhere('variant_id', $numeric)
                            ->orWhere('variant_id', 'gid://shopify/ProductVariant/'.$numeric)
                            ->orWhere('variant_id', 'like', '%ProductVariant/'.$numeric);
                    }
                })
                ->first();

            if ($byVariant) {
                return $byVariant;
            }
        }

        $handle = trim((string) ($item['handle'] ?? ''));
        if ($handle === '') {
            $productId = trim((string) ($item['product_id'] ?? ''));
            if ($productId !== '' && ! ctype_digit($productId) && ! str_starts_with($productId, 'gid://')) {
                $handle = ProductSlugResolver::resolveHandle($productId);
            }
        }

        if ($handle !== '') {
            return Product::query()->where('handle', $handle)->first();
        }

        $shopifyProductId = trim((string) ($item['product_id'] ?? ''));
        if ($shopifyProductId === '') {
            return null;
        }

        $gid = str_starts_with($shopifyProductId, 'gid://')
            ? $shopifyProductId
            : (ctype_digit($shopifyProductId) ? 'gid://shopify/Product/'.$shopifyProductId : null);

        if (! $gid) {
            return null;
        }

        return Product::query()
            ->where('shopify_product_id', $gid)
            ->orWhere('shopify_product_id', $shopifyProductId)
            ->first();
    }
}
