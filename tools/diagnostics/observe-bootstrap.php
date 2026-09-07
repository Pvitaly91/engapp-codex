<?php

// Temporary opt-in instrumentation, not an endpoint. See tools/diagnostics/README.md.
return static function (\Illuminate\Foundation\Application $app): void {
    $directory = $app->storagePath('app/seo-m2-local');
    $control = $directory.'/observer-control.json';
    if (PHP_SAPI === 'cli' || ! in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true)
        || ($_SERVER['HTTP_HOST'] ?? '') !== 'gramlyze.loc' || ! is_file($control)) {
        return;
    }
    $settings = json_decode(file_get_contents($control), true);
    if (! is_array($settings) || ! hash_equals($settings['nonce'] ?? '', $_SERVER['HTTP_X_GRAMLYZE_PROBE'] ?? '')) {
        return;
    }
    $record = [
        'id' => substr(preg_replace('/[^a-zA-Z0-9_-]/', '', $_SERVER['HTTP_X_GRAMLYZE_PROBE_ID'] ?? ''), 0, 80),
        'at' => gmdate('c'), 'pid' => getmypid(), 'sapi' => PHP_SAPI, 'php_version' => PHP_VERSION,
        'php_binary' => PHP_BINARY, 'php_zts' => PHP_ZTS, 'php_ini' => php_ini_loaded_file(),
        'base_path' => $app->basePath(), 'variables_order' => ini_get('variables_order'),
        'opcache_enabled' => ini_get('opcache.enable'), 'stages' => [],
    ];
    $observe = static function (string $stage) use ($app, &$record, $settings): void {
        $key = $app->bound('config') ? $app['config']->get('app.key') : null;
        $bytes = is_string($key) && str_starts_with($key, 'base64:') ? base64_decode(substr($key, 7), true) : $key;
        $record['stages'][] = [
            'stage' => $stage, 'time' => microtime(true),
            'environment_file' => $app->environmentFilePath(), 'cache_path' => $app->getCachedConfigPath(),
            'cache_exists' => is_file($app->getCachedConfigPath()),
            'env_key_present' => ! empty($_ENV['APP_KEY']), 'server_key_present' => ! empty($_SERVER['APP_KEY']),
            'getenv_key_present' => (getenv('APP_KEY') !== false && getenv('APP_KEY') !== ''),
            'config_key_present' => is_string($key) && $key !== '',
            'config_key_valid' => is_string($bytes) && \Illuminate\Encryption\Encrypter::supported($bytes, 'AES-256-CBC'),
            'config_key_matches_expected' => is_string($key) && hash_equals($settings['expected_key_digest'], hash('sha256', $key)),
        ];
    };
    $observe('before_environment');
    $app->afterBootstrapping(\Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables::class, static fn () => $observe('after_environment'));
    $app->afterBootstrapping(\Illuminate\Foundation\Bootstrap\LoadConfiguration::class, static fn () => $observe('after_configuration'));
    register_shutdown_function(static function () use (&$record, $directory, $observe): void {
        $observe('shutdown');
        $record['status'] = http_response_code();
        file_put_contents($directory.'/bootstrap-observations.jsonl', json_encode($record, JSON_UNESCAPED_SLASHES)."\n", FILE_APPEND | LOCK_EX);
    });
};
