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

        $shopifyResult = $this->shopify->fetchProducts(12);
        $shopifyProducts = $shopifyResult['success']
            ? $this->productTranslations->applyMany(
                $this->shopify->mapProductsForDisplay($shopifyResult['products']),
                $locale
            )
            : [];
        $shopifyProductSlides = array_chunk($shopifyProducts, 3);
        $shopifyProductsError = $shopifyResult['success'] ? null : $shopifyResult['error'];
        $shopifyProductsSource = $shopifyResult['source'] ?? null;

        $featuredBelt = $this->resolveProductForHome(ProductSlugResolver::resolveHandle('dainely-belt'));
        $dailyRelief  = $this->resolveProductForHome(ProductSlugResolver::resolveHandle('daily-relief-system'));
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
     * @return array<string, mixed>|null
     */
    private function resolveProductForHome(string $handle): ?array
    {
        $result = $this->shopify->fetchProductByHandle($handle);
        if (! $result['success'] || empty($result['product'])) {
            return null;
        }

        $mapped = $this->shopify->mapProductsForDisplay([$result['product']]);
        $product = $mapped[0] ?? null;

        return $product
            ? $this->productTranslations->apply($product, app()->getLocale())
            : null;
    }
}
