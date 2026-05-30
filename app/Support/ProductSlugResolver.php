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
        // Shopify payload may provide either `handle` or `title` (depending on mapping source).
        // Our ProductController@show expects a `slug` that can be converted into a Shopify handle.

        $handle = trim((string) ($product['handle'] ?? ''));
        if ($handle !== '') {
            return self::resolveHandle($handle);
        }

        $title = trim((string) ($product['title'] ?? ''));
        if ($title === '') {
            return self::resolveHandle('dainely-belt');
        }

        $titleForSlug = mb_strtolower($title);

        // Known legacy product titles -> Shopify handles
        return match (true) {
            str_contains($titleForSlug, 'dainely belt') => 'dainely-comfort-belt',
            str_contains($titleForSlug, 'daily relief') || str_contains($titleForSlug, 'daily-relief')
                => 'dainely-daily-comfort-system',
            default => self::resolveHandle('dainely-belt'),
        };
    }

}
