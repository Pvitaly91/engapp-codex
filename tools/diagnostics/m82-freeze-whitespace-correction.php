<?php

/** Exclusive, filesystem-only successor proposal. It does not materialize sources or touch SQL/HTTP. */
if (PHP_SAPI !== 'cli' || count($argv) !== 1) { throw new RuntimeException('M82 whitespace freeze takes no ambient input.'); }
$root = 'D:/DEV/htdocs/gramlyze.loc'; $wt = $root.'/storage/app/m75-worktree'; $dir = $root.'/storage/app/seo-m82-local';
require $root.'/vendor/autoload.php';
require_once $wt.'/app/Support/TheoryNativeContentRelease.php';
require_once $wt.'/app/Support/M82CommonMistakesLevelRevision.php';
require_once $wt.'/app/Support/M82WhitespaceRevision.php';
use App\Support\M82CommonMistakesLevelRevision as Package;
use App\Support\M82WhitespaceRevision as Revision;
use App\Support\TheoryNativeContentRelease;
$read = static function (string $path, string $sha): string {
    if (is_link($path) || !is_file($path) || !hash_equals($sha, hash_file('sha256', $path))) { throw new RuntimeException('M82 whitespace frozen predecessor drift.'); }
    $bytes = file_get_contents($path);
    if (!hash_equals($sha, hash('sha256', $bytes))) { throw new RuntimeException('M82 whitespace predecessor changed while reading.'); }
    return $bytes;
};
$json = static fn ($v): string => json_encode($v, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
$decode = static fn ($s): array => json_decode($s, true, flags: JSON_THROW_ON_ERROR);
$freeze = $decode($read($dir.'/freeze-plan-v2.json', Revision::FREEZE_SHA));
$baseline = $decode($read($dir.'/'.Revision::BASELINE, Revision::BASELINE_SHA));
if (($freeze['revision'] ?? null) !== '1.0.1' || count($freeze['outputs'] ?? []) !== 18
    || ($baseline['all_old_rows_and_timestamps_exact'] ?? null) !== true || count($baseline['sql_after'] ?? []) !== 46
    || ($baseline['db_writes'] ?? null) !== 0 || ($baseline['http_requests'] ?? null) !== 0) {
    throw new RuntimeException('M82 whitespace needs exact applied 1.0.1 lineage and independent 46-table baseline.');
}
foreach ($freeze['outputs'] as $path => $bytes) {
    if (is_link($wt.'/'.$path) || !is_file($wt.'/'.$path) || file_get_contents($wt.'/'.$path) !== $bytes) {
        throw new RuntimeException('M82 whitespace candidate must begin at exact 1.0.1 frozen source: '.$path);
    }
}
$previousSource = $decode($read($wt.'/'.Revision::PREVIOUS_SOURCE, Revision::PREVIOUS_SOURCE_SHA));
$previousMaster = $decode($read($wt.'/'.Revision::PREVIOUS_MASTER, Revision::PREVIOUS_MASTER_SHA));
$checks = $decode($read($wt.'/'.Revision::PREVIOUS_CHECKSUMS, Revision::PREVIOUS_CHECKSUMS_SHA));
$before = $decode($read($wt.'/'.Package::BEFORE, Package::BEFORE_SHA));
$bindings = $decode($read($wt.'/'.Package::BINDINGS, Package::BINDINGS_SHA));
$profile = Package::draftProfile($bindings['bindings']); $profile['version'] = Revision::VERSION;
$release = new TheoryNativeContentRelease($profile);
$master = Revision::reviseMaster($previousMaster);
$source = Revision::revisedPatch($previousSource, $before, $master, $release);
$contract = $decode($read($wt.'/'.Package::CONTRACT, Package::CONTRACT_SHA));
$release->validate($before, $source, $master, $contract); Package::assertEditorial($before, $source, $master);
$outputs = [Revision::SOURCE => $json($source), Revision::MASTER => $json($master)];
$componentsOld = 'docs/content/m82-common-mistakes-level-revision.components.v1.json';
$componentsNew = 'docs/content/m82-common-mistakes-level-revision.components.v1.0.2.json';
$components = $decode($freeze['outputs'][$componentsOld]);
foreach ($components as &$component) {
    if ($component['identity'] === Package::OWNERS[1] && isset(Revision::TARGETS[$component['source_index']])) {
        $component['body_sha256'] = hash('sha256', $source['targets'][1]['after']['page']['blocks'][$component['source_index']]['body']);
    }
}
unset($component); $outputs[$componentsNew] = $json($components);
$editorialOld = 'docs/content/m82-common-mistakes-level-revision.editorial.v1.json';
$editorialNew = 'docs/content/m82-common-mistakes-level-revision.editorial.v1.0.2.json';
$editorial = $decode($freeze['outputs'][$editorialOld]); $editorial['version'] = Revision::VERSION;
$editorial['whitespace_revision'] = $master['editorial_review']['whitespace_revision'];
$outputs[$editorialNew] = $json($editorial);
$coverageOld = 'docs/content/m82-common-mistakes-level-revision.coverage.v1.json';
$coverageNew = 'docs/content/m82-common-mistakes-level-revision.coverage.v1.0.2.json';
$coverage = $decode($freeze['outputs'][$coverageOld]); $coverage['version'] = Revision::VERSION;
$outputs[$coverageNew] = $json($coverage);
$fixtureOld = 'docs/content/m82-semantic-fixtures.v1.json';
$fixtureSha = '20172800e3accc10abea102b168d99b11c0ea7ab8ebbf10ccbefd4b9d6d3b3be';
$fixture = $decode($read($wt.'/'.$fixtureOld, $fixtureSha));
foreach ($fixture['teaching_literals'] as &$literal) {
    if ($literal['path'] === [1, 'blocks', 2, 'data', 'sections']) {
        $literal['value'] = $master['lessons'][1]['blocks'][2]['data']['sections'];
    }
}
unset($literal);
$fixture['whitespace_revision'] = ['version' => Revision::VERSION, 'predecessor_fixture_sha256' => $fixtureSha, 'changes' => Revision::changes()];
$outputs[Revision::FIXTURE] = $json($fixture);
$markdownOld = 'docs/content/m82-common-mistakes-level-revision.v1.0.1.uk.md';
$markdownNew = 'docs/content/m82-common-mistakes-level-revision.v1.0.2.uk.md';
$markdown = $freeze['outputs'][$markdownOld];
foreach (Revision::changes() as $change) {
    $old = $previousMaster['lessons'][1]['blocks']; $new = $master['lessons'][1]['blocks'];
    foreach ($change['path'] as $key) { $old = $old[$key]; $new = $new[$key]; }
    if (substr_count($markdown, $old) !== 1) { throw new RuntimeException('M82 whitespace readable predecessor literal differs.'); }
    $markdown = str_replace($old, $new, $markdown);
}
if (substr_count($markdown, 'Авторська редакція 1.0.1.') !== 1) { throw new RuntimeException('M82 whitespace readable revision differs.'); }
$outputs[$markdownNew] = str_replace('Авторська редакція 1.0.1.', 'Авторська редакція 1.0.2.', $markdown);
$bPath = $source['targets'][1]['path']; $bBytes = $json($source['targets'][1]['after']);
$manifest = ['schema_version' => 1, 'version' => Revision::VERSION, 'status' => 'frozen', 'package' => 'm82-common-mistakes-level-revision',
    'predecessor' => ['source' => Revision::PREVIOUS_SOURCE, 'source_sha256' => Revision::PREVIOUS_SOURCE_SHA,
        'master' => Revision::PREVIOUS_MASTER, 'master_sha256' => Revision::PREVIOUS_MASTER_SHA,
        'checksums' => Revision::PREVIOUS_CHECKSUMS, 'checksums_sha256' => Revision::PREVIOUS_CHECKSUMS_SHA,
        'private_freeze_basename' => 'freeze-plan-v2.json', 'private_freeze_sha256' => Revision::FREEZE_SHA,
        'independent_post_apply_basename' => Revision::BASELINE, 'independent_post_apply_sha256' => Revision::BASELINE_SHA],
    'scope' => $master['editorial_review']['whitespace_revision']['scope'], 'changes' => Revision::changes(),
    'owner' => Package::OWNERS[1], 'page_id' => 40, 'category_id' => 9, 'rows' => [],
    'sql_updates' => 2, 'sql_inserts' => 0, 'question_writes' => 0, 'visual_changes' => 0,
    'snapshot_policy' => '1.0.1 artifacts remain immutable; only active successor pointers and B generated definition move to 1.0.2.'];
foreach (Revision::TARGETS as $slot => $identity) {
    $manifest['rows'][] = ['source_index' => $slot] + $identity + [
        'before_body_sha256' => hash('sha256', $previousSource['targets'][1]['after']['page']['blocks'][$slot]['body']),
        'after_body_sha256' => hash('sha256', $source['targets'][1]['after']['page']['blocks'][$slot]['body'])];
}
$outputs[Revision::MANIFEST] = $json($manifest);
$repaths = [Package::SOURCE => Revision::SOURCE, Package::MASTER => Revision::MASTER,
    $componentsOld => $componentsNew, $editorialOld => $editorialNew, $coverageOld => $coverageNew, $markdownOld => $markdownNew];
$checks['version'] = Revision::VERSION;
foreach ($checks['artifacts'] as &$record) {
    $record['path'] = $repaths[$record['path']] ?? $record['path'];
    if (isset($outputs[$record['path']])) { $record['sha256'] = hash('sha256', $outputs[$record['path']]); $record['bytes'] = strlen($outputs[$record['path']]); }
}
unset($record);
$checks['artifacts'][] = ['path' => Revision::MANIFEST, 'sha256' => hash('sha256', $outputs[Revision::MANIFEST]), 'bytes' => strlen($outputs[Revision::MANIFEST])];
foreach ($checks['active_definitions'] as &$record) {
    if ($record['path'] === $bPath) { $record['sha256'] = hash('sha256', $bBytes); $record['bytes'] = strlen($bBytes); }
}
unset($record);
foreach ($checks['author_inputs'] as &$record) {
    if ($record['path'] === $fixtureOld) { $record = ['path' => Revision::FIXTURE, 'sha256' => hash('sha256', $outputs[Revision::FIXTURE]), 'bytes' => strlen($outputs[Revision::FIXTURE])]; }
}
unset($record);
$checks['whitespace_revision'] = ['manifest' => Revision::MANIFEST, 'predecessor_checksums_sha256' => Revision::PREVIOUS_CHECKSUMS_SHA];
$outputs[Revision::CHECKSUMS] = $json($checks);
foreach ($outputs as $path => $bytes) {
    if (file_exists($wt.'/'.$path) || is_link($wt.'/'.$path) || str_contains($bytes, "\r") || str_starts_with($bytes, "\xEF\xBB\xBF")
        || !str_ends_with($bytes, "\n") || str_ends_with($bytes, "\n\n") || preg_match('/[\t ]+$/m', $bytes)) {
        throw new RuntimeException('M82 whitespace new versioned path or UTF8/LF policy differs: '.$path);
    }
}
$plan = ['schema_version' => 1, 'revision' => Revision::VERSION, 'predecessor_private_plan_sha256' => Revision::FREEZE_SHA,
    'independent_after_sha256' => Revision::BASELINE_SHA, 'outputs' => $outputs,
    'definition_update' => ['path' => $bPath, 'before_sha256' => hash('sha256', $freeze['outputs'][$bPath]),
        'before_bytes' => $freeze['outputs'][$bPath], 'after_sha256' => hash('sha256', $bBytes), 'after_bytes' => $bBytes],
    'next_package' => ['version' => Revision::VERSION, 'SOURCE' => Revision::SOURCE, 'SOURCE_SHA' => hash('sha256', $outputs[Revision::SOURCE]),
        'MASTER' => Revision::MASTER, 'MASTER_SHA' => hash('sha256', $outputs[Revision::MASTER]),
        'CHECKSUMS' => Revision::CHECKSUMS, 'CHECKSUMS_SHA' => hash('sha256', $outputs[Revision::CHECKSUMS]),
        'semantic_fixture' => Revision::FIXTURE, 'semantic_fixture_sha256' => hash('sha256', $outputs[Revision::FIXTURE])],
    'db_connections' => 0, 'db_writes' => 0, 'http_requests' => 0, 'active_source_writes' => 0];
$file = $dir.'/freeze-whitespace-correction-v1.json';
if (file_exists($file) || is_link($file)) { throw new RuntimeException('M82 whitespace exclusive private proposal already exists.'); }
$bytes = $json($plan); $h = fopen($file, 'x');
if (!$h) { throw new RuntimeException('M82 whitespace exclusive private proposal unavailable.'); }
try { if (fwrite($h, $bytes) !== strlen($bytes) || !fflush($h)) { throw new RuntimeException('M82 whitespace proposal incomplete.'); } } finally { fclose($h); }
echo $json(['file' => basename($file), 'sha256' => hash('sha256', $bytes), 'new_versioned_artifacts' => count($outputs),
    'definition_updates' => 1, 'planned_body_updates' => 2, 'literal_slash_lists' => 5, 'next_package' => $plan['next_package'],
    'db_connections' => 0, 'db_writes' => 0, 'http_requests' => 0, 'active_source_writes' => 0]);
