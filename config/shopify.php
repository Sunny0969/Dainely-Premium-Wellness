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

    'store_domain'    => env('SHOPIFY_SHOP_DOMAIN', env('SHOPIFY_STORE_DOMAIN', 'dmede-usa.myshopify.com')),
    'access_token'    => env('SHOPIFY_ADMIN_ACCESS_TOKEN', env('SHOPIFY_ACCESS_TOKEN', '')),
    'client_id'       => env('SHOPIFY_KEY', env('SHOPIFY_CLIENT_ID', '')),
    'client_secret'   => env('SHOPIFY_SECRET', env('SHOPIFY_CLIENT_SECRET', '')),
    'webhook_secret'  => env('SHOPIFY_WEBHOOK_SECRET', ''),
    'api_version'     => env('SHOPIFY_API_VERSION', '2024-01'),
    'scopes'          => env('SHOPIFY_SCOPES', 'read_products,write_products'),
    'redirect_uri'    => env('SHOPIFY_REDIRECT_URI', rtrim(env('APP_URL', 'http://localhost:8000'), '/') . '/shopify/callback'),

    // When no shpat_ token: read catalog from public /products.json (display only)
    'use_storefront_catalog' => filter_var(env('SHOPIFY_USE_STOREFRONT_CATALOG', true), FILTER_VALIDATE_BOOLEAN),

    // Local Windows dev: set false if cURL error 60 (SSL). Production should stay true.
    'verify_ssl' => filter_var(
        env('SHOPIFY_VERIFY_SSL', env('APP_ENV', 'local') === 'production'),
        FILTER_VALIDATE_BOOLEAN
    ),

    // Shopify Admin API base URL
    'api_url' => 'https://' . env('SHOPIFY_SHOP_DOMAIN', env('SHOPIFY_STORE_DOMAIN', 'dmede-usa.myshopify.com'))
        . '/admin/api/' . env('SHOPIFY_API_VERSION', '2024-01'),
];
