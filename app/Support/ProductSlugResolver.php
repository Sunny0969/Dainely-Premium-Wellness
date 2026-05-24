<?php

namespace App\Support;

/**
 * Maps Shopify product handles/titles to static catalog slugs when possible.
 */
class ProductSlugResolver
{
    /** @var array<string, string> */
    protected static array $handleAliases = [
        'dainely-comfort-belt' => 'dainely-belt',
        'dainely-daily-comfort-system' => 'daily-relief-system',
    ];

    public static function resolveRouteSlug(string $slug): string
    {
        return self::$handleAliases[$slug] ?? $slug;
    }

    public static function resolveForShopifyProduct(array $product): string
    {
        $title = mb_strtolower(trim((string) ($product['title'] ?? '')));
        $handle = (string) ($product['handle'] ?? '');

        $fromTitle = match ($title) {
            'dainely belt', 'dainely comfort belt' => 'dainely-belt',
            'daily relief system' => 'daily-relief-system',
            default => null,
        };

        if ($fromTitle !== null) {
            return $fromTitle;
        }

        if ($handle !== '' && isset(self::$handleAliases[$handle])) {
            return self::$handleAliases[$handle];
        }

        return $handle !== '' ? $handle : 'dainely-belt';
    }
}
