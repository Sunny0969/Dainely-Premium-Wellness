<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Judge.me Reviews Integration
    |--------------------------------------------------------------------------
    |
    | Configuration for the Judge.me review platform API.
    | Reviews are fetched server-side and cached to minimize API calls.
    |
    */

    'api_token'   => env('JUDGEME_API_TOKEN', ''),
    'shop_domain' => env('JUDGEME_SHOP_DOMAIN', 'ididit555.myshopify.com'),

    // Cache TTL in seconds (default 24 hours — warm via `php artisan reviews:warm-cache`)
    'cache_ttl'   => (int) env('JUDGEME_CACHE_TTL', 86400),

    // SSL verification (disable on Windows local dev if cURL error 60)
    'verify_ssl'  => filter_var(
        env('JUDGEME_VERIFY_SSL', env('APP_ENV', 'local') === 'production'),
        FILTER_VALIDATE_BOOLEAN
    ),

    /*
    | Use Judge.me /reviews/count (+ widgets) for totals instead of counting
    | the small page-1 sample. Without this, storefront shows ~dozens, not 10K+.
    */
    'use_count_api' => filter_var(env('JUDGEME_USE_COUNT_API', true), FILTER_VALIDATE_BOOLEAN),

    // Fallback store-wide totals when a product group has no mapped Judge.me IDs
    'use_shop_totals_fallback' => filter_var(env('JUDGEME_SHOP_TOTALS_FALLBACK', true), FILTER_VALIDATE_BOOLEAN),
];
