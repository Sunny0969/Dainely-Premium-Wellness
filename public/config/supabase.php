<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Supabase Configuration
    |--------------------------------------------------------------------------
    |
    | Project: Dainely
    | Project ID: fewrygnbuizfebcwlvjz
    | Region: us-west-2
    |
    | When disabled (or pdo_pgsql missing on the host), the storefront uses
    | Shopify-only catalog data and skips Phase 2 CMS / AI tables.
    |
    */

    'enabled' => filter_var(env('FEATURES_SUPABASE', env('SUPABASE_DB_ENABLED', true)), FILTER_VALIDATE_BOOLEAN),

    'url'             => env('SUPABASE_URL', 'https://fewrygnbuizfebcwlvjz.supabase.co'),
    'publishable_key' => env('SUPABASE_PUBLISHABLE_KEY'),
    'secret_key'      => env('SUPABASE_SECRET_KEY'),
    'jwks_url'        => env('SUPABASE_JWKS_URL', 'https://fewrygnbuizfebcwlvjz.supabase.co/auth/v1/.well-known/jwks.json'),

    'db' => [
        'host'     => env('DB_SUPABASE_HOST', env('DB_HOST', 'db.fewrygnbuizfebcwlvjz.supabase.co')),
        'port'     => env('DB_SUPABASE_PORT', env('DB_PORT', 5432)),
        'database' => env('DB_SUPABASE_DATABASE', env('DB_DATABASE', 'postgres')),
        'username' => env('DB_SUPABASE_USERNAME', env('DB_USERNAME', 'postgres')),
        'password' => env('DB_SUPABASE_PASSWORD', env('DB_PASSWORD')),
        'sslmode'  => env('DB_SUPABASE_SSLMODE', 'require'),
    ],

];
