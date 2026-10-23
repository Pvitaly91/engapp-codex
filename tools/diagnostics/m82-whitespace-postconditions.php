<?php

/** SELECT-only applied-successor audit. No writer/plan/seeder/HTTP call; predecessor receipts stay immutable. */
if (PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows' || !in_array(count($argv), [6, 8], true)
    || preg_match('/^whitespace-preview-v[1-9][0-9]*\.json$/D', $argv[1]) !== 1 || preg_match('/^[a-f0-9]{64}$/D', $argv[2]) !== 1
    || preg_match('/^whitespace-backup-v[1-9][0-9]*\.json$/D', $argv[3]) !== 1 || preg_match('/^[a-f0-9]{64}$/D', $argv[4]) !== 1
    || preg_match('/^final-whitespace-readonly-v[1-9][0-9]*\.json$/D', $argv[5]) !== 1) {
    throw new RuntimeException('M82 whitespace needs reviewed preview/digest, exclusive backup/SHA and fresh final basename.');
}
$root = 'D:/DEV/htdocs/gramlyze.loc'; $dir = $root.'/storage/app/seo-m82-local';
$normal = static fn ($p): string => strtolower(str_replace('\\', '/', (string) realpath($p)));
if ($normal(dirname(__DIR__, 2)) !== $normal($root) || is_link($dir)) { throw new RuntimeException('M82 whitespace audit must inspect served ROOT.'); }
require $root.'/vendor/autoload.php';
use App\Services\M82WhitespaceCorrectionPatch as Patch;
use App\Support\M82CommonMistakesLevelRevision as Package;
use App\Support\M82WhitespaceRevision as Revision;
$hash = static function (string $path): string { return Patch::fileHash($path); };
$load = static function (string $name, ?string $sha = null) use ($dir, $hash): array {
    if (preg_match('/^[a-zA-Z0-9_-]+\.json$/D', $name) !== 1 || ($sha !== null && !hash_equals($sha, $hash($dir.'/'.$name)))) {
        throw new RuntimeException('M82 whitespace exact private evidence differs.');
    }
    return json_decode(file_get_contents($dir.'/'.$name), true, flags: JSON_THROW_ON_ERROR);
};
$preview = $load($argv[1]); $backup = $load($argv[3], $argv[4]); $copy = $preview; unset($copy['sha256']);
if (($preview['sha256'] ?? null) !== $argv[2] || App\Support\M26DetailPackage::digest($copy) !== $argv[2]
    || file_get_contents($dir.'/'.$argv[1]) !== file_get_contents($dir.'/'.$argv[3]) || $backup !== $preview
    || ($preview['patch'] ?? null) !== 'm82-whitespace-1.0.1-to-1.0.2' || ($preview['state'] ?? null) !== 'before'
    || array_column($preview['updates'] ?? [], 'id') !== [1673, 1675] || ($preview['inserts'] ?? null) !== []
    || ($preview['names'] ?? null) !== Package::OWNERS) {
    throw new RuntimeException('M82 whitespace exact two-body preview/backup lineage differs.');
}
$priorName = $argv[6] ?? null; $priorSha = $argv[7] ?? null;
if (($priorName !== null || $priorSha !== null) && (count($argv) !== 8 || !is_string($priorName) || !is_string($priorSha)
    || preg_match('/^final-whitespace-readonly-v[1-9][0-9]*\.json$/D', $priorName) !== 1 || $priorName === $argv[5]
    || preg_match('/^[a-f0-9]{64}$/D', $priorSha) !== 1)) { throw new RuntimeException('M82 whitespace repeat evidence differs.'); }
$prior = $priorName === null ? null : $load($priorName, $priorSha);
$app = require $root.'/bootstrap/app.php'; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$db = Illuminate\Support\Facades\DB::connection();
App\Services\M82LocalTargetGuard::assertConfiguredDatabase($db);
if ($db->getDriverName() !== 'mysql') { throw new RuntimeException('M82 whitespace working audit requires the exact configured local MySQL.'); }
$before = $load(Patch::BEFORE_FILE, Patch::BEFORE_SHA);
$baseline = $load(Revision::BASELINE, Revision::BASELINE_SHA);
[$sourceBefore, $patch, $master, $contract] = Package::load($root); Package::validate($sourceBefore, $patch, $master, $contract);
if (!hash_equals(Revision::PREVIOUS_SOURCE_SHA, $hash($root.'/'.Revision::PREVIOUS_SOURCE))) {
    throw new RuntimeException('M82 whitespace independent immutable predecessor source differs.');
}
$predecessor = json_decode(file_get_contents($root.'/'.Revision::PREVIOUS_SOURCE), true, flags: JSON_THROW_ON_ERROR);
$runtime = App\Services\M49LocalTargetGuard::publicIdentity($db, $root.'/public');
if (($baseline['runtime'] ?? null) !== $runtime || count($baseline['sql_after'] ?? []) !== 46 || ($baseline['all_old_rows_and_timestamps_exact'] ?? null) !== true) {
    throw new RuntimeException('M82 whitespace independent runtime/46-table predecessor differs.');
}
$guard = new App\Services\M82LocalTargetGuard; $physical = $guard->physicalEvidence();
$server = (array) $db->selectOne('SELECT DATABASE() AS db, @@port AS port, @@pid_file AS pid_file, @@datadir AS datadir, @@version_compile_os AS os');
foreach (['datadir', 'pid_file'] as $key) {
    if (!is_string($server[$key] ?? null) || preg_match('~^[A-Za-z]:/~D', str_replace('\\', '/', $server[$key])) !== 1) { throw new RuntimeException('M82 whitespace physical MySQL path is not local Windows.'); }
}
$dataPath = $normal($server['datadir']); $pidPath = $normal($server['pid_file']); $pid = trim((string) file_get_contents($server['pid_file']));
if (($server['db'] ?? null) !== 'gr2' || (int) ($server['port'] ?? 0) !== 3306 || !str_starts_with($server['os'] ?? '', 'Win')
    || !ctype_digit($pid) || !str_starts_with($pidPath, $dataPath.'/') || str_starts_with($dataPath, '//')) { throw new RuntimeException('M82 whitespace physical MySQL identity differs.'); }
$guard->assertEvidence($physical, $normal($root), (int) $pid, 3306);
$sync = $load(Patch::SYNC_RECEIPT);
$expectedPaths = array_values(array_unique([...Patch::sourcePaths($patch, $contract, $root),
    'private:'.Patch::BEFORE_FILE, 'private:'.Patch::CONSUMERS_FILE, 'private:'.Patch::SYNC_RECEIPT, 'private:'.Revision::BASELINE]));
sort($expectedPaths); $actualPaths = array_keys($preview['sources'] ?? []); sort($actualPaths);
if ($actualPaths !== $expectedPaths) { throw new RuntimeException('M82 whitespace exact correction source/evidence allowlist differs.'); }
$checkSources = static function () use ($preview, $root, $dir, $hash, $sync, $before): void {
    foreach ($preview['sources'] as $path => $sha) {
        $file = str_starts_with($path, 'private:') ? $dir.'/'.substr($path, 8) : $root.'/'.$path;
        if (!hash_equals($sha, $hash($file))) { throw new RuntimeException('M82 whitespace reviewed source/evidence changed.'); }
    }
    foreach ($sync['files'] as $record) {
        if ($hash($root.'/'.$record['path']) !== $record['root_after_sha256'] || $hash(Patch::SOURCE_WORKTREE.'/'.$record['path']) !== $record['worktree_sha256']) {
            throw new RuntimeException('M82 whitespace ROOT/WT source mirror changed.');
        }
    }
    foreach ([$before['physical_hashes'], $before['visual_hashes'], $before['frozen_hashes']] as $i => $records) {
        foreach ($records as $path => $sha) {
            if (!hash_equals($sha, $hash($i === 0 ? $path : $root.'/'.$path))) { throw new RuntimeException('M82 whitespace physical/visual/historical source changed.'); }
        }
    }
};
$checkSources();
$service = new Patch($db, $root.'/database', $dir);
(new ReflectionMethod($service, 'prepareTransition'))->invoke($service);
(new ReflectionMethod($service, 'baselineEvidence'))->invoke($service);
$fingerprints = new ReflectionMethod($service, 'correctionFingerprints');
$db->statement('SET TRANSACTION READ ONLY'); $db->beginTransaction();
try {
    $tables = $db->getSchemaBuilder()->getTableListing(schema: $db->getDatabaseName(), schemaQualified: false); sort($tables);
    if (count($tables) !== 46 || $tables !== array_keys($baseline['sql_after'])) { throw new RuntimeException('M82 whitespace all46 table names differ.'); }
    foreach ($before['schema'] as $table => $columns) {
        if ($db->getSchemaBuilder()->getColumns($table) !== $columns) { throw new RuntimeException('M82 whitespace captured schema changed.'); }
    }
    $bodies = [];
    foreach (Revision::TARGETS as $slot => $identity) {
        $row = (array) $db->table('text_blocks')->where('id', $identity['id'])->sole();
        $expected = ['id' => $identity['id'], 'uuid' => $identity['uuid'], 'page_id' => 40, 'page_category_id' => 9, 'seeder' => Package::OWNERS[1],
            'locale' => 'uk', 'sort_order' => $slot + 1, 'type' => $identity['type'], 'column' => $identity['column'], 'heading' => null, 'css_class' => null, 'level' => $identity['level']];
        foreach ($expected as $field => $value) { if (($row[$field] ?? null) !== $value) { throw new RuntimeException('M82 whitespace exact applied row metadata differs.'); } }
        $change = $preview['updates'][count($bodies)];
        $oldValue = ['body' => $predecessor['targets'][1]['after']['page']['blocks'][$slot]['body']];
        $newValue = ['body' => $patch['targets'][1]['after']['page']['blocks'][$slot]['body']];
        $expectedChange = ['table' => 'text_blocks', 'id' => $identity['id'], 'uuid' => $identity['uuid'], 'seeder' => Package::OWNERS[1],
            'before' => $oldValue, 'after' => $newValue,
            'before_sha256' => App\Support\M26DetailPackage::digest($oldValue), 'after_sha256' => App\Support\M26DetailPackage::digest($newValue)];
        if ($change !== $expectedChange || $row['body'] !== $newValue['body']) {
            throw new RuntimeException('M82 whitespace actual applied body or exact preview transition differs.');
        }
        $bodies[] = $expected + ['before_body_sha256' => hash('sha256', $change['before']['body']), 'after_body_sha256' => hash('sha256', $row['body'])];
    }
    $raw = $fingerprints->invoke($service); $normalized = $fingerprints->invoke($service, true); $protected = $fingerprints->invoke($service, false, true);
    if ($normalized !== $baseline['sql_after'] || $protected !== $preview['protected']) {
        throw new RuntimeException('M82 whitespace raw46 tables differ beyond two exact successor body values.');
    }
    if ($prior !== null && (($prior['sql_after'] ?? null) !== $raw || ($prior['normalized_1_0_1'] ?? null) !== $normalized
        || ($prior['protected'] ?? null) !== $protected || ($prior['rows'] ?? null) !== $bodies || ($prior['plan_digest'] ?? null) !== $argv[2]
        || ($prior['backup_sha256'] ?? null) !== $argv[4])) { throw new RuntimeException('M82 whitespace post-UI repeat differs.'); }
} finally { $db->rollBack(); }
$checkSources();
if (!hash_equals($argv[4], $hash($dir.'/'.$argv[3])) || file_get_contents($dir.'/'.$argv[1]) !== file_get_contents($dir.'/'.$argv[3])) { throw new RuntimeException('M82 whitespace exclusive backup changed during audit.'); }
$receipt = ['checked_at' => gmdate('c'), 'version' => Revision::VERSION, 'runtime' => $runtime, 'plan_file' => $argv[1], 'plan_digest' => $argv[2],
    'backup_file' => $argv[3], 'backup_sha256' => $argv[4], 'backup_equals_reviewed_preview' => true,
    'predecessor_final_basename' => Revision::BASELINE, 'predecessor_final_sha256' => Revision::BASELINE_SHA,
    'original_1_0_1_final_receipt_preserved' => true, 'predecessor_source_sha256' => Revision::PREVIOUS_SOURCE_SHA,
    'current_source_sha256' => Package::SOURCE_SHA, 'sql_tables' => 46, 'sql_after' => $raw, 'normalized_1_0_1' => $normalized, 'protected' => $protected,
    'rows' => $bodies, 'body_updates' => 2, 'inserts' => 0, 'deletes' => 0, 'question_family_writes' => 0,
    'all_other_rows_columns_and_timestamps_exact_to_1_0_1' => true,
    'source_sync_basename' => Patch::SYNC_RECEIPT, 'source_sync_sha256' => $hash($dir.'/'.Patch::SYNC_RECEIPT),
    'visual_files_unchanged' => count($before['visual_hashes']), 'historical_frozen_files_unchanged' => count($before['frozen_hashes']),
    'prior_post_ui_receipt' => $priorName === null ? null : ['file' => $priorName, 'sha256' => $priorSha, 'all46_after_fingerprints_exact' => true],
    'db_writes' => 0, 'http_requests' => 0, 'registry_and_http_acceptance' => 'Separate dynamic Package-based M82 registry/navigation/widget profiles; not claimed by this SQL receipt.'];
$file = $dir.'/'.$argv[5]; if (file_exists($file) || is_link($file)) { throw new RuntimeException('M82 whitespace fresh final receipt already exists.'); }
$bytes = json_encode($receipt, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n"; $h = fopen($file, 'x');
if (!$h) { throw new RuntimeException('M82 whitespace exclusive final receipt unavailable.'); }
try { if (fwrite($h, $bytes) !== strlen($bytes) || !fflush($h)) { throw new RuntimeException('M82 whitespace final receipt incomplete.'); } } finally { fclose($h); }
echo json_encode(['file' => basename($file), 'sha256' => hash('sha256', $bytes), 'version' => Revision::VERSION, 'body_updates' => 2,
    'all46_tables_exact_except_two_reviewed_bodies' => true, 'post_ui_repeat' => $priorName !== null, 'db_writes' => 0, 'http_requests' => 0], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR)."\n";
