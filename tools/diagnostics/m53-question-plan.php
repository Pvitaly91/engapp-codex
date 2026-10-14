<?php

/** Pure, explicit projection of the independently inspected Get-only insert scope. No SQL connection. */
if (PHP_SAPI !== 'cli' || count($argv) !== 1) { throw new RuntimeException('M53 question plan takes no arguments.'); }
$root = 'D:/DEV/htdocs/gramlyze.loc';
$wt = 'C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc';
if (strcasecmp(str_replace('\\', '/', realpath(dirname(__DIR__, 2))), $root) !== 0) { throw new RuntimeException('M53 question plan ROOT differs.'); }
require $root.'/vendor/autoload.php';
$dir = $root.'/storage/app/seo-m53-local';
$inventoryPath = $dir.'/question-inventory-v1.json';
if (!is_file($inventoryPath) || is_link($inventoryPath)
    || hash_file('sha256', $inventoryPath) !== App\Services\M53ContentPatch::QUESTION_INVENTORY_SHA) {
    throw new RuntimeException('M53 independent question inventory differs.');
}
$inventory = json_decode(file_get_contents($inventoryPath), true, flags: JSON_THROW_ON_ERROR);
$source = App\Support\M53QuestionSupplement::load($wt);
$patch = App\Support\M53PassiveConstructionsPackage::load($wt)[1];
$target = $patch['targets'][1];
$position = count($target['after']['page']['blocks']);
$practice = $target['after']['page']['blocks'][$position - 1];
$binding = ['owner' => App\Support\M53QuestionSupplement::OWNER, 'level' => 'B1', 'category_id' => 45,
    'source_id' => 354, 'practice_uuid' => App\Support\M26DetailPackage::uuid($target['identity'], $practice, $position)];
if (($source['scope']['practice_uuid'] ?? null) !== $binding['practice_uuid']) { throw new RuntimeException('M53 exact practice UUID differs.'); }
$projection = App\Support\M53QuestionSupplement::project($source, $binding,
    App\Services\M51ContentPatch::optionRowsFromInventory($inventory['option_matches']));
$ids = [];
foreach (App\Support\M53QuestionSupplement::ROW_COLUMNS as $table => $_) {
    $allocation = $inventory['allocation'][$table];
    $next = max($allocation['max_id'] + 1, (int) $allocation['auto_increment']['next_id']);
    $ids[$table] = [];
    foreach ($projection[$table] as $row) { $ids[$table][$row['key']] = $next++; }
}
App\Support\M51QuestionSupplement::materialize($projection, $ids, '2000-01-01 00:00:00');
$out = ['schema_version' => 1, 'base_sha' => App\Support\M53PassiveConstructionsPackage::BASE_SHA,
    'before_sha256' => App\Services\M53ContentPatch::BEFORE_SHA,
    'source_sha256' => App\Support\M53QuestionSupplement::SOURCE_SHA,
    'audit_sha256' => App\Support\M53BuilderHintPresentation::AUDIT_SHA,
    'inventory' => ['file' => 'question-inventory-v1.json', 'sha256' => App\Services\M53ContentPatch::QUESTION_INVENTORY_SHA],
    'binding' => $binding, 'projection' => $projection, 'ids' => $ids, 'taxonomy' => $inventory['taxonomy']];
$path = $dir.'/question-plan-v1.json';
if (!is_dir($dir) || is_link($dir) || file_exists($path) || is_link($path)) { throw new RuntimeException('M53 private question-plan collision.'); }
$bytes = json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
$file = fopen($path, 'x');
try { if (fwrite($file, $bytes) !== strlen($bytes) || !fflush($file)) { throw new RuntimeException('M53 incomplete question plan.'); } }
finally { fclose($file); }
echo json_encode(['status' => 'pure-question-plan', 'file' => basename($path), 'sha256' => hash('sha256', $bytes),
    'counts' => array_map('count', array_intersect_key($projection, App\Support\M53QuestionSupplement::ROW_COLUMNS)),
    'reused_options' => count($projection['reused_options']), 'binding' => $binding, 'db_writes' => 0],
    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
