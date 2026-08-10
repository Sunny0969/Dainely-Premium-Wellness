<?php

return [
    /*
    |--------------------------------------------------------------------------
    | IP Geolocation → Locale & currency
    |--------------------------------------------------------------------------
    |
    | country_locale — first-visit redirect to /fr or /de (others stay on /en).
    | country_currency — display currency from detected country (EN + Eurozone/UK).
    |
    */

    'enabled' => filter_var(env('GEO_LOCALE_ENABLED', true), FILTER_VALIDATE_BOOLEAN),

    'cache_ttl' => (int) env('GEO_LOCALE_CACHE_TTL', 86400),

    // Local/testing only: force country without VPN (e.g. NL, GB, ES)
    'test_country' => env('GEO_TEST_COUNTRY'),

    'ipapi_key' => env('IPAPI_KEY'),

    'default_locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Locale redirect (only fr/de — English stays on /en/ for NL, ES, GR, GB…)
    |--------------------------------------------------------------------------
    */
    'country_locale' => [
        'FR' => 'fr',
        'BE' => 'fr',
        'LU' => 'fr',
        'MC' => 'fr',
        'DE' => 'de',
        'AT' => 'de',
        'CH' => 'de',
        'LI' => 'de',
    ],

    /*
    |--------------------------------------------------------------------------
    | Display currency by country (used for /en/ and as fallback context)
    |--------------------------------------------------------------------------
    */
    'eurozone_countries' => [
        'AT', 'BE', 'CY', 'EE', 'FI', 'FR', 'DE', 'GR', 'IE', 'IT',
        'LV', 'LT', 'LU', 'MT', 'NL', 'PT', 'SK', 'SI', 'ES',
        // CH priced in EUR for this storefront (see QA)
        'CH',
    ],

    'country_currency' => [
        'US' => 'USD',
        'GB' => 'GBP',
        'UK' => 'GBP',
        'CA' => 'CAD',
        'AU' => 'AUD',
        'NZ' => 'NZD',
        'SE' => 'SEK',
        'NO' => 'NOK',
        'DK' => 'DKK',
        'PL' => 'PLN',
        'ZA' => 'ZAR',
        // Eurozone (explicit entries; eurozone_countries merged at runtime in service)
        'NL' => 'EUR',
        'ES' => 'EUR',
        'GR' => 'EUR',
        'FR' => 'EUR',
        'DE' => 'EUR',
        'BE' => 'EUR',
        'IT' => 'EUR',
        'PT' => 'EUR',
        'IE' => 'EUR',
        'AT' => 'EUR',
        'FI' => 'EUR',
        'LU' => 'EUR',
        'MT' => 'EUR',
        'CY' => 'EUR',
        'EE' => 'EUR',
        'LV' => 'EUR',
        'LT' => 'EUR',
        'SK' => 'EUR',
        'SI' => 'EUR',
        'CH' => 'EUR',
        'MC' => 'EUR',
        'AD' => 'EUR',
        'SM' => 'EUR',
        'VA' => 'EUR',
        'ME' => 'EUR',
        'XK' => 'EUR',
    ],
];
