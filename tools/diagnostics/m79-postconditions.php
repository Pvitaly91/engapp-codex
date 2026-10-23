<?php

/** Independent SELECT-only reconstruction. Does not call a writer plan/apply, seed, HTTP or transaction hook. */
$failure = static function (Throwable $error): void {
    fwrite(STDERR, $error instanceof RuntimeException && !$error instanceof PDOException
        ? 'M79 final audit: '.$error->getMessage()."\n" : "M79 final audit incomplete; no receipt. No SQL writes.\n");
    exit(1);
};
set_exception_handler($failure);
if (PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows' || count($argv) !== 6
    || preg_match('/^preview-v[1-9][0-9]*\.json$/D', $argv[1]) !== 1 || preg_match('/^[a-f0-9]{64}$/D', $argv[2]) !== 1
    || preg_match('/^backup-v[1-9][0-9]*\.json$/D', $argv[3]) !== 1 || preg_match('/^[a-f0-9]{64}$/D', $argv[4]) !== 1
    || preg_match('/^final-readonly-v[1-9][0-9]*\.json$/D', $argv[5]) !== 1) {
    throw new RuntimeException('Reviewed preview/digest, exclusive backup/raw SHA and fresh final basename required.');
}
[$script, $planName, $planDigest, $backupName, $backupSha, $receiptName] = $argv;
$root = 'D:/DEV/htdocs/gramlyze.loc'; $dir = $root.'/storage/app/seo-m79-local';
$normal = static fn ($path) => strtolower(str_replace('\\', '/', (string) realpath($path)));
if ($normal(dirname(__DIR__, 2)) !== $normal($root) || !is_dir($dir) || is_link($dir)
    || file_exists($dir.'/'.$receiptName) || is_link($dir.'/'.$receiptName)) { throw new RuntimeException('Actual served/private scope differs or output exists.'); }
require $root.'/vendor/autoload.php'; $app = require $root.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap(); set_exception_handler($failure);
use App\Services\M79ContentPatch as Patch;
use App\Services\M79LocalTargetGuard as Guard;
use App\Support\M79QuestionTypesCompletion as Package;
use App\Support\M79QuestionSupplement as Supplement;
use App\Support\M51QuestionSupplement as Rows;
use App\Support\M26DetailPackage as Source;
$digest = static fn (array $value) => App\Services\M26ContentPatch::digest($value);
$pins = [__FILE__ => hash_file('sha256', __FILE__)];
$hash = static function (string $path): string {
    if (!is_file($path) || is_link($path)) { throw new RuntimeException('Evidence/source absent or linked.'); }
    return hash_file('sha256', $path);
};
$read = static function (string $name, ?string $sha = null) use ($dir, $hash, &$pins): array {
    if (preg_match('/^[a-zA-Z0-9_-]+\.json$/D', $name) !== 1) { throw new RuntimeException('Direct private JSON basename required.'); }
    $path = $dir.'/'.$name; $actual = $hash($path);
    if ($sha !== null && !hash_equals($sha, $actual)) { throw new RuntimeException('Exact evidence bytes differ.'); }
    $pins[$path] = $actual; return json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
};
$same = static function (array $left, array $right, string $reason): void { ksort($left); ksort($right); if ($left !== $right) { throw new RuntimeException($reason); } };
$before = $read(Patch::BEFORE_FILE, Patch::BEFORE_SHA); $consumers = $read(Patch::CONSUMERS_FILE, Patch::CONSUMERS_SHA);
$plan = $read($planName); $backup = $read($backupName, $backupSha); $unsigned = $plan; unset($unsigned['sha256']);
if (($plan['sha256'] ?? null) !== $planDigest || $digest($unsigned) !== $planDigest || $backup !== $plan
    || file_get_contents($dir.'/'.$planName) !== file_get_contents($dir.'/'.$backupName)
    || ($plan['patch'] ?? null) !== 'm79-question-types-completion-standard-view-v1' || ($plan['version'] ?? null) !== 1
    || ($plan['names'] ?? null) !== Package::OWNERS || ($plan['state'] ?? null) !== 'before'
    || count($plan['inserts'] ?? []) !== 3 || ($plan['deletes'] ?? []) !== []
    || Package::PAGE_IDS !== [132, 137, 136, 138, 134, 135] || Package::LEVELS !== ['A2', 'B1', 'B1', 'A2', 'B1', 'B1']
    || Package::CONTINUING_COUNTS !== [13, 14, 13, 6, 6, 6]
    || ($before['base_sha'] ?? null) !== Package::BASE_SHA || ($before['served_root'] ?? null) !== $root
    || ($before['source_worktree'] ?? null) !== Patch::SOURCE_WORKTREE || array_keys($before['pages'] ?? []) !== Package::OWNERS
    || ($before['sql_before'] ?? null) !== ($before['sql_after_http'] ?? null) || count($before['sql_before'] ?? []) !== 46
    || count($before['visual_hashes'] ?? []) !== 326 || count($before['frozen_hashes'] ?? []) !== 2701
    || ($consumers['all_tables_before_http'] ?? null) !== $before['sql_before'] || ($consumers['all_tables_after_http'] ?? null) !== $before['sql_before']
    || ($consumers['unchanged_during_http'] ?? null) !== true) { throw new RuntimeException('Reviewed preview/backup/independent BEFORE scope differs.'); }
App\Services\M51ContentPatch::assertConsumerCapture($before, $consumers);
[$sourceBefore, $patch, $master, $contract] = Package::load($root); Package::validate($sourceBefore, $patch, $master, $contract);
$inventory = $read(Patch::QUESTION_INVENTORY_FILE, Patch::QUESTION_INVENTORY_SHA);
$questionPlan = $read(Patch::QUESTION_PLAN_FILE, Patch::QUESTION_PLAN_SHA); $questionSource = Supplement::load($root);
if (($inventory['base_sha'] ?? null) !== Package::BASE_SHA || ($inventory['before_sha256'] ?? null) !== Patch::BEFORE_SHA
    || ($inventory['source_sha256'] ?? null) !== Supplement::SOURCE_SHA || ($inventory['identity'] ?? null) !== $before['identity']
    || ($inventory['sql_before'] ?? null) !== $before['sql_before'] || ($inventory['db_writes'] ?? null) !== 0
    || ($inventory['http_requests'] ?? null) !== 0 || ($inventory['id_step_offset'] ?? null) !== [1, 1]
    || ($inventory['bindings'] ?? null) !== Supplement::bindings()
    || ($inventory['question_collisions'] ?? null) !== array_fill_keys(array_keys(Rows::ROW_COLUMNS), [])
    || ($questionPlan['kind'] ?? null) !== 'M79_question_insert_plan' || ($questionPlan['base_sha'] ?? null) !== Package::BASE_SHA
    || ($questionPlan['before_sha256'] ?? null) !== Patch::BEFORE_SHA || ($questionPlan['source_sha256'] ?? null) !== Supplement::SOURCE_SHA
    || ($questionPlan['inventory'] ?? null) !== ['file' => Patch::QUESTION_INVENTORY_FILE, 'sha256' => Patch::QUESTION_INVENTORY_SHA]
    || ($questionPlan['bindings'] ?? null) !== Supplement::bindings() || ($questionPlan['ids'] ?? null) !== ($inventory['ids'] ?? null)
    || ($questionPlan['row_timestamp'] ?? null) !== ($inventory['row_timestamp'] ?? null)) { throw new RuntimeException('Question source/inventory/plan independent lineage differs.'); }
$projection = Supplement::project($questionSource, Supplement::bindings(), $inventory['option_matches']);
$questionRows = Rows::materialize($projection, $inventory['ids'], $inventory['row_timestamp']);
if ($projection !== ($inventory['projection'] ?? null) || $projection !== ($questionPlan['projection'] ?? null)
    || $questionRows !== ($inventory['materialized'] ?? null) || $questionRows !== ($questionPlan['materialized'] ?? null)
    || $questionRows !== ($plan['question_supplement']['expected'] ?? null) || $projection !== ($plan['question_supplement']['projection'] ?? null)
    || ($plan['question_supplement']['actual'] ?? null) !== array_fill_keys(array_keys(Rows::ROW_COLUMNS), [])
    || count($questionRows['questions']) !== 8 || count($questionRows['question_theory_text_blocks']) !== 8
    || array_count_values(array_column($questionRows['questions'], 'seeder')) !== array_combine(Supplement::BANKS, [2, 3, 3])) { throw new RuntimeException('Eight-question/five-family pure materialization differs.'); }
$questionById = $seenQuestionRows = [];
foreach ($questionRows as $table => $rows) { $questionById[$table] = array_column($rows, null, 'id'); $seenQuestionRows[$table] = []; }
$expectedSources = array_values(array_unique([...Patch::sourcePaths($patch, $contract, $root),
    'private:'.Patch::BEFORE_FILE, 'private:'.Patch::CONSUMERS_FILE, 'private:'.Patch::SYNC_RECEIPT,
    ...array_keys(Patch::questionEvidencePins()), Supplement::SOURCE])); sort($expectedSources);
$actualSources = array_keys($plan['sources']); sort($actualSources);
if ($actualSources !== $expectedSources) { throw new RuntimeException('Complete reviewed source/evidence allowlist differs.'); }
foreach ($plan['sources'] as $path => $sha) {
    $file = str_starts_with($path, 'private:') ? $dir.'/'.substr($path, 8) : $root.'/'.$path;
    if (!hash_equals($sha, $hash($file))) { throw new RuntimeException('Reviewed preview source/evidence drifted.'); } $pins[$file] = $sha;
}
foreach ([$before['physical_hashes'], $before['visual_hashes'], $before['frozen_hashes']] as $n => $records) {
    foreach ($records as $path => $sha) { $file = $n === 0 ? $path : $root.'/'.$path; if (!hash_equals($sha, $hash($file))) { throw new RuntimeException('Physical/ROOT visual/historical frozen source changed.'); } $pins[$file] = $sha; }
}
$independent = $read('independent-source-before-v1.json', '0fa53aa56c13487594e39b8460157cbafab5967d57baa5e930e785eb3ed93f8c');
if ($independent['base_sha'] !== Package::BASE_SHA || count($independent['groups']['visual']) !== 326) { throw new RuntimeException('Independent ROOT/WT visual BEFORE differs.'); }
foreach (['served_root' => $root, 'worktree' => Patch::SOURCE_WORKTREE] as $profile => $sourceRoot) {
    $actual = [];
    foreach (['resources/views', 'resources/css', 'resources/js', 'public/js'] as $directory) {
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($sourceRoot.'/'.$directory, FilesystemIterator::SKIP_DOTS)) as $file) {
            if ($file->isLink()) { throw new RuntimeException('Visual source is linked.'); }
            if ($file->isFile()) { $actual[] = substr(str_replace('\\', '/', $file->getPathname()), strlen($sourceRoot) + 1); }
        }
    }
    $expected = $independent['groups']['visual']; sort($actual); sort($expected);
    if ($actual !== $expected) { throw new RuntimeException('Visual file inventory changed.'); }
    foreach ($expected as $path) {
        $sha = $independent['files'][$path][$profile]['raw_sha256']; $file = $sourceRoot.'/'.$path;
        if (!hash_equals($sha, $hash($file))) { throw new RuntimeException('Independent individual ROOT/WT visual profile changed.'); } $pins[$file] = $sha;
    }
}
$sync = $read(Patch::SYNC_RECEIPT); $syncPaths = array_column($sync['files'], 'path'); sort($syncPaths);
if ($syncPaths !== Patch::syncPaths($patch, $contract) || $sync['base_sha'] !== Package::BASE_SHA || $sync['served_root'] !== $root
    || $sync['source_worktree'] !== Patch::SOURCE_WORKTREE || $sync['visual_before'] !== ['file' => Patch::BEFORE_FILE, 'sha256' => Patch::BEFORE_SHA]) { throw new RuntimeException('Exact source-sync scope differs.'); }
foreach ($sync['files'] as $record) {
    foreach ([$root.'/'.$record['path'] => $record['root_after_sha256'], Patch::SOURCE_WORKTREE.'/'.$record['path'] => $record['worktree_sha256']] as $file => $sha) {
        if (!hash_equals($sha, $hash($file))) { throw new RuntimeException('ROOT/WT source-sync drifted.'); } $pins[$file] = $sha;
    }
}

$oldPages = $newPages = $oldUk = $newUk = $foreign = $changes = $insertFields = []; $expectedInserts = $practices = [];
$bodyKinds = ['subtitle' => 0, 'teaching' => 0, 'practice' => 0]; $pageTextUpdates = 0;
foreach ($patch['targets'] as $i => $target) {
    $owner = Package::OWNERS[$i]; $captured = $before['pages'][$owner]; $raw = $sourceBefore['targets'][$i]['before']; $new = $target['after'];
    if ($before['definitions'][$owner]['definition'] !== $raw || json_decode(file_get_contents($root.'/'.$target['path']), true, flags: JSON_THROW_ON_ERROR) !== $new) { throw new RuntimeException('Active source/independent definition BEFORE differs.'); }
    foreach (['page', 'category_ancestry', 'blocks'] as $field) { if (($plan['pages'][$owner][$field] ?? null) !== $captured[$field]) { throw new RuntimeException('Preview original Page/block inventory differs.'); } }
    $page = $captured['page'];
    if ($page['id'] !== Package::PAGE_IDS[$i] || $page['seeder'] !== $owner || $page['page_category_id'] !== 22
        || $page['title'] !== $raw['page']['title'] || $page['text'] !== $raw['page']['subtitle_text']) { throw new RuntimeException('Captured original Page identity differs.'); }
    $oldPages[$page['id']] = $page; $newPages[$page['id']] = array_replace($page, ['text' => $new['page']['subtitle_text']]);
    if ($page !== $newPages[$page['id']]) {
        ++$pageTextUpdates; $changes['pages:'.$page['id']] = ['table' => 'pages', 'id' => $page['id'], 'uuid' => $page['uuid'] ?? null,
            'seeder' => $owner, 'before' => ['text' => $page['text']], 'after' => ['text' => $new['page']['subtitle_text']]];
    }
    $positions = [];
    foreach ($captured['blocks'] as $row) {
        if ($row['locale'] !== 'uk') { $foreign[$row['id']] = $row; continue; }
        $position = $row['sort_order'];
        if (!is_int($position) || $position < 0 || $position > Package::CONTINUING_COUNTS[$i] || isset($positions[$position])
            || isset($oldUk[$row['id']]) || $row['seeder'] !== $owner || $row['page_id'] !== $page['id'] || $row['page_category_id'] !== 22) { throw new RuntimeException('Original UK owner/order/category differs.'); }
        $positions[$position] = true;
        $oldConfig = $position === 0 ? ['type' => 'subtitle', 'column' => 'header', 'body' => $raw['page']['subtitle_html'],
            'uuid_key' => $raw['page']['subtitle_uuid_key'] ?? 'subtitle', 'level' => $raw['page']['subtitle_level'] ?? null] : $raw['page']['blocks'][$position - 1];
        $newConfig = $position === 0 ? array_replace($oldConfig, ['body' => $new['page']['subtitle_html']]) : $new['page']['blocks'][$position - 1];
        if (Source::uuid($owner, $oldConfig, $position) !== $row['uuid'] || Source::uuid($owner, $newConfig, $position) !== $row['uuid']) { throw new RuntimeException('Continuing source UUID differs.'); }
        foreach (['type', 'column', 'heading', 'level', 'css_class', 'body'] as $field) {
            if ($row[$field] !== ($oldConfig[$field] ?? null)) { throw new RuntimeException('Original SQL/source differs: '.$field); }
            if ($field !== 'body' && ($newConfig[$field] ?? null) !== $row[$field]) { throw new RuntimeException('Unapproved continuing metadata transition: '.$field); }
        }
        $oldUk[$row['id']] = $row; $newUk[$row['id']] = array_replace($row, ['body' => $newConfig['body']]);
        if ($row['body'] !== $newConfig['body']) {
            $changes['text_blocks:'.$row['id']] = ['table' => 'text_blocks', 'id' => $row['id'], 'uuid' => $row['uuid'], 'seeder' => $owner,
                'before' => ['body' => $row['body']], 'after' => ['body' => $newConfig['body']]];
            ++$bodyKinds[$position === 0 ? 'subtitle' : ($position === Package::PRACTICE_SOURCE_INDICES[$i] + 1 ? 'practice' : 'teaching')];
        }
        if ($i < 3 && $position === Package::PRACTICE_SOURCE_INDICES[$i] + 1) {
            if ($row['type'] !== 'practice-set' || $row['level'] !== Package::LEVELS[$i]) { throw new RuntimeException('Continuing practice binding differs.'); }
            $practices[] = ['owner' => $owner, 'id' => $row['id'], 'uuid' => $row['uuid'], 'source_index' => $position - 1, 'level' => $row['level'], 'inserted' => false];
        }
    }
    $keys = array_keys($positions); sort($keys);
    if ($keys !== range(0, Package::CONTINUING_COUNTS[$i])) { throw new RuntimeException('Complete original UK position vector differs.'); }
    foreach (array_slice($new['page']['blocks'], Package::CONTINUING_COUNTS[$i], null, true) as $slot => $config) {
        if ($i < 3 || $slot !== 6 || $config['type'] !== 'practice-set' || $config['column'] !== 'left' || ($config['level'] ?? null) !== Package::LEVELS[$i]) { throw new RuntimeException('New D/E/F practice scope differs.'); }
        $fields = ['uuid' => Source::uuid($owner, $config, $slot + 1), 'page_id' => $page['id'], 'page_category_id' => 22, 'locale' => 'uk',
            'type' => 'practice-set', 'column' => 'left', 'heading' => $config['heading'] ?? null, 'css_class' => $config['css_class'] ?? null,
            'sort_order' => $slot + 1, 'body' => $config['body'], 'level' => $config['level'], 'seeder' => $owner];
        if (isset($insertFields[$fields['uuid']]) || array_filter($captured['blocks'], static fn ($r) => $r['uuid'] === $fields['uuid']) !== []) { throw new RuntimeException('New practice UUID repeats or already existed.'); }
        $insertFields[$fields['uuid']] = $fields; $expectedInserts[] = ['table' => 'text_blocks', 'seeder' => $owner, 'fields' => $fields, 'sha256' => $digest($fields)];
    }
}
if (count($oldPages) !== 6 || count($oldUk) !== 64 || count($foreign) !== 128 || count($practices) !== 3 || count($insertFields) !== 3
    || count($changes) !== count($plan['updates']) || $plan['inserts'] !== $expectedInserts) { throw new RuntimeException('Derived exact content operations/practice scope differs.'); }
$seenChanges = [];
foreach ($plan['updates'] as $change) {
    $key = $change['table'].':'.$change['id']; $expected = $changes[$key] ?? null;
    if ($expected === null || isset($seenChanges[$key]) || array_keys($change) !== ['table', 'id', 'uuid', 'seeder', 'before', 'after', 'before_sha256', 'after_sha256']
        || array_diff_key($change, ['before_sha256' => true, 'after_sha256' => true]) !== $expected
        || $change['before_sha256'] !== $digest($expected['before']) || $change['after_sha256'] !== $digest($expected['after'])) { throw new RuntimeException('Exact reviewed update allowlist differs or repeats a row.'); }
    $seenChanges[$key] = true;
}
$db = Illuminate\Support\Facades\DB::connection(); Guard::assertConfiguredDatabase($db); $guard = new Guard; $physical = $guard->physicalEvidence();
$server = (array) $db->selectOne('SELECT DATABASE() AS db, @@port AS port, @@pid_file AS pid_file, @@datadir AS datadir, @@version_compile_os AS os');
foreach (['datadir', 'pid_file'] as $key) { if (!is_string($server[$key] ?? null) || preg_match('~^[A-Za-z]:/~D', str_replace('\\', '/', $server[$key])) !== 1) { throw new RuntimeException('Actual MySQL path is not absolute local Windows.'); } }
$dataPath = $normal($server['datadir']); $pidPath = $normal($server['pid_file']); $pid = trim((string) file_get_contents($server['pid_file']));
if ($server['db'] !== 'gr2' || (int) $server['port'] !== 3306 || !str_starts_with($server['os'] ?? '', 'Win')
    || !ctype_digit($pid) || !str_starts_with($pidPath, $dataPath.'/') || str_starts_with($dataPath, '//')) { throw new RuntimeException('Fresh physical MySQL identity differs.'); }
$guard->assertEvidence($physical, $normal($root), (int) $pid, 3306); $runtime = Guard::publicIdentity($db, $root.'/public');
if ($runtime !== $before['identity']) { throw new RuntimeException('Fresh runtime differs from independently captured local identity.'); }

$db->statement('SET TRANSACTION READ ONLY'); $db->beginTransaction();
try {
    $tables = $db->getSchemaBuilder()->getTableListing(schema: $db->getDatabaseName(), schemaQualified: false); sort($tables);
    if ($tables !== array_keys($before['sql_before'])) { throw new RuntimeException('Original complete table inventory changed.'); }
    $after = $normalized = $protected = $seenPages = $seenUk = $seenForeign = $seenInserted = []; $insertedRows = [];
    foreach ($tables as $table) {
        $schema = $db->getSchemaBuilder()->getColumns($table);
        if ((isset($before['schema'][$table]) && $schema !== $before['schema'][$table])
            || (isset($inventory['schema'][$table]) && $schema !== $inventory['schema'][$table])) { throw new RuntimeException('Independently captured schema changed.'); }
        $columns = array_column($schema, 'name'); $query = $db->table($table);
        foreach (in_array('id', $columns, true) ? ['id'] : $columns as $column) { $query->orderBy($column); }
        $currentHash = hash_init('sha256'); $oldHash = hash_init('sha256'); $protectedHash = hash_init('sha256'); $count = $oldCount = $protectedCount = 0;
        foreach ($query->cursor() as $record) {
            $row = (array) $record; $id = $row['id'] ?? null; hash_update($currentHash, $digest($row)."\n"); ++$count;
            if (isset($questionById[$table][$id])) {
                if (isset($seenQuestionRows[$table][$id])) { throw new RuntimeException('Duplicate supplemental row ID.'); }
                $same($row, $questionById[$table][$id], 'Full reviewed new question-family row/timestamps differ: '.$table);
                $seenQuestionRows[$table][$id] = true; continue;
            }
            if ($table === 'text_blocks' && isset($insertFields[$row['uuid'] ?? ''])) {
                $fields = $insertFields[$row['uuid']];
                if (isset($seenInserted[$row['uuid']]) || isset($oldUk[$id]) || isset($foreign[$id]) || !is_int($id) || $id < 1) { throw new RuntimeException('New practice ID/UUID collides or repeats.'); }
                $keys = array_keys($row); $expectedKeys = [...array_keys($fields), 'id', 'created_at', 'updated_at']; sort($keys); sort($expectedKeys);
                if ($keys !== $expectedKeys) { throw new RuntimeException('New practice complete column set differs.'); }
                foreach ($fields as $field => $value) { if ($row[$field] !== $value) { throw new RuntimeException('New practice exact field differs: '.$field); } }
                if (!is_string($row['created_at']) || preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/D', $row['created_at']) !== 1 || $row['created_at'] !== $row['updated_at']) { throw new RuntimeException('New practice creation/update timestamps differ.'); }
                $seenInserted[$row['uuid']] = true; $insertedRows[] = ['owner' => $row['seeder'], 'id' => $id, 'uuid' => $row['uuid'], 'source_index' => 6,
                    'level' => $row['level'], 'inserted' => true, 'exact_fields' => true, 'creation_equals_update' => true, 'full_row_sha256' => $digest($row)];
                continue;
            }
            $oldRow = $row; $skipProtected = false;
            if ($table === 'pages' && isset($oldPages[$id])) { $same($row, $newPages[$id], 'Target Page fields/text/timestamps differ.'); $oldRow = $oldPages[$id]; $seenPages[$id] = true; }
            if ($table === 'text_blocks' && isset($oldUk[$id])) { $same($row, $newUk[$id], 'Continuing UK fields/levels/timestamps differ.'); $oldRow = $oldUk[$id]; $seenUk[$id] = true; $skipProtected = true; }
            if ($table === 'text_blocks' && isset($foreign[$id])) { $same($row, $foreign[$id], 'Original non-UK row changed.'); $seenForeign[$id] = true; }
            hash_update($oldHash, $digest($oldRow)."\n"); ++$oldCount;
            if ($skipProtected) { continue; }
            if ($table === 'pages' && isset($oldPages[$id])) { unset($row['text']); }
            hash_update($protectedHash, $digest($row)."\n"); ++$protectedCount;
        }
        $after[$table] = ['count' => $count, 'sha256' => hash_final($currentHash)]; $normalized[$table] = ['count' => $oldCount, 'sha256' => hash_final($oldHash)];
        $protected[$table] = ['count' => $protectedCount, 'sha256' => hash_final($protectedHash)];
    }
    foreach ($questionRows as $table => $rows) {
        if (count($seenQuestionRows[$table]) !== count($rows) || $after[$table]['count'] !== $before['sql_before'][$table]['count'] + count($rows)) { throw new RuntimeException('A complete approved supplemental family is missing or includes extra rows.'); }
    }
    if (count($seenPages) !== 6 || count($seenUk) !== 64 || count($seenForeign) !== 128 || count($seenInserted) !== 3
        || $normalized !== $before['sql_before'] || $protected !== $plan['protected']
        || $after['text_blocks']['count'] !== $before['sql_before']['text_blocks']['count'] + 3) { throw new RuntimeException('Full all-table BEFORE reconstruction or protected projection differs.'); }
    foreach ($after as $table => $record) { if (!in_array($table, ['pages', 'text_blocks', ...array_keys(Rows::ROW_COLUMNS)], true) && $record !== $before['sql_before'][$table]) { throw new RuntimeException('Excluded category/translation/progress/other table changed.'); } }
} finally { $db->rollBack(); }
foreach ($pins as $file => $sha) { if (!hash_equals($sha, $hash($file))) { throw new RuntimeException('Evidence/source changed during audit.'); } }
usort($insertedRows, static fn ($a, $b) => array_search($a['owner'], Package::OWNERS, true) <=> array_search($b['owner'], Package::OWNERS, true));
$allPractices = [...$practices, ...$insertedRows]; usort($allPractices, static fn ($a, $b) => array_search($a['owner'], Package::OWNERS, true) <=> array_search($b['owner'], Package::OWNERS, true));
$result = ['checked_at' => gmdate('c'), 'runtime' => $runtime, 'plan_file' => $planName, 'plan_digest' => $planDigest,
    'independent_before_sha256' => Patch::BEFORE_SHA, 'old_consumers_sha256' => Patch::CONSUMERS_SHA, 'question_plan_sha256' => Patch::QUESTION_PLAN_SHA,
    'backup_file' => $backupName, 'backup_sha256' => $backupSha, 'backup_equals_reviewed_preview' => true,
    'page_text_updates' => $pageTextUpdates, 'body_updates' => $bodyKinds, 'metadata_transitions' => [], 'update_operations' => count($plan['updates']),
    'distinct_changed_rows' => count($changes), 'inserted_content_rows' => 3, 'deleted_rows' => 0,
    'question_family_insert_counts' => array_map('count', $questionRows), 'question_insert_rows' => array_sum(array_map('count', $questionRows)),
    'continuing_uk_rows' => 64, 'unchanged_non_uk_rows' => 128, 'continuing_practices' => $practices, 'inserted_practices' => $insertedRows, 'all_six_practice_bindings' => $allPractices,
    'all_old_rows_and_timestamps_exact' => true, 'independent_before_projection_exact' => true, 'reviewed_protected_projection_exact' => true,
    'sql_after' => $after, 'normalized_before' => $normalized, 'protected' => $protected, 'sql_tables' => count($after),
    'root_visual_files_unchanged' => 326, 'worktree_visual_files_unchanged' => 326, 'frozen_root_files_unchanged' => 2701,
    'independent_visual_profiles_preserved_not_equated' => true, 'source_sync_unchanged' => true, 'all_preview_sources_exact' => true,
    'schema_tables_checked' => array_values(array_unique([...array_keys($before['schema']), ...array_keys($inventory['schema'])])),
    'db_writes' => 0, 'http_requests' => 0, 'browser_acceptance' => 'Separate actual-page evidence; not claimed by this SQL-only audit.'];
$json = json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
$handle = fopen($dir.'/'.$receiptName, 'x'); if (!$handle) { throw new RuntimeException('Exclusive final receipt unavailable.'); }
try { if (fwrite($handle, $json) !== strlen($json) || !fflush($handle)) { throw new RuntimeException('Final receipt write incomplete.'); } } finally { fclose($handle); }
echo json_encode(['file' => $receiptName, 'sha256' => hash('sha256', $json), 'tables' => count($after), 'updates' => count($plan['updates']),
    'distinct_changed_rows' => count($changes), 'new_practices' => 3, 'question_families' => array_map('count', $questionRows),
    'metadata_fields' => 0, 'all_old_rows_and_timestamps_exact' => true, 'db_writes' => 0, 'http_requests' => 0], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR)."\n";
