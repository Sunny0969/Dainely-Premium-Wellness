<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Fallback tax rates (development only)
    |--------------------------------------------------------------------------
    |
    | Used when Shopify draftOrderCalculate is unavailable (missing API scope).
    | Set SHOPIFY_TAX_FALLBACK=true in .env for local testing ONLY.
    | Production must use Shopify Tax via draftOrderCalculate.
    |
    */

    'US' => [
        'default' => 0.07,
        'states'  => [
            'NY' => 0.08875,
            'CA' => 0.0725,
            'TX' => 0.0625,
            'FL' => 0.06,
            'NJ' => 0.06625,
        ],
    ],
    'FR' => ['default' => 0.20],
    'DE' => ['default' => 0.19],
    'GB' => ['default' => 0.20],
    'CA' => ['default' => 0.13],
    'AU' => ['default' => 0.10],
    'NL' => ['default' => 0.21],
    'SE' => ['default' => 0.25],
    'NO' => ['default' => 0.25],
    'DK' => ['default' => 0.25],
    'PL' => ['default' => 0.23],
    'NZ' => ['default' => 0.15],
    'ZA' => ['default' => 0.15],
];
