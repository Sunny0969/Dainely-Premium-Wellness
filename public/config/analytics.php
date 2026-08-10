<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Phase 2 §11 — Analytics & conversion tracking
    |--------------------------------------------------------------------------
    */

    'enabled' => filter_var(env('ANALYTICS_ENABLED', true), FILTER_VALIDATE_BOOLEAN),

    /** Skip noisy/generic events from GA4 MP + Meta CAPI (still stored in DB). */
    'export_events' => [
        'product_view',
        'add_to_cart',
        'begin_checkout',
        'purchase',
        'landing_page_view',
        'education_view',
        'newsletter_signup',
        'contact_form',
    ],

    'ga4' => [
        'measurement_id' => env('GA4_MEASUREMENT_ID'),
        'api_secret' => env('GA4_API_SECRET'),
        'endpoint' => env('GA4_MP_ENDPOINT', 'https://www.google-analytics.com/mp/collect'),
    ],

    'meta' => [
        'pixel_id' => env('META_PIXEL_ID'),
        'access_token' => env('META_CAPI_ACCESS_TOKEN'),
        'api_version' => env('META_CAPI_API_VERSION', 'v21.0'),
        'test_event_code' => env('META_TEST_EVENT_CODE'),
    ],
];
