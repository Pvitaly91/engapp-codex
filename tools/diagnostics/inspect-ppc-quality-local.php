<?php

// SELECT-only inventory of the physical working .loc. No credentials or user rows.
if (PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows') { exit(1); }
$root = 'D:/DEV/htdocs/gramlyze.loc';
require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$db = Illuminate\Support\Facades\DB::connection();
$physical = (array) $db->selectOne('SELECT DATABASE() db, @@hostname server, @@port port');
if (realpath(base_path()) !== realpath($root) || $db->getDriverName() !== 'mysql'
    || !in_array($db->getConfig('host'), ['localhost', '127.0.0.1'], true)
    || $physical['db'] !== 'gr2' || (int) $physical['port'] !== 3306
    || strcasecmp($physical['server'], gethostname()) !== 0) { throw new RuntimeException('Unexpected local target.'); }
$pages = $db->table('pages')->whereIn('seeder', array_map(fn ($suffix) => 'Database\\Seeders\\Page_V3\\Tenses\\PastPerfectContinuous\\PastPerfectContinuous'.$suffix.'TheorySeeder', ['Forms', 'Negatives', 'Questions', 'TimeExpressions']))->orderBy('id')->get();
$blocks = $db->table('text_blocks')->whereIn('page_id', $pages->pluck('id'))->orderBy('id')->get();
$links = $db->table('question_theory_text_blocks')->whereIn('text_block_uuid', $blocks->pluck('uuid'))->get();
$linked = $db->table('questions')->whereIn('uuid', $links->pluck('question_uuid'))->orderBy('id')->get();
$candidates = $db->table('questions')->where('seeder', 'like', '%PastPerfectContinuous%')->orderBy('id')->get();
$tests = $db->table('saved_grammar_tests')->whereIn('id', $db->table('saved_grammar_test_questions')->whereIn('question_uuid', $linked->pluck('uuid'))->pluck('saved_grammar_test_id'))
    ->orWhere('slug', 'like', '%past-perfect-continuous%')->orderBy('id')->get();
$record = ['at' => gmdate('c'), 'target' => ['root' => $root, 'driver' => $db->getDriverName(), 'host' => $db->getConfig('host'), 'database' => $physical['db'], 'port' => $physical['port']],
    'pages' => $pages, 'blocks' => $blocks, 'links' => $links, 'linked_questions' => $linked, 'candidate_questions' => $candidates, 'saved_tests' => $tests];
$record['question_data'] = [];
foreach (['question_answers' => 'question_id', 'question_option_question' => 'question_id', 'verb_hints' => 'question_id', 'question_hints' => 'question_id', 'question_variants' => 'question_id'] as $table => $key) {
    $record['question_data'][$table] = $db->table($table)->whereIn($key, $candidates->pluck('id'))->orderBy('id')->get();
}
$record['question_data']['question_options'] = $db->table('question_options')->whereIn('id', collect($record['question_data']['question_option_question'])->pluck('option_id'))->orderBy('id')->get();
$record['saved_test_links'] = $db->table('saved_grammar_test_questions')->whereIn('saved_grammar_test_id', $tests->pluck('id'))->orderBy('id')->get();
$record['protected'] = [];
foreach ($db->select('SELECT TABLE_NAME name FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE()') as $tableRow) {
    $table = $tableRow->name;
    if (!preg_match('/(progress|attempt|state|user|session)/i', $table)) { continue; }
    $cols = $db->getSchemaBuilder()->getColumnListing($table); $query = $db->table($table);
    foreach (in_array('id', $cols, true) ? ['id'] : $cols as $column) { $query->orderBy($column); }
    $hash = hash_init('sha256'); $count = 0;
    foreach ($query->cursor() as $row) { hash_update($hash, json_encode($row)."\n"); $count++; }
    $record['protected'][$table] = ['count' => $count, 'sha256' => hash_final($hash)];
}
$name = $argv[1] ?? '';
if (!preg_match('/^[a-z0-9-]+\.json$/D', $name)) { throw new RuntimeException('Provide unique evidence basename.'); }
$dir = storage_path('app/ppc-quality-local'); if (!is_dir($dir)) { mkdir($dir, 0700, true); }
$file = fopen($dir.'/'.$name, 'x'); if (!$file) { throw new RuntimeException('Evidence exists.'); }
fwrite($file, json_encode($record, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)); fclose($file);
echo json_encode(['evidence' => $dir.'/'.$name, 'pages' => $pages->map(fn ($p) => ['id' => $p->id, 'slug' => $p->slug, 'seeder' => $p->seeder]),
    'linked_banks' => $linked->groupBy('seeder')->map(fn ($q) => ['count' => $q->count(), 'levels' => $q->groupBy('level')->map->count(), 'types' => $q->groupBy('type')->map->count()]),
    'candidates' => $candidates->groupBy('seeder')->map->count(), 'saved_tests' => $tests->map(fn ($t) => ['id' => $t->id, 'slug' => $t->slug, 'name' => $t->name]), 'protected' => $record['protected']], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n";
