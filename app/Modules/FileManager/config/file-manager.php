<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Root Path
    |--------------------------------------------------------------------------
    |
    | The root path from which the file manager will display files.
    | By default, it uses the base_path() which is the document root.
    |
    */
    'root_path' => base_path(),

    /*
    |--------------------------------------------------------------------------
    | Route Prefix
    |--------------------------------------------------------------------------
    |
    | The prefix for all file manager routes.
    |
    */
    'route_prefix' => 'admin/file-manager',

    /*
    |--------------------------------------------------------------------------
    | Middleware
    |--------------------------------------------------------------------------
    |
    | Middleware to apply to file manager routes.
    |
    */
    'middleware' => ['web', 'auth.admin'],

    /*
    |--------------------------------------------------------------------------
    | Hidden Paths
    |--------------------------------------------------------------------------
    |
    | Paths that should be hidden from the file manager.
    | These paths are relative to the root_path.
    |
    */
    'hidden_paths' => [
        '.git',
        '.env',
        '.env.example',
        'storage/framework',
        'storage/logs',
        'node_modules',
        'vendor',
    ],

    /*
    |--------------------------------------------------------------------------
    | Max File Size for Preview
    |--------------------------------------------------------------------------
    |
    | Maximum file size in bytes for file content preview.
    | Files larger than this will not display content preview.
    |
    */
    'max_preview_size' => 1024 * 1024, // 1MB

    /*
    |--------------------------------------------------------------------------
    | Allowed Extensions for Preview
    |--------------------------------------------------------------------------
    |
    | File extensions that are allowed to be previewed.
    |
    */
    'preview_extensions' => [
        'txt', 'php', 'js', 'json', 'xml', 'html', 'css', 'md',
        'yml', 'yaml', 'ini', 'log', 'sql', 'sh', 'vue', 'blade.php',
    ],
];
