<?php
/**
 * Render premium product landing views directly (no Shopify HTTP).
 * Run: php scripts/smoke_product_views.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Support\ProductLandingAssets;
use App\Support\ProductLandingLang;
use Illuminate\Support\Facades\View;

$handles = [
    'dainely-comfort-belt' => 'isDainelyBelt',
    'dainely-ball-massager' => 'isBallMassager',
    'neck-pain' => 'isNeckCloud',
    'back-pain-relief-patches-20-pcs' => 'isBackPatches',
    'dainely-unisex-heated-jacket' => 'isHeatedJacket',
    'dainely-foot-massager' => 'isFootMassager',
    'brace' => 'isKneeBrace',
    'dainely-massager' => 'isDainelyMassager',
    'shoulder-brace' => 'isShoulderBrace',
    'stretcher' => 'isNeckStretcher',
    'dainely-orthopedic-back-stretcher' => 'isBackStretcher',
    'relaxaleg-system' => 'isRelaxaLeg',
    'dainely-tourmaline-belt' => 'isTourmalineBelt',
    'daily-relief-system' => 'isDmedeSystem',
    'cushion' => 'isErgoCushion',
    'functional-mushroom-coffee' => 'isMushroomCoffee',
];

$locales = ['en', 'fr', 'de'];
$failures = 0;

foreach ($locales as $locale) {
    app()->setLocale($locale);

    foreach ($handles as $handle => $flag) {
        $flags = array_fill_keys(array_values(array_unique(array_map(
            fn ($f) => $f,
            array_values($handles)
        ))), false);
        $flags[$flag] = true;

        $langKey = ProductLandingLang::resolveLangKey($handle, $flags);
        $prefix = ProductLandingLang::translationPrefix($langKey);

        if (! $prefix) {
            echo "SKIP {$locale}/{$handle} — no lang key\n";
            continue;
        }

        $mainImg = 'https://cdn.shopify.com/s/files/1/test.webp';
        $landingAssets = ProductLandingAssets::forProduct(
            $langKey,
            $handle,
            $mainImg,
            [],
            false,
            64.0,
            119.0,
            '/en/cart/add',
            '/en/checkout',
            $prefix,
        );

        try {
            View::make('partials.product-landing-premium', [
                'langKey'         => $prefix,
                'cartProduct'     => ['id' => '1', 'title' => 'Test', 'price' => 64, 'image' => $mainImg, 'variants' => [], 'source' => 'shopify'],
                'cartAddUrl'      => '/en/cart/add',
                'checkoutUrl'     => '/en/checkout',
                'requiresOption'  => false,
                'variants'        => [],
                'handle'          => $handle,
                'mainImg'         => $mainImg,
                'price'           => 64.0,
                'compareAt'       => 119.0,
                'reviewStats'     => ['average_rating' => '4.8', 'total_reviews' => 50],
                'locale'          => $locale,
                'fmt'             => fn ($n) => '$' . number_format((float) $n, 2),
                'galleryImages'   => $landingAssets['galleryImages'] ?? [],
                'scienceImage'    => $landingAssets['scienceImage'] ?? 'spine-anatomy.png',
                'lifestyleImages' => $landingAssets['lifestyleImages'] ?? [],
                'purchaseOptions' => $landingAssets['purchaseOptions'] ?? null,
                'showSizeGuide'   => $landingAssets['showSizeGuide'] ?? false,
                'sizeGuideHref'   => '#size-guide',
            ])->render();

            echo "OK   {$locale}/{$handle}\n";
        } catch (Throwable $e) {
            $failures++;
            echo "FAIL {$locale}/{$handle}: {$e->getMessage()}\n";
        }
    }
}

echo $failures === 0
    ? "\nAll product view renders passed.\n"
    : "\n{$failures} render(s) failed.\n";

exit($failures === 0 ? 0 : 1);
