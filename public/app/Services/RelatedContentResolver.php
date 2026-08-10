<?php

namespace App\Services;

use App\Models\Supabase\LandingPage;
use App\Models\Supabase\Product;
use App\Models\Supabase\RelatedContent;
use App\Support\ContentCatalog;
use App\Support\SupabaseDb;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Phase 2 §7.2 — resolve related_content rows into locale-aware display links.
 */
class RelatedContentResolver
{
    /**
     * @return Collection<int, array{title:string,url:string,type_label:string,type:string}>
     */
    public function for(string $sourceType, int $sourceId, string $locale): Collection
    {
        if ($sourceId < 1 || ! SupabaseDb::available()) {
            return collect();
        }

        $cacheKey = \App\Support\StorefrontCache::relatedKey($sourceType, $sourceId, $locale);

        try {
            $cached = Cache::remember($cacheKey, \App\Support\StorefrontCache::relatedTtlSeconds(), function () use ($sourceType, $sourceId, $locale) {
                return $this->resolveFresh($sourceType, $sourceId, $locale)->all();
            });

            return collect(is_array($cached) ? $cached : []);
        } catch (\Throwable $e) {
            return $this->resolveFresh($sourceType, $sourceId, $locale);
        }
    }

    /**
     * @return Collection<int, array{title:string,url:string,type_label:string,type:string}>
     */
    protected function resolveFresh(string $sourceType, int $sourceId, string $locale): Collection
    {
        $rows = SupabaseDb::run(
            fn () => RelatedContent::query()
                ->where('source_type', $sourceType)
                ->where('source_id', $sourceId)
                ->orderBy('display_order')
                ->get(),
            collect()
        );

        if ($rows->isEmpty()) {
            return collect();
        }

        $productIds = $rows->where('related_type', 'product')->pluck('related_id')->map(fn ($id) => (int) $id)->unique()->values();
        $landingIds = $rows->where('related_type', 'landing_page')->pluck('related_id')->map(fn ($id) => (int) $id)->unique()->values();

        $productsById = $productIds->isEmpty()
            ? collect()
            : SupabaseDb::run(
                fn () => Product::with('productContents')->whereIn('id', $productIds)->get()->keyBy('id'),
                collect()
            );

        $landingsById = $landingIds->isEmpty()
            ? collect()
            : SupabaseDb::run(
                fn () => LandingPage::query()->whereIn('id', $landingIds)->where('published', true)->get()->keyBy('id'),
                collect()
            );

        return $rows->map(function ($rel) use ($locale, $productsById, $landingsById) {
            return match ($rel->related_type) {
                'product' => $this->productLinkFromMap((int) $rel->related_id, $locale, $productsById),
                'landing_page' => $this->landingLinkFromMap((int) $rel->related_id, $locale, $landingsById),
                'education' => $this->educationLink((int) $rel->related_id, $locale),
                'blog' => $this->blogLink((int) $rel->related_id, $locale),
                default => null,
            };
        })->filter()->values();
    }

    /**
     * @param  Collection<int, Product>  $productsById
     * @return array{title:string,url:string,type_label:string,type:string}|null
     */
    protected function productLinkFromMap(int $id, string $locale, Collection $productsById): ?array
    {
        $product = $productsById->get($id);
        if (! $product || empty($product->handle)) {
            return null;
        }

        return [
            'title' => $product->getTranslatedTitle($locale),
            'url' => route('products.show', ['locale' => $locale, 'slug' => $product->handle]),
            'type_label' => __('content_types.product'),
            'type' => 'product',
        ];
    }

    /**
     * @param  Collection<int, LandingPage>  $landingsById
     * @return array{title:string,url:string,type_label:string,type:string}|null
     */
    protected function landingLinkFromMap(int $id, string $locale, Collection $landingsById): ?array
    {
        $exact = $landingsById->get($id);
        if (! $exact) {
            return null;
        }

        $page = $exact;
        if ($exact->locale !== $locale) {
            $localized = SupabaseDb::run(
                fn () => LandingPage::query()
                    ->where('slug', $exact->slug)
                    ->where('locale', $locale)
                    ->where('published', true)
                    ->first(),
                null
            );
            $page = $localized ?: $exact;
        }

        return [
            'title' => $page->getTranslatedTitle($locale),
            'url' => route('landing.show', ['locale' => $locale, 'slug' => $page->slug]),
            'type_label' => __('content_types.landing_page'),
            'type' => 'landing_page',
        ];
    }

    protected function educationLink(int $id, string $locale): ?array
    {
        $page = ContentCatalog::educationById($id);
        if (! $page) {
            return null;
        }

        return [
            'title' => ContentCatalog::educationTitle($page, $locale),
            'url' => route($page['route'], ['locale' => $locale]),
            'type_label' => __('content_types.education'),
            'type' => 'education',
        ];
    }

    protected function blogLink(int $id, string $locale): ?array
    {
        $post = ContentCatalog::blogById($id);
        if (! $post) {
            return null;
        }

        return [
            'title' => $post['title'],
            'url' => route('blog.show', ['locale' => $locale, 'slug' => $post['slug']]),
            'type_label' => __('content_types.blog'),
            'type' => 'blog',
        ];
    }
}
