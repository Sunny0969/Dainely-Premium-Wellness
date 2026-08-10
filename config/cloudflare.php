<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cloudflare / edge HTML caching
    |--------------------------------------------------------------------------
    |
    | When enabled, eligible storefront routes send CDN-Cache-Control headers so
    | Cloudflare can edge-cache HTML (blog, education, static CMS pages).
    | Checkout, cart, products, and admin must never be full-page cached.
    |
    */
    'edge_cache_enabled' => filter_var(env('CLOUDFLARE_EDGE_CACHE', true), FILTER_VALIDATE_BOOLEAN),

    // Edge TTL for cacheable HTML (seconds). Cloudflare respects CDN-Cache-Control.
    'html_edge_ttl' => (int) env('CLOUDFLARE_HTML_EDGE_TTL', 300),

    // Browser TTL for cacheable HTML (keep low so cart badge / flashes stay fresh).
    'html_browser_ttl' => (int) env('CLOUDFLARE_HTML_BROWSER_TTL', 0),

    // Long TTL for hashed Vite assets under /build (also set Cache Rules in Cloudflare).
    'assets_edge_ttl' => (int) env('CLOUDFLARE_ASSETS_EDGE_TTL', 31536000),
];
