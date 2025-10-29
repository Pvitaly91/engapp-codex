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
];
