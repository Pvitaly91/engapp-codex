<?php

// Run deliberately after the real local DB/source inventory gate. This script
// transfers versioned files only; it never bootstraps or writes a database.
require __DIR__.'/m41-author-projection.php';
$root = dirname(__DIR__, 2); $mode = $argv[1] ?? '';
$master = m41AuthorMaster($root);
$authorBytes = file_get_contents($root.'/'.M41_AUTHOR_PATH);
if ($mode === '--audit-master') {
    echo m41Json(['master_sha256' => M41_AUTHOR_SHA, 'original_v1_reverse_sha256' => M41_ORIGINAL_AUTHOR_SHA,
        'correction_sha256' => M41_CORRECTION_SHA, 'approval_sha256' => M41_APPROVAL_SHA,
        'lessons' => count($master['lessons']), 'tasks' => array_sum(array_map(fn ($lesson) => count($lesson['practice']), $master['lessons'])),
        'controls' => array_sum(array_map(fn ($lesson) => array_sum(array_map(fn ($task) => count($task['controls']), $lesson['practice'])), $master['lessons']))], true);
    exit;
}
if ($mode === '--capture-before') {
    if (file_exists($root.'/'.M41_BEFORE_PATH)) { throw new RuntimeException('M41 exclusive before snapshot already exists.'); }
    $before = ['base_sha' => M41_BASE_SHA, 'author_master_source' => ['path' => M41_AUTHOR_PATH,
        'sha256' => M41_AUTHOR_SHA, 'bytes' => $authorBytes],
        'author_correction_source' => ['path' => M41_CORRECTION_PATH, 'sha256' => M41_CORRECTION_SHA,
            'bytes' => file_get_contents($root.'/'.M41_CORRECTION_PATH)],
        'author_approval_source' => ['path' => M41_APPROVAL_PATH, 'sha256' => M41_APPROVAL_SHA,
            'bytes' => file_get_contents($root.'/'.M41_APPROVAL_PATH)], 'targets' => []];
    foreach ($master['lessons'] as $lesson) {
        $bytes = file_get_contents($root.'/'.$lesson['definition_path']); $lf = str_replace("\r\n", "\n", $bytes);
        if (sha1('blob '.strlen($lf)."\0".$lf) !== $lesson['baseline_git_blob_sha1']) {
            throw new RuntimeException('M41 accepted baseline definition Git blob differs; stop without writes.');
        }
        $before['targets'][] = ['path' => $lesson['definition_path'], 'source_sha256' => hash('sha256', $bytes),
            'source_lf_sha256' => hash('sha256', $lf), 'source_git_blob' => $lesson['baseline_git_blob_sha1'],
            'before' => json_decode($bytes, true, flags: JSON_THROW_ON_ERROR)];
    }
    $output = m41Json($before, true); $handle = fopen($root.'/'.M41_BEFORE_PATH, 'xb');
    if ($handle === false || fwrite($handle, $output) !== strlen($output)) { throw new RuntimeException('M41 exclusive before snapshot failed.'); }
    fclose($handle); echo m41Json(['captured' => 3, 'before_sha256' => hash('sha256', $output), 'master_sha256' => M41_AUTHOR_SHA], true); exit;
}
if (!in_array($mode, ['--project', '--check'], true)) { throw new RuntimeException('Use --capture-before once; --project/--check require --linked-banks FILE --related-proof FILE.'); }
$bankArg = array_search('--linked-banks', $argv, true);
if ($bankArg === false || empty($argv[$bankArg + 1])) { throw new RuntimeException('M41 exact actual own-bank evidence is required.'); }
$evidence = json_decode(file_get_contents($argv[$bankArg + 1]), true, flags: JSON_THROW_ON_ERROR);
$banks = $evidence['linked_banks'] ?? $evidence;
if (count($banks) !== 3 || array_diff(array_keys($banks), array_column($master['lessons'], 'identity')) !== []) {
    throw new RuntimeException('M41 bank evidence contains an unknown or missing owner.');
}
$relatedArg = array_search('--related-proof', $argv, true);
if ($relatedArg === false || empty($argv[$relatedArg + 1])) { throw new RuntimeException('M41 actual local related-route verification is required.'); }
$relatedBytes = file_get_contents($argv[$relatedArg + 1]);
$relatedProof = json_decode($relatedBytes, true, flags: JSON_THROW_ON_ERROR); $verifiedRelated = [];
foreach ($relatedProof['rows'] ?? [] as $row) {
    if (($row['pass'] ?? null) !== true || ($row['path'] ?? null) !== ($row['final_path'] ?? null)
        || count($row['chain'] ?? []) !== 1 || $row['chain'][0]['url'] !== 'http://gramlyze.loc'.$row['path']
        || $row['chain'][0]['status'] !== 200 || !is_string($row['title'] ?? null) || $row['title'] === '') {
        throw new RuntimeException('M41 additional route was not accepted on the real local site without redirects.');
    }
    $verifiedRelated[$row['path']] = ['path' => $row['path'], 'h1' => $row['title'], 'http_status' => 200, 'redirects' => 0];
}
if (count($verifiedRelated) !== 4) { throw new RuntimeException('M41 exact additional author-related route scope differs.'); }
$before = json_decode(file_get_contents($root.'/'.M41_BEFORE_PATH), true, flags: JSON_THROW_ON_ERROR);
if ($before['base_sha'] !== M41_BASE_SHA || $before['author_master_source']['path'] !== M41_AUTHOR_PATH
    || $before['author_master_source']['sha256'] !== M41_AUTHOR_SHA || $before['author_master_source']['bytes'] !== $authorBytes
    || count($before['targets']) !== 3) { throw new RuntimeException('M41 immutable before/master identity differs.'); }
foreach (['author_correction_source' => [M41_CORRECTION_PATH, M41_CORRECTION_SHA],
    'author_approval_source' => [M41_APPROVAL_PATH, M41_APPROVAL_SHA]] as $field => [$path, $sha]) {
    if ($before[$field]['path'] !== $path || $before[$field]['sha256'] !== $sha
        || $before[$field]['bytes'] !== file_get_contents($root.'/'.$path)) {
        throw new RuntimeException('M41 immutable author handoff provenance differs.');
    }
}
$package = ['schema' => 'gramlyze.authored-tense-comparisons-projection.v1', 'version' => '1.0.1',
    'master_sha256' => M41_AUTHOR_SHA, 'related_routes' => ['evidence_sha256' => hash('sha256', $relatedBytes), 'verified' => $verifiedRelated],
    'targets' => []]; $files = []; $states = [];
foreach ($master['lessons'] as $i => $lesson) {
    $record = $before['targets'][$i]; $original = $record['before'];
    if ($record['path'] !== $lesson['definition_path'] || $record['source_git_blob'] !== $lesson['baseline_git_blob_sha1']) {
        throw new RuntimeException('M41 before owner/baseline identity differs.');
    }
    $target = m41Target($original, $lesson, $banks[$lesson['identity']], $i, $verifiedRelated);
    $current = json_decode(file_get_contents($root.'/'.$record['path']), true, flags: JSON_THROW_ON_ERROR);
    if ($current !== $original && $current !== $target['after']) { throw new RuntimeException('M41 refuses a manual canonical-source conflict.'); }
    $states[] = $current === $original ? 'before' : 'after';
    $package['targets'][] = $target; $files[$record['path']] = m41Json($target['after'], true);
}
$bytes = m41Json($package, true);
if ($mode === '--project') {
    if ($states !== ['before', 'before', 'before'] || file_exists($root.'/'.M41_SOURCE_PATH)) {
        throw new RuntimeException('M41 projection requires three exact before states and a fresh exclusive snapshot.');
    }
    $handle = fopen($root.'/'.M41_SOURCE_PATH, 'xb');
    if ($handle === false || fwrite($handle, $bytes) !== strlen($bytes)) { throw new RuntimeException('M41 exclusive projection snapshot failed.'); }
    fclose($handle);
    foreach ($files as $path => $output) {
        if (file_put_contents($root.'/'.$path, $output) !== strlen($output)) { throw new RuntimeException('M41 canonical transfer incomplete.'); }
    }
} elseif (!is_file($root.'/'.M41_SOURCE_PATH) || file_get_contents($root.'/'.M41_SOURCE_PATH) !== $bytes) {
    throw new RuntimeException('M41 immutable projection differs from deterministic master/native/bank reconstruction.');
}
echo m41Json(['mode' => $mode, 'states' => $states, 'master_sha256' => M41_AUTHOR_SHA,
    'before_sha256' => hash_file('sha256', $root.'/'.M41_BEFORE_PATH), 'source_sha256' => hash('sha256', $bytes),
    'definition_sha256' => array_map(fn ($output) => hash('sha256', $output), $files),
    'blocks' => array_map(fn ($target) => count($target['after']['page']['blocks']), $package['targets']),
    'details' => array_map(fn ($target) => count(array_filter($target['detail_quality_audit'], fn ($point) => $point['decision'] === 'point_detail')), $package['targets'])], true);
