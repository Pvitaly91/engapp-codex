<?php

/**
 * Independent SELECT-only M75 final gate. No writer, apply, restore, seeder or HTTP entry is called.
 * php tools/diagnostics/m75-postconditions.php preview-v1.json <reviewed-plan-digest> backup-v1.json <backup-raw-sha256> final-readonly-v1.json
 * Only exact source-bound Page.text/UK body replacements and complete proven new question rows
 * may be reversed on copies when reconstructing every independent BEFORE table fingerprint.
 */
$failure = static function (Throwable $error): void {
    if ($error instanceof \Illuminate\Database\QueryException) {
        fwrite(STDERR, 'M75 read-only query diagnostic: '.json_encode(['sql_template' => $error->getSql(),
            'sqlstate' => $error->getCode(), 'driver_code' => $error->getPrevious()?->errorInfo[1] ?? null], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n");
    }
    fwrite(STDERR, $error instanceof RuntimeException && !$error instanceof PDOException
        ? 'M75 final audit: '.$error->getMessage()."\n" : 'M75 final audit incomplete: '.get_class($error).' at '.basename($error->getFile()).':'.$error->getLine()."; no successful receipt. No SQL writes.\n");
    exit(1);
};
set_exception_handler($failure);
$previewName = $argv[1] ?? ''; $reviewedDigest = $argv[2] ?? '';
$backupName = $argv[3] ?? ''; $backupSha = $argv[4] ?? ''; $receiptName = $argv[5] ?? '';
if (PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows' || count($argv) !== 6
    || preg_match('/^[a-zA-Z0-9_-]+\.json$/D', $previewName) !== 1
    || preg_match('/^[a-f0-9]{64}$/D', $reviewedDigest) !== 1
    || preg_match('/^backup-v[1-9][0-9]*\.json$/D', $backupName) !== 1
    || preg_match('/^[a-f0-9]{64}$/D', $backupSha) !== 1
    || preg_match('/^final-readonly-v[1-9][0-9]*\.json$/D', $receiptName) !== 1
    || count(array_unique([$previewName, $backupName, $receiptName])) !== 3) {
    throw new RuntimeException('Reviewed preview/digest, exclusive apply backup/raw SHA and fresh final receipt basename are required.');
}
$root = 'D:/DEV/htdocs/gramlyze.loc'; $dir = $root.'/storage/app/seo-m75-local';
$normal = static fn ($path): string => rtrim(strtolower(str_replace('\\', '/', (string) realpath($path))), '/');
if ($normal(dirname(__DIR__, 2)) !== $normal($root) || !is_dir($dir) || is_link($dir)
    || $normal($dir) !== $normal($root).'/storage/app/seo-m75-local') {
    throw new RuntimeException('M75 final gate must execute from the actual served ROOT and private directory.');
}
$receiptPath = $dir.'/'.$receiptName;
if (file_exists($receiptPath) || is_link($receiptPath)) { throw new RuntimeException('Final receipt already exists; no overwrite.'); }
require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php'; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
set_exception_handler($failure);

use App\Services\M75ContentPatch as Patch;
use App\Services\M75LocalTargetGuard as Guard;
use App\Support\M75PronounForms as Package;
use App\Support\M75QuestionSupplement as Supplement;
use App\Support\M51QuestionSupplement as Rows;
use App\Support\M26DetailPackage;

if ($normal(base_path()) !== $normal($root) || $normal(app()->environmentPath()) !== $normal($root)
    || Patch::BEFORE_FILE !== 'before-v1.json'
    || Patch::BEFORE_SHA !== '5e238ff98020caf4ce97a3057c7a624bd48898d64791e83a720e3fe792b09c4a'
    || Patch::CONSUMERS_FILE !== 'before-old-consumers-v1.json'
    || Patch::CONSUMERS_SHA !== 'f59c52af9be8d73446bc3310ff1e435cc4b93de34f7976f488a280f20ad35090'
    || Package::BASE_SHA !== '67ebba5ed98fae05ca6022cac802870cfacf0d52'
    || Package::PAGE_IDS !== [125, 128, 122] || Package::CATEGORY_IDS !== [20, 20, 20]
    || Package::CONTINUING_COUNTS !== [11, 11, 12] || Package::LEVELS !== [null, null, null]
    || Package::PRACTICE_SOURCE_INDICES !== [9, 9, 10]
    || Package::PRACTICE_UUIDS !== ['135baaed-d147-599a-b147-5b2c93924300',
        'aedf70d6-fcc4-57b0-b303-c98548d17060', '57ef1463-3d71-5a26-971a-53022c7be0b7']) {
    throw new RuntimeException('M75 exact served/source/three-owner/NULL continuing-practice scope differs.');
}
$reserved = [Patch::BEFORE_FILE, Patch::CONSUMERS_FILE, Patch::SYNC_RECEIPT,
    Patch::QUESTION_PLAN_FILE, Patch::QUESTION_INVENTORY_FILE];
if (array_intersect([$previewName, $backupName, $receiptName], $reserved) !== []) {
    throw new RuntimeException('Final input/output may not alias required independent evidence.');
}
$hash = static fn (string $path): string => Patch::fileHash($path);
$digest = static fn (array $value): string => App\Services\M26ContentPatch::digest($value);
$sourceEvidence = [__FILE__ => $hash(__FILE__)];
$load = static function (string $name, ?string $expected = null) use ($dir, $hash, &$sourceEvidence): array {
    if (preg_match('/^[a-zA-Z0-9_-]+\.json$/D', $name) !== 1
        || ($expected !== null && preg_match('/^[a-f0-9]{64}$/D', $expected) !== 1)) {
        throw new RuntimeException('Private reviewed JSON basename/pin is invalid or pending.');
    }
    $actual = $hash($dir.'/'.$name);
    if ($expected !== null && !hash_equals($expected, $actual)) { throw new RuntimeException('Private reviewed evidence bytes differ.'); }
    $sourceEvidence[$dir.'/'.$name] = $actual;
    return json_decode(file_get_contents($dir.'/'.$name), true, flags: JSON_THROW_ON_ERROR);
};
$same = static function (array $actual, array $expected, string $message): void {
    ksort($actual); ksort($expected);
    if ($actual !== $expected) { throw new RuntimeException($message); }
};
// Independent M52-derived capture stores complete per-question families; M26
// serializes the same rows as flat table lists. Preserve every raw field and
// duplicate occurrence; only shared global option rows are coalesced by ID.
$projectCapturedRelations = static function (array $relations) use ($digest): array {
    $keys = array_keys($relations); sort($keys);
    $expectedKeys = ['page_tag', 'page_category_tag', 'tag_text_block', 'question_theory_text_blocks',
        'tags', 'questions', 'question_snapshots', 'saved_grammar_test_questions']; sort($expectedKeys);
    if ($keys !== $expectedKeys) { throw new RuntimeException('Independent relation serializer key set differs.'); }
    $out = [];
    foreach (['page_tag', 'tag_text_block', 'page_category_tag', 'question_theory_text_blocks', 'tags', 'questions'] as $table) {
        if (!is_array($relations[$table]) || !array_is_list($relations[$table])) {
            throw new RuntimeException('Independent raw relation family is not a complete row list.');
        }
        $out[$table] = $relations[$table];
    }
    $families = ['question_answers' => 'answers', 'question_option_question' => 'option_pivots',
        'question_tag' => 'tags', 'question_marker_tag' => 'marker_tags', 'verb_hints' => 'verb_hints',
        'question_hints' => 'hints', 'question_variants' => 'variants'];
    foreach ($families as $table => $_) { $out[$table] = []; }
    $uuids = array_column($relations['questions'], 'uuid'); $snapshotKeys = array_keys($relations['question_snapshots']);
    $sortedUuids = $uuids; sort($sortedUuids); sort($snapshotKeys);
    if (count(array_unique($uuids)) !== count($uuids) || $snapshotKeys !== $sortedUuids) {
        throw new RuntimeException('Independent question/snapshot identities differ or repeat.');
    }
    $options = [];
    foreach ($relations['questions'] as $question) {
        $snapshot = $relations['question_snapshots'][$question['uuid']];
        $keys = array_keys($snapshot); sort($keys);
        $expected = ['question', 'answers', 'option_pivots', 'verb_hints', 'hints', 'variants',
            'tags', 'marker_tags', 'options', 'ordered_answer_instances']; sort($expected);
        if ($keys !== $expected || $snapshot['question'] !== $question) {
            throw new RuntimeException('Independent complete question snapshot differs from its captured row.');
        }
        foreach ($families as $table => $key) {
            if (!is_array($snapshot[$key]) || !array_is_list($snapshot[$key])) {
                throw new RuntimeException('Independent question family is not a complete row list.');
            }
            foreach ($snapshot[$key] as $row) {
                if (!is_array($row) || ($row['question_id'] ?? null) !== $question['id']) {
                    throw new RuntimeException('Independent complete question-family row has a different owner.');
                }
                $out[$table][] = $row;
            }
        }
        if (!is_array($snapshot['options']) || !array_is_list($snapshot['options'])) {
            throw new RuntimeException('Independent global options are not complete row lists.');
        }
        $optionMap = [];
        foreach ($snapshot['options'] as $option) {
            $id = $option['id'] ?? null;
            if (!is_int($id) || isset($optionMap[$id]) || (isset($options[$id]) && $options[$id] !== $option)) {
                throw new RuntimeException('Independent global option identity repeats or its full shared row differs.');
            }
            $optionMap[$id] = $option; $options[$id] = $option;
        }
        $references = array_values(array_unique([...array_column($snapshot['answers'], 'option_id'),
            ...array_column($snapshot['option_pivots'], 'option_id'), ...array_column($snapshot['verb_hints'], 'option_id')]));
        $references = array_values(array_filter($references, static fn ($id) => $id !== null)); sort($references);
        $optionIds = array_keys($optionMap); sort($optionIds);
        if ($references !== $optionIds) { throw new RuntimeException('Independent option rows do not exactly cover all captured references.'); }
        $ordered = $snapshot['answers'];
        usort($ordered, static fn ($a, $b) => strnatcmp((string) $a['marker'], (string) $b['marker'])
            ?: (($a['position'] ?? 0) <=> ($b['position'] ?? 0)) ?: ($a['id'] <=> $b['id']));
        $instances = array_map(static fn ($answer) => ['id' => $answer['id'], 'marker' => $answer['marker'],
            'position' => $answer['position'] ?? null, 'option_id' => $answer['option_id'],
            'option' => $optionMap[$answer['option_id']]['option'] ?? null], $ordered);
        if ($instances !== $snapshot['ordered_answer_instances']) {
            throw new RuntimeException('Independent derived ordered instances disagree with complete raw answers/options.');
        }
    }
    $out['question_options'] = array_values($options);
    $out['saved_grammar_test_questions'] = $relations['saved_grammar_test_questions'];
    // Exact M26 rows() ordering, not a lossy row/field normalization.
    foreach ($out as &$rows) {
        usort($rows, static fn ($a, $b) => ($a['id'] ?? 0) <=> ($b['id'] ?? 0) ?: strcmp($digest($a), $digest($b)));
    }
    unset($rows);
    return $out;
};
$before = $load(Patch::BEFORE_FILE, Patch::BEFORE_SHA);
$consumers = $load(Patch::CONSUMERS_FILE, Patch::CONSUMERS_SHA);
$preview = $load($previewName); $unsigned = $preview; unset($unsigned['sha256']);
$backup = $load($backupName, $backupSha);
if (($preview['sha256'] ?? null) !== $reviewedDigest || $digest($unsigned) !== $reviewedDigest
    || $backup !== $preview || file_get_contents($dir.'/'.$backupName) !== file_get_contents($dir.'/'.$previewName)
    || ($preview['state'] ?? null) !== 'before' || ($preview['version'] ?? null) !== 1
    || ($preview['patch'] ?? null) !== 'm75-possessive-reflexive-indefinite-standard-view-v1'
    || ($preview['names'] ?? null) !== Package::OWNERS || ($preview['inserts'] ?? null) !== []
    || ($preview['deletes'] ?? []) !== [] || array_keys($before['pages'] ?? []) !== Package::OWNERS
    || ($before['base_sha'] ?? null) !== Package::BASE_SHA || ($before['served_root'] ?? null) !== $root
    || ($before['source_worktree'] ?? null) !== Patch::SOURCE_WORKTREE
    || ($before['sql_before'] ?? null) !== ($before['sql_after_http'] ?? null) || count($before['sql_before'] ?? []) !== 46
    || count($before['visual_hashes'] ?? []) !== 326 || count($before['frozen_hashes'] ?? []) !== 2624
    || ($consumers['all_tables_before_http'] ?? null) !== $before['sql_before']
    || ($consumers['all_tables_after_http'] ?? null) !== $before['sql_before']
    || ($consumers['unchanged_during_http'] ?? null) !== true) {
    throw new RuntimeException('Reviewed preview/raw backup or independent BEFORE/consumer scope differs.');
}
App\Services\M51ContentPatch::assertConsumerCapture($before, $consumers);
[$sourceBefore, $patch, $master, $contract] = Package::load($root);
Package::validate($sourceBefore, $patch, $master, $contract);
if (array_column($patch['targets'], 'identity') !== Package::OWNERS || count($contract['targets'] ?? []) !== 3) {
    throw new RuntimeException('Frozen target identities or independent thematic contract differs.');
}
$expectedSources = array_values(array_unique([...Patch::sourcePaths($patch, $contract, $root),
    'private:'.Patch::BEFORE_FILE, 'private:'.Patch::CONSUMERS_FILE, 'private:'.Patch::SYNC_RECEIPT,
    ...array_keys(Patch::questionEvidencePins())]));
sort($expectedSources); $actualSources = array_keys($preview['sources'] ?? []); sort($actualSources);
if ($actualSources !== $expectedSources) { throw new RuntimeException('Reviewed preview omits or adds a source/evidence pin.'); }
foreach ($preview['sources'] as $path => $sha) {
    $file = str_starts_with($path, 'private:') ? $dir.'/'.substr($path, 8) : $root.'/'.$path;
    if (preg_match('/^[a-f0-9]{64}$/D', $sha) !== 1 || !hash_equals($sha, $hash($file))) {
        throw new RuntimeException('Reviewed preview source/evidence bytes changed: '.$path);
    }
    $sourceEvidence[$file] = $sha;
}
foreach ($before['physical_hashes'] as $path => $sha) {
    if (!hash_equals($sha, $hash($path))) { throw new RuntimeException('Physical target/configuration changed.'); }
    $sourceEvidence[$path] = $sha;
}
foreach ([$before['visual_hashes'], $before['frozen_hashes']] as $hashes) {
    foreach ($hashes as $path => $sha) {
        if (!hash_equals($sha, $hash($root.'/'.$path))) { throw new RuntimeException('Visual or historical frozen source changed: '.$path); }
        $sourceEvidence[$root.'/'.$path] = $sha;
    }
}
$sync = $load(Patch::SYNC_RECEIPT); $syncPaths = array_column($sync['files'] ?? [], 'path'); sort($syncPaths);
if ($syncPaths !== Patch::syncPaths($patch, $contract) || ($sync['base_sha'] ?? null) !== Package::BASE_SHA
    || ($sync['served_root'] ?? null) !== $root || ($sync['source_worktree'] ?? null) !== Patch::SOURCE_WORKTREE
    || ($sync['visual_before'] ?? null) !== ['file' => Patch::BEFORE_FILE, 'sha256' => Patch::BEFORE_SHA]) {
    throw new RuntimeException('Exact finite source-sync receipt scope differs.');
}
foreach ($sync['files'] as $record) {
    if ($hash($root.'/'.$record['path']) !== ($record['root_after_sha256'] ?? null)
        || $hash(Patch::SOURCE_WORKTREE.'/'.$record['path']) !== ($record['worktree_sha256'] ?? null)) {
        throw new RuntimeException('Served/worktree source-sync bytes changed.');
    }
    $sourceEvidence[$root.'/'.$record['path']] = $record['root_after_sha256'];
    $sourceEvidence[Patch::SOURCE_WORKTREE.'/'.$record['path']] = $record['worktree_sha256'];
}

$inventory = $load(Patch::QUESTION_INVENTORY_FILE, Patch::QUESTION_INVENTORY_SHA);
$questionPlan = $load(Patch::QUESTION_PLAN_FILE, Patch::QUESTION_PLAN_SHA);
$questionSource = Supplement::load($root);
if (($inventory['schema_version'] ?? null) !== 1 || ($inventory['kind'] ?? null) !== 'M75_question_supplement_inventory'
    || ($questionPlan['schema_version'] ?? null) !== 1 || ($questionPlan['kind'] ?? null) !== 'M75_question_insert_plan'
    || ($inventory['base_sha'] ?? null) !== Package::BASE_SHA || ($questionPlan['base_sha'] ?? null) !== Package::BASE_SHA
    || ($inventory['before_sha256'] ?? null) !== Patch::BEFORE_SHA || ($questionPlan['before_sha256'] ?? null) !== Patch::BEFORE_SHA
    || ($inventory['source_sha256'] ?? null) !== Supplement::SOURCE_SHA || ($questionPlan['source_sha256'] ?? null) !== Supplement::SOURCE_SHA
    || ($questionPlan['inventory'] ?? null) !== ['file' => Patch::QUESTION_INVENTORY_FILE, 'sha256' => Patch::QUESTION_INVENTORY_SHA]
    || ($inventory['identity'] ?? null) !== $before['identity'] || ($inventory['sql_before'] ?? null) !== $before['sql_before']
    || ($inventory['binding'] ?? null) !== Supplement::binding() || ($questionPlan['binding'] ?? null) !== Supplement::binding()
    || ($inventory['id_step_offset'] ?? null) !== [1, 1] || ($inventory['db_writes'] ?? null) !== 0
    || ($inventory['http_requests'] ?? null) !== 0
    || ($inventory['question_collisions'] ?? null) !== array_fill_keys(array_keys(Rows::ROW_COLUMNS), [])) {
    throw new RuntimeException('Independent question source/inventory/plan lineage differs.');
}
$projection = Supplement::project($questionSource, Supplement::binding(), $inventory['option_matches'] ?? []);
$ids = $inventory['ids'] ?? []; $timestamp = $inventory['row_timestamp'] ?? null;
if (!is_string($timestamp) || preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/D', $timestamp) !== 1
    || ($questionPlan['ids'] ?? null) !== $ids || ($questionPlan['row_timestamp'] ?? null) !== $timestamp
    || ($inventory['projection'] ?? null) !== $projection || ($questionPlan['projection'] ?? null) !== $projection
    || array_keys($ids) !== array_keys(Rows::ROW_COLUMNS)
    || array_keys($inventory['allocation'] ?? []) !== array_keys(Rows::ROW_COLUMNS)
    || array_keys($inventory['schema'] ?? []) !== array_keys(Rows::ROW_COLUMNS)) {
    throw new RuntimeException('Reviewed question projection/ID/timestamp/schema scope differs.');
}
$newRows = Rows::materialize($projection, $ids, $timestamp);
if ($newRows !== ($inventory['materialized'] ?? null) || $newRows !== ($questionPlan['materialized'] ?? null)
    || count($newRows['questions']) !== 2 || count($newRows['question_answers']) !== 10
    || count($newRows['question_theory_text_blocks']) !== 2 || count($newRows['question_options']) !== 0) {
    throw new RuntimeException('Exact two insert-only families/zero new shared options differ.');
}
$expectedEvidence = $questionPlan; $expectedEvidence['_inventory'] = $inventory; $expectedEvidence['_before'] = $before;
$expectedScope = ['evidence' => $expectedEvidence, 'projection' => $projection, 'expected' => $newRows,
    'actual' => array_fill_keys(array_keys(Rows::ROW_COLUMNS), [])];
if (($preview['question_supplement'] ?? null) !== $expectedScope) {
    throw new RuntimeException('Reviewed BEFORE preview question scope differs from independent frozen evidence.');
}
foreach (Rows::ROW_COLUMNS as $table => $_) {
    $allocation = $inventory['allocation'][$table]; $expectedIds = [];
    if (array_keys($allocation) !== ['max_id', 'next_id'] || !is_int($allocation['max_id']) || $allocation['max_id'] < 0
        || !is_int($allocation['next_id']) || $allocation['next_id'] < 1) {
        throw new RuntimeException('Captured question-family allocation is incomplete.');
    }
    $start = max($allocation['max_id'] + 1, $allocation['next_id']);
    foreach (array_column($projection[$table], 'key') as $offset => $key) { $expectedIds[$key] = $start + $offset; }
    if ($ids[$table] !== $expectedIds) { throw new RuntimeException('New question IDs are not the exact reviewed sequential allocation.'); }
}
$approved = []; $seenNew = [];
foreach ($newRows as $table => $rows) {
    $approved[$table] = array_column($rows, null, 'id'); $seenNew[$table] = [];
    if (count($approved[$table]) !== count($rows)) { throw new RuntimeException('New question rows repeat an ID.'); }
}

$pageBefore = []; $pageAfter = []; $ukBefore = []; $ukAfter = []; $enPlBefore = [];
$expectedChanges = []; $ukByOwner = []; $continuingPractices = []; $subtitles = 0;
$bodyKinds = ['subtitle' => 0, 'practice' => 0, 'teaching' => 0]; $practiceIds = [4011, 4103, 3924];
foreach ($patch['targets'] as $i => $target) {
    $owner = Package::OWNERS[$i]; $captured = $before['pages'][$owner]; $oldPage = $captured['page'];
    $oldSource = $sourceBefore['targets'][$i]['before'];
    Patch::assertDefinitionScope($oldSource, $target['after'], $target, $i);
    if (($before['definitions'][$owner]['definition'] ?? null) !== $oldSource
        || $oldPage['id'] !== Package::PAGE_IDS[$i] || $oldPage['page_category_id'] !== 20
        || $oldPage['seeder'] !== $owner || $oldPage['slug'] !== Package::SLUGS[$i] || $oldPage['type'] !== 'theory'
        || array_column($captured['category_ancestry'], 'slug') !== Package::ANCESTRIES[$i]
        || $oldPage['title'] !== $oldSource['page']['title'] || $oldPage['text'] !== $oldSource['page']['subtitle_text']
        || count($target['after']['page']['blocks']) !== Package::CONTINUING_COUNTS[$i]) {
        throw new RuntimeException('Independent Page/category/source/continuing slot binding differs.');
    }
    foreach (['page', 'category_ancestry', 'blocks'] as $field) {
        if (($preview['pages'][$owner][$field] ?? null) !== ($captured[$field] ?? null)) {
            throw new RuntimeException('Reviewed preview does not preserve exact independent BEFORE page/relation inventory.');
        }
    }
    if (($preview['pages'][$owner]['relations'] ?? null) !== $projectCapturedRelations($captured['relations'])) {
        throw new RuntimeException('Reviewed preview does not preserve complete independent BEFORE relation rows through the exact serializer projection.');
    }
    $pageBefore[$oldPage['id']] = $oldPage;
    $pageAfter[$oldPage['id']] = array_replace($oldPage, ['text' => $target['after']['page']['subtitle_text']]);
    if ($oldPage['text'] !== $pageAfter[$oldPage['id']]['text']) {
        $expectedChanges['pages:'.$oldPage['id']] = ['table' => 'pages', 'id' => $oldPage['id'], 'uuid' => $oldPage['uuid'] ?? null,
            'seeder' => $owner, 'before' => ['text' => $oldPage['text']], 'after' => ['text' => $pageAfter[$oldPage['id']]['text']]];
    }
    $ukByOwner[$owner] = 0; $positions = [];
    foreach ($captured['blocks'] as $old) {
        if ($old['locale'] !== 'uk') {
            if (!in_array($old['locale'], ['en', 'pl'], true) || isset($enPlBefore[$old['id']])) {
                throw new RuntimeException('Non-UK rows are not the exact separate EN/PL inventory.');
            }
            $enPlBefore[$old['id']] = $old; continue;
        }
        $position = $old['sort_order'];
        if (!is_int($position) || $position < 0 || $position > Package::CONTINUING_COUNTS[$i]
            || isset($positions[$position]) || isset($ukBefore[$old['id']])
            || $old['seeder'] !== $owner || $old['page_id'] !== $oldPage['id'] || $old['page_category_id'] !== 20
            || $old['level'] !== null) {
            throw new RuntimeException('Independent UK owner/order/NULL-level inventory differs or repeats a row.');
        }
        $positions[$position] = true; ++$ukByOwner[$owner];
        if ($position === 0) {
            if ($old['type'] !== 'subtitle' || $old['column'] !== 'header') { throw new RuntimeException('Continuing subtitle metadata differs.'); }
            ++$subtitles; $oldBody = $oldSource['page']['subtitle_html']; $body = $target['after']['page']['subtitle_html'];
            $kind = 'subtitle';
        } else {
            $config = $target['after']['page']['blocks'][$position - 1]; $oldConfig = $oldSource['page']['blocks'][$position - 1];
            if (M26DetailPackage::uuid($owner, $oldConfig, $position) !== $old['uuid']) {
                throw new RuntimeException('Continuing SQL UUID differs from independently frozen source identity.');
            }
            foreach (['type', 'column', 'heading', 'css_class', 'level'] as $field) {
                if ($old[$field] !== ($config[$field] ?? null)) {
                    throw new RuntimeException('M75 source attempts an unapproved metadata transition: '.$field);
                }
            }
            $oldBody = $oldConfig['body']; $body = $config['body']; $kind = $old['type'] === 'practice-set' ? 'practice' : 'teaching';
        }
        if (!is_string($body) || $old['body'] !== $oldBody) { throw new RuntimeException('Independent SQL/source BEFORE body differs.'); }
        $ukBefore[$old['id']] = $old; $ukAfter[$old['id']] = array_replace($old, ['body' => $body]);
        if ($old['body'] !== $body) {
            ++$bodyKinds[$kind];
            $expectedChanges['text_blocks:'.$old['id']] = ['table' => 'text_blocks', 'id' => $old['id'], 'uuid' => $old['uuid'],
                'seeder' => $owner, 'before' => ['body' => $old['body']], 'after' => ['body' => $body]];
        }
        if ($old['type'] === 'practice-set') {
            if ($old['id'] !== $practiceIds[$i] || $old['uuid'] !== Package::PRACTICE_UUIDS[$i]
                || $position !== Package::PRACTICE_SOURCE_INDICES[$i] + 1 || $old['column'] !== 'left') {
                throw new RuntimeException('Continuing practice ID/UUID/source position differs.');
            }
            $continuingPractices[] = ['owner' => $owner, 'id' => $old['id'], 'uuid' => $old['uuid'],
                'source_index' => Package::PRACTICE_SOURCE_INDICES[$i], 'level' => null, 'inserted' => false];
        }
    }
    $actualPositions = array_keys($positions); sort($actualPositions);
    if ($actualPositions !== range(0, Package::CONTINUING_COUNTS[$i])) { throw new RuntimeException('Continuing SQL positions are incomplete.'); }
}
if (array_keys($pageBefore) !== Package::PAGE_IDS || count($ukBefore) !== 37 || array_values($ukByOwner) !== [12, 12, 13]
    || $subtitles !== 3 || count($enPlBefore) !== 74 || count($continuingPractices) !== 3
    || count($preview['updates'] ?? []) !== count($expectedChanges)) {
    throw new RuntimeException('Exact three-Page/37UK/74ENPL/three-practice inventory differs.');
}
$reviewedKeys = [];
foreach ($preview['updates'] as $change) {
    $key = $change['table'].':'.$change['id']; $expected = $expectedChanges[$key] ?? null;
    if ($expected === null || isset($reviewedKeys[$key])
        || array_keys($change) !== ['table', 'id', 'uuid', 'seeder', 'before', 'after', 'before_sha256', 'after_sha256']
        || array_diff_key($change, ['before_sha256' => null, 'after_sha256' => null]) !== $expected
        || $change['before_sha256'] !== $digest($expected['before']) || $change['after_sha256'] !== $digest($expected['after'])) {
        throw new RuntimeException('Reviewed preview has an unapproved changed row/field or duplicated operation.');
    }
    $reviewedKeys[$key] = true;
}
$capturedPractice = array_values(array_filter($before['pages'][Supplement::OWNER]['blocks'],
    static fn ($b) => $b['id'] === Supplement::PRACTICE_ID));
if (count($capturedPractice) !== 1 || ($inventory['practice_before'] ?? null) !== $capturedPractice[0]
    || ($inventory['taxonomy'] ?? null) !== ($questionPlan['taxonomy'] ?? null)
    || array_keys($inventory['taxonomy'] ?? []) !== ['categories', 'sources']
    || array_column($inventory['taxonomy']['categories'] ?? [], 'id') !== [Supplement::CATEGORY_ID]
    || array_column($inventory['taxonomy']['sources'] ?? [], 'id') !== [Supplement::SOURCE_ID]) {
    throw new RuntimeException('Supplement continuing practice or exact existing taxonomy differs.');
}
$reference = $inventory['taxonomy_question'] ?? null;
if (!is_array($reference) || !in_array($reference, $before['pages'][Supplement::OWNER]['relations']['questions'], true)
    || $reference['seeder'] !== Supplement::OLD_BANK || (string) $reference['type'] !== '4'
    || $reference['category_id'] !== Supplement::CATEGORY_ID || $reference['source_id'] !== Supplement::SOURCE_ID) {
    throw new RuntimeException('Independently captured thematic taxonomy reference differs.');
}

$db = Illuminate\Support\Facades\DB::connection(); Guard::assertConfiguredDatabase($db);
$db->statement('SET TRANSACTION READ ONLY'); $db->beginTransaction();
try {
    $runtime = Guard::publicIdentity($db, $root.'/public');
    if ($runtime !== $before['identity'] || $runtime !== $inventory['identity']) {
        throw new RuntimeException('Fresh local runtime differs from independent BEFORE/question inventory.');
    }
    $tables = $db->getSchemaBuilder()->getTableListing(schema: $db->getDatabaseName(), schemaQualified: false); sort($tables);
    if (count($tables) !== 46 || $tables !== array_keys($before['sql_before'])) { throw new RuntimeException('Actual SQL table inventory changed.'); }
    $variables = (array) $db->selectOne('SELECT @@auto_increment_increment AS step_value, @@auto_increment_offset AS offset_value');
    if ([(int) $variables['step_value'], (int) $variables['offset_value']] !== [1, 1]) {
        throw new RuntimeException('Automatic ID step/offset differs from captured question allocation.');
    }
    $queryRows = static function ($query) use ($db): array {
        if (!is_string($query->from) || preg_match('/^[a-z_]+$/D', $query->from) !== 1) {
            throw new RuntimeException('Read-only relation query must name one exact table.');
        }
        $columns = $db->getSchemaBuilder()->getColumnListing($query->from);
        foreach (in_array('id', $columns, true) ? ['id'] : $columns as $column) { $query->orderBy($column); }
        return $query->get()->map(static fn ($r) => (array) $r)->all();
    };
    $actualFamily = []; $questionIds = array_column($newRows['questions'], 'id');
    foreach (Rows::ROW_COLUMNS as $table => $_) {
        $newIds = array_column($newRows[$table], 'id');
        $actualFamily[$table] = $queryRows($db->table($table)->where(function ($q) use ($table, $newIds, $questionIds): void {
            $q->whereIn('id', $newIds);
            if ($table === 'questions') { $q->orWhereIn('uuid', Supplement::UUIDS)->orWhere('seeder', Supplement::BANK); }
            elseif ($table === 'question_theory_text_blocks') { $q->orWhereIn('question_uuid', Supplement::UUIDS); }
            elseif (in_array($table, ['question_answers', 'question_option_question'], true)) { $q->orWhereIn('question_id', $questionIds); }
        }));
    }
    Rows::assertApplied($newRows, $actualFamily);
    foreach ($inventory['option_matches'] as $token => $matches) {
        if ($queryRows($db->table('question_options')->where('option', $token)) !== $matches) {
            throw new RuntimeException('Original complete SQL-collation option rows or timestamps changed.');
        }
    }
    foreach ($inventory['taxonomy'] as $table => $rows) {
        if ($queryRows($db->table($table)->whereIn('id', array_column($rows, 'id'))) !== $rows) {
            throw new RuntimeException('Original taxonomy rows or timestamps changed.');
        }
    }
    if ($queryRows($db->table('questions')->where('id', $reference['id'])) !== [$reference]) {
        throw new RuntimeException('Original taxonomy reference question changed.');
    }
    foreach (['question_tag', 'question_marker_tag', 'verb_hints', 'question_hints', 'question_variants'] as $table) {
        if ($queryRows($db->table($table)->whereIn('question_id', $questionIds)) !== []) {
            throw new RuntimeException('New question family gained an unapproved relation.');
        }
    }
    if ($queryRows($db->table('saved_grammar_test_questions')->whereIn('question_uuid', Supplement::UUIDS)) !== []) {
        throw new RuntimeException('New question families changed standalone saved-test membership.');
    }
    $rawAfter = []; $normalized = []; $protected = []; $seenPages = []; $seenUk = []; $seenEnPl = []; $allocationAfter = [];
    foreach ($tables as $table) {
        $metadata = $db->getSchemaBuilder()->getColumns($table);
        if (isset($before['schema'][$table]) && $metadata !== $before['schema'][$table]) {
            throw new RuntimeException('Independently captured original schema changed: '.$table);
        }
        if (isset($inventory['schema'][$table]) && $metadata !== $inventory['schema'][$table]) {
            throw new RuntimeException('Question-family detailed schema changed: '.$table);
        }
        $columns = array_column($metadata, 'name'); $query = $db->table($table);
        foreach (in_array('id', $columns, true) ? ['id'] : $columns as $column) { $query->orderBy($column); }
        $rawHash = hash_init('sha256'); $oldHash = hash_init('sha256'); $protectedHash = hash_init('sha256');
        $rawCount = 0; $oldCount = 0; $protectedCount = 0;
        foreach ($query->cursor() as $record) {
            $row = (array) $record; hash_update($rawHash, $digest($row)."\n"); ++$rawCount;
            $id = $row['id'] ?? null; $projected = $row; $skipProtected = false;
            if (isset($approved[$table][$id])) {
                $same($row, $approved[$table][$id], 'A new row differs from its complete exact materialization.');
                if (isset($seenNew[$table][$id])) { throw new RuntimeException('A new row was counted twice.'); }
                $seenNew[$table][$id] = true; continue;
            }
            if ($table === 'pages' && isset($pageBefore[$id])) {
                $same($row, $pageAfter[$id], 'Target Page metadata/text/timestamps differ.');
                $projected = $pageBefore[$id]; $seenPages[$id] = true;
            }
            if ($table === 'text_blocks' && isset($ukBefore[$id])) {
                $same($row, $ukAfter[$id], 'Continuing UK body/owner/locale/metadata/timestamps differ.');
                $projected = $ukBefore[$id]; $seenUk[$id] = true; $skipProtected = true;
            }
            if ($table === 'text_blocks' && isset($enPlBefore[$id])) {
                $same($row, $enPlBefore[$id], 'An original EN/PL row changed.'); $seenEnPl[$id] = true;
            }
            hash_update($oldHash, $digest($projected)."\n"); ++$oldCount;
            if ($skipProtected) { continue; }
            if ($table === 'pages' && isset($pageBefore[$id])) { unset($row['text']); }
            hash_update($protectedHash, $digest($row)."\n"); ++$protectedCount;
        }
        $rawAfter[$table] = ['count' => $rawCount, 'sha256' => hash_final($rawHash)];
        $normalized[$table] = ['count' => $oldCount, 'sha256' => hash_final($oldHash)];
        $protected[$table] = ['count' => $protectedCount, 'sha256' => hash_final($protectedHash)];
        if (isset($inventory['allocation'][$table])) {
            $allocation = $inventory['allocation'][$table]; $count = count($newRows[$table]);
            $expectedMax = $count === 0 ? $allocation['max_id'] : max(array_column($newRows[$table], 'id'));
            $expectedNext = $count === 0 ? $allocation['next_id'] : $expectedMax + 1;
            $auto = (array) $db->selectOne('SELECT AUTO_INCREMENT AS next_id FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?', [$db->getDatabaseName(), $table]);
            $allocationAfter[$table] = ['max_id' => (int) ($db->table($table)->max('id') ?? 0), 'next_id' => (int) ($auto['next_id'] ?? 0)];
            if ($allocationAfter[$table] !== ['max_id' => $expectedMax, 'next_id' => $expectedNext]) {
                throw new RuntimeException('Captured question-family maximum/automatic allocation advanced outside its exact new rows.');
            }
        }
    }
    if (count($seenPages) !== 3 || count($seenUk) !== 37 || count($seenEnPl) !== 74) {
        throw new RuntimeException('A continuing Page/UK block/EN-PL row disappeared.');
    }
    foreach ($approved as $table => $rows) {
        $expectedIds = array_keys($rows); $actualIds = array_keys($seenNew[$table]); sort($expectedIds); sort($actualIds);
        if ($actualIds !== $expectedIds) { throw new RuntimeException('An exact new row disappeared from the independent full-table scan.'); }
    }
    if ($normalized !== $before['sql_before']) {
        throw new RuntimeException('An original row, timestamp, question, option, relation or progress field changed beyond exact source-bound content.');
    }
    if ($protected !== ($preview['protected'] ?? null)) { throw new RuntimeException('Independent reviewed protected projection differs.'); }
} finally { $db->rollBack(); }

foreach ($sourceEvidence as $file => $sha) {
    if (!hash_equals($sha, $hash($file))) { throw new RuntimeException('Reviewed source/evidence changed during the final SQL scan.'); }
}
$pageUpdates = count(array_filter($expectedChanges, static fn ($change) => $change['table'] === 'pages'));
$blockUpdates = count($expectedChanges) - $pageUpdates; $insertedCounts = array_map('count', $newRows);
$newRowCount = array_sum($insertedCounts);
$receipt = [
    'checked_at' => gmdate('c'), 'runtime' => $runtime, 'plan_file' => $previewName, 'plan_digest' => $reviewedDigest,
    'independent_before_sha256' => Patch::BEFORE_SHA, 'old_consumers_sha256' => Patch::CONSUMERS_SHA,
    'backup_file' => $backupName, 'backup_sha256' => $backupSha, 'backup_equals_reviewed_preview' => true,
    'question_inventory_file' => Patch::QUESTION_INVENTORY_FILE, 'question_inventory_sha256' => Patch::QUESTION_INVENTORY_SHA,
    'question_plan_file' => Patch::QUESTION_PLAN_FILE, 'question_plan_sha256' => Patch::QUESTION_PLAN_SHA,
    'question_source_sha256' => Supplement::SOURCE_SHA,
    'page_text_updates' => $pageUpdates, 'existing_block_body_updates' => $blockUpdates,
    'subtitle_body_updates' => $bodyKinds['subtitle'], 'teaching_body_updates' => $bodyKinds['teaching'],
    'practice_body_updates' => $bodyKinds['practice'], 'metadata_transitions' => 0,
    'update_operations' => count($expectedChanges), 'distinct_updated_rows' => count($expectedChanges),
    'insert_operations' => $newRowCount, 'distinct_inserted_rows' => $newRowCount,
    'total_write_operations' => count($expectedChanges) + $newRowCount,
    'distinct_changed_rows' => count($expectedChanges) + $newRowCount,
    'continuing_uk_rows' => count($ukBefore), 'continuing_structured_blocks' => array_sum(Package::CONTINUING_COUNTS),
    'continuing_subtitle_rows' => $subtitles, 'uk_rows_by_owner' => $ukByOwner,
    'unchanged_en_pl_rows' => count($enPlBefore), 'continuing_practices' => $continuingPractices,
    'new_practices' => [], 'new_text_blocks' => 0, 'deleted_rows' => 0, 'new_question_families' => $insertedCounts,
    'all_old_rows_and_timestamps_exact' => true, 'independent_before_projection_exact' => true,
    'reviewed_protected_projection_exact' => true, 'sql_tables' => count($rawAfter),
    'sql_after' => $rawAfter, 'normalized_before' => $normalized, 'protected' => $protected,
    'all_captured_allocations_exact' => true, 'allocation_tables_checked' => count($allocationAfter), 'allocation_after' => $allocationAfter,
    'allocation_boundary' => 'Only the five independently captured insert-family allocations are asserted; other table rows/schema are fully fingerprinted.',
    'visual_files_unchanged' => count($before['visual_hashes']), 'frozen_files_unchanged' => count($before['frozen_hashes']),
    'source_sync_unchanged' => true, 'source_sync_file' => Patch::SYNC_RECEIPT, 'all_preview_sources_exact' => true,
    'db_writes' => 0, 'http_requests' => 0,
    'http_metadata_and_browser_acceptance' => 'Separate evidence; this gate does not perform HTTP or browser interaction.',
];
$bytes = json_encode($receipt, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
$handle = fopen($receiptPath, 'x');
if ($handle === false) { throw new RuntimeException('Exclusive final receipt creation failed.'); }
try {
    if (fwrite($handle, $bytes) !== strlen($bytes) || !fflush($handle)) { throw new RuntimeException('Final private receipt write incomplete.'); }
} finally { fclose($handle); }
echo json_encode(['file' => $receiptName, 'sha256' => hash('sha256', $bytes), 'sql_tables' => count($rawAfter),
    'page_text_updates' => $pageUpdates, 'subtitle_body_updates' => $bodyKinds['subtitle'],
    'teaching_body_updates' => $bodyKinds['teaching'], 'practice_body_updates' => $bodyKinds['practice'],
    'update_operations' => count($expectedChanges), 'distinct_changed_rows' => count($expectedChanges) + $newRowCount,
    'continuing_uk_rows' => count($ukBefore), 'unchanged_en_pl_rows' => count($enPlBefore),
    'new_question_families' => $insertedCounts, 'all_old_rows_and_timestamps_exact' => true, 'db_writes' => 0, 'http_requests' => 0],
    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
