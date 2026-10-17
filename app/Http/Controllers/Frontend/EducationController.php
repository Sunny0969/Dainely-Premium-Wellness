<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Catalog\EducationPage;
use App\Services\BreadcrumbBuilder;
use App\Services\RelatedContentResolver;
use App\Services\ShopifyService;
use App\Support\ContentCatalog;
use App\Support\SupabaseDb;
use Illuminate\Support\Facades\Cache;

class EducationController extends Controller
{
    public function __construct(
        protected ShopifyService $shopify,
        protected RelatedContentResolver $related,
        protected BreadcrumbBuilder $breadcrumbs,
    ) {}

    protected function featuredProduct(): ?object
    {
        return Cache::remember('featured_shopify_product_v1', 15 * 60, function () {
            return $this->shopify->featuredProduct();
        });
    }



    public function index(string $locale)
    {
        // Cache the grouped pages to prevent DB hits on every index view
        $groupedPages = Cache::remember("education_index_pages_v1_{$locale}", 3600, function() use ($locale) {
            $pages = EducationPage::where('is_active', true)
                ->where('locale', $locale)
                ->whereNotNull('category')
                ->where('category', '!=', '')
                ->orderBy('created_at', 'desc')
                ->get();

            return $pages->groupBy('category');
        });

        if ($locale !== 'en') {
            $this->healIndexTranslations($locale, $groupedPages);
        }

        $breadcrumbs = [
            ['name' => __('products.breadcrumb_home'), 'url' => route('home', ['locale' => $locale])],
            ['name' => __('nav.education') !== 'nav.education' ? __('nav.education') : 'Education', 'url' => null],
        ];

        return view('education.index', compact('locale', 'groupedPages', 'breadcrumbs'));
    }

    public function show(string $locale, string $slug)
    {
        $page = EducationPage::where('slug', $slug)
            ->where('locale', $locale)
            ->where('is_active', true)
            ->first();
            
        // Fallback to English if translation doesn't exist yet
        if (!$page) {
            $page = EducationPage::where('slug', $slug)
                ->where('locale', 'en')
                ->where('is_active', true)
                ->firstOrFail();
        }
        
        $product = $this->featuredProduct();

        $this->healMissingTranslation($page, $locale);
        
        $relatedLinks = collect(); // Legacy support placeholder if needed
        $breadcrumbs = $this->breadcrumbs->forEducation($locale, $slug);
        
        $relatedProducts = collect();
        if (!empty($page->related_products) && is_array($page->related_products)) {
            $relatedProducts = \App\Models\Supabase\Product::whereIn('id', $page->related_products)->get();
        }
        
        app(\App\Services\AnalyticsEventService::class)->track('education_view', [
            'education_id' => $page->id,
            'slug' => $slug,
            'title' => $page->title ?? $slug,
            'content_type' => 'education',
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        $pageBlocks = collect();
        $faqItems = collect();

        if (SupabaseDb::available()) {
            $cmsKey = \App\Support\StorefrontCache::educationCmsKey($page->id, $locale);
            $cms = Cache::get($cmsKey);

            if (! is_array($cms)) {
                $cms = $this->loadEducationCms($page, $locale);

                // Missing locale rows must not punch a hole in the page: fall back to
                // the English copy and re-check within minutes, rather than hiding the
                // section until somebody remembers to sync.
                Cache::put(
                    $cmsKey,
                    $cms,
                    ! empty($cms['fallback']) ? 600 : \App\Support\StorefrontCache::cmsTtlSeconds()
                );
            }

            $pageBlocks = collect($cms['pageBlocks'] ?? []);
            $faqItems = collect($cms['faqItems'] ?? []);
        }

        return view('education.show', compact('locale', 'page', 'product', 'relatedLinks', 'breadcrumbs', 'pageBlocks', 'faqItems', 'relatedProducts'));
    }

    /**
     * @return array{pageBlocks: list<object>, faqItems: list<object>, fallback: bool}
     */
    protected function loadEducationCms(EducationPage $page, string $locale): array
    {
        $pageBlocks = $page->pageBlocks()->where('locale', $locale)->where('visible', true)->get()->values()->all();
        $faqItems = $page->faqs()->where('locale', $locale)->approved()->get()->values()->all();

        $fallback = false;

        if ($locale !== 'en') {
            if ($pageBlocks === []) {
                $pageBlocks = $page->pageBlocks()->where('locale', 'en')->where('visible', true)->get()->values()->all();
                $fallback = $pageBlocks !== [];
            }

            if ($faqItems === []) {
                $faqItems = $page->faqs()->where('locale', 'en')->approved()->get()->values()->all();
                $fallback = $fallback || $faqItems !== [];
            }
        }

        return ['pageBlocks' => $pageBlocks, 'faqItems' => $faqItems, 'fallback' => $fallback];
    }

    /**
     * Self-heal: if FR/DE content is missing or still English, re-run the
     * translation pipeline once (guarded so it never loops or stacks up).
     */
    protected function healMissingTranslation(object $page, string $locale): void
    {
        if ($locale === 'en' || ! SupabaseDb::available()) {
            return;
        }

        $englishPage = $page;

        if ((string) $page->locale !== 'en') {
            $englishPage = EducationPage::where('slug', $page->slug)
                ->where('locale', 'en')
                ->where('is_active', true)
                ->first();

            if (! $englishPage) {
                return;
            }

            if (! $this->stillReadsAsEnglish($page, $englishPage)) {
                return;
            }
        }

        if (! Cache::add('education.translation.heal.'.$locale.'.'.$page->id, 1, 600)) {
            return;
        }

        $englishId = (int) $englishPage->id;

        dispatch(function () use ($englishId) {
            try {
                @set_time_limit(300);

                $source = EducationPage::find($englishId);
                if ($source) {
                    app(\App\Http\Controllers\Admin\AdminEducationController::class)->syncTranslations($source);
                }
            } catch (\Throwable $e) {
                logger()->warning('Deferred education translation heal failed: '.$e->getMessage());
            }
        })->afterResponse();
    }

    /**
     * The index view only reads the locale rows (cached an hour), so a page whose
     * FR/DE copy was never written would sit in English until somebody pressed
     * sync. Spot the ones still matching English and fix them in the background —
     * a few per request, once per locale every 10 minutes.
     *
     * @param  \Illuminate\Support\Collection<string, mixed>  $groupedPages
     */
    protected function healIndexTranslations(string $locale, $groupedPages): void
    {
        if (! SupabaseDb::available()) {
            return;
        }

        if (! Cache::add('education.index.heal.'.$locale, 1, 600)) {
            return;
        }

        $pages = collect($groupedPages)->flatten(1)->values();
        if ($pages->isEmpty()) {
            return;
        }

        $englishBySlug = EducationPage::where('locale', 'en')
            ->whereIn('slug', $pages->pluck('slug')->all())
            ->get()
            ->keyBy('slug');

        if ($englishBySlug->isEmpty()) {
            return;
        }

        $staleSlugs = $pages
            ->filter(function ($page) use ($englishBySlug) {
                $english = $englishBySlug->get((string) $page->slug);

                return $english && $this->stillReadsAsEnglish($page, $english);
            })
            ->take(4)
            ->pluck('slug')
            ->all();

        if ($staleSlugs === []) {
            return;
        }

        dispatch(function () use ($staleSlugs) {
            try {
                @set_time_limit(300);

                $syncer = app(\App\Http\Controllers\Admin\AdminEducationController::class);

                foreach ($staleSlugs as $slug) {
                    $source = EducationPage::where('slug', $slug)
                        ->where('locale', 'en')
                        ->first();

                    if ($source) {
                        $syncer->syncTranslations($source);
                    }
                }
            } catch (\Throwable $e) {
                logger()->warning('Deferred education index heal failed: '.$e->getMessage());
            }
        })->afterResponse();
    }

    /**
     * A long prose title (or hero line) on a locale row that is still byte-for-byte
     * the English one means the translation pipeline never landed for that page.
     */
    protected function stillReadsAsEnglish(object $localePage, object $englishPage): bool
    {
        $title = trim(strip_tags((string) ($localePage->title ?? '')));
        $englishTitle = trim(strip_tags((string) ($englishPage->title ?? '')));

        if ($title !== '' && $title === $englishTitle && mb_strlen($title) >= 15) {
            return true;
        }

        $hero = trim(strip_tags((string) ($localePage->hero_description ?? '')));
        $englishHero = trim(strip_tags((string) ($englishPage->hero_description ?? '')));

        return $hero !== '' && $hero === $englishHero && mb_strlen($hero) >= 40;
    }
}
