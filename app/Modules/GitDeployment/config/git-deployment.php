<?php

return [
    'preserve_paths' => [
        '.git',
        '.env',
        'storage',
        'vendor',
        'node_modules',
    ],
    'github' => [
        'owner' => env('DEPLOYMENT_GITHUB_OWNER'),
        'repo' => env('DEPLOYMENT_GITHUB_REPO'),
        'token' => env('DEPLOYMENT_GITHUB_TOKEN'),
        'user_agent' => env('DEPLOYMENT_GITHUB_USER_AGENT', 'EngappDeploymentBot/1.0'),
    ],
    'content_preview' => [
        'with_release_check' => env('DEPLOYMENT_CONTENT_PREVIEW_WITH_RELEASE_CHECK', true),
        'check_profile' => env('DEPLOYMENT_CONTENT_PREVIEW_CHECK_PROFILE', 'release'),
        'strict' => env('DEPLOYMENT_CONTENT_PREVIEW_STRICT', true),
    ],
    'content_apply' => [
        'enabled_by_default' => env('DEPLOYMENT_CONTENT_APPLY_ENABLED_BY_DEFAULT', true),
        'with_release_check' => env('DEPLOYMENT_CONTENT_APPLY_WITH_RELEASE_CHECK', true),
        'skip_release_check' => env('DEPLOYMENT_CONTENT_APPLY_SKIP_RELEASE_CHECK', false),
        'check_profile' => env('DEPLOYMENT_CONTENT_APPLY_CHECK_PROFILE', 'release'),
        'strict' => env('DEPLOYMENT_CONTENT_APPLY_STRICT', true),
    ],
];
