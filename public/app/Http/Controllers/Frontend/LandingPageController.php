<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Supabase\LandingPage;
use App\Services\BreadcrumbBuilder;
use App\Services\BundleDisplayService;
use App\Services\JsonLdBuilder;
use App\Services\LandingOfferService;
use App\Services\RelatedContentResolver;
use App\Support\StorefrontCache;
use App\Support\SupabaseDb;
use Illuminate\Support\Facades\Cache;

class LandingPageController extends Controller
{
    public function show(
        string $locale,
        string $slug,
        RelatedContentResolver $related,
        BreadcrumbBuilder $breadcrumbs,
        JsonLdBuilder $jsonLdBuilder,
        LandingOfferService $offers,
        BundleDisplayService $bundleDisplay
    ) {
        $payload = Cache::remember(
            StorefrontCache::landingKey($slug, $locale),
            StorefrontCache::cmsTtlSeconds(),
            function () use ($slug, $locale) {
                return SupabaseDb::run(function () use ($slug, $locale) {
                    $page = LandingPage::query()
                        ->where('slug', $slug)
                        ->where('locale', $locale)
                        ->where('published', true)
                        ->first();

                    if (! $page) {
                        return null;
                    }

                    $blocks = $page->pageBlocks()
                        ->where('locale', $locale)
                        ->visible()
                        ->orderBy('sort_order')
                        ->get();

                    return [
                        'page' => $page,
                        'blocks' => $blocks,
                    ];
                }, null);
            }
        );

        if (! is_array($payload) || empty($payload['page'])) {
            abort(404);
        }

        /** @var LandingPage $page */
        $page = $payload['page'];
        $blocks = $payload['blocks'] ?? collect();

        $relatedLinks = $related->for('landing_page', (int) $page->id, $locale);
        $crumbTrail = $breadcrumbs->forLandingPage($page, $locale);
        $offer = $offers->resolve($page, $locale);
        $bundleViews = $bundleDisplay->mapForBlocks($blocks, $locale, $page->bundle_id ? (int) $page->bundle_id : null);

        $analyticsPayload = [
            'landing_page_id' => $page->id,
            'slug' => $page->slug,
            'title' => $page->title,
            'content_type' => 'landing_page',
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ];
        dispatch(function () use ($analyticsPayload) {
            try {
                app(\App\Services\AnalyticsEventService::class)->track('landing_page_view', $analyticsPayload);
            } catch (\Throwable $e) {
                logger()->warning('Deferred landing analytics failed: '.$e->getMessage());
            }
        })->afterResponse();

        $jsonLd = Cache::remember("landing_{$page->id}_{$locale}_v3", StorefrontCache::cmsTtlSeconds(), function () use ($jsonLdBuilder, $page, $locale, $breadcrumbs, $crumbTrail) {
            $schema = $jsonLdBuilder->buildForLandingPage($page, $locale);
            $url = route('landing.show', ['locale' => $locale, 'slug' => $page->slug]);

            if (isset($schema['@graph']) && is_array($schema['@graph'])) {
                foreach ($schema['@graph'] as $i => $node) {
                    if (($node['@type'] ?? null) === 'BreadcrumbList') {
                        $schema['@graph'][$i] = $breadcrumbs->toSchema($crumbTrail, $url);
                    }
                }
            }

            return $schema;
        });

        return view('landing.show', [
            'page'         => $page,
            'blocks'       => $blocks,
            'schemaJson'   => json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            'locale'       => $locale,
            'relatedLinks' => $relatedLinks,
            'breadcrumbs'  => $crumbTrail,
            'offer'        => $offer,
            'bundleViews'  => $bundleViews,
        ]);
    }

    /**
     * Phase 2 §8.4 — CTA checkout from landing offer (product or bundle).
     */
    public function checkout(string $locale, int $id, LandingOfferService $offers)
    {
        $page = LandingPage::query()
            ->where('id', $id)
            ->where('published', true)
            ->firstOrFail();

        return $offers->addOfferToCartAndRedirect($page, $locale);
    }
}
