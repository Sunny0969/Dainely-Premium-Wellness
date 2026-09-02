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
            $cms = Cache::remember(
                \App\Support\StorefrontCache::educationCmsKey($page->id, $locale),
                \App\Support\StorefrontCache::cmsTtlSeconds(),
                function () use ($page, $locale) {
                    return [
                        'pageBlocks' => $page->pageBlocks()->where('locale', $locale)->where('visible', true)->get()->values()->all(),
                        'faqItems' => $page->faqs()->where('locale', $locale)->approved()->get()->values()->all(),
                    ];
                }
            );
            $pageBlocks = collect($cms['pageBlocks'] ?? []);
            $faqItems = collect($cms['faqItems'] ?? []);
        }

        return view('education.show', compact('locale', 'page', 'product', 'relatedLinks', 'breadcrumbs', 'pageBlocks', 'faqItems', 'relatedProducts'));
    }
}
