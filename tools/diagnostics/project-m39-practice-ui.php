<?php

// Deterministic mechanical presentation generation from the frozen accepted M39
// V1 snapshot only. No Laravel/DB boot, ROOT sync, seeds or runtime hooks.
require __DIR__.'/m39-practice-ui-projection.php';

$root = dirname(__DIR__, 2);
$masterBytes = file_get_contents($root.'/docs/content/m23-authored-content.v1.json');
$masterLf = str_replace("\r\n", "\n", $masterBytes);
$notesBytes = file_get_contents($root.'/docs/content/m23-author-sources.md');
$notesLf = str_replace("\r\n", "\n", $notesBytes);
if (hash('sha256', $masterLf) !== 'eae0e632ae528e3500eeb40188d4b61d275703bfe722cd2fa9fe495f1d2b1ff6'
    || hash('sha1', 'blob '.strlen($masterLf)."\0".$masterLf) !== '34a03a7146141fbff50c66ec8e41f3fc2a59b787'
    || hash('sha1', 'blob '.strlen($notesLf)."\0".$notesLf) !== '859c4263cb00c0ff318bf2b41f3e450ca65efc0d') {
    throw new RuntimeException('M39 UI immutable author source differs; no writes.');
}
$sourcePath = 'database/content-patches/m39-m23-authored-revision.v1.json';
$sourceSha = 'bbfe17773121f6d13cda6beda3d15b5285c54fe73360c605045080e8beddb817';
$outputPath = 'database/content-patches/m39-practice-ui.v1.json';
$mode = $argv[1] ?? '--check';
$previous = null;
if ($mode === '--replace-projection-sha') {
    $previousBytes = file_get_contents($root.'/'.$outputPath);
    if (!isset($argv[2]) || !hash_equals($argv[2], hash('sha256', $previousBytes))) {
        throw new RuntimeException('M39 UI prior draft snapshot hash differs; no writes.');
    }
    $previous = json_decode($previousBytes, true, flags: JSON_THROW_ON_ERROR);
    if (($previous['patch'] ?? null) !== 'm39-practice-ui-quality-v1' || count($previous['targets'] ?? []) !== 3) {
        throw new RuntimeException('M39 UI prior draft identity differs.');
    }
}
$sourceBytes = file_get_contents($root.'/'.$sourcePath);
if (hash('sha256', $sourceBytes) !== $sourceSha) { throw new RuntimeException('Frozen M39 V1 source differs; no writes.'); }
$source = json_decode($sourceBytes, true, flags: JSON_THROW_ON_ERROR);
if (count($source['targets']) !== 3) { throw new RuntimeException('M39 UI source owner scope differs.'); }
$identities = ['Database\\Seeders\\Page_V3\\FormalEnglish\\NominalStyleAndInformationDensityTheorySeeder',
    'Database\\Seeders\\Page_V3\\BasicGrammar\\C1MixedRevisionTheorySeeder',
    'Database\\Seeders\\Page_V3\\BasicGrammar\\C2MixedRevisionTheorySeeder'];
if (array_column($source['targets'], 'identity') !== $identities) { throw new RuntimeException('M39 UI frozen owner mapping differs.'); }

$snapshot = ['version' => 1, 'patch' => 'm39-practice-ui-quality-v1', 'targets' => []];
$currentStates = []; $files = []; $manualCount = 0; $controlCount = 0;
foreach ($source['targets'] as $owner => $target) {
    $before = $target['after'];
    $indices = array_keys(array_filter($before['page']['blocks'], fn ($block) => $block['type'] === 'practice-set'));
    if ($indices !== [7]) { throw new RuntimeException('M39 UI practice block position differs.'); }
    $body = json_decode($before['page']['blocks'][7]['body'], true, flags: JSON_THROW_ON_ERROR);
    $projected = m39PracticeUiProjection($body, $owner);
    foreach ($projected['cases'] as $case) {
        foreach ($case['controls'] as $control) {
            $controlCount++; if ($control['kind'] === 'manual') { $manualCount++; }
            if (isset($control['prompt']) || isset($control['author_explanation']) || isset($case['prompt']) || isset($case['author_explanation'])) {
                throw new RuntimeException('M39 UI cases must not duplicate prompts or explanations.');
            }
        }
    }
    $after = $before;
    $after['page']['blocks'][7]['body'] = json_encode($projected, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    $restored = $after; $restored['page']['blocks'][7]['body'] = $before['page']['blocks'][7]['body'];
    if ($restored !== $before) { throw new RuntimeException('M39 UI changes fields beyond the practice body.'); }
    $current = json_decode(file_get_contents($root.'/'.$target['path']), true, flags: JSON_THROW_ON_ERROR);
    if ($previous !== null && ($previous['targets'][$owner]['before'] !== $before
        || $previous['targets'][$owner]['identity'] !== $target['identity'] || $previous['targets'][$owner]['path'] !== $target['path'])) {
        throw new RuntimeException('M39 UI prior draft/source owner differs.');
    }
    if ($current !== $before && $current !== $after && $current !== ($previous['targets'][$owner]['after'] ?? null)) {
        throw new RuntimeException('M39 UI refuses to overwrite a changed canonical definition.');
    }
    $currentStates[] = $previous !== null && $current === $previous['targets'][$owner]['after'] ? 'previous' : ($current === $before ? 'before' : 'after');
    $snapshot['targets'][] = ['identity' => $target['identity'], 'path' => $target['path'], 'before' => $before, 'after' => $after];
    $files[$target['path']] = json_encode($after, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
}
if ($manualCount !== 17 || $controlCount !== 23) { throw new RuntimeException('M39 UI finite control mapping differs.'); }
$snapshotBytes = json_encode($snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
if (!in_array($mode, ['--check', '--write', '--replace-projection-sha'], true)) { throw new RuntimeException('Use --check, one exclusive --write, or an exact known draft hash replacement.'); }

if ($mode === '--write') {
    if ($currentStates !== ['before', 'before', 'before'] || file_exists($root.'/'.$outputPath)) {
        throw new RuntimeException('M39 UI generation requires all exact before states and a new exclusive snapshot.');
    }
    // All three targets have been validated before creating any output.
    $file = fopen($root.'/'.$outputPath, 'xb');
    if ($file === false || fwrite($file, $snapshotBytes) !== strlen($snapshotBytes)) {
        if (is_resource($file)) { fclose($file); }
        throw new RuntimeException('M39 UI exclusive snapshot creation failed.');
    }
    fclose($file);
    foreach ($files as $path => $bytes) {
        if (file_put_contents($root.'/'.$path, $bytes) !== strlen($bytes)) { throw new RuntimeException('M39 UI canonical write incomplete: '.$path); }
    }
} elseif ($mode === '--replace-projection-sha') {
    if ($currentStates !== ['previous', 'previous', 'previous']) {
        throw new RuntimeException('M39 UI draft replacement requires all exact old draft after states.');
    }
    if (file_put_contents($root.'/'.$outputPath, $snapshotBytes) !== strlen($snapshotBytes)) { throw new RuntimeException('M39 UI draft snapshot replacement incomplete.'); }
    foreach ($files as $path => $bytes) {
        if (file_put_contents($root.'/'.$path, $bytes) !== strlen($bytes)) { throw new RuntimeException('M39 UI draft canonical write incomplete: '.$path); }
    }
}
$summary = ['mode' => $mode, 'states' => $currentStates, 'snapshot_path' => $outputPath,
    'snapshot_sha256' => hash('sha256', $snapshotBytes), 'cases' => 18, 'controls' => $controlCount, 'manual_controls' => $manualCount,
    'master_raw_sha256' => hash('sha256', $masterBytes), 'notes_raw_sha256' => hash('sha256', $notesBytes),
    'definition_sha256' => array_map(fn ($bytes) => hash('sha256', $bytes), $files)];
echo json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
