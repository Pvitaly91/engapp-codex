<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Route Prefix
    |--------------------------------------------------------------------------
    |
    | The URL prefix for the Artisan Manager routes.
    | Default: 'admin/artisan'
    |
    */
    'route_prefix' => 'admin/artisan',

    /*
    |--------------------------------------------------------------------------
    | Middleware
    |--------------------------------------------------------------------------
    |
    | Middleware to apply to all Artisan Manager routes.
    | Make sure to use appropriate authentication middleware.
    |
    */
    'middleware' => ['web', 'auth.admin'],

    /*
    |--------------------------------------------------------------------------
    | Enabled Commands
    |--------------------------------------------------------------------------
    |
    | List of commands that are available in the interface.
    | You can disable commands by removing them from this array.
    |
    */
    'enabled_commands' => [
        'cache_clear',
        'config_clear',
        'route_clear',
        'view_clear',
        'clear_compiled',
        'optimize',
        'optimize_clear',
        'storage_link',
        'queue_restart',
    ],
];
