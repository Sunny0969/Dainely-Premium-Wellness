<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\ShopifyService;
use App\Support\ProductSlugResolver;
// use App\Models\Product;
// use App\Models\Testimonial;

class HomeController extends Controller
{
    public function __construct(protected ShopifyService $shopify) {}

    public function index()
    {
        $locale = app()->getLocale();

        $shopifyResult = $this->shopify->fetchProducts(12);
        $shopifyProducts = $shopifyResult['success']
            ? $this->shopify->mapProductsForDisplay($shopifyResult['products'])
            : [];
        $shopifyProductSlides = array_chunk($shopifyProducts, 3);
        $shopifyProductsError = $shopifyResult['success'] ? null : $shopifyResult['error'];
        $shopifyProductsSource = $shopifyResult['source'] ?? null;

        $featuredBelt = $this->resolveProductForHome(ProductSlugResolver::resolveHandle('dainely-belt'));
        $dailyRelief  = $this->resolveProductForHome(ProductSlugResolver::resolveHandle('daily-relief-system'));
        $heroVideo    = is_file(public_path('videos/day-in-motion.mp4'))
            ? asset('videos/day-in-motion.mp4')
            : null;

        // Database disabled — home view uses hardcoded products & testimonials
        // $products = Product::with('translations')
        //     ->active()
        //     ->featured()
        //     ->orderBy('sort_order')
        //     ->get();
        //
        // $testimonials = Testimonial::where('is_active', true)
        //     ->where('is_featured', true)
        //     ->orderBy('sort_order')
        //     ->limit(3)
        //     ->get();

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

        return $mapped[0] ?? null;
    }
}
