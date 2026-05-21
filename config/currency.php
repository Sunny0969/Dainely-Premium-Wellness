<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Multi-Currency Configuration
    |--------------------------------------------------------------------------
    */

    'base_currency' => env('BASE_CURRENCY', 'USD'),

    'open_exchange_rates' => [
        'app_id'   => env('OPEN_EXCHANGE_RATES_APP_ID', ''),
        'api_url'  => 'https://openexchangerates.org/api/latest.json',
        'cache_ttl' => 3600, // 1 hour in seconds
    ],

    // Supported currencies with display settings
    'supported' => [
        'USD' => [
            'symbol'    => '$',
            'name'      => 'US Dollar',
            'locale'    => 'en_US',
            'decimals'  => 2,
        ],
        'EUR' => [
            'symbol'    => '€',
            'name'      => 'Euro',
            'locale'    => 'fr_FR',
            'decimals'  => 2,
        ],
        'GBP' => [
            'symbol'    => '£',
            'name'      => 'British Pound',
            'locale'    => 'en_GB',
            'decimals'  => 2,
        ],
    ],

    // Currency mapped to locale
    'locale_currency' => [
        'en' => 'USD',
        'fr' => 'EUR',
        'de' => 'EUR',
    ],
];
