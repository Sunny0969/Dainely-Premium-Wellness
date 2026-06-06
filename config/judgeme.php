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

    // Cache TTL in seconds (default 1 hour)
    'cache_ttl'   => (int) env('JUDGEME_CACHE_TTL', 3600),

    // SSL verification (disable on Windows local dev if cURL error 60)
    'verify_ssl'  => filter_var(
        env('JUDGEME_VERIFY_SSL', env('APP_ENV', 'local') === 'production'),
        FILTER_VALIDATE_BOOLEAN
    ),
];
