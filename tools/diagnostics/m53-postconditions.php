<?php

/**
 * Independent final read-only M53 gate. No apply, restore, seeder or HTTP call.
 * php tools/diagnostics/m53-postconditions.php preview-v1.json <reviewed-plan-digest> final-readonly-v1.json
 * Reconstruct every BEFORE table fingerprint by reverting only the exact reviewed
 * content fields and omitting only independently verified new practice/question rows.
 */
set_exception_handler(static function (Throwable $error): void {
    fwrite(STDERR, $error instanceof RuntimeException && !$error instanceof PDOException
        ? 'M53 final audit: '.$error->getMessage()."\n" : 'M53 final audit incomplete; no successful receipt. No SQL writes.');
    exit(1);
});
$previewName = $argv[1] ?? ''; $reviewedDigest = $argv[2] ?? ''; $receiptName = $argv[3] ?? '';
if (PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows' || count($argv) !== 4
    || preg_match('/^[a-zA-Z0-9_-]+\.json$/D', $previewName) !== 1
    || preg_match('/^[a-f0-9]{64}$/D', $reviewedDigest) !== 1
    || preg_match('/^final-readonly-v[1-9][0-9]*\.json$/D', $receiptName) !== 1) {
    throw new RuntimeException('Explicit preview basename, reviewed digest and new final receipt basename required.');
}
$root = 'D:/DEV/htdocs/gramlyze.loc'; $dir = $root.'/storage/app/seo-m53-local';
$normal = static fn ($path): string => rtrim(strtolower(str_replace('\\', '/', (string) realpath($path))), '/');
if ($normal(dirname(__DIR__, 2)) !== $normal($root) || is_link($dir) || !is_dir($dir)
    || $normal($dir) !== $normal($root).'/storage/app/seo-m53-local') {
    throw new RuntimeException('Final audit must run from the actual served ROOT/private directory.');
}
$receiptPath = $dir.'/'.$receiptName;
if (file_exists($receiptPath) || is_link($receiptPath)) { throw new RuntimeException('Final audit receipt already exists; no overwrite.'); }
require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php'; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if ($normal(base_path()) !== $normal($root) || $normal(app()->environmentPath()) !== $normal($root)) {
    throw new RuntimeException('Final application/environment root differs.');
}
$hash = static function (string $path): string { return App\Services\M53ContentPatch::fileHash($path); };
$load = static function (string $name, ?string $expected = null) use ($dir, $hash): array {
    if (preg_match('/^[a-zA-Z0-9_-]+\.json$/D', $name) !== 1 || ($expected !== null && !hash_equals($expected, $hash($dir.'/'.$name)))) {
        throw new RuntimeException('Private reviewed evidence identity/bytes differ.');
    }
    $hash($dir.'/'.$name);
    return json_decode(file_get_contents($dir.'/'.$name), true, flags: JSON_THROW_ON_ERROR);
};
$digest = static fn (array $value): string => App\Services\M26ContentPatch::digest($value);
$same = static function (array $actual, array $expected, string $message): void {
    ksort($actual); ksort($expected);
    if ($actual !== $expected) { throw new RuntimeException($message); }
};
$before = $load(App\Services\M53ContentPatch::BEFORE_FILE, App\Services\M53ContentPatch::BEFORE_SHA);
$preview = $load($previewName); $plan = $preview; unset($plan['sha256']);
if (($preview['sha256'] ?? null) !== $reviewedDigest || $digest($plan) !== $reviewedDigest
    || ($preview['state'] ?? null) !== 'before' || ($preview['names'] ?? null) !== App\Support\M53PassiveConstructionsPackage::OWNERS
    || array_keys($before['pages'] ?? []) !== App\Support\M53PassiveConstructionsPackage::OWNERS
    || ($before['base_sha'] ?? null) !== App\Support\M53PassiveConstructionsPackage::BASE_SHA
    || ($before['sql_before'] ?? null) !== ($before['sql_after_http'] ?? null)) {
    throw new RuntimeException('Reviewed preview/base or independent BEFORE scope differs.');
}
[$sourceBefore, $patch, $master, $contract] = App\Support\M53PassiveConstructionsPackage::load($root);
App\Support\M53PassiveConstructionsPackage::validate($sourceBefore, $patch, $master, $contract);
foreach ($preview['sources'] as $path => $sha) {
    $file = str_starts_with($path, 'private:') ? $dir.'/'.substr($path, strlen('private:')) : $root.'/'.$path;
    if (!hash_equals($sha, $hash($file))) { throw new RuntimeException('Reviewed preview source/evidence changed: '.$path); }
}
foreach ($before['physical_hashes'] as $path => $sha) {
    if (!hash_equals($sha, $hash($path))) { throw new RuntimeException('Physical target/configuration changed.'); }
}
foreach ([$before['visual_hashes'], $before['frozen_hashes']] as $hashes) {
    foreach ($hashes as $path => $sha) {
        if (!hash_equals($sha, $hash($root.'/'.$path))) { throw new RuntimeException('Visual/historical frozen source changed: '.$path); }
    }
}
$sync = $load(App\Services\M53ContentPatch::SYNC_RECEIPT);
$syncPaths = array_column($sync['files'] ?? [], 'path'); sort($syncPaths);
if ($syncPaths !== App\Services\M53ContentPatch::syncPaths($patch, $contract)
    || ($sync['base_sha'] ?? null) !== App\Support\M53PassiveConstructionsPackage::BASE_SHA
    || ($sync['served_root'] ?? null) !== $root || ($sync['source_worktree'] ?? null) !== App\Services\M53ContentPatch::SOURCE_WORKTREE) {
    throw new RuntimeException('Reviewed finite source-sync scope differs.');
}
foreach ($sync['files'] as $record) {
    if ($hash($root.'/'.$record['path']) !== $record['root_after_sha256']
        || $hash(App\Services\M53ContentPatch::SOURCE_WORKTREE.'/'.$record['path']) !== $record['worktree_sha256']) {
        throw new RuntimeException('Reviewed ROOT/worktree source-sync bytes changed.');
    }
}

$db = Illuminate\Support\Facades\DB::connection();
App\Services\M53LocalTargetGuard::assertConfiguredDatabase($db); // Pure config check, before lazy PDO.
$db->statement('SET TRANSACTION READ ONLY'); $db->beginTransaction();
try {
    $runtime = App\Services\M53LocalTargetGuard::publicIdentity($db, $root.'/public');
    if ($runtime !== ($before['identity'] ?? null)) { throw new RuntimeException('Final local runtime identity differs from independent BEFORE.'); }
    $pageBefore = []; $pageAfter = []; $ukBefore = []; $ukAfter = []; $nonUk = 0;
    foreach ($patch['targets'] as $i => $target) {
        $owner = $target['identity']; $captured = $before['pages'][$owner]; $oldPage = $captured['page'];
        $pageBefore[$oldPage['id']] = $oldPage; $pageAfter[$oldPage['id']] = array_replace($oldPage, ['text' => $target['after']['page']['subtitle_text']]);
        foreach ($captured['blocks'] as $old) {
            if ($old['locale'] !== 'uk') { ++$nonUk; continue; }
            $position = (int) $old['sort_order']; $body = $position === 0 ? $target['after']['page']['subtitle_html']
                : ($target['after']['page']['blocks'][$position - 1]['body'] ?? null);
            if (!is_string($body) || isset($ukBefore[$old['id']])) { throw new RuntimeException('Independent UK inventory/order is incomplete or duplicated.'); }
            $ukBefore[$old['id']] = $old; $ukAfter[$old['id']] = array_replace($old, ['body' => $body]);
        }
    }
    if (count($pageBefore) !== count($patch['targets']) || count($preview['inserts'] ?? []) !== count($patch['targets'])) {
        throw new RuntimeException('Exact three-owner/native practice scope differs.');
    }
    $newPractices = []; $practiceFacts = [];
    foreach ($preview['inserts'] as $insert) {
        $i = array_search($insert['seeder'], App\Support\M53PassiveConstructionsPackage::OWNERS, true);
        if ($i === false || $insert['table'] !== 'text_blocks' || $insert['sha256'] !== $digest($insert['fields'])) {
            throw new RuntimeException('Reviewed practice insert scope differs.');
        }
        $target = $patch['targets'][$i]; $source = end($target['after']['page']['blocks']); $position = count($target['after']['page']['blocks']);
        $uuid = App\Support\M26DetailPackage::uuid($target['identity'], $source, $position);
        if ($insert['fields']['uuid'] !== $uuid || $source['type'] !== 'practice-set') { throw new RuntimeException('Reviewed practice UUID differs from frozen source.'); }
        $actual = (array) $db->table('text_blocks')->where('uuid', $uuid)->sole();
        if (!is_int($actual['id']) || $actual['id'] < 1 || isset($newPractices[$actual['id']])
            || !is_string($actual['created_at']) || $actual['created_at'] === '' || $actual['created_at'] !== $actual['updated_at']) {
            throw new RuntimeException('New practice identity/timestamps differ.');
        }
        $expected = $insert['fields'] + ['id' => $actual['id'], 'created_at' => $actual['created_at'], 'updated_at' => $actual['created_at']];
        $same($actual, $expected, 'New practice row has an unreviewed field/value.'); $newPractices[$actual['id']] = $expected;
        $practiceFacts[] = ['owner' => $target['identity'], 'id' => $actual['id'], 'uuid' => $uuid];
    }
    // This method only SELECTs and validates existing frozen evidence. No writer or plan/apply entry is called.
    $service = new App\Services\M53ContentPatch($db, $root.'/database', $dir);
    $scope = (new ReflectionMethod($service, 'm53QuestionState'))->invoke($service, 'after', $patch, false);
    if (($scope['evidence']['projection'] ?? null) !== ($preview['question_supplement']['evidence']['projection'] ?? null)
        || ($scope['evidence']['ids'] ?? null) !== ($preview['question_supplement']['evidence']['ids'] ?? null)) {
        throw new RuntimeException('Applied question projection differs from reviewed preview.');
    }
    $timestamp = $scope['actual']['questions'][0]['created_at'] ?? '';
    $newRows = App\Support\M51QuestionSupplement::materialize($scope['evidence']['projection'], $scope['evidence']['ids'], $timestamp);
    App\Support\M51QuestionSupplement::assertApplied($newRows, $scope['actual']);
    $approved = []; foreach ($newRows as $table => $rows) { $approved[$table] = array_column($rows, null, 'id'); }
    $tables = $db->getSchemaBuilder()->getTableListing(schema: $db->getDatabaseName(), schemaQualified: false); sort($tables);
    if ($tables !== array_keys($before['sql_before'])) { throw new RuntimeException('Actual SQL table inventory changed.'); }
    $rawAfter = []; $normalized = []; $protected = []; $seenPages = []; $seenUk = []; $seenPractices = [];
    foreach ($tables as $table) {
        $columns = $db->getSchemaBuilder()->getColumnListing($table); $query = $db->table($table);
        foreach (in_array('id', $columns, true) ? ['id'] : $columns as $column) { $query->orderBy($column); }
        $rawHash = hash_init('sha256'); $oldHash = hash_init('sha256'); $protectedHash = hash_init('sha256');
        $rawCount = 0; $oldCount = 0; $protectedCount = 0;
        foreach ($query->cursor() as $record) {
            $row = (array) $record; hash_update($rawHash, $digest($row)."\n"); ++$rawCount;
            $id = $row['id'] ?? null; $projected = $row; $skipProtected = false;
            if ($table === 'text_blocks' && isset($newPractices[$id])) {
                $same($row, $newPractices[$id], 'New practice changed during final scan.'); $seenPractices[$id] = true; continue;
            }
            if (isset($approved[$table][$id])) {
                $same($row, $approved[$table][$id], 'New question family changed during final scan.'); continue;
            }
            if ($table === 'pages' && isset($pageBefore[$id])) {
                $same($row, $pageAfter[$id], 'Target Page metadata/text/timestamps differ.');
                $projected = $pageBefore[$id]; $seenPages[$id] = true;
            }
            if ($table === 'text_blocks' && isset($ukBefore[$id])) {
                $same($row, $ukAfter[$id], 'Continuing UK body/owner/locale/metadata/timestamps differ.');
                $projected = $ukBefore[$id]; $seenUk[$id] = true; $skipProtected = true;
            }
            hash_update($oldHash, $digest($projected)."\n"); ++$oldCount;
            if ($skipProtected) { continue; }
            if ($table === 'pages' && isset($pageBefore[$id])) { unset($row['text']); }
            hash_update($protectedHash, $digest($row)."\n"); ++$protectedCount;
        }
        $rawAfter[$table] = ['count' => $rawCount, 'sha256' => hash_final($rawHash)];
        $normalized[$table] = ['count' => $oldCount, 'sha256' => hash_final($oldHash)];
        $protected[$table] = ['count' => $protectedCount, 'sha256' => hash_final($protectedHash)];
    }
    if (count($seenPages) !== count($pageBefore) || count($seenUk) !== count($ukBefore) || count($seenPractices) !== count($newPractices)) {
        throw new RuntimeException('A target continuing Page/block/practice disappeared from the final scan.');
    }
    if ($normalized !== $before['sql_before']) { throw new RuntimeException('Any old row/progress/non-target payload changed outside exact reviewed content fields.'); }
    if ($protected !== $preview['protected']) { throw new RuntimeException('Reviewed preview protected fingerprints differ.'); }
} finally { $db->rollBack(); }
$receipt = ['checked_at' => gmdate('c'), 'runtime' => $runtime, 'plan_file' => $previewName, 'plan_digest' => $reviewedDigest,
    'independent_before_sha256' => App\Services\M53ContentPatch::BEFORE_SHA,
    'question_plan_sha256' => App\Services\M53ContentPatch::QUESTION_PLAN_SHA,
    'continuing_uk_rows' => count($ukBefore), 'unchanged_en_pl_rows' => $nonUk, 'new_practices' => $practiceFacts,
    'new_question_families' => array_map('count', $newRows), 'all_old_rows_and_timestamps_exact' => true,
    'independent_before_projection_exact' => true, 'reviewed_protected_projection_exact' => true,
    'sql_tables' => count($rawAfter), 'sql_after' => $rawAfter, 'normalized_before' => $normalized, 'protected' => $protected,
    'visual_files_unchanged' => count($before['visual_hashes']), 'frozen_files_unchanged' => count($before['frozen_hashes']),
    'source_sync_unchanged' => true, 'db_writes' => 0, 'http_requests' => 0,
    'http_metadata_and_browser_acceptance' => 'separate evidence; not performed by this SQL/source audit'];
$bytes = json_encode($receipt, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
$handle = fopen($receiptPath, 'x'); if ($handle === false) { throw new RuntimeException('Exclusive final receipt creation failed.'); }
try {
    if (fwrite($handle, $bytes) !== strlen($bytes) || !fflush($handle)) { throw new RuntimeException('Final receipt write incomplete.'); }
} finally { fclose($handle); }
echo json_encode(['file' => $receiptName, 'sha256' => hash('sha256', $bytes), 'sql_tables' => count($rawAfter),
    'continuing_uk_rows' => count($ukBefore), 'unchanged_en_pl_rows' => $nonUk, 'new_practices' => count($practiceFacts),
    'new_question_families' => array_map('count', $newRows), 'all_old_rows_and_timestamps_exact' => true, 'db_writes' => 0],
    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
