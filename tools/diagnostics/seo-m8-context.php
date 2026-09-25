<?php
/** Read-only local DB evidence. No kernel, exports, credentials or learner state. */
declare(strict_types=1);
if (($argv[1] ?? '') !== '--local-read-only' || !preg_match('/^[a-z0-9-]+$/', $argv[2] ?? '')) { exit(2); }
$root = dirname(__DIR__, 2);
require $root.'/vendor/autoload.php';
$env = Dotenv\Dotenv::parse(file_get_contents($root.'/.env'));
if (($env['DB_CONNECTION'] ?? '') !== 'mysql' || !in_array($env['DB_HOST'] ?? '', ['localhost', '127.0.0.1'], true)) { exit(2); }
$pdo = new PDO('mysql:host='.$env['DB_HOST'].';port='.($env['DB_PORT'] ?? '3306').';dbname='.$env['DB_DATABASE'].';charset=utf8mb4', $env['DB_USERNAME'], $env['DB_PASSWORD'] ?? '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$identity = $pdo->query('SELECT DATABASE() AS db, @@hostname AS server, @@port AS port')->fetch(PDO::FETCH_ASSOC);
if ($identity['db'] !== $env['DB_DATABASE']) { throw new RuntimeException('Actual database mismatch'); }
$pdo->exec('SET SESSION TRANSACTION READ ONLY');
$pdo->beginTransaction();
$result = ['at' => gmdate('c'), 'connection' => $identity, 'pages' => [], 'tables' => []];
$seeders = array_map(fn ($name) => 'Database\\Seeders\\Page_V3\\PronounsDemonstratives\\PronounsDemonstratives'.$name.'TheorySeeder', ['OneOnes', 'ReciprocalPronouns']);
$query = $pdo->prepare('SELECT * FROM pages WHERE seeder = ?');
$blocks = $pdo->prepare('SELECT * FROM text_blocks WHERE page_id = ? ORDER BY locale,sort_order,id');
foreach ($seeders as $seeder) {
    $query->execute([$seeder]); $pages = $query->fetchAll(PDO::FETCH_ASSOC);
    if (count($pages) !== 1) { throw new RuntimeException('Ambiguous/missing target Page'); }
    $page = $pages[0]; $blocks->execute([$page['id']]);
    $result['pages'][] = ['page' => $page, 'blocks' => $blocks->fetchAll(PDO::FETCH_ASSOC)];
}
$tables = ['pages', 'page_categories', 'text_blocks', 'tag_text_block', 'page_tag', 'page_category_tag', 'tags',
    'questions', 'question_answers', 'question_options', 'question_option_question', 'question_theory_text_blocks',
    'question_tag', 'question_marker_tag', 'question_hints', 'verb_hints', 'question_variants',
    'saved_grammar_tests', 'saved_grammar_test_questions', 'tests', 'site_tree_items', 'site_tree_variants'];
$existing = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
foreach ($tables as $table) {
    if (!in_array($table, $existing, true)) { continue; }
    $columns = $pdo->query('SHOW COLUMNS FROM `'.$table.'`')->fetchAll(PDO::FETCH_COLUMN);
    $order = in_array('id', $columns, true) ? '`id`' : implode(',', array_map(fn ($c) => '`'.$c.'`', $columns));
    $q = $pdo->query('SELECT * FROM `'.$table.'` ORDER BY '.$order);
    $hash = hash_init('sha256'); $count = 0; $rows = [];
    while ($row = $q->fetch(PDO::FETCH_ASSOC)) {
        $encoded = json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        hash_update($hash, $encoded."\n"); $count++;
        if ($table === 'text_blocks') { $rows[$row['id']] = hash('sha256', $encoded); }
    }
    $result['tables'][$table] = ['count' => $count, 'sha256' => hash_final($hash)];
    if ($rows) { $result['text_block_row_hashes'] = $rows; }
}
$pdo->rollBack();
$dir = $root.'/storage/app/seo-m8-local';
if (!is_dir($dir)) { mkdir($dir, 0700, true); }
$path = $dir.'/'.$argv[2].'-db.json';
$file = fopen($path, 'x');
if (!$file) { throw new RuntimeException('Evidence exists'); }
fwrite($file, json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)); fclose($file);
echo json_encode(['file' => $path, 'connection' => $identity, 'pages' => array_map(fn ($p) => ['slug' => $p['page']['slug'], 'blocks' => count($p['blocks']), 'empty_uk' => array_values(array_map(fn ($b) => ['id' => $b['id'], 'uuid' => $b['uuid'], 'sort_order' => $b['sort_order'], 'type' => $b['type'], 'heading' => $b['heading']], array_filter($p['blocks'], fn ($b) => $b['locale'] === 'uk' && trim($b['body'] ?? '') === '')))], $result['pages']), 'tables' => count($result['tables'])], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE).PHP_EOL;
