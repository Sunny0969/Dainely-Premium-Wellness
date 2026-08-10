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
        'CAD' => [
            'symbol'    => 'CA$',
            'name'      => 'Canadian Dollar',
            'locale'    => 'en_CA',
            'decimals'  => 2,
        ],
        'AUD' => [
            'symbol'    => 'AU$',
            'name'      => 'Australian Dollar',
            'locale'    => 'en_AU',
            'decimals'  => 2,
        ],
        'NZD' => [
            'symbol'    => 'NZ$',
            'name'      => 'New Zealand Dollar',
            'locale'    => 'en_NZ',
            'decimals'  => 2,
        ],
        'SEK' => [
            'symbol'    => 'kr',
            'name'      => 'Swedish Krona',
            'locale'    => 'sv_SE',
            'decimals'  => 2,
        ],
        'NOK' => [
            'symbol'    => 'kr',
            'name'      => 'Norwegian Krone',
            'locale'    => 'nb_NO',
            'decimals'  => 2,
        ],
        'DKK' => [
            'symbol'    => 'kr',
            'name'      => 'Danish Krone',
            'locale'    => 'da_DK',
            'decimals'  => 2,
        ],
        'PLN' => [
            'symbol'    => 'zł',
            'name'      => 'Polish Złoty',
            'locale'    => 'pl_PL',
            'decimals'  => 2,
        ],
        'ZAR' => [
            'symbol'    => 'R',
            'name'      => 'South African Rand',
            'locale'    => 'en_ZA',
            'decimals'  => 2,
        ],
    ],

    // Currency mapped to locale (when no geo country is available)
    'locale_currency' => [
        'en' => 'USD',
        'fr' => 'EUR',
        'de' => 'EUR',
    ],
];
