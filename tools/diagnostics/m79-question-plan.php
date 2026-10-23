<?php

/** Pure projection review; private exclusive plan only, no application/SQL/HTTP. */
if (PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows' || count($argv) !== 1) { throw new RuntimeException('M79 pure fixed question plan takes no arguments.'); }
$root = 'D:/DEV/htdocs/gramlyze.loc'; $wt = $root.'/storage/app/m75-worktree'; $dir = $root.'/storage/app/seo-m79-local';
require $root.'/vendor/autoload.php'; require_once $wt.'/app/Support/M79QuestionSupplement.php'; require_once $wt.'/app/Services/M79QuestionInsertScope.php'; require_once $wt.'/app/Services/M79ContentPatch.php';
use App\Support\M79QuestionSupplement as Supplement;
use App\Support\M51QuestionSupplement as Rows;
use App\Services\M79ContentPatch as Patch;
$name = Patch::QUESTION_INVENTORY_FILE; $sha = Patch::QUESTION_INVENTORY_SHA;
if (preg_match('/^[a-f0-9]{64}$/D', $sha) !== 1 || is_link($dir) || !is_dir($dir) || is_link($dir.'/'.$name) || !is_file($dir.'/'.$name)
    || !hash_equals($sha, hash_file('sha256', $dir.'/'.$name))) { throw new RuntimeException('M79 exact independent inventory bytes are pending or changed.'); }
$i = json_decode(file_get_contents($dir.'/'.$name), true, flags: JSON_THROW_ON_ERROR); $source = Supplement::load($wt);
$projection = Supplement::project($source, Supplement::bindings(), $i['option_matches']); $rows = Rows::materialize($projection, $i['ids'], $i['row_timestamp']);
if ($projection !== $i['projection'] || $rows !== $i['materialized'] || $i['source_sha256'] !== Supplement::SOURCE_SHA || $i['base_sha'] !== Supplement::BASE_SHA
    || $i['db_writes'] !== 0 || $i['http_requests'] !== 0 || count($rows['questions']) !== 8 || count($rows['question_theory_text_blocks']) !== 8
    || array_count_values(array_column($rows['questions'], 'seeder')) !== array_combine(Supplement::BANKS, [2, 3, 3])) { throw new RuntimeException('M79 pure eight-question projection differs from independent inventory.'); }
$out = ['schema_version' => 1, 'kind' => 'M79_question_insert_plan', 'base_sha' => $i['base_sha'], 'before_sha256' => $i['before_sha256'],
    'source_sha256' => Supplement::SOURCE_SHA, 'inventory' => ['file' => $name, 'sha256' => $sha], 'bindings' => $i['bindings'],
    'projection' => $projection, 'ids' => $i['ids'], 'row_timestamp' => $i['row_timestamp'], 'materialized' => $rows, 'taxonomy' => $i['taxonomy']];
$json = json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
$file = $dir.'/'.Patch::QUESTION_PLAN_FILE;
if (file_exists($file) || is_link($file)) { throw new RuntimeException('M79 private question plan already exists.'); }
$handle = fopen($file, 'x'); if ($handle === false) { throw new RuntimeException('M79 exclusive plan creation failed.'); }
try { if (fwrite($handle, $json) !== strlen($json) || !fflush($handle)) { throw new RuntimeException('M79 private plan write incomplete.'); } } finally { fclose($handle); }
echo json_encode(['file' => Patch::QUESTION_PLAN_FILE, 'sha256' => hash('sha256', $json), 'new_rows' => array_sum(array_map('count', $rows)),
    'family_counts' => array_map('count', $rows), 'db_connections' => 0, 'db_writes' => 0, 'http_requests' => 0], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR)."\n";
