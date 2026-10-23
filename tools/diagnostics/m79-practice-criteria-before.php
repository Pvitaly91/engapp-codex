<?php

/** Explicit independent SELECT-only current-v1 capture for the finite visible-criteria correction. */
if (PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows' || count($argv) !== 2 || $argv[1] !== '--target=gramlyze.loc') {
    throw new RuntimeException('M79 criteria BEFORE requires explicit local target.');
}
$root = 'D:/DEV/htdocs/gramlyze.loc'; $dir = $root.'/storage/app/seo-m79-local';
$file = $dir.'/m79-practice-criteria-before-v1.json';
if (!is_dir($dir) || is_link($dir) || file_exists($file) || is_link($file)) { throw new RuntimeException('M79 criteria exclusive private path differs.'); }
require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php'; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if (strtolower(str_replace('\\', '/', base_path())) !== strtolower($root)) { throw new RuntimeException('M79 actual ROOT differs.'); }
$db = Illuminate\Support\Facades\DB::connection(); App\Services\M79LocalTargetGuard::assertConfiguredDatabase($db);
$postPath = $dir.'/final-readonly-v1.json';
if (!is_file($postPath) || is_link($postPath) || hash_file('sha256', $postPath) !== 'c9c8f0d1b42d5a0158555ef29f3843c66a448cbcacb963348f4f46c1e5c5fb67') { throw new RuntimeException('M79 independent original apply audit differs.'); }
$post = json_decode(file_get_contents($postPath), true, flags: JSON_THROW_ON_ERROR);
$out = ['schema_version'=>1, 'kind'=>'M79_practice_criteria_before', 'target'=>'gramlyze.loc', 'served_root'=>$root,
    'created_at'=>gmdate('c'), 'original_postconditions_sha256'=>hash_file('sha256', $postPath),
    'row_ids'=>[4243,4372], 'schema'=>[], 'sql_before'=>[], 'rows'=>[], 'db_writes'=>0, 'http_requests'=>0];
$db->statement('SET TRANSACTION READ ONLY'); $db->beginTransaction();
try {
    $out['identity'] = App\Services\M79LocalTargetGuard::publicIdentity($db, $root.'/public');
    $tables = $db->getSchemaBuilder()->getTableListing(schema:$db->getDatabaseName(), schemaQualified:false); sort($tables);
    if ($tables !== array_keys($post['sql_after'])) { throw new RuntimeException('M79 complete table set drifted.'); }
    foreach ($tables as $table) {
        $schema = $db->getSchemaBuilder()->getColumns($table); $out['schema'][$table] = $schema;
        $columns = array_column($schema, 'name'); $query = $db->table($table);
        foreach (in_array('id', $columns, true) ? ['id'] : $columns as $column) { $query->orderBy($column); }
        $hash = hash_init('sha256'); $count = 0;
        foreach ($query->cursor() as $record) {
            $row = (array) $record; hash_update($hash, App\Services\M26ContentPatch::digest($row)."\n"); ++$count;
            if ($table === 'text_blocks' && in_array($row['id'] ?? null, [4243,4372], true)) { $out['rows'][] = $row; }
        }
        $out['sql_before'][$table] = ['count'=>$count, 'sha256'=>hash_final($hash)];
    }
    if ($out['sql_before'] !== $post['sql_after'] || array_column($out['rows'], 'id') !== [4243,4372]) { throw new RuntimeException('M79 current-v1 baseline no longer exact.'); }
} finally { $db->rollBack(); }
$json = json_encode($out, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n";
$h = fopen($file, 'x'); if (!$h) { throw new RuntimeException('M79 criteria capture collision.'); }
try { if (fwrite($h, $json) !== strlen($json) || !fflush($h)) { throw new RuntimeException('M79 criteria capture incomplete.'); } } finally { fclose($h); }
echo json_encode(['file'=>basename($file),'sha256'=>hash('sha256',$json),'tables'=>count($out['sql_before']),'row_ids'=>[4243,4372],'db_writes'=>0],JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR)."\n";
