<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Sendlane Email / CRM Configuration
    |--------------------------------------------------------------------------
    */

    'api_key'    => env('SENDLANE_API_KEY', ''),
    'api_secret' => env('SENDLANE_API_SECRET', ''),
    'subdomain'  => env('SENDLANE_SUBDOMAIN', ''),
    'api_url'    => 'https://api.sendlane.com/v2/',

    // List IDs per locale (set in .env or here after setup)
    'lists' => [
        'en' => env('SENDLANE_LIST_EN', ''),
        'fr' => env('SENDLANE_LIST_FR', ''),
        'de' => env('SENDLANE_LIST_DE', ''),
    ],
];
