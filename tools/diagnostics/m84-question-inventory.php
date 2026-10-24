<?php

/** Explicit SELECT-only inventory; rollback precedes exclusive private receipt creation. Never apply/seed/HTTP. */
if (PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows' || count($argv) !== 3 || $argv[1] !== '--target=gramlyze.loc'
    || preg_match('/^question-inventory-v[1-9][0-9]*\.json$/D', $argv[2]) !== 1) {
    throw new RuntimeException('M84 requires explicit local target and exclusive private inventory basename.');
}
$root = 'D:/DEV/htdocs/gramlyze.loc'; $wt = $root.'/storage/app/m75-worktree'; $directory = $root.'/storage/app/seo-m84-local';
$normal = static fn ($p) => strtolower(str_replace('\\', '/', (string) realpath($p)));
if (!in_array($normal(dirname(__DIR__, 2)), [$normal($root), $normal($wt)], true)
    || !is_dir($directory) || is_link($directory) || !is_dir($wt) || is_link($wt)) {
    throw new RuntimeException('M84 inventory application/source/private paths differ.');
}
$file = $directory.'/'.$argv[2];
if (file_exists($file) || is_link($file)) { throw new RuntimeException('M84 private inventory already exists or is linked.'); }
$beforePath = $directory.'/before-v1.json'; $beforeSha = 'babac2b3efa88b87fc8ea50b1ad45fb9feb8cadfdf766c33e76ca7b24aa19916';
if (!is_file($beforePath) || is_link($beforePath) || !hash_equals($beforeSha, hash_file('sha256', $beforePath))) { throw new RuntimeException('M84 exact independent BEFORE is unavailable or changed.'); }
$before = json_decode(file_get_contents($beforePath), true, flags: JSON_THROW_ON_ERROR);
require $root.'/vendor/autoload.php'; require_once $wt.'/app/Support/M84QuestionSupplement.php';
use App\Support\M84QuestionSupplement as Supplement;
use App\Support\M51QuestionSupplement as Rows;
$sourcePath = $wt.'/'.Supplement::SOURCE;
if (!is_file($sourcePath) || is_link($sourcePath)) { throw new RuntimeException('M84 candidate question source missing or linked.'); }
$sourceBytes = file_get_contents($sourcePath); $source = json_decode($sourceBytes, true, flags: JSON_THROW_ON_ERROR); Supplement::validate($source);
if ($source['status'] === 'frozen' && !hash_equals(Supplement::SOURCE_SHA, hash('sha256', $sourceBytes))) { throw new RuntimeException('M84 frozen source pin differs; draft inventory cannot authorize apply.'); }
$app = require $root.'/bootstrap/app.php'; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if ($normal(base_path()) !== $normal($root)) { throw new RuntimeException('M84 actual application ROOT differs.'); }
$db = Illuminate\Support\Facades\DB::connection(); App\Services\M49LocalTargetGuard::assertConfiguredDatabase($db);
$rows = static fn ($q) => $q->get()->map(static fn ($r) => (array) $r)->all();
$out = ['schema_version' => 1, 'kind' => 'M84_question_supplement_inventory', 'base_sha' => Supplement::BASE_SHA,
    'before_sha256' => $beforeSha, 'source_sha256' => hash('sha256', $sourceBytes), 'created_at' => gmdate('c'),
    'bindings' => Supplement::bindings(), 'option_matches' => [], 'schema' => [], 'allocation' => [],
    'db_writes' => 0, 'http_requests' => 0];
$db->statement('SET TRANSACTION READ ONLY'); $db->beginTransaction();
try {
    $out['identity'] = App\Services\M49LocalTargetGuard::publicIdentity($db, $root.'/public');
    $out['sql_before'] = App\Services\M74BankCompletionPatch::fingerprints($db);
    if (($before['base_sha'] ?? null) !== Supplement::BASE_SHA || ($before['identity'] ?? null) !== $out['identity']
        || ($before['sql_before'] ?? null) !== $out['sql_before'] || ($before['sql_after_http'] ?? null) !== $out['sql_before']) { throw new RuntimeException('M84 fresh identity/full database differs from independent BEFORE.'); }
    $out['heroes_before'] = []; $out['taxonomy_questions'] = [];
    foreach (Supplement::bindings() as $i => $binding) {
        $owner = $binding['owner'];
        $hero = $rows($db->table('text_blocks')->where('id', $binding['theory_block_id'])->orderBy('id'));
        $captured = array_values(array_filter($before['pages'][$owner]['blocks'] ?? [], static fn ($b) => $b['id'] === $binding['theory_block_id']));
        if (count($hero) !== 1 || $hero !== $captured) { throw new RuntimeException('M84 independently captured continuing hero changed.'); }
        foreach (['uuid' => $binding['theory_block_uuid'], 'seeder' => $owner, 'page_id' => $binding['page_id'], 'page_category_id' => 48,
            'locale' => 'uk', 'type' => 'hero', 'level' => null, 'sort_order' => 1] as $field => $value) {
            if (!array_key_exists($field, $hero[0]) || $hero[0][$field] !== $value) { throw new RuntimeException('M84 exact existing hero metadata differs.'); }
        }
        $out['heroes_before'][$owner] = $hero[0];
        $reference = $rows($db->table('questions')->where('seeder', Supplement::OLD_BANKS[$i])->where('type', '4')->where('level', Supplement::LEVELS[$i])
            ->where('category_id', $binding['category_id'])->where('source_id', $binding['source_id'])->orderBy('uuid')->limit(1));
        if (count($reference) !== 1 || !in_array($reference[0], $before['pages'][$owner]['relations']['questions'] ?? [], true)) { throw new RuntimeException('M84 original thematic taxonomy reference is not captured.'); }
        $out['taxonomy_questions'][$owner] = $reference[0];
    }
    $out['taxonomy'] = ['categories' => $rows($db->table('categories')->where('id', 45)->orderBy('id')),
        'sources' => $rows($db->table('sources')->whereIn('id', Supplement::SOURCE_IDS)->orderBy('id'))];
    if (array_column($out['taxonomy']['categories'], 'id') !== [45] || array_column($out['taxonomy']['sources'], 'id') !== Supplement::SOURCE_IDS) { throw new RuntimeException('M84 original taxonomy is missing or ambiguous.'); }
    $tokens = [];
    foreach ($source['banks'] as $bank) { foreach ($bank['questions'] as $q) { foreach ([...$q['tokens_correct'], ...$q['distractors']] as $token) { $tokens[$token] = true; } } }
    foreach (array_keys($tokens) as $token) { $out['option_matches'][$token] = $rows($db->table('question_options')->where('option', $token)->orderBy('id')); }
    $out['option_case_review'] = Supplement::optionCaseReview($source, $out['option_matches']);
    if (array_filter($out['option_case_review'], static fn ($case) => !$case['exact']) !== []) {
        echo json_encode(['status' => 'refused-option-case-review', 'cases' => $out['option_case_review'], 'db_writes' => 0, 'receipt_created' => false], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
        throw new RuntimeException('M84 original option labels cannot reproduce one or more exact reviewed visible sentences; no receipt.');
    }
    $out['projection'] = Supplement::project($source, $out['bindings'], $out['option_matches']); $out['ids'] = [];
    foreach (Rows::ROW_COLUMNS as $table => $columns) {
        $actual = $db->getSchemaBuilder()->getColumnListing($table); sort($actual); sort($columns);
        if ($actual !== $columns) { throw new RuntimeException('M84 exact insert-family columns differ: '.$table); }
        $out['schema'][$table] = $db->getSchemaBuilder()->getColumns($table);
        $auto = (array) $db->selectOne('SELECT AUTO_INCREMENT AS next_id FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?', [$db->getDatabaseName(), $table]);
        $out['allocation'][$table] = ['max_id' => (int) ($db->table($table)->max('id') ?? 0), 'next_id' => (int) ($auto['next_id'] ?? 0)];
        if ($out['allocation'][$table]['next_id'] < 1) { throw new RuntimeException('M84 physical automatic allocation metadata unavailable.'); }
        $start = max($out['allocation'][$table]['max_id'] + 1, $out['allocation'][$table]['next_id']); $out['ids'][$table] = [];
        foreach (array_column($out['projection'][$table], 'key') as $n => $key) { $out['ids'][$table][$key] = $start + $n; }
    }
    $variables = (array) $db->selectOne('SELECT @@auto_increment_increment AS step_value, @@auto_increment_offset AS offset_value');
    $out['id_step_offset'] = [(int) $variables['step_value'], (int) $variables['offset_value']];
    if ($out['id_step_offset'] !== [1, 1]) { throw new RuntimeException('M84 exact physical 1/1 allocation required.'); }
    $out['row_timestamp'] = gmdate('Y-m-d H:i:s'); $out['materialized'] = Rows::materialize($out['projection'], $out['ids'], $out['row_timestamp']);
    $questionIds = array_values($out['ids']['questions']); $out['question_collisions'] = [];
    foreach (Rows::ROW_COLUMNS as $table => $_) {
        $ids = array_values($out['ids'][$table]);
        $out['question_collisions'][$table] = $rows($db->table($table)->where(function ($q) use ($table, $ids, $questionIds) {
            $q->whereIn('id', $ids);
            if ($table === 'questions') { $q->orWhereIn('uuid', Supplement::UUIDS)->orWhereIn('seeder', Supplement::BANKS); }
            elseif ($table === 'question_theory_text_blocks') { $q->orWhereIn('question_uuid', Supplement::UUIDS); }
            elseif (in_array($table, ['question_answers', 'question_option_question'], true)) { $q->orWhereIn('question_id', $questionIds); }
        })->orderBy('id'));
    }
    Rows::assertAbsent($out['question_collisions']);
    if (App\Services\M74BankCompletionPatch::fingerprints($db) !== $out['sql_before']) { throw new RuntimeException('M84 read-only inventory observed database drift.'); }
} finally { $db->rollBack(); }
if (file_exists($file) || is_link($file)) { throw new RuntimeException('M84 private receipt collided.'); }
$json = json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
$handle = fopen($file, 'x'); if ($handle === false) { throw new RuntimeException('M84 exclusive inventory creation failed.'); }
try { if (fwrite($handle, $json) !== strlen($json) || !fflush($handle)) { throw new RuntimeException('M84 inventory write incomplete.'); } } finally { fclose($handle); }
echo json_encode(['status' => 'read-only-question-inventory', 'file' => $argv[2], 'sha256' => hash('sha256', $json),
    'source_status' => $source['status'], 'source_sha256' => $out['source_sha256'], 'family_counts' => array_map('count', $out['materialized']),
    'reused_options' => count($out['projection']['reused_options']), 'db_writes' => 0, 'http_requests' => 0], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR)."\n";
