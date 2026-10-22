<?php

/** Explicit CLI SELECT-only inventory. Creates only a new private receipt after rollback; never apply/seeds/HTTP. */
if (PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows' || count($argv) !== 3
    || $argv[1] !== '--target=gramlyze.loc'
    || preg_match('/^question-inventory-v[1-9][0-9]*\.json$/D', $argv[2]) !== 1) {
    throw new RuntimeException('M75 requires explicit local target and exclusive private inventory basename.');
}
$root = 'D:/DEV/htdocs/gramlyze.loc';
$wt = $root.'/storage/app/m75-worktree';
$directory = $root.'/storage/app/seo-m75-local';
$normal = static fn ($p) => strtolower(str_replace('\\', '/', (string) realpath($p)));
if (!in_array($normal(dirname(__DIR__, 2)), [$normal($root), $normal($wt)], true)
    || !is_dir($directory) || is_link($directory) || !is_dir($wt) || is_link($wt)) {
    throw new RuntimeException('M75 inventory application/source/private paths differ.');
}
$file = $directory.'/'.$argv[2];
if (file_exists($file) || is_link($file)) { throw new RuntimeException('M75 private receipt already exists or is linked.'); }
$beforePath = $directory.'/before-v1.json';
$beforeSha = '5e238ff98020caf4ce97a3057c7a624bd48898d64791e83a720e3fe792b09c4a';
if (!is_file($beforePath) || is_link($beforePath) || !hash_equals($beforeSha, hash_file('sha256', $beforePath))) {
    throw new RuntimeException('M75 independent BEFORE is missing or changed.');
}
$before = json_decode(file_get_contents($beforePath), true, flags: JSON_THROW_ON_ERROR);
require $root.'/vendor/autoload.php';
require_once $wt.'/app/Support/M75PronounForms.php';
require_once $wt.'/app/Support/M75QuestionSupplement.php';
use App\Support\M75QuestionSupplement as Supplement;
use App\Support\M51QuestionSupplement as Rows;
$sourcePath = $wt.'/'.Supplement::SOURCE;
if (!is_file($sourcePath) || is_link($sourcePath)) { throw new RuntimeException('M75 exact candidate question source unavailable.'); }
$sourceBytes = file_get_contents($sourcePath); $source = json_decode($sourceBytes, true, flags: JSON_THROW_ON_ERROR);
Supplement::validate($source);
if ($source['status'] === 'frozen' && !hash_equals(Supplement::SOURCE_SHA, hash('sha256', $sourceBytes))) {
    throw new RuntimeException('M75 frozen source pin differs; a draft receipt cannot authorize apply.');
}
$app = require $root.'/bootstrap/app.php'; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if ($normal(base_path()) !== $normal($root)) { throw new RuntimeException('M75 served application ROOT differs.'); }
$db = Illuminate\Support\Facades\DB::connection(); App\Services\M49LocalTargetGuard::assertConfiguredDatabase($db);
$rows = static fn ($q) => $q->get()->map(static fn ($r) => (array) $r)->all();
$out = ['schema_version' => 1, 'kind' => 'M75_question_supplement_inventory', 'base_sha' => App\Support\M75PronounForms::BASE_SHA,
    'before_sha256' => $beforeSha, 'source_sha256' => hash('sha256', $sourceBytes), 'created_at' => gmdate('c'),
    'binding' => Supplement::binding(), 'option_matches' => [], 'schema' => [], 'allocation' => [],
    'db_writes' => 0, 'http_requests' => 0];
$db->statement('SET TRANSACTION READ ONLY'); $db->beginTransaction();
try {
    $out['identity'] = App\Services\M49LocalTargetGuard::publicIdentity($db, $root.'/public');
    $out['sql_before'] = App\Services\M74BankCompletionPatch::fingerprints($db);
    if (($before['base_sha'] ?? null) !== App\Support\M75PronounForms::BASE_SHA || ($before['identity'] ?? null) !== $out['identity']
        || ($before['sql_before'] ?? null) !== $out['sql_before'] || ($before['sql_after_http'] ?? null) !== $out['sql_before']) {
        throw new RuntimeException('M75 fresh runtime/complete original database differs from independent BEFORE.');
    }
    $practice = $rows($db->table('text_blocks')->where('id', Supplement::PRACTICE_ID));
    $captured = array_values(array_filter($before['pages'][Supplement::OWNER]['blocks'] ?? [],
        static fn ($b) => $b['id'] === Supplement::PRACTICE_ID));
    if (count($practice) !== 1 || $practice !== $captured) { throw new RuntimeException('M75 independent continuing practice changed.'); }
    $out['practice_before'] = $practice[0];
    foreach (['uuid' => Supplement::PRACTICE_UUID, 'seeder' => Supplement::OWNER, 'page_id' => Supplement::PAGE_ID,
        'page_category_id' => Supplement::PAGE_CATEGORY_ID, 'locale' => 'uk', 'type' => 'practice-set',
        'column' => 'left', 'level' => null, 'sort_order' => 11] as $key => $value) {
        if (!array_key_exists($key, $practice[0]) || $practice[0][$key] !== $value) {
            throw new RuntimeException('M75 exact actual continuing practice binding differs.');
        }
    }
    $out['taxonomy'] = [
        'categories' => $rows($db->table('categories')->where('id', Supplement::CATEGORY_ID)->orderBy('id')),
        'sources' => $rows($db->table('sources')->where('id', Supplement::SOURCE_ID)->orderBy('id')),
    ];
    if (count($out['taxonomy']['categories']) !== 1 || count($out['taxonomy']['sources']) !== 1) {
        throw new RuntimeException('M75 existing taxonomy is unavailable or ambiguous.');
    }
    $reference = $rows($db->table('questions')->where('seeder', Supplement::OLD_BANK)->where('type', '4')
        ->where('category_id', Supplement::CATEGORY_ID)->where('source_id', Supplement::SOURCE_ID)
        ->where('level', 'A2')->orderBy('uuid')->limit(1));
    if (count($reference) !== 1 || !in_array($reference[0], $before['pages'][Supplement::OWNER]['relations']['questions'] ?? [], true)) {
        throw new RuntimeException('M75 original thematic taxonomy reference is not independently captured.');
    }
    $out['taxonomy_question'] = $reference[0];
    $tokens = [];
    foreach ($source['questions'] as $question) { foreach ($question['options'] as $token) { $tokens[$token] = true; } }
    foreach (array_keys($tokens) as $token) {
        $out['option_matches'][$token] = $rows($db->table('question_options')->where('option', $token)->orderBy('id'));
    }
    $out['projection'] = Supplement::project($source, $out['binding'], $out['option_matches']);
    $out['ids'] = [];
    foreach (Rows::ROW_COLUMNS as $table => $columns) {
        $actual = $db->getSchemaBuilder()->getColumnListing($table); sort($columns); sort($actual);
        if ($actual !== $columns) { throw new RuntimeException('M75 exact insert-family columns differ: '.$table); }
        $out['schema'][$table] = $db->getSchemaBuilder()->getColumns($table);
        $auto = (array) $db->selectOne('SELECT AUTO_INCREMENT AS next_id FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?', [$db->getDatabaseName(), $table]);
        $out['allocation'][$table] = ['max_id' => (int) ($db->table($table)->max('id') ?? 0), 'next_id' => (int) ($auto['next_id'] ?? 0)];
        if ($out['allocation'][$table]['next_id'] < 1) { throw new RuntimeException('M75 automatic allocation metadata unavailable.'); }
        $start = max($out['allocation'][$table]['max_id'] + 1, $out['allocation'][$table]['next_id']);
        $out['ids'][$table] = [];
        foreach (array_column($out['projection'][$table], 'key') as $offset => $key) { $out['ids'][$table][$key] = $start + $offset; }
    }
    $variables = (array) $db->selectOne('SELECT @@auto_increment_increment AS step_value, @@auto_increment_offset AS offset_value');
    $out['id_step_offset'] = [(int) $variables['step_value'], (int) $variables['offset_value']];
    if ($out['id_step_offset'] !== [1, 1]) { throw new RuntimeException('M75 exact physical 1/1 allocation is required.'); }
    $out['row_timestamp'] = gmdate('Y-m-d H:i:s');
    $out['materialized'] = Rows::materialize($out['projection'], $out['ids'], $out['row_timestamp']);
    $questionIds = array_values($out['ids']['questions']); $out['question_collisions'] = [];
    foreach (Rows::ROW_COLUMNS as $table => $_) {
        $ids = array_values($out['ids'][$table]);
        $out['question_collisions'][$table] = $rows($db->table($table)->where(function ($q) use ($table, $ids, $questionIds) {
            $q->whereIn('id', $ids);
            if ($table === 'questions') { $q->orWhereIn('uuid', Supplement::UUIDS)->orWhere('seeder', Supplement::BANK); }
            elseif ($table === 'question_theory_text_blocks') { $q->orWhereIn('question_uuid', Supplement::UUIDS); }
            elseif (in_array($table, ['question_answers', 'question_option_question'], true)) { $q->orWhereIn('question_id', $questionIds); }
        })->orderBy('id'));
    }
    Rows::assertAbsent($out['question_collisions']);
    if (App\Services\M74BankCompletionPatch::fingerprints($db) !== $out['sql_before']) {
        throw new RuntimeException('M75 SELECT-only inventory observed database drift.');
    }
} finally { $db->rollBack(); }
if (file_exists($file) || is_link($file)) { throw new RuntimeException('M75 exclusive private receipt collided.'); }
$json = json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
$handle = fopen($file, 'x');
if ($handle === false) { throw new RuntimeException('M75 exclusive private receipt cannot be opened.'); }
try {
    if (fwrite($handle, $json) !== strlen($json) || !fflush($handle)) { throw new RuntimeException('M75 private inventory write was incomplete.'); }
} finally { fclose($handle); }
echo json_encode(['status' => 'read-only-question-inventory', 'file' => $argv[2], 'sha256' => hash('sha256', $json),
    'source_status' => $source['status'], 'source_sha256' => $out['source_sha256'], 'family_counts' => array_map('count', $out['materialized']),
    'reused_options' => count($out['projection']['reused_options']), 'db_writes' => 0, 'http_requests' => 0],
    JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
