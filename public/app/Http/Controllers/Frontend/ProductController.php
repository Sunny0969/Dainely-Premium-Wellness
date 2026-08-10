<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Supabase\Product as SupabaseProduct;
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
                \App\Support\ProductVisibility::filterShopifyProducts(
                    $this->shopify->mapProductsForDisplay($result['products'])
                ),
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

        if (\App\Support\ProductVisibility::isHandleHidden($handle)) {
            abort(404);
        }

        $shopifyResult = $this->shopify->fetchProductByHandle($handle);

        if (! $shopifyResult['success'] || empty($shopifyResult['product'])) {
            abort(404);
        }

        $product = $this->productTranslations->apply($shopifyResult['product'], $locale);

        // Cached stats only (no blocking Judge.me call). Client prefetches + updates hero.
        $productHandle = $product['handle'] ?? $handle;
        $reviewStats   = $this->reviews->getCachedStats($productHandle);

        if (($reviewStats['total_reviews'] ?? 0) === 0) {
            $warmHandle = $productHandle;
            dispatch(function () use ($warmHandle) {
                try {
                    app(ReviewService::class)->getProductReviews($warmHandle, 100);
                } catch (\Throwable $e) {
                    logger()->warning('Deferred review cache warm failed: '.$e->getMessage());
                }
            })->afterResponse();
        }

        // ── Phase 2 overlay: one eager Supabase round-trip (cached 10 min) ──
        $overlay = $this->loadPdpOverlay($productHandle, $locale);
        $dbContent = null;
        $faqs = collect();
        $blocks = collect();
        $faqItems = collect();
        $dbProduct = null;

        if ($overlay['found'] ?? false) {
            /** @var SupabaseProduct $dbProduct */
            $dbProduct = $overlay['product'];
            $dbContent = $overlay['content'];
            $blocks = $overlay['blocks'];

            if ($dbContent) {
                $filled = static fn ($value): bool => is_string($value) && trim($value) !== '';

                // Only override Shopify/lang defaults when Admin overlay fields are filled
                if ($filled($dbContent->overview)) {
                    $product['body_html'] = $dbContent->overview;
                }
                if ($filled($dbContent->seo_title)) {
                    $product['seo_title'] = trim($dbContent->seo_title);
                }
                if ($filled($dbContent->seo_description)) {
                    $product['seo_description'] = trim($dbContent->seo_description);
                }
                if ($filled($dbContent->canonical_url)) {
                    $product['canonical_url'] = trim($dbContent->canonical_url);
                }
                $product['localized_content'] = [
                    'overview'        => $filled($dbContent->overview) ? $dbContent->overview : null,
                    'benefits'        => $filled($dbContent->benefits) ? $dbContent->benefits : null,
                    'how_it_works'    => $filled($dbContent->how_it_works) ? $dbContent->how_it_works : null,
                    'who_is_it_for'   => $filled($dbContent->who_is_it_for) ? $dbContent->who_is_it_for : null,
                    'specifications'  => $filled($dbContent->specifications) ? $dbContent->specifications : null,
                    'care'            => $filled($dbContent->care) ? $dbContent->care : null,
                    'seo_title'       => $filled($dbContent->seo_title) ? trim($dbContent->seo_title) : null,
                    'seo_description' => $filled($dbContent->seo_description) ? trim($dbContent->seo_description) : null,
                    'canonical_url'   => $filled($dbContent->canonical_url) ? trim($dbContent->canonical_url) : null,
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

        // §6.2–6.3 JSON-LD @graph (cached 24h) — prefer locale CMS, else EN for schema only
        $schemaContent = $dbContent ?? ($overlay['content_en'] ?? null);
        $productJsonLd = $jsonLdBuilder->buildProductSchema($dbProduct, $schemaContent);

        $relatedLinks = collect();
        $breadcrumbs = app(\App\Services\BreadcrumbBuilder::class)->forProduct(
            $locale,
            $product['title'] ?? $dbProduct->title,
            route('products.show', ['locale' => $locale, 'slug' => $productHandle])
        );

        if ($dbProduct && $dbProduct->id) {
            $relatedLinks = app(\App\Services\RelatedContentResolver::class)
                ->for('product', (int) $dbProduct->id, $locale);

            $analyticsPayload = [
                'product_id' => $dbProduct->id,
                'handle' => $productHandle,
                'title' => $product['title'] ?? $dbProduct->title,
                'price' => $dbProduct->price,
                'content_type' => 'product',
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'country_code' => session('country_code') ?? session('geo_country'),
            ];
            $activityContext = [
                'handle' => $productHandle,
                'title'  => $product['title'] ?? '',
            ];
            $productId = (int) $dbProduct->id;

            // Keep conversion tracking, but do not block TTFB on remote writes.
            dispatch(function () use ($analyticsPayload, $activityContext, $productId) {
                try {
                    app(\App\Services\AnalyticsEventService::class)->track('product_view', $analyticsPayload);
                    app(\App\Services\AnalyticsService::class)->logActivity(
                        'view_product',
                        SupabaseProduct::class,
                        $productId,
                        $activityContext
                    );
                } catch (\Throwable $e) {
                    logger()->warning('Deferred product analytics failed: '.$e->getMessage());
                }
            })->afterResponse();
        }

        // Locale-aware FAQs: CMS for this language, else auto-translate from English.
        $faqs = app(\App\Services\FaqLocalizationService::class)->resolveForProduct(
            $productHandle,
            $locale,
            collect($overlay['faqs'] ?? []),
            collect($overlay['faqs_en'] ?? [])
        );

        foreach ($faqs as $faq) {
            $faqItems->push((object) [
                'question' => $faq->question,
                'answer'   => $faq->answer,
            ]);
        }

        foreach (collect($overlay['signals'] ?? []) as $signal) {
            $question = trim((string) ($signal->question ?? ''));
            $answer = trim((string) ($signal->answer ?? ''));
            if ($question !== '' && $answer !== '') {
                $faqItems->push((object) [
                    'question' => $question,
                    'answer'   => $answer,
                ]);
            }
        }

        // Admin EN page blocks → auto-translate for FR/DE on the storefront.
        $blocks = app(\App\Services\PageBlockLocalizationService::class)->forLocale(
            collect($overlay['blocks'] ?? []),
            $locale
        );

        return view('products.show', [
            'product'       => $product,
            'reviews'       => [],
            'reviewStats'   => $reviewStats,
            'productJsonLd' => $productJsonLd,
            'dbContent'     => $dbContent,
            'faqs'          => $faqs,
            'faqItems'      => $faqItems,
            'pageBlocks'    => $blocks,
            'relatedLinks'  => $relatedLinks,
            'breadcrumbs'   => $breadcrumbs,
            'overlay'       => $overlay,
        ]);
    }

    /**
     * Single eager Supabase fetch for PDP overlays (content, FAQs, signals, blocks).
     *
     * @return array{found:bool,product?:SupabaseProduct,content?:mixed,faqs?:\Illuminate\Support\Collection,signals?:\Illuminate\Support\Collection,blocks?:\Illuminate\Support\Collection}
     */
    protected function loadPdpOverlay(string $handle, string $locale): array
    {
        $cacheKey = \App\Support\StorefrontCache::pdpOverlayKey($handle, $locale);
        $ttl = \App\Support\StorefrontCache::cmsTtlSeconds();

        try {
            $cached = \Illuminate\Support\Facades\Cache::get($cacheKey);
            // Never trust a previously cached miss / connection failure.
            if (is_array($cached) && ($cached['found'] ?? false) === true) {
                return $cached;
            }

            $fresh = $this->fetchPdpOverlay($handle, $locale);

            // Only cache successful overlays — a transient Supabase blip must not
            // hide page blocks / FAQs on FR/DE for the full CMS TTL.
            if (($fresh['found'] ?? false) === true) {
                \Illuminate\Support\Facades\Cache::put($cacheKey, $fresh, $ttl);
            }

            return $fresh;
        } catch (\Throwable $e) {
            logger()->warning('PDP overlay cache failed; fetching fresh', ['error' => $e->getMessage()]);

            return $this->fetchPdpOverlay($handle, $locale);
        }
    }

    /**
     * @return array{found:bool,product?:SupabaseProduct,content?:mixed,faqs?:\Illuminate\Support\Collection,signals?:\Illuminate\Support\Collection,blocks?:\Illuminate\Support\Collection}
     */
    protected function fetchPdpOverlay(string $handle, string $locale): array
    {
        return SupabaseDb::run(function () use ($handle, $locale) {
            $dbProduct = SupabaseProduct::query()
                ->where('handle', $handle)
                ->with([
                    'productContents',
                    'faqs' => fn ($q) => $q->approved()->orderBy('sort_order'),
                    'knowledgeSignals' => fn ($q) => $q->approved(),
                    'pageBlocks' => fn ($q) => $q->visible()->orderBy('sort_order'),
                ])
                ->first();

            if (! $dbProduct) {
                return ['found' => false];
            }

            \App\Support\StorefrontCache::rememberProductHandle((int) $dbProduct->id, (string) $dbProduct->handle);

            $pickLocale = static function ($items) use ($locale) {
                $matched = $items->where('locale', $locale)->values();

                return $matched->isNotEmpty()
                    ? $matched
                    : $items->where('locale', 'en')->values();
            };

            $toBlockDto = static function ($block): object {
                return (object) [
                    'id'         => (int) ($block->id ?? 0),
                    'locale'     => (string) ($block->locale ?? 'en'),
                    'block_type' => (string) ($block->block_type ?? ''),
                    'title'      => (string) ($block->title ?? ''),
                    'content'    => (string) ($block->content ?? ''),
                    'sort_order' => (int) ($block->sort_order ?? 0),
                    'visible'    => (bool) ($block->visible ?? true),
                ];
            };

            $dbContent = $dbProduct->productContents->firstWhere('locale', $locale);
            // Keep EN row only as translation source — do not display EN CMS on FR/DE pages.
            $dbContentEn = $dbProduct->productContents->firstWhere('locale', 'en');

            // FAQs: keep locale rows separate from English (no silent EN fallback for display).
            $faqsForLocale = $dbProduct->faqs->where('locale', $locale)->values();
            $faqsEn = $dbProduct->faqs->where('locale', 'en')->values();

            // Page blocks: admin writes English only — always use EN rows as source.
            $blocksEn = $dbProduct->pageBlocks->where('locale', 'en')->values();
            if ($blocksEn->isEmpty()) {
                // Legacy: older FR/DE-only rows — fall back so nothing disappears.
                $blocksEn = $pickLocale($dbProduct->pageBlocks);
            }

            return [
                'found' => true,
                'product' => $dbProduct,
                'content' => $dbContent,
                'content_en' => $dbContentEn,
                'faqs' => $faqsForLocale,
                'faqs_en' => $faqsEn,
                'signals' => $pickLocale($dbProduct->knowledgeSignals),
                // Plain DTOs — safe to cache; Eloquent models often poison FR/DE entries.
                'blocks' => $blocksEn->map($toBlockDto)->values(),
            ];
        }, ['found' => false]);
    }
}
