<?php

namespace App\Support;

/**
 * Resolves product URL slugs to Shopify handles (including legacy static slugs).
 */
class ProductSlugResolver
{
    /** @var array<string, string> Legacy static slugs → Shopify handles */
    protected static array $legacySlugToHandle = [
        'dainely-belt' => 'dainely-comfort-belt',
        'daily-relief-system' => 'dainely-daily-comfort-system',
    ];

    public static function resolveHandle(string $slug): string
    {
        return self::$legacySlugToHandle[$slug] ?? $slug;
    }

    public static function resolveForShopifyProduct(array $product): string
    {
        $handle = (string) ($product['handle'] ?? '');

        return $handle !== '' ? $handle : self::resolveHandle('');
    }
}
