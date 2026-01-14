<?php

return [
    'enabled' => env('COMING_SOON_ENABLED', false),

    'route_names' => [
        'catalog.tests-cards',
        'test.show',
    ],

    'route_name_prefixes' => [
        // 'test.',
    ],

    'path_prefixes' => [
        '/catalog/tests-cards',
        '/test',
    ],

    'retry_after' => 86400,
];
