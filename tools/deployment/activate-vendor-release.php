<?php

// Activate the bundled dependencies without running Composer on the hosting server.
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('CLI only.');
}
if (PHP_VERSION_ID < 80500) {
    fwrite(STDERR, "Run this script with PHP 8.5 CLI.\n");
    exit(1);
}

$root = dirname(__DIR__, 2);
chdir($root);
require $root.'/vendor/autoload.php';

foreach (['bootstrap/cache', 'storage/framework/views', 'storage/framework/cache/data', 'storage/framework/sessions', 'storage/logs'] as $directory) {
    $path = $root.'/'.$directory;
    if (!is_dir($path) && !mkdir($path, 0775, true) && !is_dir($path)) {
        fwrite(STDERR, "Cannot create runtime directory: $directory\n");
        exit(1);
    }
}

// Old package/config caches can refer to providers absent from the new vendor.
// Remove them before booting Laravel; never touch application data or .env.
$cacheDirectory = $root.'/bootstrap/cache';
$cacheFiles = array_merge(
    ['config.php', 'packages.php', 'services.php', 'events.php'],
    array_map('basename', glob($cacheDirectory.'/routes*.php') ?: [])
);

foreach (array_unique($cacheFiles) as $name) {
    $path = $cacheDirectory.'/'.$name;
    if (is_file($path) && !unlink($path)) {
        fwrite(STDERR, "Cannot remove generated cache: bootstrap/cache/$name\n");
        exit(1);
    }
}

$app = require $root.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
foreach (['package:discover', 'view:clear'] as $command) {
    $status = $kernel->call($command, ['--no-interaction' => true]);
    echo $kernel->output();
    if ($status !== 0) {
        exit($status);
    }
}

echo 'Active PHP: '.PHP_VERSION.PHP_EOL;
foreach (['laravel/framework', 'livewire/livewire'] as $package) {
    echo $package.': '.Composer\InstalledVersions::getPrettyVersion($package).PHP_EOL;
}
