<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Route Prefix
    |--------------------------------------------------------------------------
    |
    | This is the URI prefix for all language manager routes.
    |
    */
    'route_prefix' => 'admin/languages',

    /*
    |--------------------------------------------------------------------------
    | Route Name Prefix
    |--------------------------------------------------------------------------
    |
    | This is the name prefix for all language manager routes.
    |
    */
    'route_name_prefix' => 'language-manager.',

    /*
    |--------------------------------------------------------------------------
    | Middleware
    |--------------------------------------------------------------------------
    |
    | Middleware to apply to all language manager routes.
    |
    */
    'middleware' => ['web', 'auth.admin'],

    /*
    |--------------------------------------------------------------------------
    | Default Language Code
    |--------------------------------------------------------------------------
    |
    | Fallback language code if no default is set in the database.
    |
    */
    'fallback_locale' => 'uk',

    /*
    |--------------------------------------------------------------------------
    | URL Prefix Mode
    |--------------------------------------------------------------------------
    |
    | When true, non-default languages will have URL prefix (e.g., /en/about)
    | The default language will be at root (e.g., /about)
    |
    */
    'use_url_prefix' => true,

    /*
    |--------------------------------------------------------------------------
    | Store Locale In
    |--------------------------------------------------------------------------
    |
    | Where to store the selected locale: 'session', 'cookie', or 'both'
    |
    */
    'store_locale_in' => 'both',

    /*
    |--------------------------------------------------------------------------
    | Cookie Lifetime
    |--------------------------------------------------------------------------
    |
    | How long (in minutes) to keep the locale cookie.
    | Default is 1 year (525600 minutes)
    |
    */
    'cookie_lifetime' => 525600,
];
