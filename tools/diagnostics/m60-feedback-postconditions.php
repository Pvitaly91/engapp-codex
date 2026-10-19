<?php

/** Explicit SELECT-only cumulative verification after the finite three-body correction. */
if (PHP_SAPI !== 'cli' || count($argv) !== 2
    || preg_match('/^final-readonly-v[3-9][0-9]*\.json$/D', $argv[1]) !== 1) {
    throw new RuntimeException('Reviewed M60 cumulative final-readonly basename required.');
}
$root = 'D:/DEV/htdocs/gramlyze.loc';
if (strtolower(str_replace('\\', '/', (string) realpath(dirname(__DIR__, 2)))) !== strtolower($root)) {
    throw new RuntimeException('M60 cumulative verification ROOT differs.');
}
require $root.'/vendor/autoload.php'; $app = require $root.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$db = Illuminate\Support\Facades\DB::connection();
App\Services\M49LocalTargetGuard::assertConfiguredDatabase($db);
$dir = $root.'/storage/app/seo-m60-local'; $file = $dir.'/'.$argv[1];
if (!is_dir($dir) || is_link($dir) || is_link($file) || file_exists($file)) {
    throw new RuntimeException('M60 cumulative verification evidence unavailable or already exists.');
}
$db->getPdo()->exec('SET TRANSACTION READ ONLY'); $db->beginTransaction();
try {
    $service = new App\Services\M60PracticeFeedbackCorrectionPatch($db, $root.'/database', $dir);
    $result = $service->verification();
} finally { if ($db->transactionLevel() > 0) { $db->rollBack(); } }
if (($result['sql_tables'] ?? null) !== 46 || ($result['source_sync_unchanged'] ?? null) !== true
    || ($result['db_writes'] ?? null) !== 0 || ($result['http_requests'] ?? null) !== 0) {
    throw new RuntimeException('M60 cumulative verification did not prove full read-only scope.');
}
$bytes = json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
$handle = fopen($file, 'x');
try {
    if (!$handle || fwrite($handle, $bytes) !== strlen($bytes) || !fflush($handle)) {
        throw new RuntimeException('M60 cumulative verification receipt incomplete.');
    }
} finally { if (is_resource($handle)) { fclose($handle); } }
echo json_encode(['file' => $argv[1], 'sha256' => hash('sha256', $bytes), 'sql_tables' => 46,
    'correction_bodies' => 3, 'db_writes' => 0, 'http_requests' => 0], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR)."\n";
