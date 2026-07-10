<?php

namespace App\Support;

class ProductLandingAssets
{
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
    ): array {
        $shopifyImages = collect($variants)->isNotEmpty()
            ? null
            : null;

        $defaults = [
            'galleryImages'   => $mainImg ? [$mainImg] : [],
            'lifestyleImages' => ['recovery-edu.png', 'lifestyle-desk-professional.png', 'lifestyle-everyday-movement.png'],
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

        $map = [
            'products_belt' => [
                'galleryImages'   => array_filter([
                    $mainImg ?: asset('images/dainely-belt-product.png'),
                    asset('images/lifestyle-desk-professional.png'),
                    asset('images/lifestyle-everyday-movement.png'),
                ]),
                'lifestyleImages' => ['lifestyle-desk-professional.png', 'lifestyle-everyday-movement.png', 'lifestyle-travel-commute.png'],
                'scienceImage'    => 'spine-anatomy.png',
                'showSizeGuide'   => true,
                'purchaseOptions' => [
                    'showSizeGuide' => true,
                ],
            ],
            'products_ball' => [
                'lifestyleImages' => ['neck-pain-edu.png', 'posture-edu.png', 'recovery-edu.png'],
                'scienceImage'    => 'neck-pain-edu.png',
                'price'           => $price ?: 39.95,
                'compareAt'       => $compareAt ?: 59.95,
                'purchaseOptions' => array_merge($defaults['purchaseOptions'], ['requiresOption' => false, 'options' => []]),
            ],
            'products_neck' => [
                'lifestyleImages' => ['neck-pain-edu.png', 'posture-edu.png', 'recovery-edu.png'],
                'scienceImage'    => 'neck-pain-edu.png',
            ],
            'products_patches' => [
                'lifestyleImages' => ['back-pain-edu.png', 'lifestyle-desk-professional.png', 'recovery-edu.png'],
                'scienceImage'    => 'back-pain-edu.png',
            ],
            'products_jacket' => [
                'lifestyleImages' => ['recovery-edu.png', 'lifestyle-travel-commute.png', 'lifestyle-everyday-movement.png'],
                'scienceImage'    => 'recovery-edu.png',
            ],
            'products_fm' => [
                'galleryImages'   => array_filter([
                    $mainImg ?: asset('images/foot-massager-main.png'),
                    asset('images/foot-massager-lifestyle.png'),
                    asset('images/foot-reflexology-chart.png'),
                    asset('images/recovery-edu.png'),
                ]),
                'lifestyleImages' => ['foot-massager-lifestyle.png', 'lifestyle-desk-professional.png', 'recovery-edu.png'],
                'scienceImage'    => 'foot-reflexology-chart.png',
                'price'           => $price ?: 49.95,
                'compareAt'       => $compareAt ?: 79.95,
            ],
            'products_knee' => [
                'lifestyleImages' => ['knee-brace-main.png', 'knee-brace-lifestyle.png', 'recovery-edu.png'],
                'scienceImage'    => 'knee-brace-main.png',
            ],
            'products_percussion' => [
                'lifestyleImages' => ['massager-main.png', 'massager-lifestyle.png', 'recovery-edu.png'],
                'scienceImage'    => 'massager-main.png',
            ],
            'products_shoulder' => [
                'lifestyleImages' => ['shoulder-brace-main.png', 'shoulder-brace-lifestyle.png', 'recovery-edu.png'],
                'scienceImage'    => 'shoulder-brace-main.png',
            ],
            'products_neck_stretcher' => [
                'lifestyleImages' => ['neck-stretcher-main.png', 'neck-stretcher-lifestyle.png', 'recovery-edu.png'],
                'scienceImage'    => 'neck-stretcher-main.png',
            ],
            'products_back_stretcher' => [
                'lifestyleImages' => ['back-stretcher-main.png', 'back-pain-edu.png', 'recovery-edu.png'],
                'scienceImage'    => 'back-stretcher-main.png',
            ],
            'products_relaxaleg' => [
                'lifestyleImages' => ['relaxaleg-main.png', 'relaxaleg-lifestyle.png', 'recovery-edu.png'],
                'scienceImage'    => 'relaxaleg-main.png',
            ],
            'products_tourmaline' => [
                'lifestyleImages' => ['tourmaline-belt-main.png', 'lifestyle-desk-professional.png', 'recovery-edu.png'],
                'scienceImage'    => 'tourmaline-belt-main.png',
            ],
            'products_dmede' => [
                'galleryImages'   => [asset('images/daily-relief-system.png')],
                'lifestyleImages' => ['daily-relief-system.png', 'lifestyle-desk-professional.png', 'recovery-edu.png'],
                'scienceImage'    => 'daily-relief-system.png',
            ],
            'products_cushion' => [
                'lifestyleImages' => ['cushion-main.png', 'lifestyle-desk-professional.png', 'recovery-edu.png'],
                'scienceImage'    => 'cushion-main.png',
            ],
            'products_coffee' => [
                'lifestyleImages' => ['mushroom-coffee-main.png', 'lifestyle-desk-professional.png', 'recovery-edu.png'],
                'scienceImage'    => 'mushroom-coffee-main.png',
            ],
        ];

        if ($langKey === null || ! isset($map[$langKey])) {
            return $defaults;
        }

        return array_replace_recursive($defaults, $map[$langKey]);
    }
}
