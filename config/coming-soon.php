<?php

return [
    'enabled' => env('COMING_SOON_ENABLED', false),

    'route_names' => [
        'catalog.tests-cards',
        'test.show',
    ],

    'path_prefixes' => [
        // '/pricing',
        // '/features',
    ],

    'retry_after' => 86400,
];
