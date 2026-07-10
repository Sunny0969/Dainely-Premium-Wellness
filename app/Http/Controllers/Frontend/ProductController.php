<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\ProductTranslationService;
use App\Services\ShopifyService;
use App\Services\ReviewService;
use App\Services\CurrencyService;
use App\Support\ProductSlugResolver;

class ProductController extends Controller
{
    public function __construct(
        protected ShopifyService $shopify,
        protected ReviewService $reviews,
        protected CurrencyService $currency,
        protected ProductTranslationService $productTranslations,
    ) {}

    public function index(string $locale)
    {
        $result = $this->shopify->fetchProducts(50);
        $products = $result['success']
            ? $this->productTranslations->applyMany(
                $this->shopify->mapProductsForDisplay($result['products']),
                $locale
            )
            : [];

        $error = $result['success'] ? null : ($result['error'] ?? 'Could not load products from Shopify.');

        // --- Search / Filter (client-side params, server-side filtering) ---
        $q = trim((string) request()->query('q', ''));
        $minPrice = request()->query('min_price');
        $maxPrice = request()->query('max_price');
        $sort = request()->query('sort', '');

        $min = $minPrice !== null && $minPrice !== '' ? (float) $minPrice : null;
        $max = $maxPrice !== null && $maxPrice !== '' ? (float) $maxPrice : null;

        if (!empty($q)) {
            $needle = mb_strtolower($q);
            $products = array_values(array_filter($products, function (array $p) use ($needle) {
                return isset($p['title']) && mb_strpos(mb_strtolower((string) $p['title']), $needle) !== false;
            }));
        }

        if ($min !== null || $max !== null) {
            $products = array_values(array_filter($products, function (array $p) use ($min, $max) {
                $price = isset($p['price']) ? (float) $p['price'] : 0.0;
                if ($min !== null && $price < $min) {
                    return false;
                }
                if ($max !== null && $price > $max) {
                    return false;
                }
                return true;
            }));
        }

        if (!empty($sort)) {
            usort($products, function (array $a, array $b) use ($sort) {
                switch ($sort) {
                    case 'price_desc':
                        $pa = isset($a['price']) ? (float) $a['price'] : 0.0;
                        $pb = isset($b['price']) ? (float) $b['price'] : 0.0;
                        return $pb <=> $pa;
                    case 'price_asc':
                        $pa = isset($a['price']) ? (float) $a['price'] : 0.0;
                        $pb = isset($b['price']) ? (float) $b['price'] : 0.0;
                        return $pa <=> $pb;
                    case 'title_asc':
                        $ta = isset($a['title']) ? (string) $a['title'] : '';
                        $tb = isset($b['title']) ? (string) $b['title'] : '';
                        return strcasecmp($ta, $tb);
                    case 'title_desc':
                        $ta = isset($a['title']) ? (string) $a['title'] : '';
                        $tb = isset($b['title']) ? (string) $b['title'] : '';
                        return strcasecmp($tb, $ta);
                    default:
                        return 0;
                }
            });
        }

        $reviewHandles = array_values(array_unique(array_filter(array_map(
            fn (array $p) => ProductSlugResolver::resolveHandle((string) ($p['handle'] ?? '')),
            $products
        ))));
        $reviewStatsByHandle = $this->reviews->getCachedStatsForHandles($reviewHandles);

        $freeShippingLabel = __('products.free_shipping', [
            'amount' => $this->currency->formatForLocale($this->currency->freeShippingThresholdUsd(), $locale),
        ]);

        return view('pages.products.index', compact('products', 'locale', 'error', 'reviewStatsByHandle', 'freeShippingLabel'))
            ->with('filters', [
                'q' => $q,
                'min_price' => $minPrice,
                'max_price' => $maxPrice,
                'sort' => $sort,
            ]);
    }

    public function show(string $locale, string $slug)
    {
        $handle = ProductSlugResolver::resolveHandle($slug);
        $shopifyResult = $this->shopify->fetchProductByHandle($handle);

        if (! $shopifyResult['success'] || empty($shopifyResult['product'])) {
            abort(404);
        }

        $product = $this->productTranslations->apply($shopifyResult['product'], $locale);

        // Cached stats only (no blocking API calls). Reviews load via AJAX.
        $productHandle = $product['handle'] ?? $handle;
        $reviewStats   = $this->reviews->getCachedStats($productHandle);

        return view('products.show', [
            'product'     => $product,
            'reviews'     => [],
            'reviewStats' => $reviewStats,
        ]);
    }
}
