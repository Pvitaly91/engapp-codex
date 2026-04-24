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
    'contentops_ci_status' => [
        'enabled' => env('DEPLOYMENT_CONTENTOPS_CI_STATUS_ENABLED', true),
        'provider' => env('DEPLOYMENT_CONTENTOPS_CI_STATUS_PROVIDER', 'github_actions'),
        'workflow_file' => env('DEPLOYMENT_CONTENTOPS_CI_STATUS_WORKFLOW_FILE', 'contentops-release-gate.yml'),
        'workflow_name' => env('DEPLOYMENT_CONTENTOPS_CI_STATUS_WORKFLOW_NAME', 'ContentOps Release Gate'),
        'required_for_deploy' => env('DEPLOYMENT_CONTENTOPS_CI_STATUS_REQUIRED_FOR_DEPLOY', false),
        'cache_ttl_seconds' => env('DEPLOYMENT_CONTENTOPS_CI_STATUS_CACHE_TTL_SECONDS', 0),
        'max_age_minutes' => env('DEPLOYMENT_CONTENTOPS_CI_STATUS_MAX_AGE_MINUTES', 1440),
        'require_exact_sha' => env('DEPLOYMENT_CONTENTOPS_CI_STATUS_REQUIRE_EXACT_SHA', true),
        'allow_in_progress' => env('DEPLOYMENT_CONTENTOPS_CI_STATUS_ALLOW_IN_PROGRESS', false),
        'accepted_conclusions' => ['success'],
        'default_base_ref' => env('DEPLOYMENT_CONTENTOPS_CI_STATUS_DEFAULT_BASE_REF', 'origin/main'),
        'dispatch_with_release_check' => env('DEPLOYMENT_CONTENTOPS_CI_DISPATCH_WITH_RELEASE_CHECK', true),
        'dispatch_strict' => env('DEPLOYMENT_CONTENTOPS_CI_DISPATCH_STRICT', true),
    ],
];
