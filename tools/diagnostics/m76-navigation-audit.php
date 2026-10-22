<?php

/**
 * M76 read-only native navigation/metadata proof. Review before explicit execution:
 * php tools/diagnostics/m76-navigation-audit.php nav-metadata-v1.json final-readonly-v1.json <raw-sha256>
 * php tools/diagnostics/m76-navigation-audit.php successor nav-metadata-v3.json anchor-final-readonly-v1.json <raw-sha256>
 * Nine fixed-loopback anonymous GETs only. No writer, route change or browser claim.
 */
$failure = static function (Throwable $error): void {
    fwrite(STDERR, $error instanceof RuntimeException && !$error instanceof PDOException
        ? 'M76 navigation: '.$error->getMessage()."\n" : "M76 navigation incomplete; no successful receipt. No SQL writes.\n");
    exit(1);
};
set_exception_handler($failure);
if (($argv[1] ?? '') === 'prospective-meta') {
    if (PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows' || count($argv) !== 5
        || preg_match('/^author-projection-v[1-9][0-9]*\.json$/D', $argv[2] ?? '') !== 1
        || preg_match('/^[a-f0-9]{64}$/D', $argv[3] ?? '') !== 1
        || preg_match('/^(?:prospective-metadata|nav-metadata)-v[1-9][0-9]*\.json$/D', $argv[4] ?? '') !== 1) {
        throw new RuntimeException('Prospective metadata requires reviewed projection basename/raw SHA and fresh private output.');
    }
    $root = 'D:/DEV/htdocs/gramlyze.loc'; $dir = $root.'/storage/app/seo-m76-local';
    $normalized = static fn (string $path): string => strtolower(str_replace('\\', '/', (string) realpath($path)));
    if (!in_array($normalized(dirname(__DIR__, 2)), [$normalized($root), $normalized($root.'/storage/app/m75-worktree')], true)
        || !is_dir($dir) || is_link($dir) || $normalized($dir) !== $normalized($root).'/storage/app/seo-m76-local') {
        throw new RuntimeException('Prospective metadata is restricted to exact local ROOT/WT and private directory.');
    }
    $file = $dir.'/'.$argv[2]; $output = $dir.'/'.$argv[4];
    if (!is_file($file) || is_link($file) || !hash_equals($argv[3], hash_file('sha256', $file)) || file_exists($output) || is_link($output)) {
        throw new RuntimeException('Prospective metadata draft pin differs or exclusive output already exists.');
    }
    $draft = json_decode(file_get_contents($file), true, flags: JSON_THROW_ON_ERROR);
    $independentBeforeSha = 'b0cae6f629aae1b4354aefcf8d704b73030ef2022846c2cd1ea2f9f5d3a05985';
    $independentBeforePath = $dir.'/before-v1.json';
    if (!is_file($independentBeforePath) || is_link($independentBeforePath) || !hash_equals($independentBeforeSha, hash_file('sha256', $independentBeforePath))) {
        throw new RuntimeException('Prospective metadata independent BEFORE bytes differ.');
    }
    $independentBefore = json_decode(file_get_contents($independentBeforePath), true, flags: JSON_THROW_ON_ERROR);
    $pins = [
        'app/Support/PageMetadata.php' => '1d782d27483bdc0cff63515a1d69153dc908adbf2c3b3eea47bec35f141e0a9e',
        'app/Http/Middleware/AddStructuredData.php' => 'ea3774ba90512bfa2bf7cee9277b87a3951e4a43fbd48eeed21ffb83ec2a58ac',
    ];
    foreach ($pins as $path => $sha) {
        if (!is_file($root.'/'.$path) || is_link($root.'/'.$path) || !hash_equals($sha, hash_file('sha256', $root.'/'.$path))) {
            throw new RuntimeException('Prospective metadata native policy bytes differ.');
        }
    }
    // Composer only. No application bootstrap, .env, PDO, HTTP or renderer.
    require $root.'/vendor/autoload.php';
    if ($normalized((new ReflectionClass(App\Support\PageMetadata::class))->getFileName()) !== $normalized($root.'/app/Support/PageMetadata.php')) {
        throw new RuntimeException('Prospective metadata helper is not the actual served native class.');
    }
    $owners = [
        'Database\\Seeders\\Page_V3\\PronounsDemonstratives\\PronounsDemonstrativesRelativePronounsTheorySeeder',
        'Database\\Seeders\\Page_V3\\PronounsDemonstratives\\PronounsDemonstrativesEachEveryAllTheorySeeder',
        'Database\\Seeders\\Page_V3\\PronounsDemonstratives\\PronounsDemonstrativesReciprocalPronounsTheorySeeder',
    ];
    if (array_column($draft['master']['lessons'] ?? [], 'identity') !== $owners || array_column($draft['patch']['targets'] ?? [], 'identity') !== $owners) {
        throw new RuntimeException('Prospective metadata exact three draft owners differ.');
    }
    $plain = static fn (string $value): string => trim((string) preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    $rows = [];
    foreach ($owners as $i => $owner) {
        $lesson = $draft['master']['lessons'][$i]; $definition = $draft['patch']['targets'][$i]['after'];
        if (($definition['seeder']['class'] ?? null) !== $owner || ($definition['page']['locale'] ?? null) !== 'uk'
            || ($definition['page']['category']['slug'] ?? null) !== 'zaimennyky-ta-vkazivni-slova'
            || ($lesson['raw_title'] ?? null) !== ($definition['page']['title'] ?? null)) { throw new RuntimeException('Prospective metadata raw source identity differs.'); }
        $heroes = array_values(array_filter($definition['page']['blocks'], static fn ($block) => $block['type'] === 'hero'));
        if (count($heroes) !== 1) { throw new RuntimeException('Prospective metadata requires exactly one authored native hero.'); }
        $hero = json_decode($heroes[0]['body'], true, flags: JSON_THROW_ON_ERROR);
        if (!is_string($hero['intro'] ?? null) || trim($hero['intro']) === '' || preg_match('/[\x{0400}-\x{04ff}]/u', $hero['intro']) !== 1) {
            throw new RuntimeException('Prospective native hero lacks meaningful Ukrainian intro.');
        }
        $seo = App\Support\PageMetadata::theory($definition['page']['title'], '', $hero['intro'], $owner, 'uk');
        $oldHttp = array_values(array_filter($independentBefore['http'] ?? [], static fn ($row) => ($row['kind'] ?? null) === 'theory' && ($row['identity'] ?? null) === $owner));
        if (count($oldHttp) !== 1 || ($oldHttp[0]['url'] ?? null) !== $lesson['local_url'] || ($oldHttp[0]['h1'] ?? null) !== $lesson['preserved_h1']
            || ($oldHttp[0]['raw_page_title'] ?? null) !== $definition['page']['title'] || ($oldHttp[0]['title'] ?? null) !== $seo['title']) {
            throw new RuntimeException('Prospective metadata changes the independently captured protected title/H1 identity.');
        }
        $resourceDescription = null;
        foreach ($definition['page']['blocks'] as $block) {
            $data = json_decode($block['body'], true); if (!is_array($data)) { continue; }
            foreach (['intro', 'description', 'summary', 'subtitle', 'text'] as $key) {
                if (is_string($data[$key] ?? null) && trim(strip_tags($data[$key])) !== '') { $resourceDescription = Illuminate\Support\Str::limit($plain($data[$key]), 300, '…'); break 2; }
            }
        }
        if (!is_string($resourceDescription) || $resourceDescription === '') { throw new RuntimeException('Prospective native structured description is absent.'); }
        $rows[] = ['identity' => $owner, 'raw_title' => $definition['page']['title'], 'preserved_h1' => $lesson['preserved_h1'],
            'local_url' => $lesson['local_url'], 'hero_intro_sha256' => hash('sha256', $hero['intro']),
            'independent_before_protected' => array_intersect_key($oldHttp[0], array_flip(['h1_count', 'h1', 'title', 'breadcrumb', 'canonical', 'robots_header', 'robots_meta'])),
            'prospective_page_metadata' => $seo, 'prospective_learning_resource_description' => $resourceDescription];
    }
    foreach ($pins as $path => $sha) { if (!hash_equals($sha, hash_file('sha256', $root.'/'.$path))) { throw new RuntimeException('Native metadata helper changed during projection.'); } }
    if (!hash_equals($argv[3], hash_file('sha256', $file)) || !hash_equals($independentBeforeSha, hash_file('sha256', $independentBeforePath))) { throw new RuntimeException('Draft/independent BEFORE changed during metadata projection.'); }
    $result = ['schema_version' => 1, 'purpose' => 'M76 pre-apply prospective native metadata; not served-page acceptance',
        'projection_file' => $argv[2], 'projection_sha256' => $argv[3], 'independent_before_sha256' => $independentBeforeSha, 'native_policy_pins' => $pins, 'metadata' => $rows,
        'application_bootstrapped' => false, 'working_env_loaded' => false, 'db_connections' => 0, 'db_queries' => 0, 'db_writes' => 0,
        'http_requests' => 0, 'browser_interaction' => 'not_checked', 'private_output_only' => true];
    $bytes = json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)."\n";
    $handle = fopen($output, 'x'); if (!$handle) { throw new RuntimeException('Prospective metadata output is not exclusively available.'); }
    try { if (fwrite($handle, $bytes) !== strlen($bytes) || !fflush($handle)) { throw new RuntimeException('Prospective metadata output incomplete.'); } } finally { fclose($handle); }
    echo json_encode(['file' => $argv[4], 'sha256' => hash('sha256', $bytes), 'pages' => count($rows), 'db_writes' => 0, 'http_requests' => 0], JSON_THROW_ON_ERROR), PHP_EOL;
    exit(0);
}
$successorMode = ($argv[1] ?? '') === 'successor'; $args = $argv;
if ($successorMode) { array_splice($args, 1, 1); }
$name = $args[1] ?? ''; $requestedFinalName = $args[2] ?? ''; $requestedFinalSha = $args[3] ?? '';
if (PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows' || count($args) !== 4
    || preg_match('/^nav-metadata-v[1-9][0-9]*\.json$/D', $name) !== 1
    || preg_match($successorMode ? '/^anchor-final-readonly-v[1-9][0-9]*\.json$/D' : '/^final-readonly-v[1-9][0-9]*\.json$/D', $requestedFinalName) !== 1
    || preg_match('/^[a-f0-9]{64}$/D', $requestedFinalSha) !== 1) {
    throw new RuntimeException('M76 requires a fresh nav-metadata output and reviewed final-readonly basename/raw SHA.');
}
$finalName = $successorMode ? 'final-readonly-v1.json' : $requestedFinalName;
$finalSha = $successorMode ? '214ddb282ee6daad426c84ccc3cb9e9b26d12423a0ace44efe33aa2dab21b053' : $requestedFinalSha;
$root = 'D:/DEV/htdocs/gramlyze.loc'; $dir = $root.'/storage/app/seo-m76-local';
$normal = static function (string $path): string {
    $real = realpath($path); if ($real === false) { throw new RuntimeException('Required local path is missing.'); }
    return rtrim(strtolower(str_replace('\\', '/', $real)), '/');
};
if ($normal(dirname(__DIR__, 2)) !== $normal($root) || !is_dir($dir) || is_link($dir)
    || $normal($dir) !== $normal($root).'/storage/app/seo-m76-local'
    || file_exists($dir.'/'.$name) || is_link($dir.'/'.$name)) {
    throw new RuntimeException('Actual served ROOT/private scope differs or receipt already exists.');
}
$hash = static function (string $path): string {
    if (!is_file($path) || is_link($path)) { throw new RuntimeException('Pinned source/evidence missing or linked.'); }
    $sha = hash_file('sha256', $path); if (!is_string($sha)) { throw new RuntimeException('Pinned source cannot be hashed.'); }
    return $sha;
};
$inputs = [__FILE__ => $hash(__FILE__)];
$read = static function (string $basename, string $sha) use ($dir, $hash, &$inputs): array {
    if (preg_match('/^[a-zA-Z0-9_-]+\.json$/D', $basename) !== 1 || !hash_equals($sha, $hash($dir.'/'.$basename))) {
        throw new RuntimeException('Independent private evidence basename/bytes differ.');
    }
    $inputs[$dir.'/'.$basename] = $sha;
    return json_decode(file_get_contents($dir.'/'.$basename), true, flags: JSON_THROW_ON_ERROR);
};
$beforeSha = 'b0cae6f629aae1b4354aefcf8d704b73030ef2022846c2cd1ea2f9f5d3a05985';
$consumerSha = 'b00cc9b447c028f747460e33161a02abf01356aef5fab5e5535f0007e3b2917a';
$before = $read('before-v1.json', $beforeSha); $consumers = $read('before-old-consumers-v1.json', $consumerSha);
$final = $read($finalName, $finalSha);
$activeFinal = $successorMode ? $read($requestedFinalName, $requestedFinalSha) : $final;
if (($before['served_root'] ?? null) !== $root || ($before['sql_before'] ?? null) !== ($before['sql_after_http'] ?? null)
    || count($before['sql_before'] ?? []) !== 46 || ($final['independent_before_sha256'] ?? null) !== $beforeSha
    || ($final['old_consumers_sha256'] ?? null) !== $consumerSha || ($final['runtime'] ?? null) !== ($before['identity'] ?? null)
    || ($final['normalized_before'] ?? null) !== $before['sql_before']
    || ($final['all_old_rows_and_timestamps_exact'] ?? null) !== true || ($final['independent_before_projection_exact'] ?? null) !== true
    || ($final['reviewed_protected_projection_exact'] ?? null) !== true || ($final['backup_equals_reviewed_preview'] ?? null) !== true
    || ($final['visual_files_unchanged'] ?? null) !== 326 || ($final['frozen_files_unchanged'] ?? null) !== 2642
    || ($final['source_sync_unchanged'] ?? null) !== true || ($final['all_preview_sources_exact'] ?? null) !== true
    || ($final['insert_operations'] ?? null) !== 0 || ($final['deleted_rows'] ?? null) !== 0
    || ($final['continuing_uk_rows'] ?? null) !== 39 || ($final['unchanged_non_uk_rows'] ?? null) !== 56
    || count($final['metadata_transitions'] ?? []) !== 14 || count(array_unique(array_column($final['metadata_transitions'] ?? [], 'id'))) !== 10
    || ($final['db_writes'] ?? null) !== 0 || ($final['http_requests'] ?? null) !== 0
    || count($final['sql_after'] ?? []) !== 46 || count($before['visual_hashes'] ?? []) !== 326 || count($before['frozen_hashes'] ?? []) !== 2642) {
    throw new RuntimeException('Reviewed final receipt does not prove the complete independent M76 AFTER/BEFORE scope.');
}

require $root.'/vendor/autoload.php'; $app = require $root.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
set_exception_handler($failure);

use App\Models\Page;
use App\Models\PageCategory;
use App\Services\M76ContentPatch as Patch;
use App\Services\M76LocalTargetGuard as Guard;
use App\Services\M76AnchorPatch as Anchor;
use App\Support\M76PronounRelations as Package;
use App\Support\M26DetailPackage as Source;
use App\Support\TheorySentenceBuilderAcceptance as Acceptance;

$same = static function (mixed $actual, mixed $expected, string $label): void {
    if ($actual !== $expected) { throw new RuntimeException($label.' differs.'); }
};
$sameRow = static function (array $actual, array $expected, string $label): void {
    ksort($actual); ksort($expected); if ($actual !== $expected) { throw new RuntimeException($label.' differs.'); }
};
$digest = static fn (array $row): string => App\Services\M26ContentPatch::digest($row);
if ($normal(base_path()) !== $normal($root) || $normal(app()->environmentPath()) !== $normal($root) || app()->getLocale() !== 'uk'
    || Package::BASE_SHA !== 'e0720938bad114c7e43e8e6efafa8355c1993fc1' || Package::PAGE_IDS !== [129, 121, 127]
    || Package::CATEGORY_IDS !== [20, 20, 20] || Package::LEVELS !== [null, null, null]
    || Patch::BEFORE_SHA !== $beforeSha || Patch::CONSUMERS_SHA !== $consumerSha) {
    throw new RuntimeException('Exact served application/locale/M76 scope differs.');
}
App\Services\M51ContentPatch::assertConsumerCapture($before, $consumers);
[$sourceBefore, $patch, $master, $contract] = Package::load($root); Package::validate($sourceBefore, $patch, $master, $contract);
foreach (Package::profile()['artifacts'] as $path => $sha) { $same($hash($root.'/'.$path), $sha, 'Frozen M76 artifact'); $inputs[$root.'/'.$path] = $sha; }
$manifest = json_decode(file_get_contents($root.'/'.Package::CHECKSUMS), true, flags: JSON_THROW_ON_ERROR);
foreach ([...$manifest['artifacts'], ...$manifest['active_definitions']] as $record) {
    $file = $root.'/'.$record['path']; $same($hash($file), $record['sha256'], 'Frozen complete artifact/definition manifest');
    $same(filesize($file), $record['bytes'], 'Frozen manifest exact byte length'); $inputs[$file] = $record['sha256'];
}
$previousPatch = $previousMaster = $revision = null;
$pathPredecessors = [
    'database/content-patches/m76-relative-each-reciprocal.v1.0.1.json' => 'database/content-patches/m76-relative-each-reciprocal.v1.json',
    'docs/content/m76-relative-each-reciprocal.v1.0.1.json' => 'docs/content/m76-relative-each-reciprocal.v1.0.0.json',
    'docs/content/m76-source-checksums.v1.0.1.json' => 'docs/content/m76-source-checksums.v1.0.0.json',
];
$successorSyncSha = 'b70e89462582e1a1938b317672aacd5df5da23d1a7abdb9c0f553d98619e568a';
if ($successorMode) {
    $same([Package::SOURCE, Package::MASTER, Package::CHECKSUMS], array_keys($pathPredecessors), 'Exact successor artifact paths');
    $same([Package::SOURCE_SHA, Package::MASTER_SHA, Package::CHECKSUMS_SHA], [
        'e5320ab32be7f15684797173a46f68892fc492e2821b2c41f6306cd730c8e5fc',
        '33fa160344fcc980d8d980aadf19e0db03212b1a54a443100e2f24c3bc033738',
        '3a8d147f12436087489fcba478f9fcda6442fdf28c3c4c5b550909b0f64f2e6c'], 'Exact reviewed successor artifact bytes');
    foreach ([Anchor::PREVIOUS_PATCH => Anchor::PREVIOUS_PATCH_SHA, Anchor::PREVIOUS_MASTER => Anchor::PREVIOUS_MASTER_SHA,
        'docs/content/m76-source-checksums.v1.0.0.json' => 'c1006fa98d4cca96bb0bbcb4177737210cdef646c518675dad5f1671777b923c',
        Anchor::REVISION => '9d30fa87cd71ba53730f7f48d77eec73fb66c245f4149db2a296ee64a8b1bbfb'] as $path => $sha) {
        $same($hash($root.'/'.$path), $sha, 'Immutable predecessor/successor ledger bytes'); $inputs[$root.'/'.$path] = $sha;
    }
    $previousPatch = json_decode(file_get_contents($root.'/'.Anchor::PREVIOUS_PATCH), true, flags: JSON_THROW_ON_ERROR);
    $previousMaster = json_decode(file_get_contents($root.'/'.Anchor::PREVIOUS_MASTER), true, flags: JSON_THROW_ON_ERROR);
    $revision = json_decode(file_get_contents($root.'/'.Anchor::REVISION), true, flags: JSON_THROW_ON_ERROR);
    $transition = Anchor::exactTransition($previousPatch, $patch, $previousMaster, $master);
    $same($revision['baseline'] ?? null, ['file' => $finalName, 'sha256' => $finalSha], 'Successor ledger independent predecessor');
    $same($revision['transition']['before_body_sha256'] ?? null, hash('sha256', $transition['before']), 'Successor exact old hero body');
    $same($revision['transition']['after_body_sha256'] ?? null, hash('sha256', $transition['after']), 'Successor exact new hero body');
    $same($revision['transition']['allowed_field'] ?? null, 'body', 'Successor only body field');
    $same([$revision['transition']['id'] ?? null, $revision['transition']['page_id'] ?? null, $revision['transition']['uuid'] ?? null],
        [4077, 127, 'a4b3630c-46f0-5de6-914d-459208252fc6'], 'Successor exact continuing header identity');
    foreach (['original_final_file' => $finalName, 'original_final_sha256' => $finalSha, 'successor_state' => 'after',
        'predecessor_sql_exact' => true, 'independent_before_projection_exact' => true, 'reviewed_protected_projection_exact' => true,
        'all_old_rows_and_timestamps_exact' => true, 'source_sync_unchanged' => true, 'source_sync_file' => 'source-sync-v2.json',
        'revision_sha256' => '9d30fa87cd71ba53730f7f48d77eec73fb66c245f4149db2a296ee64a8b1bbfb',
        'update_operations' => 1, 'initial_update_operations' => 42, 'continuing_uk_rows' => 39, 'unchanged_non_uk_rows' => 56,
        'insert_operations' => 0, 'deleted_rows' => 0, 'cumulative_update_operations' => 43, 'distinct_changed_rows' => 42,
        'anchor_body_updates' => 1, 'successor_version' => '1.0.1', 'source_sync_sha256' => $successorSyncSha,
        'db_writes' => 0, 'http_requests' => 0] as $field => $expected) { $same($activeFinal[$field] ?? null, $expected, 'Reviewed successor final '.$field); }
    $same($activeFinal['runtime'] ?? null, $final['runtime'], 'Successor same local runtime');
    $same($activeFinal['normalized_before'] ?? null, $before['sql_before'], 'Successor independently reconstructed original BEFORE');
    $same($activeFinal['protected'] ?? null, $final['protected'], 'Successor original protected rows');
    $same($activeFinal['predecessor_sql'] ?? null, $final['sql_after'], 'Successor exact independently reconstructed all46 predecessor tables');
    $same(count($activeFinal['sql_after'] ?? []), 46, 'Successor complete all46 actual table fingerprints');
    $anchorPlanName = $activeFinal['plan_file'] ?? ''; $anchorBackupName = $activeFinal['backup_file'] ?? '';
    if (preg_match('/^anchor-preview-v[1-9][0-9]*\.json$/D', $anchorPlanName) !== 1
        || preg_match('/^anchor-backup-v[1-9][0-9]*\.json$/D', $anchorBackupName) !== 1) { throw new RuntimeException('Exact successor preview/backup basenames differ.'); }
    $anchorPlan = $read($anchorPlanName, $hash($dir.'/'.$anchorPlanName));
    $anchorBackup = $read($anchorBackupName, $activeFinal['backup_sha256']); $anchorUnsigned = $anchorPlan; unset($anchorUnsigned['sha256']);
    $same($anchorPlan, $anchorBackup, 'Successor preview/backup complete data');
    $same(file_get_contents($dir.'/'.$anchorPlanName), file_get_contents($dir.'/'.$anchorBackupName), 'Successor exclusive backup raw bytes');
    $same($digest($anchorUnsigned), $activeFinal['plan_digest'], 'Successor exact reviewed preview digest');
    $same($anchorPlan['sha256'], $activeFinal['plan_digest'], 'Successor signed preview digest');
    $same([$anchorPlan['patch'], $anchorPlan['version'], $anchorPlan['names'], $anchorPlan['state'], $anchorPlan['inserts']],
        ['m76-anchor-revision-v1.0.1', 1, [Package::OWNERS[2]], 'before', []], 'Successor exact finite preview scope');
    $same($anchorPlan['protected'], $final['sql_after'], 'Successor plan protects entire predecessor');
    $same(count($anchorPlan['updates']), 1, 'Successor exactly one update'); $change = $anchorPlan['updates'][0];
    $same([$change['table'], $change['id'], $change['seeder'], $change['uuid'], $change['before'], $change['after']],
        ['text_blocks', 4077, Package::OWNERS[2], 'a4b3630c-46f0-5de6-914d-459208252fc6', ['body' => $transition['before']], ['body' => $transition['after']]], 'Successor exact one-body update');
    $same($change['before_sha256'], $digest($change['before']), 'Successor old body digest'); $same($change['after_sha256'], $digest($change['after']), 'Successor new body digest');
    foreach ($anchorPlan['sources'] as $path => $sha) {
        $file = str_starts_with($path, 'private:') ? $dir.'/'.substr($path, 8) : $root.'/'.$path;
        $same($hash($file), $sha, 'Every reviewed successor preview source'); $inputs[$file] = $sha;
    }
}
$planName = $final['plan_file'] ?? ''; $backupName = $final['backup_file'] ?? ''; $backupSha = $final['backup_sha256'] ?? '';
if (preg_match('/^preview-v[1-9][0-9]*\.json$/D', $planName) !== 1 || preg_match('/^backup-v[1-9][0-9]*\.json$/D', $backupName) !== 1
    || preg_match('/^[a-f0-9]{64}$/D', $backupSha) !== 1) { throw new RuntimeException('Reviewed final preview/backup lineage differs.'); }
$plan = $read($planName, $hash($dir.'/'.$planName)); $backup = $read($backupName, $backupSha); $unsigned = $plan; unset($unsigned['sha256']);
if (($plan['sha256'] ?? null) !== ($final['plan_digest'] ?? null) || $digest($unsigned) !== $final['plan_digest'] || $plan !== $backup
    || file_get_contents($dir.'/'.$planName) !== file_get_contents($dir.'/'.$backupName)
    || ($plan['patch'] ?? null) !== 'm76-relative-each-reciprocal-standard-view-v1' || ($plan['version'] ?? null) !== 1
    || ($plan['names'] ?? null) !== Package::OWNERS || ($plan['state'] ?? null) !== 'before'
    || ($plan['inserts'] ?? null) !== [] || ($plan['deletes'] ?? []) !== []
    || ($final['update_operations'] ?? null) !== count($plan['updates'] ?? [])
    || ($final['distinct_changed_rows'] ?? null) !== count($plan['updates'] ?? [])) { throw new RuntimeException('Exact reviewed M76 plan/backup differs.'); }
$historicalSyncName = $successorMode ? 'source-sync-v1.json' : Patch::SYNC_RECEIPT;
$historicalPaths = Patch::sourcePaths($patch, $contract, $root);
if ($successorMode) { $historicalPaths = array_map(static fn ($path) => $pathPredecessors[$path] ?? $path, $historicalPaths); }
$expectedSources = array_values(array_unique([...$historicalPaths, 'private:'.Patch::BEFORE_FILE,
    'private:'.Patch::CONSUMERS_FILE, 'private:'.$historicalSyncName])); sort($expectedSources);
$actualSources = array_keys($plan['sources']); sort($actualSources); $same($actualSources, $expectedSources, 'Complete reviewed source list');
$historicalSync = $read($historicalSyncName, $plan['sources']['private:'.$historicalSyncName]);
$sync = $successorMode ? $read('source-sync-v2.json', $successorSyncSha) : $historicalSync;
$syncPaths = array_column($sync['files'], 'path'); sort($syncPaths);
$same($syncPaths, Patch::syncPaths($patch, $contract), 'Exact current served/worktree sync scope');
$historicalSyncPaths = array_column($historicalSync['files'], 'path'); sort($historicalSyncPaths);
$expectedHistoricalSyncPaths = $successorMode ? array_map(static fn ($path) => $pathPredecessors[$path] ?? $path, $syncPaths) : $syncPaths;
sort($expectedHistoricalSyncPaths); $same($historicalSyncPaths, $expectedHistoricalSyncPaths, 'Exact immutable historical source-sync scope');
$oldSyncByPath = array_column($historicalSync['files'], null, 'path'); $syncByPath = array_column($sync['files'], null, 'path');
$driftPaths = $successorMode ? ['app/Services/M76ContentPatch.php', 'app/Support/M76PronounRelations.php', $patch['targets'][2]['path']] : [];
foreach ($sync['files'] as $row) {
    foreach ([$root.'/'.$row['path'] => $row['root_after_sha256'], Patch::SOURCE_WORKTREE.'/'.$row['path'] => $row['worktree_sha256']] as $file => $sha) {
        $same($hash($file), $sha, 'Exact current source-sync bytes'); $inputs[$file] = $sha;
    }
}
$actualDrift = [];
foreach ($historicalSync['files'] as $row) {
    $path = $row['path']; $current = in_array($path, $driftPaths, true) ? $syncByPath[$path] : $row;
    if (in_array($path, $driftPaths, true)) {
        if ($row['root_after_sha256'] === $current['root_after_sha256'] || $row['worktree_sha256'] === $current['worktree_sha256']) {
            throw new RuntimeException('Declared successor drift did not change both exact source locations.');
        }
        $actualDrift[] = $path;
    }
    foreach ([$root.'/'.$path => $current['root_after_sha256'], Patch::SOURCE_WORKTREE.'/'.$path => $current['worktree_sha256']] as $file => $sha) {
        $same($hash($file), $sha, 'Immutable historical source or exact declared successor drift'); $inputs[$file] = $sha;
    }
}
sort($actualDrift); $expectedDrift = $driftPaths; sort($expectedDrift); $same($actualDrift, $expectedDrift, 'Only three exact historical source byte transitions');
foreach ($plan['sources'] as $path => $sha) {
    $file = str_starts_with($path, 'private:') ? $dir.'/'.substr($path, 8) : $root.'/'.$path;
    if (in_array($path, $driftPaths, true)) {
        $same($sha, $oldSyncByPath[$path]['root_after_sha256'], 'Original preview exact predecessor source bytes');
        $currentSha = $syncByPath[$path]['root_after_sha256']; $same($hash($file), $currentSha, 'Reviewed explicit successor source bytes'); $inputs[$file] = $currentSha;
    } else { $same($hash($file), $sha, 'Reviewed preview source'); $inputs[$file] = $sha; }
}
foreach ([$historicalSync, $sync] as $record) {
    $same($record['visual_before'] ?? null, ['file' => Patch::BEFORE_FILE, 'sha256' => $beforeSha], 'Sync independent BEFORE');
    $same($record['served_root'] ?? null, $root, 'Sync served root'); $same($record['source_worktree'] ?? null, Patch::SOURCE_WORKTREE, 'Sync worktree');
    $same($record['base_sha'] ?? null, Package::BASE_SHA, 'Sync base');
}
foreach ([$before['physical_hashes'], $before['visual_hashes'], $before['frozen_hashes']] as $kind => $records) {
    foreach ($records as $path => $sha) {
        if ($kind > 0 && preg_match('~^(?:[A-Za-z]:|/|\\\\)|(?:^|[/\\\\])\.\.(?:[/\\\\]|$)~D', $path)) {
            throw new RuntimeException('Visual/frozen pin is not repository relative.');
        }
        $file = $kind === 0 ? $path : $root.'/'.$path; $same($hash($file), $sha, 'Physical/visual/frozen source'); $inputs[$file] = $sha;
    }
}
$controllerPath = 'app/Http/Controllers/PageController.php'; $controllerSha = $before['shared_source_hashes'][$controllerPath]['root'] ?? null;
if (!is_string($controllerSha)) { throw new RuntimeException('Independent native PageController pin missing.'); }
$metadataPins = [
    $controllerPath => $controllerSha,
    'app/Support/PageMetadata.php' => '1d782d27483bdc0cff63515a1d69153dc908adbf2c3b3eea47bec35f141e0a9e',
    'app/Http/Middleware/AddStructuredData.php' => 'ea3774ba90512bfa2bf7cee9277b87a3951e4a43fbd48eeed21ffb83ec2a58ac',
];
foreach ($metadataPins as $path => $sha) { $same($hash($root.'/'.$path), $sha, 'Unchanged native metadata/controller policy'); $inputs[$root.'/'.$path] = $sha; }
foreach ([App\Http\Controllers\PageController::class => $controllerPath, App\Support\PageMetadata::class => 'app/Support/PageMetadata.php',
    App\Http\Middleware\AddStructuredData::class => 'app/Http/Middleware/AddStructuredData.php', Package::class => 'app/Support/M76PronounRelations.php',
    Guard::class => 'app/Services/M76LocalTargetGuard.php'] as $class => $path) {
    $same($normal((new ReflectionClass($class))->getFileName()), $normal($root.'/'.$path), 'Actual served helper location');
}

// The pre-apply values are independently frozen in both author artifacts. Check
// them against the current sources before issuing even the first local GET.
$editorialPath = $successorMode ? 'docs/content/m76-relative-each-reciprocal.editorial.v1.0.1.json' : 'docs/content/m76-relative-each-reciprocal.editorial.v1.json';
$editorialSha = $successorMode ? '4e250f7abb0a5c5c511c3fc793d498bd28568d15ee27e3c47873cd42f7d6f7a1' : '8efefe425e5d9cadc57dc8313a080ca8dceae70f57d7b27c75fd044cf31cb486';
$same($hash($root.'/'.$editorialPath), $editorialSha, 'Frozen editorial metadata artifact'); $inputs[$root.'/'.$editorialPath] = $editorialSha;
$editorial = json_decode(file_get_contents($root.'/'.$editorialPath), true, flags: JSON_THROW_ON_ERROR);
$approvedMetadata = $master['editorial_review']['expected_http_metadata'] ?? null;
$same($approvedMetadata, $editorial['expected_http_metadata'] ?? null, 'Master/editorial pre-apply metadata receipt');
if (!is_array($approvedMetadata) || ($approvedMetadata['schema_version'] ?? null) !== 1
    || ($approvedMetadata['independent_before_sha256'] ?? null) !== $beforeSha
    || ($approvedMetadata['application_bootstrapped'] ?? null) !== false || ($approvedMetadata['db_queries'] ?? null) !== 0
    || ($approvedMetadata['db_writes'] ?? null) !== 0 || ($approvedMetadata['http_requests'] ?? null) !== 0
    || count($approvedMetadata['metadata'] ?? []) !== 3) { throw new RuntimeException('Frozen pre-apply metadata receipt is incomplete.'); }
$same($approvedMetadata['native_policy_pins'] ?? null, array_intersect_key($metadataPins, array_flip([
    'app/Support/PageMetadata.php', 'app/Http/Middleware/AddStructuredData.php'])), 'Pre-apply native metadata policy pins');
$metadataPlain = static fn (string $value): string => trim((string) preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
$approvedByOwner = [];
foreach ($patch['targets'] as $i => $target) {
    $owner = Package::OWNERS[$i]; $expected = $approvedMetadata['metadata'][$i]; $definition = $target['after'];
    $same($expected['identity'] ?? null, $owner, 'Pre-apply metadata owner order');
    $same($expected['raw_title'] ?? null, $definition['page']['title'], 'Pre-apply raw title');
    $same($expected['preserved_h1'] ?? null, Package::RENDERED_TITLES[$i], 'Pre-apply preserved H1');
    $same($expected['local_url'] ?? null, $target['local_url'], 'Pre-apply exact local URL');
    $heroes = array_values(array_filter($definition['page']['blocks'], static fn ($block) => $block['type'] === 'hero'));
    $same(count($heroes), 1, 'One exact frozen metadata hero'); $hero = json_decode($heroes[0]['body'], true, flags: JSON_THROW_ON_ERROR);
    $same(hash('sha256', $hero['intro']), $expected['hero_intro_sha256'] ?? null, 'Pre-apply hero intro bytes');
    $same(App\Support\PageMetadata::theory($definition['page']['title'], '', $hero['intro'], $owner, 'uk'),
        $expected['prospective_page_metadata'] ?? null, 'Frozen pre-apply PageMetadata title/description');
    $resourceDescription = null;
    foreach ($definition['page']['blocks'] as $block) {
        $body = json_decode($block['body'], true); if (!is_array($body)) { continue; }
        foreach (['intro', 'description', 'summary', 'subtitle', 'text'] as $field) {
            if (is_string($body[$field] ?? null) && trim(strip_tags($body[$field])) !== '') {
                $resourceDescription = Illuminate\Support\Str::limit($metadataPlain($body[$field]), 300, '…'); break 2;
            }
        }
    }
    if (!is_string($resourceDescription) || $resourceDescription === '') { throw new RuntimeException('Frozen pre-apply structured description is empty.'); }
    $same($resourceDescription, $expected['prospective_learning_resource_description'] ?? null, 'Frozen pre-apply LearningResource description');
    $oldHttp = array_values(array_filter($before['http'], static fn ($row) => ($row['kind'] ?? null) === 'theory' && ($row['identity'] ?? null) === $owner));
    $same(count($oldHttp), 1, 'Pre-apply independent protected metadata row');
    $same(array_intersect_key($oldHttp[0], array_flip(['h1_count', 'h1', 'title', 'breadcrumb', 'canonical', 'robots_header', 'robots_meta'])),
        $expected['independent_before_protected'] ?? null, 'Frozen pre-apply protected metadata');
    $approvedByOwner[$owner] = $expected;
}

$db = Illuminate\Support\Facades\DB::connection(); Guard::assertConfiguredDatabase($db); $guard = new Guard;
$physical = $guard->physicalEvidence();
$server = (array) $db->selectOne('SELECT DATABASE() AS db, @@port AS port, @@pid_file AS pid_file, @@datadir AS datadir, @@version_compile_os AS os');
foreach (['datadir', 'pid_file'] as $key) { if (!is_string($server[$key] ?? null) || preg_match('~^[A-Za-z]:/~D', str_replace('\\', '/', $server[$key])) !== 1) { throw new RuntimeException('Physical MySQL path is not absolute local Windows.'); } }
$dataPath = $normal($server['datadir']); $pidPath = $normal($server['pid_file']); $pid = trim((string) file_get_contents($server['pid_file']));
if (($server['db'] ?? null) !== 'gr2' || (int) ($server['port'] ?? 0) !== 3306 || !str_starts_with($server['os'] ?? '', 'Win')
    || !ctype_digit($pid) || !str_starts_with($pidPath, $dataPath.'/') || str_starts_with($dataPath, '//')) { throw new RuntimeException('Actual local MySQL identity differs.'); }
$guard->assertEvidence($physical, $normal($root), (int) $pid, 3306);
$runtime = Guard::publicIdentity($db, $root.'/public'); $same($runtime, $before['identity'], 'Independent local runtime'); $same($runtime, $final['runtime'], 'Reviewed final runtime');
$readOnly = static function (Closure $callback) use ($db): mixed {
    $db->statement('SET TRANSACTION READ ONLY'); $db->beginTransaction();
    try { return $callback(); } finally { $db->rollBack(); }
};

// Reconstruct the raw BEFORE from full current rows, reversing only the declared
// Page.text and UK body/type/heading deltas. Every timestamp/other field stays exact.
$oldPages = $newPages = $oldUk = $newUk = []; $foreignCount = 0;
foreach ($patch['targets'] as $i => $target) {
    $owner = Package::OWNERS[$i]; $captured = $before['pages'][$owner]; $raw = $sourceBefore['targets'][$i]['before'];
    Patch::assertDefinitionScope($raw, $target['after'], $target, $i);
    $same($before['definitions'][$owner]['definition'], $raw, 'Independent source BEFORE');
    $same(json_decode(file_get_contents($root.'/'.$target['path']), true, flags: JSON_THROW_ON_ERROR), $target['after'], 'Active exact AFTER definition');
    $page = $captured['page']; $oldPages[$page['id']] = $page; $newPages[$page['id']] = array_replace($page, ['text' => $target['after']['page']['subtitle_text']]);
    $same($page['id'], Package::PAGE_IDS[$i], 'Independent actual Page ID'); $same($page['page_category_id'], 20, 'Independent category');
    $positions = [];
    foreach ($captured['blocks'] as $row) {
        if ($row['locale'] !== 'uk') { ++$foreignCount; continue; }
        $position = $row['sort_order'];
        if (!is_int($position) || $position < 0 || $position > 12 || isset($positions[$position]) || isset($oldUk[$row['id']])
            || $row['seeder'] !== $owner || $row['page_id'] !== $page['id'] || $row['page_category_id'] !== 20 || $row['level'] !== null) {
            throw new RuntimeException('Independent continuing UK row identity differs.');
        }
        $positions[$position] = true;
        $oldConfig = $position === 0 ? ['type' => 'subtitle', 'column' => 'header', 'body' => $raw['page']['subtitle_html'], 'uuid_key' => $raw['page']['subtitle_uuid_key'] ?? 'subtitle'] : $raw['page']['blocks'][$position - 1];
        $newConfig = $position === 0 ? array_replace($oldConfig, ['body' => $target['after']['page']['subtitle_html']]) : $target['after']['page']['blocks'][$position - 1];
        $same(Source::uuid($owner, $oldConfig, $position), $row['uuid'], 'Original UUID'); $same(Source::uuid($owner, $newConfig, $position), $row['uuid'], 'Continuing UUID');
        foreach (['type', 'column', 'heading', 'level', 'css_class', 'body'] as $field) { $same($row[$field], $oldConfig[$field] ?? null, 'Independent source/row field'); }
        $newValues = []; foreach (['body', 'type', 'heading'] as $field) { $newValues[$field] = $newConfig[$field] ?? null; }
        $oldUk[$row['id']] = $row; $newUk[$row['id']] = array_replace($row, $newValues);
    }
    $slots = array_keys($positions); sort($slots); $same($slots, range(0, 12), 'Complete continuing slot vector');
}
$same([count($oldPages), count($oldUk), $foreignCount], [3, 39, 56], 'Independent full target/foreign inventory');
$scan = static function () use ($db, $before, $oldPages, $newPages, $oldUk, $newUk, $digest, $sameRow, $same): array {
    $tables = $db->getSchemaBuilder()->getTableListing(schema: $db->getDatabaseName(), schemaQualified: false); sort($tables);
    $same($tables, array_keys($before['sql_before']), 'All46 table names'); $actual = $normalized = $protected = []; $seenPages = $seenUk = [];
    foreach ($tables as $table) {
        $schema = $db->getSchemaBuilder()->getColumns($table);
        if (isset($before['schema'][$table])) { $same($schema, $before['schema'][$table], 'Captured table schema'); }
        $columns = array_column($schema, 'name'); $query = $db->table($table);
        foreach (in_array('id', $columns, true) ? ['id'] : $columns as $column) { $query->orderBy($column); }
        $currentHash = hash_init('sha256'); $oldHash = hash_init('sha256'); $protectedHash = hash_init('sha256'); $count = $protectedCount = 0;
        foreach ($query->cursor() as $record) {
            $row = (array) $record; $id = $row['id'] ?? null; hash_update($currentHash, $digest($row)."\n"); ++$count; $old = $row; $skip = false;
            if ($table === 'pages' && isset($oldPages[$id])) { $sameRow($row, $newPages[$id], 'Full current target Page including timestamps'); $old = $oldPages[$id]; $seenPages[$id] = true; }
            if ($table === 'text_blocks' && isset($oldUk[$id])) { $sameRow($row, $newUk[$id], 'Full continuing UK row including timestamps'); $old = $oldUk[$id]; $seenUk[$id] = true; $skip = true; }
            hash_update($oldHash, $digest($old)."\n"); if ($skip) { continue; }
            if ($table === 'pages' && isset($oldPages[$id])) { unset($row['text']); }
            hash_update($protectedHash, $digest($row)."\n"); ++$protectedCount;
        }
        $actual[$table] = ['count' => $count, 'sha256' => hash_final($currentHash)]; $normalized[$table] = ['count' => $count, 'sha256' => hash_final($oldHash)];
        $protected[$table] = ['count' => $protectedCount, 'sha256' => hash_final($protectedHash)];
    }
    $same([count($seenPages), count($seenUk)], [3, 39], 'Every original target row present');
    return compact('actual', 'normalized', 'protected');
};
$sqlBefore = $readOnly($scan); $same($sqlBefore['actual'], $activeFinal['sql_after'], 'Current all46 reviewed AFTER tables');
$same($sqlBefore['normalized'], $before['sql_before'], 'Fresh full46 BEFORE reconstruction');
$same($sqlBefore['protected'], $final['protected'], 'Fresh reviewed protected rows'); $same($sqlBefore['protected'], $plan['protected'], 'Exact preview protected rows');

$resolver = new class extends App\Http\Controllers\PageController {
    public function __construct() {}
    public function path(PageCategory $category): string { return $this->categorySlugPath($category); }
    public function resolve(string $path): ?PageCategory { return $this->resolveCategoryBySlugPath($path); }
    public function displayTitle(?string $subtitle, string $fallback): string { return $this->extractLocalizedTitleFromSubtitle($subtitle) ?? $fallback; }
};
$routes = $navigation = $targetByPath = []; $chips = $chipAnchors = $formLinks = 0;
$readOnly(static function () use (&$routes, &$navigation, &$targetByPath, &$chips, &$chipAnchors, &$formLinks, $master, $patch, $sourceBefore, $before, $resolver, $same, $root, $hash, &$inputs): void {
    foreach ($master['lessons'] as $i => $lesson) {
        $navs = array_values(array_filter($lesson['blocks'], static fn ($block) => $block['type'] === 'navigation-chips'));
        $same(count($navs), 1, 'One actually rendered native navigation group'); $data = $navs[0]['data']; $same(count($data['items']), 9, 'Nine footer chips');
        $oldItems = json_decode($sourceBefore['targets'][$i]['before']['page']['blocks'][11]['body'], true, flags: JSON_THROW_ON_ERROR)['items'];
        $labelState = static fn (array $items): array => array_map(static fn ($item) => array_intersect_key($item, array_flip(['label', 'current'])), $items);
        $same($labelState($data['items']), $labelState($oldItems), 'All original labels/current states');
        $owner = $lesson['identity']; $ownerPath = parse_url($patch['targets'][$i]['local_url'], PHP_URL_PATH); $extra = [];
        if ($i === 2) {
            $legacy = $lesson['blocks'][10]; $same([$legacy['type'], $legacy['column']], ['forms-grid', 'footer'], 'Finite footer form projection');
            $extra = $legacy['data']['items']; $same(count($extra), 2, 'Two preserved extra links'); $formLinks += count($extra);
        }
        $targetByPath[$ownerPath] = ['index' => $i, 'owner' => $owner, 'data' => $data, 'forms' => $extra]; $current = [];
        $navigation[$owner] = ['chips' => $data['items'], 'footer_forms' => $extra];
        foreach ([...$data['items'], ...$extra] as $n => $item) {
            $url = $item['url'] ?? null;
            if (!is_string($url) || preg_match('~^/theory/zaimennyky-ta-vkazivni-slova/([a-z0-9-]+)$~D', $url, $match) !== 1
                || !is_bool($item['current'] ?? null) || !is_string($item['label'] ?? null) || trim($item['label']) === '') { throw new RuntimeException('Exact nonempty local footer item differs.'); }
            if ($n < 9) { ++$chips; if ($item['current']) { $current[] = $url; } else { ++$chipAnchors; } }
            elseif ($item['current']) { throw new RuntimeException('Extra footer form is not a current chip.'); }
            $category = $resolver->resolve('zaimennyky-ta-vkazivni-slova');
            if (!$category instanceof PageCategory || $category->id !== 20 || $category->type !== 'theory' || $category->language !== 'uk') { throw new RuntimeException('Native exact UK category resolution differs.'); }
            $page = Page::query()->where('page_category_id', 20)->where('slug', $match[1])->where('type', 'theory')->sole();
            $same(route('theory.show', ['categoryPath' => $resolver->path($category), 'pageSlug' => $page->slug], false), $url, 'Native actual destination');
            $definitionPath = Acceptance::definitionPath($page->seeder); $file = $root.'/'.$definitionPath; $sourceSha = $hash($file);
            if (isset($inputs[$file])) { $same($sourceSha, $inputs[$file], 'Repeated pinned destination source'); } $inputs[$file] = $sourceSha;
            $definition = json_decode(file_get_contents($file), true, flags: JSON_THROW_ON_ERROR);
            if (($definition['seeder']['class'] ?? null) !== $page->seeder || ($definition['slug'] ?? null) !== $page->slug
                || ($definition['page']['locale'] ?? null) !== 'uk' || ($definition['page']['category']['slug'] ?? null) !== 'zaimennyky-ta-vkazivni-slova') { throw new RuntimeException('Resolved SQL page has no exact versioned UK source.'); }
            $same($page->getRawOriginal('title'), $definition['page']['title'], 'Versioned raw destination Page title');
            $record = ['identity' => $page->seeder, 'page_id' => $page->id, 'category_id' => 20, 'raw_title' => $page->getRawOriginal('title'), 'url' => $url,
                'expected_h1' => $resolver->displayTitle($definition['page']['subtitle_html'] ?? null, $page->getRawOriginal('title'))];
            if (isset($routes[$url])) { $same($routes[$url]['record'], $record, 'Repeated destination exact identity'); }
            $routes[$url] = ['record' => $record, 'definition' => $definition];
        }
        $same($current, [$ownerPath], 'One exact current resource'); $same($routes[$ownerPath]['record']['page_id'], Package::PAGE_IDS[$i], 'Actual current Page ID');
    }
});
$same([$chips, $chipAnchors, $formLinks, count($routes)], [27, 24, 2, 9], 'Exact29 source items26 clickable links9 destinations');
$plain = static fn (string $value): string => trim((string) preg_replace('/[\s\x{00a0}]+/u', ' ', html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8')));
$started = gmdate('c'); $rows = []; $renderedChips = $renderedChipAnchors = $renderedForms = 0;
foreach ($routes as $path => $resolved) {
    echo json_encode(['checking_local_path' => $path], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR), PHP_EOL;
    $url = 'http://gramlyze.loc'.$path; $curl = curl_init($url); $robots = null; $location = null;
    curl_setopt_array($curl, [CURLOPT_HTTPGET => true, CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => false, CURLOPT_MAXREDIRS => 0,
        CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_TIMEOUT => 60, CURLOPT_PROXY => '', CURLOPT_NOPROXY => '*', CURLOPT_NETRC => CURL_NETRC_IGNORED,
        CURLOPT_COOKIE => '', CURLOPT_PROTOCOLS => CURLPROTO_HTTP, CURLOPT_RESOLVE => ['gramlyze.loc:80:127.0.0.1'], CURLOPT_HTTPHEADER => ['Accept: text/html'],
        CURLOPT_HEADERFUNCTION => static function ($handle, string $header) use (&$robots, &$location): int {
            if (stripos($header, 'X-Robots-Tag:') === 0) { $robots = trim(substr($header, 13)); }
            if (stripos($header, 'Location:') === 0) { $location = trim(substr($header, 9)); }
            return strlen($header);
        }]);
    try {
        $html = curl_exec($curl); $status = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        if (!is_string($html) || $status !== 200 || $location !== null || curl_getinfo($curl, CURLINFO_PRIMARY_IP) !== '127.0.0.1'
            || curl_getinfo($curl, CURLINFO_PRIMARY_PORT) !== 80 || curl_getinfo($curl, CURLINFO_EFFECTIVE_URL) !== $url || curl_getinfo($curl, CURLINFO_REDIRECT_COUNT) !== 0) { throw new RuntimeException('Anonymous direct loopback GET failed: '.$path); }
    } finally { unset($curl); }
    $dom = new DOMDocument('1.0', 'UTF-8'); $oldErrors = libxml_use_internal_errors(true);
    try { $loaded = $dom->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NONET); } finally { libxml_clear_errors(); libxml_use_internal_errors($oldErrors); }
    if (!$loaded) { throw new RuntimeException('Destination HTML cannot be parsed.'); } $xp = new DOMXPath($dom);
    $one = static function (string $query, ?DOMNode $context = null) use ($xp): DOMElement {
        $nodes = $xp->query($query, $context); if ($nodes->length !== 1 || !$nodes->item(0) instanceof DOMElement) { throw new RuntimeException('Expected exactly one native DOM metadata node.'); }
        return $nodes->item(0);
    };
    $main = $one('//*[@data-theory-main]'); $h1 = $one('//h1'); $canonical = $one('//link[@rel="canonical"]');
    $same($plain($h1->textContent), $resolved['record']['expected_h1'], 'Source/native-derived H1');
    $same($canonical->getAttribute('href'), 'https://gramlyze.com'.$path, 'Canonical string only; never requested'); $same($robots, 'noindex, nofollow, noarchive', 'Local robots header');
    $facts = ['h1_count' => 1, 'h1' => $plain($h1->textContent), 'title' => $plain($one('//title')->textContent),
        'breadcrumb' => $plain($one('//nav[@aria-label][contains(@class,"mb-8")]')->textContent), 'canonical' => $canonical->getAttribute('href'),
        'robots_header' => $robots, 'robots_meta' => $xp->query('//meta[@name="robots"]')->item(0)?->getAttribute('content')];
    $definition = $resolved['definition']; $hero = null;
    foreach (['hero-v2', 'hero'] as $type) { foreach ($definition['page']['blocks'] as $block) { if ($block['type'] === $type) { $hero = json_decode($block['body'], true, flags: JSON_THROW_ON_ERROR); break 2; } } }
    $seo = App\Support\PageMetadata::theory($definition['page']['title'], '', $hero['intro'] ?? '', $resolved['record']['identity'], 'uk');
    $same($facts['title'], $seo['title'], 'Unchanged native source-bound SEO title');
    $description = $one('//meta[@name="description"]')->getAttribute('content'); $same($description, $seo['description'], 'Native source-bound SEO description');
    $row = $resolved['record'] + ['http_status' => $status, 'html_sha256' => hash('sha256', $html), 'target' => isset($targetByPath[$path]), 'metadata' => $facts + ['description' => $description]];
    if (isset($targetByPath[$path])) {
        $target = $targetByPath[$path]; $i = $target['index']; $owner = $target['owner'];
        $approved = $approvedByOwner[$owner];
        $same(['title' => $facts['title'], 'description' => $description], $approved['prospective_page_metadata'], 'Served metadata equals frozen pre-apply approval');
        $oldHttp = array_values(array_filter($before['http'], static fn ($r) => ($r['kind'] ?? null) === 'theory' && ($r['identity'] ?? null) === $owner));
        $same(count($oldHttp), 1, 'One independent original target HTTP record'); $oldHttp = $oldHttp[0];
        $same($oldHttp['url'], $url, 'Independent target URL'); $same($oldHttp['h1'], Package::RENDERED_TITLES[$i], 'Independent preserved H1');
        foreach (['h1_count', 'h1', 'title', 'breadcrumb', 'canonical', 'robots_header', 'robots_meta'] as $field) { $same($facts[$field], $oldHttp[$field], 'Protected original target metadata '.$field); }
        $resources = []; $resourceDescriptions = [];
        $visit = static function (mixed $value) use (&$visit, &$resources, &$resourceDescriptions): void {
            if (!is_array($value)) { return; }
            if (in_array('LearningResource', (array) ($value['@type'] ?? []), true)) { $resources[] = array_intersect_key($value, array_flip(['@type', 'url', 'name', 'headline', 'inLanguage'])); $resourceDescriptions[] = $value['description'] ?? null; }
            foreach ($value as $child) { if (is_array($child)) { $visit($child); } }
        };
        foreach ($xp->query('//script[@type="application/ld+json"]') as $script) { $visit(json_decode($script->textContent, true, flags: JSON_THROW_ON_ERROR)); }
        $oldResources = array_map(static fn ($r) => array_intersect_key($r, array_flip(['@type', 'url', 'name', 'headline', 'inLanguage'])), $oldHttp['learning_resources']);
        $same(count($resources), 1, 'One LearningResource'); $same($resources, $oldResources, 'Protected LearningResource identity');
        $expectedResourceDescription = null;
        foreach ($definition['page']['blocks'] as $block) {
            $body = json_decode($block['body'], true); if (!is_array($body)) { continue; }
            foreach (['intro', 'description', 'summary', 'subtitle', 'text'] as $field) { $value = $body[$field] ?? null;
                if (is_string($value) && trim(strip_tags($value)) !== '') { $expectedResourceDescription = Illuminate\Support\Str::limit($plain(strip_tags($value)), 300, '…'); break 2; }
            }
        }
        if (!is_string($expectedResourceDescription) || $expectedResourceDescription === '') { throw new RuntimeException('Frozen structured description source missing.'); }
        $same($resourceDescriptions, [$expectedResourceDescription], 'Native new-hero structured description');
        $same($resourceDescriptions, [$approved['prospective_learning_resource_description']], 'Served structured description equals frozen pre-apply approval');
        $anchors = []; foreach ($xp->query('//*[@id]') as $node) { $anchors[] = $node->getAttribute('id'); }
        $same(array_values(array_diff(array_unique($oldHttp['anchors']), array_unique($anchors))), [], 'Every original target anchor preserved');
        $same($xp->query('.//*[@data-theory-render-fallback]', $main)->length, 0, 'No target renderer fallback');
        $data = $target['data']; $nav = $one('.//nav[p[normalize-space(.)="'.$data['title'].'"]]', $main);
        $nodes = $xp->query('./div/*', $nav); $same($nodes->length, 9, 'Actual native9 chip DOM'); $links = [];
        foreach ($data['items'] as $n => $item) {
            $node = $nodes->item($n); $same($plain($node->textContent), $plain($item['label']), 'Complete footer label without prefix/format stripping'); ++$renderedChips;
            if ($item['current']) { $same($node->nodeName, 'span', 'Current item not clickable'); $same($item['url'], $path, 'Current owner resource'); }
            else { $same($node->nodeName, 'a', 'Noncurrent item clickable'); $same($node->getAttribute('href'), $item['url'], 'Actual exact footer href'); $links[] = $item['url']; ++$renderedChipAnchors; }
        }
        if ($i === 2) {
            $oldRows = array_values(array_filter($before['pages'][$owner]['blocks'], static fn ($r) => $r['locale'] === 'uk' && $r['sort_order'] === 11));
            $same(count($oldRows), 1, 'One original extra footer identity'); $formSection = $one('.//*[@id="block-'.$oldRows[0]['id'].'"]', $main);
            $cards = $xp->query('.//a[@data-theory-component="form"]', $formSection); $same($cards->length, 2, 'Two actual clickable native footer form cards');
            foreach ($target['forms'] as $n => $item) {
                $card = $cards->item($n); $same($card->getAttribute('href'), $item['url'], 'Actual native form href');
                $same($plain($one('./div[1]/span', $card)->textContent), $plain($item['label']), 'Complete native form label');
                $same($plain($one('./div[1]/h3', $card)->textContent), $plain($item['title']), 'Native form title');
                $same($plain($one('./div[1]/p', $card)->textContent), $plain(strip_tags($item['subtitle'])), 'Native form subtitle');
                $links[] = $item['url']; ++$renderedForms;
            }
        }
        $row['rendered_footer_links'] = $links; $row['rendered_footer_current'] = $path;
        $row['metadata']['learning_resource_identity'] = $resources[0]; $row['metadata']['learning_resource_description'] = $expectedResourceDescription;
        $row['metadata']['preserved_original_anchors'] = count(array_unique($oldHttp['anchors']));
    }
    $rows[$path] = $row;
}
$same([$renderedChips, $renderedChipAnchors, $renderedForms], [27, 24, 2], 'Actual27 chips24 chipanchors2 footer form anchors');
$linkGets = [];
foreach ($rows as $row) { foreach ($row['rendered_footer_links'] ?? [] as $href) {
    $destination = $rows[$href] ?? null; if (($destination['http_status'] ?? null) !== 200) { throw new RuntimeException('A clickable destination lacks fresh direct GET200 evidence.'); }
    $linkGets[] = ['owner' => $row['identity'], 'href' => $href, 'status' => 200, 'destination_page_id' => $destination['page_id'], 'destination_html_sha256' => $destination['html_sha256']];
} }
$same(count($linkGets), 26, 'Every clickable footer item has actual GET evidence');
$sqlAfter = $readOnly($scan); $same($sqlAfter, $sqlBefore, 'All46 full/protected/normalized fingerprints unchanged during resolver/GET');
foreach ($inputs as $file => $sha) { $same($hash($file), $sha, 'Every evidence/source/visual pin after GET'); }
$receipt = ['schema_version' => 1, 'status' => 'PASS', 'started_at' => $started, 'finished_at' => gmdate('c'), 'base_sha' => Package::BASE_SHA,
    'independent_before_sha256' => $beforeSha, 'old_consumers_sha256' => $consumerSha, 'reviewed_final_file' => $requestedFinalName, 'reviewed_final_sha256' => $requestedFinalSha,
    'original_final_file' => $finalName, 'original_final_sha256' => $finalSha, 'successor_mode' => $successorMode,
    'runtime' => $runtime, 'diagnostic_sha256' => $inputs[__FILE__], 'package_sha256' => Package::SOURCE_SHA, 'master_sha256' => Package::MASTER_SHA,
    'contract_sha256' => Package::CONTRACT_SHA, 'native_metadata_pins' => $metadataPins,
    'pre_apply_metadata_verified_before_http' => 3, 'pre_apply_metadata_master_editorial_exact' => true,
    'transport' => ['method' => 'GET', 'anonymous' => true, 'fixed_loopback' => '127.0.0.1:80', 'redirects' => 0, 'proxy' => false, 'cookies_sent' => false, 'auth_sent' => false],
    'native_resolver_destinations' => array_map(static fn ($r) => $r['record'], array_values($routes)), 'source_footer_items' => $navigation, 'HTTP' => array_values($rows), 'HTTP_200' => count($rows),
    'source_items' => 29, 'footer_chips' => 27, 'current_nonanchors' => 3, 'clickable_chip_anchors' => 24, 'clickable_footer_form_anchors' => 2, 'clickable_anchors' => 26,
    'distinct_destinations' => 9, 'every_footer_anchor_get_200' => $linkGets, 'protected_before_http_metadata_pages' => 3,
    'visual_files_unchanged' => 326, 'frozen_files_unchanged' => 2642, 'source_sync_unchanged' => true, 'all_old_rows_and_timestamps_exact' => true,
    'all_46_tables_before_http' => $sqlBefore['actual'], 'all_46_tables_after_http' => $sqlAfter['actual'],
    'normalized_before_http' => $sqlBefore['normalized'], 'normalized_after_http' => $sqlAfter['normalized'], 'protected_before_http' => $sqlBefore['protected'], 'protected_after_http' => $sqlAfter['protected'],
    'sql_unchanged_during_http' => true, 'production_requests' => 0, 'db_writes' => 0, 'runtime_or_route_edits' => 0,
    'not_checked' => ['Browser layout/visibility/interaction', 'Neighbor BEFORE HTTP parity: the independent capture contains only three target theory pages; six neighbor destinations are source/resolver/native-metadata checked',
        'Neighbor learning DOM equality and own-practice scoring are separate acceptance evidence']];
$bytes = json_encode($receipt, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)."\n";
$handle = fopen($dir.'/'.$name, 'x'); if (!$handle) { throw new RuntimeException('Exclusive navigation receipt creation failed.'); }
try { if (fwrite($handle, $bytes) !== strlen($bytes) || !fflush($handle)) { throw new RuntimeException('Private navigation receipt write incomplete.'); } } finally { fclose($handle); }
echo json_encode(['status' => 'PASS', 'file' => $name, 'sha256' => hash('sha256', $bytes), 'HTTP_200' => count($rows),
    'source_items' => 29, 'clickable_anchors_get_200' => count($linkGets), 'distinct_destinations' => 9,
    'sql_unchanged_during_http' => true, 'db_writes' => 0, 'browser_interaction' => 'not_checked'], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR), PHP_EOL;
