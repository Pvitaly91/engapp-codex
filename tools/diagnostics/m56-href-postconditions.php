<?php

/** Explicit SELECT-only aggregate proof: 1.0.1 -> four bodies -> reviewed 1.0.0 -> original BEFORE. */
set_exception_handler(static function (Throwable $error): void {
    fwrite(STDERR, $error instanceof RuntimeException && !$error instanceof PDOException
        ? 'M56 href audit: '.$error->getMessage()."\n" : "M56 href read-only audit incomplete; no successful receipt.\n");
    exit(1);
});
$planName = $argv[1] ?? ''; $digest = $argv[2] ?? ''; $receiptName = $argv[3] ?? '';
if (PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows' || count($argv) !== 4
    || preg_match('/^[a-zA-Z0-9_-]+\.json$/D', $planName) !== 1
    || preg_match('/^[a-f0-9]{64}$/D', $digest) !== 1
    || preg_match('/^final-readonly-v[1-9][0-9]*\.json$/D', $receiptName) !== 1) {
    throw new RuntimeException('Explicit reviewed correction plan, digest and fresh final receipt basename required.');
}
$root = 'D:/DEV/htdocs/gramlyze.loc'; $dir = $root.'/storage/app/seo-m56-local';
$normal = static fn ($path) => rtrim(strtolower(str_replace('\\', '/', (string) realpath($path))), '/');
if ($normal(dirname(__DIR__, 2)) !== $normal($root) || is_link($dir) || !is_dir($dir)
    || $normal($dir) !== $normal($root).'/storage/app/seo-m56-local'
    || is_link($dir.'/'.$receiptName) || file_exists($dir.'/'.$receiptName)) {
    throw new RuntimeException('M56 aggregate proof ROOT/private receipt path differs or exists.');
}
require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php'; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if ($normal(base_path()) !== $normal($root) || $normal(app()->environmentPath()) !== $normal($root)) {
    throw new RuntimeException('M56 aggregate proof application/environment ROOT differs.');
}
$hash = static fn ($path) => App\Services\M56ContentPatch::fileHash($path);
$hash($dir.'/'.$planName);
$plan = json_decode(file_get_contents($dir.'/'.$planName), true, flags: JSON_THROW_ON_ERROR);
$provided = $plan['sha256'] ?? ''; unset($plan['sha256']);
if ($provided !== $digest || App\Services\M26ContentPatch::digest($plan) !== $digest
    || ($plan['patch'] ?? null) !== 'm56-href-correction-1.0.0-to-1.0.1'
    || ($plan['state'] ?? null) !== 'before' || ($plan['names'] ?? null) !== App\Services\M56HrefCorrectionPatch::NAMES
    || count($plan['updates'] ?? []) !== 4 || ($plan['inserts'] ?? null) !== []) {
    throw new RuntimeException('M56 aggregate proof needs the exact reviewed four-body BEFORE plan.');
}
foreach ($plan['sources'] as $path => $sha) {
    $file = str_starts_with($path, 'private:') ? $dir.'/'.substr($path, 8) : $root.'/'.$path;
    if (!hash_equals($sha, $hash($file))) { throw new RuntimeException('M56 reviewed correction source/evidence drifted: '.$path); }
}
$beforePath = $dir.'/'.App\Services\M56ContentPatch::BEFORE_FILE;
if (!hash_equals(App\Services\M56ContentPatch::BEFORE_SHA, $hash($beforePath))) {
    throw new RuntimeException('M56 original independent BEFORE differs.');
}
$initial = json_decode(file_get_contents($beforePath), true, flags: JSON_THROW_ON_ERROR);
foreach ([$initial['visual_hashes'], $initial['frozen_hashes'], $initial['physical_hashes']] as $hashes) {
    foreach ($hashes as $path => $sha) {
        $file = preg_match('~^[A-Za-z]:/~D', str_replace('\\', '/', $path)) === 1 ? $path : $root.'/'.$path;
        if (!hash_equals($sha, $hash($file))) { throw new RuntimeException('M56 aggregate visual/frozen/physical source changed.'); }
    }
}
$delta = App\Services\M56HrefCorrectionPatch::correction($root);
if (array_column($plan['updates'], 'id') !== App\Services\M56HrefCorrectionPatch::IDS) {
    throw new RuntimeException('M56 aggregate proof row IDs differ.');
}
foreach ($delta['updates'] as $n => $change) {
    if ($plan['updates'][$n]['before'] !== ['body' => $change['before_body']]
        || $plan['updates'][$n]['after'] !== ['body' => $change['after_body']]) {
        throw new RuntimeException('M56 reviewed href values differ from immutable transition.');
    }
}
$db = Illuminate\Support\Facades\DB::connection();
App\Services\M49LocalTargetGuard::assertConfiguredDatabase($db);
$guard = new App\Services\M49LocalTargetGuard; $physical = $guard->physicalEvidence();
$server = (array) $db->selectOne('SELECT DATABASE() AS db, @@port AS port, @@pid_file AS pid_file, @@datadir AS datadir, @@version_compile_os AS os');
foreach (['pid_file', 'datadir'] as $key) {
    if (!is_string($server[$key] ?? null) || preg_match('~^[A-Za-z]:/~D', str_replace('\\', '/', $server[$key])) !== 1) {
        throw new RuntimeException('M56 aggregate MySQL evidence is not an absolute local Windows path.');
    }
}
$pid = trim((string) file_get_contents($server['pid_file']));
if ($server['db'] !== 'gr2' || (int) $server['port'] !== 3306 || !str_starts_with($server['os'], 'Win')
    || !ctype_digit($pid) || !str_starts_with($normal($server['pid_file']), $normal($server['datadir']).'/')) {
    throw new RuntimeException('M56 aggregate physical MySQL identity differs.');
}
$guard->assertEvidence($physical, $normal($root), (int) $pid, 3306);
$db->statement('SET TRANSACTION READ ONLY'); $db->beginTransaction();
try {
    $service = new App\Services\M56HrefCorrectionPatch($db, $root.'/database', $dir);
    $evidence = $service->verification();
} finally { $db->rollBack(); }
$baseline = json_decode(file_get_contents($dir.'/'.App\Services\M56HrefCorrectionPatch::BASELINE), true, flags: JSON_THROW_ON_ERROR);
$result = ['checked_at' => gmdate('c'), 'runtime' => $evidence['runtime'], 'plan_file' => $planName,
    'plan_digest' => $digest, 'href_delta_sha256' => App\Services\M56HrefCorrectionPatch::DELTA_SHA,
    'independent_before_sha256' => $evidence['independent_before_sha256'],
    'after_1_0_0_baseline_sha256' => $evidence['baseline_sha256'],
    'all_old_rows_and_timestamps_exact' => true, 'independent_before_projection_exact' => true,
    'reviewed_protected_projection_exact' => true, 'sql_tables' => count($evidence['sql_after']),
    'sql_after' => $evidence['sql_after'], 'normalized_before' => $evidence['normalized_before'],
    'normalized_after_1_0_0' => $evidence['normalized_after_1_0_0'],
    'continuing_uk_rows' => $baseline['continuing_uk_rows'], 'unchanged_en_pl_rows' => $baseline['unchanged_en_pl_rows'],
    'new_practices' => $baseline['new_practices'], 'new_question_families' => $baseline['new_question_families'],
    'visual_files_unchanged' => count($initial['visual_hashes']), 'frozen_files_unchanged' => count($initial['frozen_hashes']),
    'db_writes' => 0, 'http_requests' => 0, 'browser_acceptance' => 'separate evidence; not performed here'];
$bytes = json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
$file = fopen($dir.'/'.$receiptName, 'x');
if (!$file) { throw new RuntimeException('M56 aggregate exclusive receipt collision.'); }
try { if (fwrite($file, $bytes) !== strlen($bytes) || !fflush($file)) { throw new RuntimeException('M56 aggregate receipt incomplete.'); } }
finally { fclose($file); }
echo json_encode(['file' => $receiptName, 'sha256' => hash('sha256', $bytes),
    'sql_tables' => count($evidence['sql_after']), 'href_body_corrections' => 4,
    'all_old_rows_and_timestamps_exact' => true, 'db_writes' => 0], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR)."\n";
