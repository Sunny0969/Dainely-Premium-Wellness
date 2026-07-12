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
    */

    'url'             => env('SUPABASE_URL', 'https://fewrygnbuizfebcwlvjz.supabase.co'),
    'publishable_key' => env('SUPABASE_PUBLISHABLE_KEY'),
    'secret_key'      => env('SUPABASE_SECRET_KEY'),
    'jwks_url'        => env('SUPABASE_JWKS_URL', 'https://fewrygnbuizfebcwlvjz.supabase.co/auth/v1/.well-known/jwks.json'),

    'db' => [
        'host'     => env('DB_HOST', 'db.fewrygnbuizfebcwlvjz.supabase.co'),
        'port'     => env('DB_PORT', 5432),
        'database' => env('DB_DATABASE', 'postgres'),
        'username' => env('DB_USERNAME', 'postgres'),
        'password' => env('DB_PASSWORD'),
        'sslmode'  => 'require',
    ],

];
