<?php

/** Explicit read-only lookup for the reviewed three-question Get-only supplement. */
if (PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows' || count($argv) !== 2
    || preg_match('/^question-inventory-v[1-9][0-9]*\.json$/D', $argv[1]) !== 1) {
    throw new RuntimeException('Explicit private M53 inventory basename required.');
}
$root = 'D:/DEV/htdocs/gramlyze.loc';
$wt = 'C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc';
if (strcasecmp(str_replace('\\', '/', realpath(dirname(__DIR__, 2))), $root) !== 0) {
    throw new RuntimeException('M53 inventory must run from served ROOT.');
}
require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php'; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$db = Illuminate\Support\Facades\DB::connection(); App\Services\M49LocalTargetGuard::assertConfiguredDatabase($db);
$source = App\Support\M53QuestionSupplement::load($wt);
$bytes = file_get_contents($wt.'/'.App\Support\M53QuestionSupplement::SOURCE);
if ($source['scope']['page_id'] !== 95 || count($source['questions']) !== 3
    || $source['seeder']['class'] !== 'Database\\Seeders\\V3\\Polyglot\\M53GetPassiveB1SupplementSeeder') {
    throw new RuntimeException('M53 question inventory scope differs.');
}
$tables = App\Support\M51QuestionSupplement::ROW_COLUMNS;
$out = ['schema_version' => 1, 'base_sha' => 'ba489be880d9a7c2b3dbc1698e887a05eb166fbe',
    'source_draft_sha256' => $source['source_draft_sha256'], 'source_sha256' => hash('sha256', $bytes), 'created_at' => gmdate('c'),
    'identity' => App\Services\M49LocalTargetGuard::publicIdentity($db, $root.'/public'),
    'option_matches' => [], 'allocation' => [], 'schema' => [], 'taxonomy' => []];
$rows = static fn ($query) => $query->get()->map(static fn ($row) => (array) $row)->all();
$db->statement('SET TRANSACTION READ ONLY'); $db->beginTransaction();
try {
    foreach ($tables as $table => $columns) {
        $actual = $db->getSchemaBuilder()->getColumnListing($table); sort($actual); sort($columns);
        if ($actual !== $columns) { throw new RuntimeException('M53 insert-family schema differs: '.$table); }
        $out['schema'][$table] = $db->getSchemaBuilder()->getColumns($table);
        $out['allocation'][$table] = ['max_id' => (int) $db->table($table)->max('id'), 'auto_increment' =>
            (array) $db->selectOne('SELECT AUTO_INCREMENT AS next_id FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?', [$table])];
    }
    $tokens = [];
    foreach ($source['questions'] as $question) { foreach ($question['options'] as $token) { $tokens[$token] = true; } }
    foreach (array_keys($tokens) as $token) {
        $out['option_matches'][$token] = ['requested' => $token,
            'rows' => $rows($db->table('question_options')->where('option', $token)->orderBy('id'))];
    }
    $out['taxonomy'] = ['categories' => $rows($db->table('categories')->where('id', 45)),
        'sources' => $rows($db->table('sources')->where('id', 354))];
    $out['question_collisions'] = $rows($db->table('questions')->where(function ($query) use ($source) {
        $query->whereIn('uuid', array_column($source['questions'], 'uuid'))->orWhere('seeder', $source['seeder']['class']);
    })->orderBy('id'));
} finally { $db->rollBack(); }
if (count($out['taxonomy']['categories']) !== 1 || count($out['taxonomy']['sources']) !== 1 || $out['question_collisions'] !== []) {
    throw new RuntimeException('M53 existing taxonomy unavailable or new question UUID/bank collision.');
}
$directory = $root.'/storage/app/seo-m53-local'; $file = $directory.'/'.$argv[1];
if (!is_dir($directory) || is_link($directory) || file_exists($file) || is_link($file)) { throw new RuntimeException('Unsafe/collided private inventory.'); }
$json = json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
$handle = fopen($file, 'x');
try { if (fwrite($handle, $json) !== strlen($json) || !fflush($handle)) { throw new RuntimeException('Incomplete private inventory.'); } }
finally { fclose($handle); }
echo json_encode(['status' => 'read-only-question-inventory', 'file' => $argv[1], 'sha256' => hash('sha256', $json),
    'tokens' => count($tokens), 'missing_options' => array_keys(array_filter($out['option_matches'], static fn ($row) => $row['rows'] === [])),
    'ambiguous_options' => array_keys(array_filter($out['option_matches'], static fn ($row) => count($row['rows']) > 1)),
    'taxonomy' => $out['taxonomy'], 'db_writes' => 0], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
