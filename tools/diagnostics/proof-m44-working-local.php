<?php

// Explicit read-only check. Does not create routes or write application/database data.
if (PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows' || ($argv[1] ?? '') !== '--check' || count($argv) !== 3) { exit(1); }
$source = dirname(__DIR__, 2);
$root = 'D:/DEV/htdocs/gramlyze.loc';
require $root.'/vendor/autoload.php';
require_once $source.'/app/Services/M11LocalTargetGuard.php';
require_once $source.'/app/Services/M44LocalTargetGuard.php';
use App\Services\M44LocalTargetGuard as Guard;

$name = $argv[2];
$proof = Guard::readFreshProof($name);
$app = require $root.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if (realpath(base_path()) !== realpath($root) || realpath(app()->environmentPath()) !== realpath($root)) {
    throw new RuntimeException('M44 wrong working application/environment root.');
}
$db = Illuminate\Support\Facades\DB::connection();
Guard::assertConfiguredDatabase($db);
if ($db->getReadPdo() !== $db->getPdo()) { throw new RuntimeException('M44 split connection; no proof query permitted.'); }
$physical = (array) $db->selectOne('SELECT DATABASE() AS db, @@hostname AS server, @@port AS port');
(new Guard)->verify($db, 'gramlyze.loc', Guard::privateDirectory(), $name, $physical);
echo json_encode(['at' => gmdate('c'), 'physical_and_vhost_proof' => true, 'scope' => Guard::SCOPE,
    'physical_basename' => $proof['physical_basename'], 'physical_sha256' => $proof['physical_sha256'],
    'runtime' => Guard::publicIdentity($db, $root.'/public')],
    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
