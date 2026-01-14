<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Coming Soon Feature Enabled
    |--------------------------------------------------------------------------
    |
    | This determines whether the Coming Soon middleware is active.
    | When enabled, requests matching the configured routes or path prefixes
    | will display a "Coming Soon" page instead of the actual content.
    |
    */

    'enabled' => env('COMING_SOON_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Protected Routes
    |--------------------------------------------------------------------------
    |
    | List of route names that should display the Coming Soon page.
    | Example: ['pricing.index', 'features.show']
    |
    | Note: Admin users (with admin_authenticated session) can still access these routes.
    |
    */

    'routes' => [
        'catalog.tests-cards',
        'test.show',
        'test.step',
        'test.step-input',
        'test.step-manual',
        'test.step-select',
        'test.select',
        'test.input',
        'test.manual',
    ],

    /*
    |--------------------------------------------------------------------------
    | Protected Path Prefixes
    |--------------------------------------------------------------------------
    |
    | List of URL path prefixes that should display the Coming Soon page.
    | Example: ['/pricing', '/features']
    | Note: These are matched against the path without locale prefix
    |
    */

    'path_prefixes' => [
        // Add path prefixes here, e.g., '/pricing', '/features'
    ],

    /*
    |--------------------------------------------------------------------------
    | Retry-After Header
    |--------------------------------------------------------------------------
    |
    | Number of seconds to include in the Retry-After header (optional).
    | This tells clients when they should check back. Default is 24 hours.
    |
    */

    'retry_after' => env('COMING_SOON_RETRY_AFTER', 86400),
];
