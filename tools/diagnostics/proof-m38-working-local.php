<?php

// Explicit read-only physical/vhost/CLI-web identity check. Does not create a route or change the DB.
if (PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows' || ($argv[1] ?? '') !== '--check') { exit(1); }
$root = 'D:/DEV/htdocs/gramlyze.loc'; $source = dirname(__DIR__, 2);
$name = $argv[2] ?? '';
if (!preg_match('/^local-proof-[a-f0-9]{32}\.json$/D', $name)) { throw new RuntimeException('Use an existing private M38 proof basename.'); }
require $root.'/vendor/autoload.php';
require_once $source.'/app/Services/M11LocalTargetGuard.php';
require_once $source.'/app/Services/M38LocalTargetGuard.php';
$app = require $root.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if (realpath(base_path()) !== realpath($root)) { throw new RuntimeException('Wrong working root.'); }
$db = Illuminate\Support\Facades\DB::connection();
if ($db->getDriverName() !== 'mysql' || !in_array($db->getConfig('host'), ['localhost', '127.0.0.1'], true)
    || $db->getConfig('database') !== 'gr2' || (int) $db->getConfig('port') !== 3306
    || $db->getConfig('url') || $db->getConfig('unix_socket') || $db->getConfig('read') || $db->getConfig('write')) {
    throw new RuntimeException('Unexpected configured target; no database query permitted.');
}
if ($db->getReadPdo() !== $db->getPdo()) { throw new RuntimeException('Split read connection; no proof query permitted.'); }
$physical = (array) $db->selectOne('SELECT DATABASE() AS db, @@hostname AS server, @@port AS port');
if ($physical['db'] !== 'gr2') { throw new RuntimeException('Wrong working database.'); }
(new App\Services\M38LocalTargetGuard)->verify($db, 'gramlyze.loc', $root.'/storage/app/seo-m38-local', $name, $physical);
echo json_encode(['at' => gmdate('c'), 'physical_and_vhost_proof' => true,
    'runtime' => App\Services\M38LocalTargetGuard::publicIdentity($db, $root.'/public')],
    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
