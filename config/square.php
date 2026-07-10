<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Square Payment Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Square Payments API integration.
    | All sensitive values should be stored in .env
    |
    */

    'application_id' => env('SQUARE_APPLICATION_ID', ''),
    'access_token'   => env('SQUARE_ACCESS_TOKEN', ''),
    'location_id'    => env('SQUARE_LOCATION_ID', ''),
    'environment'    => env('SQUARE_ENVIRONMENT', 'sandbox'), // 'sandbox' or 'production'
    'webhook_signature_key' => env('SQUARE_WEBHOOK_SIGNATURE_KEY', ''),
    'verify_ssl'     => env('SQUARE_VERIFY_SSL', true),

    // Square merchant account processes charges in this currency (USD only for DMEDE).
    'charge_currency' => strtoupper(env('SQUARE_CHARGE_CURRENCY', 'USD')),

    // Square API base URLs
    'api_url' => env('SQUARE_ENVIRONMENT', 'sandbox') === 'production'
        ? 'https://connect.squareup.com'
        : 'https://connect.squareupsandbox.com',
];
