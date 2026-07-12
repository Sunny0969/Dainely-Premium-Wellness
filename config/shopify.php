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

    // Shopify Storefront API (GraphQL)
    'storefront_domain'       => env('SHOPIFY_STOREFRONT_DOMAIN', env('SHOPIFY_SHOP_DOMAIN', 'dmede-usa.myshopify.com')),
    'storefront_access_token' => env('SHOPIFY_STOREFRONT_ACCESS_TOKEN', ''),
    'native_checkout'         => filter_var(env('SHOPIFY_NATIVE_CHECKOUT', true), FILTER_VALIDATE_BOOLEAN),
    'client_id'       => env('SHOPIFY_KEY', env('SHOPIFY_CLIENT_ID', '')),
    'client_secret'   => env('SHOPIFY_SECRET', env('SHOPIFY_CLIENT_SECRET', '')),
    'webhook_secret'  => env('SHOPIFY_WEBHOOK_SECRET', ''),
    'api_version'     => env('SHOPIFY_API_VERSION', '2024-01'),

    // When no Admin token: read catalog from public /products.json (fallback)
    'use_storefront_catalog' => filter_var(env('SHOPIFY_USE_STOREFRONT_CATALOG', true), FILTER_VALIDATE_BOOLEAN),

    // Local Windows dev: set false if cURL error 60 (SSL). Production should stay true.
    'verify_ssl' => filter_var(
        env('SHOPIFY_VERIFY_SSL', env('APP_ENV', 'local') === 'production'),
        FILTER_VALIDATE_BOOLEAN
    ),

    // Shopify Admin API base URL
    'api_url' => 'https://' . env('SHOPIFY_SHOP_DOMAIN', env('SHOPIFY_STORE_DOMAIN', 'dmede-usa.myshopify.com'))
        . '/admin/api/' . env('SHOPIFY_API_VERSION', '2024-01'),

    // Single product page cache (seconds)
    'product_cache_ttl' => (int) env('SHOPIFY_PRODUCT_CACHE_TTL', 900),

    // Discount / coupon code lookup cache (seconds)
    'discount_cache_ttl' => (int) env('SHOPIFY_DISCOUNT_CACHE_TTL', 300),

    // Push website checkout orders to Shopify Admin → Orders
    'sync_orders' => filter_var(env('SHOPIFY_SYNC_ORDERS', true), FILTER_VALIDATE_BOOLEAN),

    // Shopify Tax via draftOrderCalculate GraphQL
    'tax_enabled'    => filter_var(env('SHOPIFY_TAX_ENABLED', true), FILTER_VALIDATE_BOOLEAN),
    'tax_fallback'   => filter_var(env('SHOPIFY_TAX_FALLBACK', false), FILTER_VALIDATE_BOOLEAN),
    'tax_cache_ttl'  => (int) env('SHOPIFY_TAX_CACHE_TTL', 300),
    'shop_currency'  => env('SHOPIFY_SHOP_CURRENCY', 'USD'),

    // Tags applied to every website order in Shopify Admin
    'order_tags' => [
        'DainelyLab_Order',
        'Dainely_Order',
        'Square_Checkout',
        'Laravel_Order',
    ],
];
