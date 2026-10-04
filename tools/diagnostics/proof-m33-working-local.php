<?php

// Explicit read-only physical/vhost/CLI-web identity check. Does not create a route or change the DB.
if (PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows' || ($argv[1] ?? '') !== '--check') { exit(1); }
$root = 'D:/DEV/htdocs/gramlyze.loc'; $source = dirname(__DIR__, 2);
$name = $argv[2] ?? '';
if (!preg_match('/^local-proof-[a-f0-9]{32}\.json$/D', $name)) { throw new RuntimeException('Use an existing private M33 proof basename.'); }
require $root.'/vendor/autoload.php';
require_once $source.'/app/Services/M11LocalTargetGuard.php';
require_once $source.'/app/Services/M33LocalTargetGuard.php';
$app = require $root.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if (realpath(base_path()) !== realpath($root)) { throw new RuntimeException('Wrong working root.'); }
$db = Illuminate\Support\Facades\DB::connection();
$physical = (array) $db->selectOne('SELECT DATABASE() AS db, @@hostname AS server, @@port AS port');
if ($physical['db'] !== 'gr2') { throw new RuntimeException('Wrong working database.'); }
(new App\Services\M33LocalTargetGuard)->verify($db, 'gramlyze.loc', $root.'/storage/app/seo-m33-local', $name, $physical);
echo json_encode(['at' => gmdate('c'), 'physical_and_vhost_proof' => true,
    'runtime' => App\Services\M33LocalTargetGuard::publicIdentity($db, $root.'/public')],
    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
