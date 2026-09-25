<?php

// Bare-PDO, local-only educational snapshot. Never boot Laravel/observers.
if (PHP_SAPI !== 'cli' || count($argv) !== 3) exit(2);
$repo = realpath($argv[1]);
$destination = $argv[2];
$parent = realpath(dirname($destination));
if (!$repo || !$parent || !preg_match('~/storage/app/seo-m3-2-local/stands-[a-f0-9]{32}$~', str_replace('\\', '/', $parent))
    || !str_starts_with(str_replace('\\', '/', $parent), str_replace('\\', '/', $repo).'/')
    || basename($destination) !== 'data.sqlite' || file_exists($destination) || is_link($destination)) exit(3);
try {
    require $repo.'/vendor/autoload.php';
    $env = Dotenv\Dotenv::parse(file_get_contents($repo.'/.env'));
} catch (Throwable) {
    // Dotenv exceptions can echo an invalid source line containing a secret.
    fwrite(STDERR, "Unable to parse local configuration safely\n");
    exit(4);
}
if (($env['DB_CONNECTION'] ?? 'mysql') !== 'mysql'
    || !in_array($env['DB_HOST'] ?? '', ['localhost', '127.0.0.1', '::1'], true)
    || !preg_match('/^[a-zA-Z0-9_]+$/', $env['DB_DATABASE'] ?? '')
    || !ctype_digit((string) ($env['DB_PORT'] ?? '3306'))) exit(4);
$tables = ['languages', 'page_categories', 'pages', 'text_blocks', 'tags', 'page_tag',
    'page_category_tag', 'tag_text_block', 'site_tree_variants', 'site_tree_items',
    'categories', 'sources', 'questions', 'question_options', 'question_option_question',
    'question_answers', 'verb_hints', 'question_tag', 'question_marker_tag', 'question_hints',
    'question_variants', 'question_theory_text_blocks', 'saved_grammar_tests', 'saved_grammar_test_questions'];
try {
    $source = new PDO('mysql:host='.$env['DB_HOST'].';port='.($env['DB_PORT'] ?? '3306').';dbname='.$env['DB_DATABASE'].';charset=utf8mb4',
        $env['DB_USERNAME'] ?? '', $env['DB_PASSWORD'] ?? '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    unset($env);
    $source->exec('SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ');
    $source->exec('SET SESSION TRANSACTION READ ONLY');
    $source->exec('START TRANSACTION WITH CONSISTENT SNAPSHOT');
    $target = new PDO('sqlite:'.$destination, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $target->beginTransaction();
    $counts = [];
    foreach ($tables as $table) {
        $status = $source->query("SHOW TABLE STATUS WHERE Name = '$table'")->fetch(PDO::FETCH_ASSOC);
        if (!$status || $status['Engine'] !== 'InnoDB') throw new RuntimeException('Missing/nontransactional allowlisted table: '.$table);
        $columns = $source->query('SHOW COLUMNS FROM `'.$table.'`')->fetchAll(PDO::FETCH_ASSOC);
        $defs = [];
        foreach ($columns as $column) {
            $type = preg_match('/^(tinyint|smallint|mediumint|int|bigint|bit)/', $column['Type']) ? 'INTEGER'
                : (preg_match('/^(float|double|decimal)/', $column['Type']) ? 'REAL' : 'TEXT');
            $defs[] = '"'.$column['Field'].'" '.$type;
        }
        $target->exec('CREATE TABLE "'.$table.'" ('.implode(',', $defs).')');
        $insert = $target->prepare('INSERT INTO "'.$table.'" VALUES ('.implode(',', array_fill(0, count($columns), '?')).')');
        $count = 0;
        foreach ($source->query('SELECT * FROM `'.$table.'`', PDO::FETCH_NUM) as $row) {
            $insert->execute($row);
            $count++;
        }
        $indexes = [];
        foreach ($source->query('SHOW INDEX FROM `'.$table.'`') as $index) {
            if (!$index['Column_name']) continue;
            $indexes[$index['Key_name']][(int) $index['Seq_in_index']] = '"'.$index['Column_name'].'"';
        }
        foreach ($indexes as $name => $fields) {
            ksort($fields);
            $target->exec('CREATE INDEX "'.$table.'_'.$name.'" ON "'.$table.'" ('.implode(',', $fields).')');
        }
        $counts[$table] = $count;
    }
    $source->rollBack();
    $target->commit();
    $target = null;
    echo json_encode(['tables' => $counts, 'sourcePolicy' => 'local MySQL; repeatable-read consistent snapshot; transaction READ ONLY',
        'target' => 'private SQLite educational allowlist; no users/auth/session/deployment tables', 'sha256' => hash_file('sha256', $destination)]);
} catch (Throwable $error) {
    // PDO messages can contain connection details; do not publish them.
    fwrite(STDERR, 'Snapshot failed: '.get_class($error)."; current table=".($table ?? 'connection')."\n");
    exit(5);
}
