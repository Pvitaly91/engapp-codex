<?php

return [
    'catalog_max_tests' => max(20, (int) env('CATALOG_MAX_TESTS', 100)),

    'tech_info_enabled' => (bool) env('TEST_TECH_INFO_ENABLED', true),
    'compose_shuffle_enabled' => (bool) env('TEST_COMPOSE_SHUFFLE_ENABLED', env('APP_ENV') !== 'testing'),
    'compose_shuffle_seed' => env('TEST_COMPOSE_SHUFFLE_SEED'),
];
