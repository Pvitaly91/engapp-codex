<?php

return [
    // Route prefix for file manager
    'route_prefix' => 'admin/file-manager',

    // Base path to browse (relative to Laravel base path)
    // Leave empty to browse from project root
    'base_path' => '',

    // Directories to exclude from browsing
    'excluded_directories' => [
        'node_modules',
        'vendor',
        'storage/framework',
        'storage/logs',
        '.git',
        '.idea',
        'nbproject',
    ],

    // File extensions to exclude
    'excluded_extensions' => [
        // Add file extensions to hide, e.g., '.log', '.cache'
    ],

    // Maximum file size to display content (in bytes)
    'max_file_display_size' => 1048576, // 1MB

    // Enable file download
    'allow_download' => true,

    // Enable file content preview
    'allow_preview' => true,

    // Enable in-browser editing for writable text files
    'allow_edit' => true,
];
