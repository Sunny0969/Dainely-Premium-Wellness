<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Shopify Integration Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the DMEDE.com Shopify store integration.
    | This store handles all fulfillment, inventory, and shipping.
    |
    */

    'store_domain'    => env('SHOPIFY_STORE_DOMAIN', 'dmede.myshopify.com'),
    'access_token'    => env('SHOPIFY_ACCESS_TOKEN', ''),
    'webhook_secret'  => env('SHOPIFY_WEBHOOK_SECRET', ''),
    'api_version'     => '2024-10',

    // Shopify Admin API base URL
    'api_url' => 'https://' . env('SHOPIFY_STORE_DOMAIN', 'dmede.myshopify.com') . '/admin/api/2024-10',
];
