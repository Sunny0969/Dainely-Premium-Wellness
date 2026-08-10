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

    protected function render(string $locale, string $slug, string $view)
    {
        $catalog = ContentCatalog::educationBySlug($slug);
        $educationId = (int) ($catalog['id'] ?? 0);
        $product = $this->featuredProduct();
        $relatedLinks = $educationId
            ? $this->related->for('education', $educationId, $locale)
            : collect();
        $breadcrumbs = $this->breadcrumbs->forEducation($locale, $slug);

        if ($educationId > 0) {
            app(\App\Services\AnalyticsEventService::class)->track('education_view', [
                'education_id' => $educationId,
                'slug' => $slug,
                'title' => $catalog['title'] ?? $slug,
                'content_type' => 'education',
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        }

        $edu = $educationId > 0 ? EducationPage::findCatalog($educationId) : null;
        $pageBlocks = collect();
        $faqItems = collect();
        if ($edu && SupabaseDb::available()) {
            $cms = Cache::remember(
                \App\Support\StorefrontCache::educationCmsKey($educationId, $locale),
                \App\Support\StorefrontCache::cmsTtlSeconds(),
                function () use ($edu, $locale) {
                    return [
                        'pageBlocks' => $edu->pageBlocks($locale)->where('visible', true)->values()->all(),
                        'faqItems' => $edu->faqs($locale, true)->values()->all(),
                    ];
                }
            );
            $pageBlocks = collect($cms['pageBlocks'] ?? []);
            $faqItems = collect($cms['faqItems'] ?? []);
        }

        return view($view, compact('locale', 'product', 'relatedLinks', 'breadcrumbs', 'pageBlocks', 'faqItems'));
    }

    public function backPain(string $locale)
    {
        return $this->render($locale, 'back-pain', 'education.back-pain');
    }

    public function sciatica(string $locale)
    {
        return $this->render($locale, 'sciatica', 'education.sciatica');
    }

    public function posture(string $locale)
    {
        return $this->render($locale, 'posture', 'education.posture');
    }

    public function neckPain(string $locale)
    {
        return $this->render($locale, 'neck-pain', 'education.neck-pain');
    }

    public function mobility(string $locale)
    {
        return $this->render($locale, 'mobility', 'education.mobility');
    }

    public function recovery(string $locale)
    {
        return $this->render($locale, 'recovery', 'education.recovery');
    }
}
