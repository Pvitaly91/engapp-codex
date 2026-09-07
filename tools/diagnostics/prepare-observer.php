<?php

if (PHP_SAPI !== 'cli') {
    exit(1);
}
require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$key = config('app.key');
if (! is_string($key) || $key === '' || config('database.connections.mysql.host') !== 'localhost') {
    throw new RuntimeException('Refusing unsafe/missing local configuration.');
}
$directory = $app->storagePath('app/seo-m2-local');
if (! is_dir($directory)) mkdir($directory, 0700, true);
file_put_contents($directory.'/observer-control.json', json_encode([
    'nonce' => bin2hex(random_bytes(24)), 'expected_key_digest' => hash('sha256', $key),
], JSON_THROW_ON_ERROR), LOCK_EX);
echo json_encode(['key_present' => true, 'sapi' => PHP_SAPI, 'php_binary' => PHP_BINARY,
    'php_ini' => php_ini_loaded_file(), 'environment_file' => $app->environmentFilePath(),
    'config_cache' => $app->getCachedConfigPath(), 'config_cached' => $app->configurationIsCached()]);
