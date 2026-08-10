<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

/**
 * Storefront multi-layer cache keys + invalidation.
 * Works with CACHE_DRIVER=file or redis (same Cache facade).
 */
class StorefrontCache
{
    public static function cmsTtlSeconds(): int
    {
        return max(60, (int) config('storefront.cms_cache_ttl', 86400));
    }

    public static function shopifyTtlSeconds(): int
    {
        return max(60, (int) config('storefront.shopify_cache_ttl', config('shopify.product_cache_ttl', 900)));
    }

    public static function relatedTtlSeconds(): int
    {
        return max(60, (int) config('storefront.related_cache_ttl', 86400));
    }

    public static function pdpOverlayKey(string $handle, string $locale): string
    {
        // v2: never cache failed overlays; blocks stored as plain DTOs (not Eloquent).
        return 'storefront.pdp_overlay.v2.'.strtolower(trim($handle)).'.'.$locale;
    }

    public static function landingKey(string $slug, string $locale): string
    {
        return 'storefront.landing.'.md5(strtolower(trim($slug)).'|'.$locale);
    }

    public static function educationCmsKey(int $educationId, string $locale): string
    {
        return "storefront.education.{$educationId}.{$locale}";
    }

    public static function relatedKey(string $sourceType, int $sourceId, string $locale): string
    {
        return "storefront.related.{$sourceType}.{$sourceId}.{$locale}";
    }

    public static function handleByIdKey(int $productId): string
    {
        return 'storefront.handle_by_id.'.$productId;
    }

    public static function rememberProductHandle(int $productId, string $handle): void
    {
        if ($productId < 1 || trim($handle) === '') {
            return;
        }
        Cache::put(self::handleByIdKey($productId), trim($handle), self::cmsTtlSeconds());
    }

    /**
     * Drop PDP overlay + JSON-LD + related for one product (all locales).
     */
    public static function forgetProduct(?string $handle, ?int $productId = null): void
    {
        $handle = is_string($handle) ? trim($handle) : '';

        if ($handle === '' && $productId) {
            $cached = Cache::get(self::handleByIdKey((int) $productId));
            if (is_string($cached) && trim($cached) !== '') {
                $handle = trim($cached);
            }
        }

        foreach (['en', 'fr', 'de'] as $locale) {
            if ($handle !== '') {
                Cache::forget(self::pdpOverlayKey($handle, $locale));
                // Legacy key (pre-v2) — may still hold poisoned found=false entries.
                Cache::forget('storefront.pdp_overlay.'.strtolower(trim($handle)).'.'.$locale);
                Cache::forget(self::relatedKey('product', (int) ($productId ?? 0), $locale));
            }
            if ($productId) {
                Cache::forget("product_{$productId}_{$locale}");
                Cache::forget("json_ld_product_{$productId}_{$locale}");
                Cache::forget(self::relatedKey('product', $productId, $locale));
                Cache::forget('product_tmp_'.md5($handle !== '' ? $handle : (string) $productId)."_{$locale}");
                Cache::forget("storefront.pdp_overlay.{$productId}.{$locale}");
                Cache::forget("storefront.pdp_overlay.v2.{$productId}.{$locale}");
            }
        }

        if ($handle !== '') {
            app(\App\Services\ShopifyService::class)->forgetCatalogCaches($handle);
        }

        ProductVisibility::forgetCache();
    }

    /**
     * Force-publish: clear every storefront cache for a product so FAQs show immediately.
     */
    public static function publishProductFaqs(int $productId, ?string $handle = null): void
    {
        if ($handle) {
            self::rememberProductHandle($productId, $handle);
        }
        self::forgetProduct($handle, $productId);

        // Also clear view compiled cache is not needed; data cache is enough.
        Cache::forget('admin.supabase_ping_fail');
        Cache::forget('supabase.tcp_fail');
    }

    /**
     * Invalidate storefront caches tied to an FAQ row's parent.
     *
     * @param  object{faqable_type?:string,faqable_id?:int,handle?:string}  $faq
     */
    public static function forgetForFaq(object $faq): void
    {
        $type = (string) ($faq->faqable_type ?? '');
        $id = (int) ($faq->faqable_id ?? 0);
        if ($id < 1) {
            return;
        }

        if ($type === \App\Models\Supabase\Product::class || str_ends_with($type, '\\Product')) {
            $handle = isset($faq->handle) && is_string($faq->handle) ? trim($faq->handle) : null;
            if (! $handle) {
                $cached = Cache::get(self::handleByIdKey($id));
                $handle = is_string($cached) ? $cached : null;
            }
            if (! $handle) {
                try {
                    $handle = SupabaseDb::run(
                        fn () => \App\Models\Supabase\Product::query()->where('id', $id)->value('handle'),
                        null
                    );
                } catch (\Throwable) {
                    $handle = null;
                }
            }
            if (is_string($handle) && $handle !== '') {
                self::rememberProductHandle($id, $handle);
            }
            self::forgetProduct(is_string($handle) ? $handle : null, $id);

            return;
        }

        if ($type === \App\Models\Supabase\LandingPage::class || str_contains($type, 'LandingPage')) {
            $slug = null;
            $locale = 'en';
            try {
                $row = SupabaseDb::run(
                    fn () => \App\Models\Supabase\LandingPage::query()->where('id', $id)->first(['slug', 'locale']),
                    null
                );
                if ($row) {
                    $slug = (string) $row->slug;
                    $locale = (string) ($row->locale ?: 'en');
                }
            } catch (\Throwable) {
                // ignore
            }
            self::forgetLanding($id, $locale, $slug);

            return;
        }

        if (str_contains($type, 'EducationPage') || $type === \App\Models\Catalog\EducationPage::class) {
            self::forgetEducation($id);
        }
    }

    public static function forgetLanding(int $id, string $locale, ?string $slug = null): void
    {
        Cache::forget("landing_{$id}_{$locale}");
        Cache::forget("landing_{$id}_{$locale}_v2");
        Cache::forget("landing_{$id}_{$locale}_v3");
        Cache::forget(self::relatedKey('landing_page', $id, $locale));

        if (is_string($slug) && $slug !== '') {
            Cache::forget(self::landingKey($slug, $locale));
            foreach (['en', 'fr', 'de'] as $loc) {
                Cache::forget(self::landingKey($slug, $loc));
            }
        }
    }

    public static function forgetEducation(int $educationId): void
    {
        foreach (['en', 'fr', 'de'] as $locale) {
            Cache::forget(self::educationCmsKey($educationId, $locale));
            Cache::forget(self::relatedKey('education', $educationId, $locale));
        }
    }

    public static function forgetRelated(string $sourceType, int $sourceId): void
    {
        foreach (['en', 'fr', 'de'] as $locale) {
            Cache::forget(self::relatedKey($sourceType, $sourceId, $locale));
        }
    }
}
