<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Postal / ZIP code rules by country
    |--------------------------------------------------------------------------
    |
    | patterns — PCRE regex (without delimiters) used server- and client-side.
    | uppercase_on_validate — normalize to uppercase before matching (UK, CA, IE, NL).
    |
    */
    'uppercase_on_validate' => ['GB', 'UK', 'CA', 'IE', 'NL'],

    /*
    | Countries where pre-filling Square CardOptions.postalCode breaks validation (US ZIP rules).
    */
    'square_skip_prefill_countries' => ['AU', 'NZ'],

    /*
    | Countries where postal codes include letters. Square US-ZIP card fields strip
    | letters from configure(), so never pre-fill these into the Square card form.
    */
    'square_alphanumeric_postal_countries' => ['GB', 'UK', 'CA', 'IE', 'NL'],

    'patterns' => [
        'US' => '^\d{5}(-\d{4})?$',
        'GB' => '^[A-Z]{1,2}\d[A-Z\d]? ?\d[A-Z]{2}$',
        'UK' => '^[A-Z]{1,2}\d[A-Z\d]? ?\d[A-Z]{2}$',
        'CA' => '^[A-Z]\d[A-Z] ?\d[A-Z]\d$',
        'IE' => '^[A-Z0-9]{3} ?[A-Z0-9]{4}$',
        'NL' => '^\d{4} ?[A-Z]{2}$',
        'PT' => '^\d{4}-\d{3}$|^\d{7}$',
        'PL' => '^\d{2}-\d{3}$',
        'SE' => '^\d{3} ?\d{2}$|^\d{5}$',
        'AU' => '^\d{4}$',
        'NZ' => '^\d{4}$',
        'FR' => '^\d{5}$',
        'DE' => '^\d{5}$',
        'ES' => '^\d{5}$',
        'IT' => '^\d{5}$',
        'BE' => '^\d{4}$',
        'AT' => '^\d{4}$',
        'CH' => '^\d{4}$',
        'NO' => '^\d{4}$',
        'DK' => '^\d{4}$',
        'ZA' => '^\d{4}$',
    ],

    'square_sandbox_numeric_zip' => '11111',

    'placeholders' => [
        'US' => '10001',
        'GB' => 'SW1A 1AA',
        'CA' => 'K1A 0B1',
        'AU' => '2000',
        'FR' => '75001',
        'DE' => '10115',
        'NL' => '1012 AB',
        'BE' => '1000',
        'ES' => '28001',
        'IT' => '00118',
        'SE' => '111 22',
        'NO' => '0150',
        'DK' => '1050',
        'CH' => '8001',
        'AT' => '1010',
        'PL' => '00-001',
        'PT' => '1000-001',
        'IE' => 'D02 X285',
        'NZ' => '1010',
        'ZA' => '2000',
    ],
];
