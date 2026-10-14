<?php

/** Pure author projection in the established isolated runtime. No database query or active definition write. */
if (PHP_SAPI !== 'cli' || !in_array($argv[1] ?? null, ['draft', 'questions-draft', 'questions-reviewed', 'controller-draft'], true)) { throw new RuntimeException('Explicit M51 author projection required.'); }
$root = dirname(__DIR__, 2);
require $root.'/tests/bootstrap.php';
$app = require $root.'/bootstrap/app.php';
Tests\Support\IsolatedTestEnvironment::configure($app);
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if (app()->environmentPath() === base_path() || config('database.default') !== 'sqlite'
    || config('database.connections.sqlite.database') !== ':memory:' || config('cache.default') !== 'array'
    || config('session.driver') !== 'array') { throw new RuntimeException('M51 pure author projection runtime is not isolated.'); }
$read = static fn ($path) => json_decode(file_get_contents($root.'/'.$path), true, flags: JSON_THROW_ON_ERROR);
$master = $read(App\Support\M51ModalMeaningsPackage::MASTER);
$before = $read(App\Support\M51ModalMeaningsPackage::BEFORE);
if ($argv[1] === 'controller-draft') {
    $private = 'D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m51-local'; $results = [];
    foreach (['root', 'worktree'] as $copy) {
        $file = $private.'/controller-before-v1-'.$copy.'.bin';
        $expected = $copy === 'root' ? App\Services\M51ContentPatch::CONTROLLER_BEFORE_SHA : App\Services\M51ContentPatch::CONTROLLER_WT_BEFORE_SHA;
        if (hash_file('sha256', $file) !== $expected) { throw new RuntimeException('M51 controller original bytes changed.'); }
        $after = App\Services\M51ContentPatch::approvedControllerAfter(file_get_contents($file));
        $path = $private.'/controller-draft-after-'.$copy.'.bin'; $handle = fopen($path, 'x');
        if (!$handle) { throw new RuntimeException('M51 controller draft artifact already exists.'); }
        try { if (fwrite($handle, $after) !== strlen($after) || !fflush($handle)) { throw new RuntimeException('M51 controller draft write incomplete.'); } }
        finally { fclose($handle); }
        $results[$copy] = ['file' => basename($path), 'sha256' => hash('sha256', $after)];
    }
    echo json_encode(['status' => 'isolated-controller-draft', 'database_used' => false, 'active_controllers_written' => false,
        'copies' => $results], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n"; exit(0);
}
if (in_array($argv[1], ['questions-draft', 'questions-reviewed'], true)) {
    $reviewed = $argv[1] === 'questions-reviewed';
    $private = 'D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m51-local';
    $file = $private.'/'.($reviewed ? 'question-inventory-v4.json' : 'question-inventory-v3.json');
    $inventory = json_decode(file_get_contents($file), true, flags: JSON_THROW_ON_ERROR);
    $sourceFile = $root.'/'.App\Support\M51QuestionSupplement::SOURCE;
    $source = json_decode(file_get_contents($sourceFile), true, flags: JSON_THROW_ON_ERROR);
    if ($reviewed) { App\Support\M51QuestionSupplement::load($root); }
    if ($inventory['draft_sha256'] !== hash_file('sha256', $sourceFile)
        || $inventory['sql_before'] !== $inventory['sql_after_inventory']) { throw new RuntimeException('M51 question inventory/source is stale.'); }
    $patch = $read(App\Support\M51ModalMeaningsPackage::SOURCE);
    $bindings = [];
    foreach ($patch['targets'] as $i => $target) {
        $bindings[] = ['owner' => $target['identity'], 'level' => App\Support\M51QuestionSupplement::LEVELS[$i],
            'category_id' => 45, 'source_id' => [198, 204, 397][$i],
            'practice_uuid' => App\Support\M26DetailPackage::uuid($target['identity'], $target['after']['page']['blocks'][6], 7)];
    }
    $options = App\Services\M51ContentPatch::optionRowsFromInventory($inventory['option_matches']);
    $projection = App\Support\M51QuestionSupplement::project($source, $bindings, $options);
    $ids = [];
    foreach (App\Support\M51QuestionSupplement::ROW_COLUMNS as $table => $_) {
        $next = (int) $inventory['allocation'][$table]['auto_increment']['next_id'];
        if ($next <= (int) $inventory['allocation'][$table]['max_id']) { throw new RuntimeException('M51 explicit new ID inventory is invalid.'); }
        foreach ($projection[$table] as $row) { $ids[$table][$row['key']] = $next++; }
        $ids[$table] ??= [];
    }
    App\Support\M51QuestionSupplement::materialize($projection, $ids, '2026-10-14 00:00:00');
    $plan = ['schema_version' => 1, 'base_sha' => App\Support\M51ModalMeaningsPackage::BASE_SHA,
        'before_sha256' => 'f4ade377a942b92afee53ed3349ed49861bba43bde9f704408df8bace3b6daef',
        'source_sha256' => hash_file('sha256', $sourceFile),
        'inventory' => ['file' => basename($file), 'sha256' => hash_file('sha256', $file)],
        'bindings' => $bindings, 'taxonomy' => ['question_categories' => array_values(array_filter($inventory['categories'], static fn ($row) => $row['id'] === 45)),
            'sources' => $inventory['sources']], 'projection' => $projection, 'ids' => $ids];
    $bytes = json_encode($plan, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
    $path = $private.'/'.($reviewed ? 'question-plan-v1.json' : 'question-plan-draft-v1.json'); $handle = fopen($path, 'x');
    if (!$handle) { throw new RuntimeException('M51 private question draft plan already exists.'); }
    try { if (fwrite($handle, $bytes) !== strlen($bytes) || !fflush($handle)) { throw new RuntimeException('M51 question draft plan write incomplete.'); } }
    finally { fclose($handle); }
    echo json_encode(['status' => $reviewed ? 'isolated-reviewed-question-projection' : 'isolated-question-draft-projection', 'database_used' => false,
        'file' => basename($path), 'sha256' => hash('sha256', $bytes),
        'new_rows' => array_map(static fn ($table) => count($projection[$table]), array_combine(array_keys(App\Support\M51QuestionSupplement::ROW_COLUMNS), array_keys(App\Support\M51QuestionSupplement::ROW_COLUMNS))),
        'reused_options' => count($projection['reused_options']), 'bindings' => $bindings], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
    exit(0);
}
$patch = ['schema_version' => 1, 'base_sha' => App\Support\M51ModalMeaningsPackage::BASE_SHA, 'targets' => []];
$contract = ['schema_version' => 1, 'targets' => []];
foreach ($master['lessons'] as $i => $lesson) {
    $old = $before['targets'][$i];
    $patch['targets'][] = ['identity' => $lesson['identity'], 'slug' => $lesson['slug'], 'path' => $old['path'],
        'ancestry' => ['modal-verbs'], 'local_url' => $lesson['local_url'], 'page_binding' => $old['page_binding'],
        'block_keys' => array_column($lesson['blocks'], 'key'),
        'after' => App\Support\M51ModalMeaningsPackage::definition($lesson, $old['before'])];
    $contract['targets'][] = ['identity' => $lesson['identity'], 'definition' => $old['path'], 'local_url' => $lesson['local_url'],
        'level' => $lesson['technical_block_level'], 'bank_seeders' => [App\Support\M51ModalMeaningsPackage::BANKS[$i]]];
}
App\Support\M51ModalMeaningsPackage::validate($before, $patch, $master);
$sizes = [];
foreach ($patch['targets'] as $target) {
    foreach ($target['after']['page']['blocks'] as $i => $block) {
        App\Services\PracticeCompletionPatch::assertBodyCapacity($block['body'], 65535);
        $sizes[$target['identity']][$i] = strlen($block['body']);
    }
}
$write = static function ($path, $value) use ($root): void {
    $bytes = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
    $handle = fopen($root.'/'.$path, 'x');
    if (!$handle) { throw new RuntimeException('M51 author projection already exists; no overwrite.'); }
    try { if (fwrite($handle, $bytes) !== strlen($bytes) || !fflush($handle)) { throw new RuntimeException('Incomplete M51 draft artifact write.'); } }
    finally { fclose($handle); }
};
$write(App\Support\M51ModalMeaningsPackage::SOURCE, $patch);
$write('docs/content/theory-sentence-builders/m51-modal-meanings.v1.json', $contract);
echo json_encode(['status' => 'isolated-draft-projection', 'database_used' => false, 'active_definitions_written' => false,
    'source_sha256' => hash_file('sha256', $root.'/'.App\Support\M51ModalMeaningsPackage::SOURCE),
    'before_sha256' => hash_file('sha256', $root.'/'.App\Support\M51ModalMeaningsPackage::BEFORE),
    'master_sha256' => hash_file('sha256', $root.'/'.App\Support\M51ModalMeaningsPackage::MASTER), 'body_bytes' => $sizes],
    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
