<?php

// Exclusive deterministic package generation from frozen master and independently captured BEFORE.
if (PHP_SAPI !== 'cli' || !in_array($argv[1] ?? null, ['--prepare', '--apply-canonical'], true)) { exit(1); }
require_once __DIR__.'/m44-author-projection.php';
$root = 'C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc';
$private = 'D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m44-local';
if ($argv[1] === '--apply-canonical') {
    if (count($argv) !== 4 || $argv[2] !== '--sha256') { throw new RuntimeException('Reviewed immutable package SHA required.'); }
    require $root.'/vendor/autoload.php';
    [$before, $package] = App\Support\M44AuthoredFutureFormsPackage::load($root);
    if (!hash_equals(App\Support\M44AuthoredFutureFormsPackage::SOURCE_SHA, $argv[3])) { throw new RuntimeException('M44 reviewed package SHA differs.'); }
    App\Support\M44AuthoredFutureFormsPackage::validate($before, $package, $root);
    $pending = [];
    foreach ($package['targets'] as $i => $target) {
        $file = $root.'/'.$target['path']; $current = json_decode(file_get_contents($file), true, flags: JSON_THROW_ON_ERROR);
        if ($current !== $before['targets'][$i]['before'] && $current !== $target['after']) { throw new RuntimeException('M44 canonical conflict; no writes.'); }
        $pending[$file] = m44Json($target['after'], true);
    }
    foreach ($pending as $file => $bytes) {
        if (file_get_contents($file) !== $bytes && file_put_contents($file, $bytes, LOCK_EX) !== strlen($bytes)) { throw new RuntimeException('Incomplete canonical M44 source write.'); }
    }
    echo m44Json(['canonical_sources' => count($pending), 'package_sha256' => $argv[3], 'db_access' => false], true);
    exit;
}
$dbBefore = json_decode(file_get_contents($private.'/m44-before-v1.json'), true, flags: JSON_THROW_ON_ERROR);
$master = m44AuthorMaster($root); $mapping = m44NativeMapping($root);
$before = ['schema' => 'gramlyze.m44-baseline.v1', 'base_sha' => M44_BASE_SHA,
    'author_master_source' => ['path' => M44_AUTHOR_PATH, 'sha256' => M44_AUTHOR_SHA, 'bytes' => file_get_contents($root.'/'.M44_AUTHOR_PATH)],
    'native_mapping_source' => ['path' => M44_MAPPING_PATH, 'sha256' => M44_MAPPING_SHA, 'bytes' => file_get_contents($root.'/'.M44_MAPPING_PATH)], 'targets' => []];
$package = ['schema' => 'gramlyze.authored-future-forms-projection.v1', 'version' => '1.0.0', 'base_sha' => M44_BASE_SHA,
    'master_sha256' => M44_AUTHOR_SHA, 'mapping_sha256' => M44_MAPPING_SHA, 'targets' => []];
foreach ($master['lessons'] as $i => $lesson) {
    $record = $dbBefore['targets'][$i]; $bytes = file_get_contents($root.'/'.$lesson['definition_path']);
    if ($record['identity'] !== $lesson['identity'] || hash('sha256', $bytes) !== $lesson['baseline_definition_sha256']) { throw new RuntimeException('M44 fresh baseline/source differs.'); }
    $original = json_decode($bytes, true, flags: JSON_THROW_ON_ERROR);
    $git = ['git', '-c', 'safe.directory='.$root, '-c', 'core.bare=false', '-C', $root, 'rev-parse', M44_BASE_SHA.':'.$lesson['definition_path']];
    $process = proc_open($git, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
    $blob = trim(stream_get_contents($pipes[1])); $error = stream_get_contents($pipes[2]); fclose($pipes[1]); fclose($pipes[2]);
    if (proc_close($process) !== 0 || !preg_match('/^[a-f0-9]{40}$/D', $blob)) { throw new RuntimeException('M44 baseline Git blob unavailable.'); }
    $banks = array_values(array_filter($record['banks'], fn ($b) => $b['seeder_class'] === M44_OWN_BANKS[$i]));
    if (count($banks) !== 1 || !$banks[0]['full_own_bank'] || $banks[0]['linked_types'] !== ['4']) { throw new RuntimeException('M44 exact independently inventoried own bank absent.'); }
    $actual = $banks[0]; $bank = ['seeder_class' => $actual['seeder_class'], 'question_type' => '4', 'question_count' => $actual['linked_count'],
        'question_ids_sha256' => hash('sha256', m44Json($actual['linked_ids']))];
    $before['targets'][] = ['path' => $lesson['definition_path'], 'identity' => $lesson['identity'], 'source_git_blob' => $blob,
        'definition_sha256' => $lesson['baseline_definition_sha256'], 'ancestry' => $lesson['category_path'], 'before' => $original];
    $package['targets'][] = m44Target($original, $lesson, $bank, $i, $mapping['targets'][$i]);
}
$files = [M44_BEFORE_PATH => m44Json($before, true), M44_SOURCE_PATH => m44Json($package, true)];
foreach ($files as $path => $bytes) { $handle = fopen($root.'/'.$path, 'xb'); if (!$handle) { throw new RuntimeException('Do not replace a frozen M44 package.'); }
    try { if (fwrite($handle, $bytes) !== strlen($bytes) || !fflush($handle)) { throw new RuntimeException('Incomplete frozen package.'); } } finally { fclose($handle); }
}
echo m44Json(['files' => array_map(fn ($b) => hash('sha256', $b), $files), 'targets' => array_map(fn ($t) => ['identity' => $t['identity'],
    'inserts' => count($t['after']['page']['blocks']) - $t['native_count'], 'sections' => count($t['section_slots']), 'practice_slot' => $t['practice_slot']], $package['targets'])], true);
