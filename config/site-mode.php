<?php

$csv = static fn (string $value): array => array_values(array_filter(array_map(
    static fn (string $item): string => trim($item),
    explode(',', $value)
)));

return [
    'production_domains' => $csv((string) env(
        'SITE_PRODUCTION_DOMAINS',
        'gramlyze.com,www.gramlyze.com,gramlyze.ub'
    )),

    'production_origin' => env('SITE_PRODUCTION_ORIGIN', 'https://gramlyze.com'),
    'development_origin' => env('SITE_DEVELOPMENT_ORIGIN', 'http://engapp-codex.loc'),

    'production_locales' => $csv((string) env('SITE_PRODUCTION_LOCALES', 'uk')),

    'development_features' => $csv((string) env(
        'SITE_DEV_FEATURES',
        'mode-inspector,experimental-ui'
    )),

    'response_cache' => [
        'enabled' => (bool) env('SITE_PRODUCTION_RESPONSE_CACHE', true),
        'ttl' => max(0, (int) env('SITE_PRODUCTION_RESPONSE_CACHE_TTL', 300)),
        'excluded_paths' => $csv((string) env(
            'SITE_PRODUCTION_CACHE_EXCEPT',
            'admin,admin/*,login,logout,test,test/*,api,api/*,health'
        )),
    ],

    'expose_mode_header' => (bool) env('SITE_MODE_HEADER', true),
];
