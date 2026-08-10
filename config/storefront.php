<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Storefront cache TTLs (seconds)
    |--------------------------------------------------------------------------
    |
    | Use CACHE_DRIVER=redis in production when Redis is available; file works
    | the same way for local/dev. Invalidate via Admin CMS / Shopify webhooks.
    |
    */

    // Supabase CMS overlays, landings, education blocks/FAQs
    'cms_cache_ttl' => (int) env('STOREFRONT_CMS_CACHE_TTL', 86400),

    // Shopify catalog + single-product handle cache (browse only; checkout can bypass)
    'shopify_cache_ttl' => (int) env('STOREFRONT_SHOPIFY_CACHE_TTL', env('SHOPIFY_PRODUCT_CACHE_TTL', 3600)),

    // Related content links on PDP / landings / education
    'related_cache_ttl' => (int) env('STOREFRONT_RELATED_CACHE_TTL', 86400),
];
