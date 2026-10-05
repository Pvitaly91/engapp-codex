<?php

if (PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows') { exit(1); }
$root = 'D:/DEV/htdocs/gramlyze.loc'; $source = dirname(__DIR__, 2);
require $root.'/vendor/autoload.php';
require_once $source.'/app/Services/M11LocalTargetGuard.php';
require_once $source.'/app/Services/PpcQualityLocalTargetGuard.php';
$app = require $root.'/bootstrap/app.php'; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$db = Illuminate\Support\Facades\DB::connection();
$physical = (array) $db->selectOne('SELECT DATABASE() db, @@hostname server, @@port port');
(new App\Services\PpcQualityLocalTargetGuard)->verify($db, 'gramlyze.loc', $root.'/storage/app/ppc-quality-local', $argv[1] ?? null, $physical);
echo json_encode(['at' => gmdate('c'), 'physical_vhost_cli_web_identity' => true, 'database' => $physical['db'], 'environment' => app()->environment(), 'site_mode' => app(App\Support\SiteMode::class)->forHost('gramlyze.loc')])."\n";
