<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\ProductTranslationService;
use App\Services\ShopifyService;
use App\Support\ProductSlugResolver;

class HomeController extends Controller
{
    public function __construct(
        protected ShopifyService $shopify,
        protected ProductTranslationService $productTranslations,
    ) {}

    public function index()
    {
        $locale = app()->getLocale();

        // One cached Shopify catalog call (shared with header / products index).
        $shopifyResult = $this->shopify->fetchProducts(50);
        $mapped = $shopifyResult['success']
            ? \App\Support\ProductVisibility::filterShopifyProducts(
                $this->shopify->mapProductsForDisplay($shopifyResult['products'])
            )
            : [];

        $shopifyProducts = $this->productTranslations->applyMany(
            array_slice($mapped, 0, 12),
            $locale
        );
        $shopifyProductSlides = array_chunk($shopifyProducts, 3);
        $shopifyProductsError = $shopifyResult['success'] ? null : ($shopifyResult['error'] ?? null);
        $shopifyProductsSource = $shopifyResult['source'] ?? null;

        // Reuse the same list — no extra Shopify round-trips for hero CTAs.
        $featuredBelt = $this->findMappedProduct($mapped, ProductSlugResolver::resolveHandle('dainely-belt'), $locale);
        $dailyRelief  = $this->findMappedProduct($mapped, ProductSlugResolver::resolveHandle('daily-relief-system'), $locale);
        $heroVideo    = is_file(public_path('videos/day-in-motion.mp4'))
            ? asset('videos/day-in-motion.mp4')
            : null;

        return view('pages.home', compact(
            'locale',
            'shopifyProducts',
            'shopifyProductSlides',
            'shopifyProductsError',
            'shopifyProductsSource',
            'featuredBelt',
            'dailyRelief',
            'heroVideo',
        ));
    }

    /**
     * @param  list<array<string, mixed>>  $mapped
     * @return array<string, mixed>|null
     */
    private function findMappedProduct(array $mapped, string $handle, string $locale): ?array
    {
        $handle = strtolower(trim($handle));
        if ($handle === '' || \App\Support\ProductVisibility::isHandleHidden($handle)) {
            return null;
        }

        foreach ($mapped as $product) {
            if (strtolower((string) ($product['handle'] ?? '')) === $handle) {
                return $this->productTranslations->apply($product, $locale);
            }
        }

        return null;
    }
}
