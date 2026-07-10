<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Products that require an explicit size/variant before add-to-cart
    |--------------------------------------------------------------------------
    */
    'requires_size' => [
        'handles' => [
            'dainely-belt',
            'dainely-comfort-belt',
            'dainely-belt-2-b',
            'dainely-belt-2-c',
        ],
        'title_patterns' => [
            'dainely belt',
            'dainely comfort belt',
            'ceinture dainely',
            'dainely gurtel',
            'dainely gürtel',
        ],
    ],
];
