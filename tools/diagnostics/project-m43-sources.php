<?php

// Deliberate versioned-file transfer only; never bootstraps Laravel or a DB.
require __DIR__.'/m43-author-projection.php';
$root = dirname(__DIR__, 2); $mode = $argv[1] ?? '';
$master = m43AuthorMaster($root); $mapping = m43NativeMapping($root);
$authorBytes = file_get_contents($root.'/'.M43_AUTHOR_PATH);
$mappingBytes = file_get_contents($root.'/'.M43_MAPPING_PATH);
if ($mode === '--audit-master') {
    echo m43Json(['master_sha256' => M43_AUTHOR_SHA, 'mapping_sha256' => M43_MAPPING_SHA,
        'lessons' => count($master['lessons']), 'tasks' => array_sum(array_map(fn ($l) => count($l['practice']), $master['lessons'])),
        'controls' => array_sum(array_map(fn ($l) => array_sum(array_map(fn ($t) => count($t['controls']), $l['practice'])), $master['lessons']))], true);
    exit;
}
if ($mode === '--capture-before') {
    if (file_exists($root.'/'.M43_BEFORE_PATH)) { throw new RuntimeException('M43 exclusive before snapshot already exists.'); }
    $before = ['base_sha' => M43_BASE_SHA, 'author_master_source' => ['path' => M43_AUTHOR_PATH, 'sha256' => M43_AUTHOR_SHA, 'bytes' => $authorBytes],
        'native_mapping_source' => ['path' => M43_MAPPING_PATH, 'sha256' => M43_MAPPING_SHA, 'bytes' => $mappingBytes], 'targets' => []];
    foreach ($master['lessons'] as $i => $lesson) {
        $bytes = file_get_contents($root.'/'.$lesson['definition_path']); $lf = str_replace("\r\n", "\n", $bytes);
        if (sha1('blob '.strlen($lf)."\0".$lf) !== M43_BASE_BLOBS[$i]) {
            throw new RuntimeException('M43 accepted baseline definition Git blob differs; no writes.');
        }
        $before['targets'][] = ['path' => $lesson['definition_path'], 'source_sha256' => hash('sha256', $bytes),
            'source_lf_sha256' => hash('sha256', $lf), 'source_git_blob' => M43_BASE_BLOBS[$i],
            'before' => json_decode($bytes, true, flags: JSON_THROW_ON_ERROR)];
    }
    $output = m43Json($before, true); $handle = fopen($root.'/'.M43_BEFORE_PATH, 'xb');
    if ($handle === false || fwrite($handle, $output) !== strlen($output)) { throw new RuntimeException('M43 exclusive before snapshot failed.'); }
    fclose($handle); echo m43Json(['captured' => 3, 'before_sha256' => hash('sha256', $output)], true); exit;
}
if (!in_array($mode, ['--project', '--check'], true)) { throw new RuntimeException('Use --audit-master, --capture-before, --project or --check --linked-banks FILE.'); }
$arg = array_search('--linked-banks', $argv, true);
if ($arg === false || empty($argv[$arg + 1])) { throw new RuntimeException('M43 actual local before-bank inventory is required.'); }
$evidence = json_decode(file_get_contents($argv[$arg + 1]), true, flags: JSON_THROW_ON_ERROR);
if (count($evidence['targets'] ?? []) !== 3 || array_column($evidence['targets'], 'identity') !== array_column($master['lessons'], 'identity')) {
    throw new RuntimeException('M43 actual bank evidence owner scope differs.');
}
$before = json_decode(file_get_contents($root.'/'.M43_BEFORE_PATH), true, flags: JSON_THROW_ON_ERROR);
if ($before['base_sha'] !== M43_BASE_SHA || count($before['targets']) !== 3) { throw new RuntimeException('M43 before scope differs.'); }
foreach (['author_master_source' => [M43_AUTHOR_PATH, M43_AUTHOR_SHA, $authorBytes], 'native_mapping_source' => [M43_MAPPING_PATH, M43_MAPPING_SHA, $mappingBytes]] as $key => [$path, $sha, $bytes]) {
    if ($before[$key] !== ['path' => $path, 'sha256' => $sha, 'bytes' => $bytes]) { throw new RuntimeException('M43 frozen handoff evidence differs.'); }
}
$package = ['schema' => 'gramlyze.authored-tense-usage-projection.v1', 'version' => '1.0.0', 'base_sha' => M43_BASE_SHA,
    'master_sha256' => M43_AUTHOR_SHA, 'mapping_sha256' => M43_MAPPING_SHA, 'targets' => []];
$states = []; $files = [];
foreach ($master['lessons'] as $i => $lesson) {
    $record = $before['targets'][$i]; $actual = $evidence['targets'][$i];
    if ($record['path'] !== $lesson['definition_path'] || $record['source_git_blob'] !== M43_BASE_BLOBS[$i]
        || $actual['path'] !== $record['path'] || ($actual['source_db_exact'] ?? null) !== true
        || $actual['definition_sha256'] !== hash('sha256', m43Json($record['before']))) {
        throw new RuntimeException('M43 baseline/actual target evidence differs.');
    }
    $matches = array_values(array_filter($actual['banks'], fn ($b) => $b['seeder_class'] === M43_OWN_BANKS[$i]));
    if (count($matches) !== 1) { throw new RuntimeException('M43 exact own-bank absent or ambiguous.'); }
    $b = $matches[0];
    if ($b['full_own_bank'] !== true || $b['linked_count'] !== 72 || $b['global_count'] !== 72
        || $b['linked_ids'] !== $b['global_ids'] || count(array_unique($b['linked_ids'])) !== 72
        || array_map('strval', $b['linked_types']) !== ['4']) { throw new RuntimeException('M43 actual complete own-bank differs.'); }
    $bank = ['seeder_class' => $b['seeder_class'], 'question_type' => '4', 'levels' => $b['linked_levels'],
        'question_count' => 72, 'question_ids_sha256' => hash('sha256', m43Json($b['linked_ids']))];
    $target = m43Target($record['before'], $lesson, $bank, $i, $mapping['targets'][$i]);
    $current = json_decode(file_get_contents($root.'/'.$record['path']), true, flags: JSON_THROW_ON_ERROR);
    if ($current !== $record['before'] && $current !== $target['after']) { throw new RuntimeException('M43 canonical source conflict; no writes.'); }
    $states[] = $current === $record['before'] ? 'before' : 'after';
    $package['targets'][] = $target; $files[$record['path']] = m43Json($target['after'], true);
}
$bytes = m43Json($package, true);
if ($mode === '--project') {
    if ($states !== ['before', 'before', 'before'] || file_exists($root.'/'.M43_SOURCE_PATH)) { throw new RuntimeException('M43 projection requires fresh exact before sources.'); }
    $handle = fopen($root.'/'.M43_SOURCE_PATH, 'xb');
    if ($handle === false || fwrite($handle, $bytes) !== strlen($bytes)) { throw new RuntimeException('M43 exclusive package snapshot failed.'); }
    fclose($handle);
    foreach ($files as $path => $output) {
        if (file_put_contents($root.'/'.$path, $output) !== strlen($output)) { throw new RuntimeException('M43 canonical file transfer incomplete.'); }
    }
} elseif ($states !== ['after', 'after', 'after'] || !is_file($root.'/'.M43_SOURCE_PATH) || file_get_contents($root.'/'.M43_SOURCE_PATH) !== $bytes) {
    throw new RuntimeException('M43 immutable package/canonical sources differ from deterministic reconstruction.');
}
echo m43Json(['mode' => $mode, 'states' => $states, 'before_sha256' => hash_file('sha256', $root.'/'.M43_BEFORE_PATH),
    'source_sha256' => hash('sha256', $bytes), 'definition_sha256' => array_map(fn ($output) => hash('sha256', $output), $files),
    'blocks' => array_map(fn ($t) => count($t['after']['page']['blocks']), $package['targets']),
    'details' => array_map(fn ($t) => count(array_filter($t['detail_quality_audit'], fn ($p) => $p['decision'] === 'point_detail')), $package['targets'])], true);
