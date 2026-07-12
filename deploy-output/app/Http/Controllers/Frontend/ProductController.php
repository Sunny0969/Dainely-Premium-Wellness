<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Supabase\Product as SupabaseProduct;
use App\Models\Supabase\ProductKnowledgeSignal;
use App\Services\ProductTranslationService;
use App\Services\ShopifyService;
use App\Services\ReviewService;
use App\Services\CurrencyService;
use App\Support\ProductSlugResolver;
use App\Support\SupabaseDb;

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

    public function show(string $locale, string $slug, \App\Services\JsonLdBuilder $jsonLdBuilder)
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

        // ── Phase 2 §5.2 + §6: locale-scoped content (optional Supabase overlay) ──
        $dbContent = null;
        $faqs = collect();
        $blocks = collect();
        $faqItems = collect();

        $dbProduct = SupabaseDb::run(
            fn () => SupabaseProduct::where('handle', $productHandle)->first(),
            null
        );

        if ($dbProduct) {
            $dbContent = SupabaseDb::run(
                fn () => $dbProduct->productContents()->forLocale($locale)->first()
                    ?? $dbProduct->productContents()->forLocale('en')->first(),
                null
            );

            $faqs = SupabaseDb::run(
                fn () => $dbProduct->faqs()
                    ->approved()
                    ->forLocale($locale)
                    ->orderBy('sort_order')
                    ->get(),
                collect()
            );

            foreach ($faqs as $faq) {
                if ($faq->question && $faq->answer) {
                    $faqItems->push((object) [
                        'question' => $faq->question,
                        'answer'   => $faq->answer,
                    ]);
                }
            }

            $signals = SupabaseDb::run(
                fn () => ProductKnowledgeSignal::query()
                    ->where('product_id', $dbProduct->id)
                    ->forLocale($locale)
                    ->approved()
                    ->get(),
                collect()
            );

            foreach ($signals as $signal) {
                $faqItems->push((object) [
                    'question' => $signal->question,
                    'answer'   => $signal->answer,
                ]);
            }

            $blocks = SupabaseDb::run(
                fn () => $dbProduct->pageBlocks()
                    ->forLocale($locale)
                    ->visible()
                    ->orderBy('sort_order')
                    ->get(),
                collect()
            );

            if ($dbContent) {
                if (! empty($dbContent->overview)) {
                    $product['body_html'] = $dbContent->overview;
                }
                if (! empty($dbContent->seo_title)) {
                    $product['seo_title'] = $dbContent->seo_title;
                }
                if (! empty($dbContent->seo_description)) {
                    $product['seo_description'] = $dbContent->seo_description;
                }
                $product['localized_content'] = [
                    'overview'        => $dbContent->overview,
                    'benefits'        => $dbContent->benefits,
                    'how_it_works'    => $dbContent->how_it_works,
                    'who_is_it_for'   => $dbContent->who_is_it_for,
                    'specifications'  => $dbContent->specifications,
                    'care'            => $dbContent->care,
                    'seo_title'       => $dbContent->seo_title,
                    'seo_description' => $dbContent->seo_description,
                    'canonical_url'   => $dbContent->canonical_url,
                ];
            }
        } else {
            // In-memory stand-in for JSON-LD (no DB required)
            $dbProduct = new SupabaseProduct([
                'title'  => $product['title'] ?? '',
                'handle' => $productHandle,
                'price'  => $product['price'] ?? ($product['variants'][0]['price'] ?? null),
                'status' => 'active',
                'featured_image' => $product['images'][0]['src'] ?? ($product['image']['src'] ?? null),
                'sku' => $product['variants'][0]['sku'] ?? null,
            ]);
        }

        // §6.2–6.3 JSON-LD @graph (cached 24h)
        $jsonLd = $jsonLdBuilder->buildForProduct($dbProduct, $locale);
        $productJsonLd = json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return view('products.show', [
            'product'       => $product,
            'reviews'       => [],
            'reviewStats'   => $reviewStats,
            'productJsonLd' => $productJsonLd,
            'dbContent'     => $dbContent,
            'faqs'          => $faqs,
            'faqItems'      => $faqItems,
            'pageBlocks'    => $blocks,
        ]);
    }
}
