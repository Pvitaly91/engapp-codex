<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Coming Soon Mode
    |--------------------------------------------------------------------------
    |
    | When enabled, requests to the routes or path prefixes listed below will
    | show the "Coming Soon" page instead of the actual content.
    |
    */

    'enabled' => env('COMING_SOON_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Routes Under Development
    |--------------------------------------------------------------------------
    |
    | List of route names that should show the Coming Soon page.
    | Example: ['pricing.index', 'features.show']
    |
    */

    'routes' => [
        // Add route names here, e.g.:
        // 'pricing.index',
        // 'features.show',
    ],

    /*
    |--------------------------------------------------------------------------
    | Path Prefixes Under Development
    |--------------------------------------------------------------------------
    |
    | List of URL path prefixes that should show the Coming Soon page.
    | Example: ['/pricing', '/features']
    |
    */

    'prefixes' => [
        // Add path prefixes here, e.g.:
        // '/pricing',
        // '/features',
        '/catalog/tests-cards',
        '/test/',
    ],

    /*
    |--------------------------------------------------------------------------
    | Retry-After Header
    |--------------------------------------------------------------------------
    |
    | Number of seconds to include in the Retry-After header.
    | Default is 86400 (24 hours).
    |
    */

    'retry_after' => 86400,

];
