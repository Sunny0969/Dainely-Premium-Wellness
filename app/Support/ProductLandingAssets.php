<?php

namespace App\Support;

class ProductLandingAssets
{
    /**
     * Normalize Shopify product image URLs for galleries / cart.
     *
     * @param  array<int, mixed>  $images  Shopify `images` array or list of URL strings
     * @return list<string>
     */
    public static function shopifyImageUrls(array $images, ?string $mainImg = null): array
    {
        $urls = [];

        foreach ($images as $img) {
            if (is_string($img) && trim($img) !== '') {
                $urls[] = trim($img);
                continue;
            }

            if (is_array($img)) {
                $src = $img['src'] ?? $img['url'] ?? null;
                if (is_string($src) && trim($src) !== '') {
                    $urls[] = trim($src);
                }
            }
        }

        if ($mainImg && trim($mainImg) !== '' && ! in_array($mainImg, $urls, true)) {
            array_unshift($urls, trim($mainImg));
        }

        return array_values(array_unique($urls));
    }

    /**
     * Request a resized Shopify CDN derivative (reduces bytes; host remains Shopify CDN).
     */
    public static function cdnSized(?string $url, int $width = 800): string
    {
        $url = trim((string) $url);
        if ($url === '') {
            return '';
        }

        if (str_starts_with($url, '//')) {
            $url = 'https:'.$url;
        }

        if (! preg_match('#^https?://#i', $url)) {
            return $url;
        }

        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        if ($host === '' || (! str_contains($host, 'shopify') && ! str_contains($host, 'myshopify'))) {
            return $url;
        }

        if (preg_match('/[?&]width=\d+/i', $url)) {
            return $url;
        }

        $sep = str_contains($url, '?') ? '&' : '?';

        return $url.$sep.'width='.max(40, min(2000, $width));
    }

    /**
     * @param  array<int, array<string, mixed>>  $variants
     * @return array<int, array<string, mixed>>
     */
    public static function mapVariantsForCart(array $variants): array
    {
        return collect($variants)->values()->map(function (array $variant, int $index): array {
            return [
                'index'            => $index,
                'id'               => (string) ($variant['id'] ?? $index),
                'title'            => $variant['title'] ?? 'Option',
                'price'            => (float) ($variant['price'] ?? 0),
                'compare_at_price' => isset($variant['compare_at_price']) ? (float) $variant['compare_at_price'] : null,
            ];
        })->all();
    }

    /**
     * Product hero gallery always uses Shopify CDN images.
     * Lifestyle / science sections keep local assets for marketing copy only.
     *
     * @param  list<string>  $shopifyImageUrls
     * @return array{
     *   galleryImages?: array<int, string>,
     *   lifestyleImages?: array<int, string>,
     *   scienceImage?: string,
     *   showSizeGuide?: bool,
     *   sizeGuideHref?: string,
     *   purchaseOptions?: array<string, mixed>,
     *   price?: float|null,
     *   compareAt?: float|null,
     * }
     */
    public static function forProduct(
        ?string $langKey,
        string $handle,
        ?string $mainImg,
        array $variants,
        bool $requiresOption,
        float $price,
        ?float $compareAt,
        string $cartAddUrl,
        string $checkoutUrl,
        string $langPrefix,
        array $shopifyImageUrls = [],
    ): array {
        $gallery = self::shopifyImageUrls($shopifyImageUrls, $mainImg);

        $defaults = [
            'galleryImages'   => $gallery,
            'lifestyleImages' => ['recovery-edu.webp', 'lifestyle-everyday-movement.webp', 'lifestyle-travel-commute.png'],
            'scienceImage'    => 'spine-anatomy.png',
            'showSizeGuide'   => false,
            'sizeGuideHref'   => '#size-guide',
            'price'           => $price,
            'compareAt'       => $compareAt,
            'purchaseOptions' => [
                'cartAddUrl'     => $cartAddUrl,
                'checkoutUrl'    => $checkoutUrl,
                'requiresOption' => $requiresOption,
                'options'        => self::mapVariantsForCart($variants),
                'optionType'     => 'shopify',
                'optionLabel'    => __($langPrefix . '.select_option'),
                'showSizeGuide'  => false,
                'sizeGuideHref'  => '#size-guide',
                'addToCartText'  => __($langPrefix . '.add_to_cart'),
                'orderNowText'   => __($langPrefix . '.order_now'),
            ],
        ];

        // Per-product marketing sections only — never override galleryImages (Shopify).
        $map = [
            'products_belt' => [
                // Order: At the Standing Desk → During Daily Movement → Commute & Travel
                'lifestyleImages' => ['back-pain-edu.webp', 'man-sitting.jpg', 'women-walking.jpg'],
                'scienceImage'    => 'spine-anatomy.png',
                'showSizeGuide'   => true,
                'purchaseOptions' => [
                    'showSizeGuide' => true,
                ],
            ],
            'products_ball' => [
                'lifestyleImages' => ['neck-pain-edu.png', 'posture-edu.png', 'recovery-edu.webp'],
                'scienceImage'    => 'neck-pain-edu.png',
                'price'           => $price ?: 39.95,
                'compareAt'       => $compareAt ?: 59.95,
                'purchaseOptions' => array_merge($defaults['purchaseOptions'], ['requiresOption' => false, 'options' => []]),
            ],
            'products_neck' => [
                'lifestyleImages' => ['NeckCloud-At-Desk.jpg', 'NeckCloud-After-Commute.jpg', 'Evening-Decompression-Neck-Pain.jpg'],
                'scienceImage'    => 'NeckCloud-Routine.jpg',
            ],
            'products_patches' => [
                // Order matches lifestyle_cards: Active Movement → At Your Desk → Overnight Healing
                // Use Shopify CDN (always on server); local lifestyle webp files are missing on production.
                'lifestyleImages' => [
                    'lifestyle-everyday-movement.webp',
                    'back-pain-edu.webp',
                    'recovery-edu.webp',
                ],
                'scienceImage' => $gallery[0] ?? 'lifestyle-everyday-movement.webp',
            ],
            'products_jacket' => [
                'lifestyleImages' => ['recovery-edu.webp', 'lifestyle-travel-commute.png', 'lifestyle-everyday-movement.webp'],
                'scienceImage'    => 'recovery-edu.webp',
            ],
            'products_fm' => [
                'lifestyleImages' => ['foot-massager-lifestyle.png', 'lifestyle-everyday-movement.webp', 'recovery-edu.webp'],
                'scienceImage'    => 'foot-reflexology-chart.png',
                'price'           => $price ?: 49.95,
                'compareAt'       => $compareAt ?: 79.95,
            ],
            'products_knee' => [
                // Order matches lifestyle_cards: Workouts → Walking → Sitting-to-Standing
                'lifestyleImages' => [
                    'Workouts-and-Athletics.webp',
                    'Stairs-and-Daily-Walking.webp',
                    'Sitting-to-Standing.webp',
                ],
                'scienceImage'    => 'Built-for-Everyday-Comfort.webp',
            ],
            'products_percussion' => [
                'lifestyleImages' => ['Massager-Unwind-after-an-active-day.jpg', 'Massager-Take-a-few-minutes-to-reset.jpg', 'Massager-Give-hardworking-muscles-some-attention.jpg'],
                'scienceImage'    => 'Massager-Personalized-Percussion.jpg',
            ],
            'products_shoulder' => [
                'lifestyleImages' => ['ShoulderBrace-Female-Workout.jpg', 'ShoulderBrace-Female-Gardening.jpg', 'ShoulderBrace-Female-Comfort-When-You-Need.jpg'],
                'scienceImage'    => 'shoulder-brace-main.png',
            ],
            'products_neck_stretcher' => [
                'lifestyleImages' => ['Desk-Screen-Fatigue-new.jpg', 'Post-Workout-Stretch-new.jpg', 'Bedtime-Tension-Melt-new.jpg'],
                'scienceImage'    => 'Cervical-Spine-Recovery-new.jpg',
            ],
            'products_back_stretcher' => [
                'lifestyleImages' => ['back-stretcher-main.png', 'lifestyle-everyday-movement.webp', 'recovery-edu.webp'],
                'scienceImage'    => 'back-stretcher-main.png',
            ],
            'products_relaxaleg' => [
                'lifestyleImages' => ['relaxaleg-main.png', 'relaxaleg-lifestyle.png', 'recovery-edu.webp'],
                'scienceImage'    => 'relaxaleg-main.png',
            ],
            'products_tourmaline' => [
                'lifestyleImages' => ['tourmaline-belt-main.png', 'lifestyle-everyday-movement.webp', 'recovery-edu.webp'],
                'scienceImage'    => 'tourmaline-belt-main.png',
            ],
            'products_dmede' => [
                'lifestyleImages' => ['daily-relief-system1.png', 'lifestyle-everyday-movement.webp', 'recovery-edu.webp'],
                'scienceImage'    => 'daily-relief-system1.png',
            ],
            'products_cushion' => [
                'lifestyleImages' => ['cushion-main.png', 'lifestyle-everyday-movement.webp', 'recovery-edu.webp'],
                'scienceImage'    => 'cushion-main.png',
            ],
            'products_coffee' => [
                'lifestyleImages' => ['mushroom-coffee-main.png', 'lifestyle-everyday-movement.webp', 'recovery-edu.webp'],
                'scienceImage'    => 'mushroom-coffee-main.png',
            ],
        ];

        if ($langKey === null || ! isset($map[$langKey])) {
            return $defaults;
        }

        $merged = array_replace_recursive($defaults, $map[$langKey]);
        // Hard guarantee: product gallery is always Shopify (never local overrides).
        $merged['galleryImages'] = $gallery;

        return $merged;
    }
}
