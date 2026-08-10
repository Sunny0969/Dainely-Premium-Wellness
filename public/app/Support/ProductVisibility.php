<?php

namespace App\Support;

use App\Models\Supabase\Product;
use Illuminate\Support\Facades\Cache;

/**
 * Admin can unpublish catalog rows so they never appear on the storefront,
 * even if Shopify still returns them.
 */
class ProductVisibility
{
    public const STATUS_UNPUBLISHED = 'unpublished';

    /**
     * @return list<string> lowercase handles that must be hidden
     */
    public static function hiddenHandles(): array
    {
        if (! SupabaseDb::available()) {
            return [];
        }

        return Cache::remember('storefront.hidden_product_handles', \App\Support\StorefrontCache::cmsTtlSeconds() > 0
            ? min(600, \App\Support\StorefrontCache::cmsTtlSeconds())
            : 600, function () {
            return SupabaseDb::run(function () {
                return Product::query()
                    ->where('status', self::STATUS_UNPUBLISHED)
                    ->pluck('handle')
                    ->filter()
                    ->map(fn ($h) => strtolower((string) $h))
                    ->unique()
                    ->values()
                    ->all();
            }, []);
        });
    }

    public static function forgetCache(): void
    {
        Cache::forget('storefront.hidden_product_handles');
    }

    public static function isHandleHidden(?string $handle): bool
    {
        $handle = strtolower(trim((string) $handle));
        if ($handle === '') {
            return false;
        }

        return in_array($handle, self::hiddenHandles(), true);
    }

    /**
     * @param  array<int, array<string, mixed>>  $products
     * @return array<int, array<string, mixed>>
     */
    public static function filterShopifyProducts(array $products): array
    {
        $hidden = self::hiddenHandles();
        if ($hidden === []) {
            return $products;
        }

        return array_values(array_filter($products, function (array $p) use ($hidden) {
            $handle = strtolower((string) ($p['handle'] ?? ''));

            return $handle === '' || ! in_array($handle, $hidden, true);
        }));
    }
}
