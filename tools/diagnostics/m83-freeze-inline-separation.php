<?php

/** Pure, exclusive M83 1.0.1 successor proposal; never materializes sources or accesses SQL/HTTP. */
if (PHP_SAPI !== 'cli' || count($argv) !== 1) {
    throw new RuntimeException('M83 inline separation freeze accepts no ambient input.');
}
$root = 'D:/DEV/htdocs/gramlyze.loc';
$wt = $root.'/storage/app/m75-worktree';
$dir = $root.'/storage/app/seo-m83-local';
require $root.'/vendor/autoload.php';
require_once $wt.'/app/Support/TheoryNativeContentRelease.php';
require_once $wt.'/app/Support/M83B2RevisionBasicGrammar.php';

use App\Support\M83B2RevisionBasicGrammar as Package;
use App\Support\TheoryInlineHtml;
use App\Support\TheoryNativeContentRelease;

$freezeSha = '1fbc218ee227ad1eaa4eb695cce0ea2e4a1783c7550e04b9d5b9832d9fc9460e';
$baselineFile = 'final-readonly-v2.json';
$baselineSha = 'c5d643f519d26b19081113a1400920b168d9e7f02fb975c0419d9cfcaf277b62';
$oldSource = 'database/content-patches/m83-b2-revision-basic-grammar.v1.json';
$oldMaster = 'docs/content/m83-b2-revision-basic-grammar.v1.json';
$oldChecks = 'docs/content/m83-source-checksums.v1.json';
$oldComponents = 'docs/content/m83-b2-revision-basic-grammar.components.v1.json';
$oldMarkdown = 'docs/content/m83-b2-revision-basic-grammar.v1.0.0.uk.md';
$nextSource = 'database/content-patches/m83-b2-revision-basic-grammar.v1.0.1.json';
$nextMaster = 'docs/content/m83-b2-revision-basic-grammar.v1.0.1.json';
$nextChecks = 'docs/content/m83-source-checksums.v1.0.1.json';
$nextComponents = 'docs/content/m83-b2-revision-basic-grammar.components.v1.0.1.json';
$nextMarkdown = 'docs/content/m83-b2-revision-basic-grammar.v1.0.1.uk.md';
$manifestPath = 'docs/content/m83-inline-separation.v1.0.1.json';
$sourceSha = 'b88875f26454dd2a6e6be1b81ca2ccc8dfbdfef1731231b87c325bf311a05cbc';
$masterSha = '94fce3e749a17ada2979a7a0f08c44a337b78b74a540de719dbff6cff4ab0669';
$checksSha = 'cddc5b76cf3f65286014f1a4a3a51b764d565ada1c68ae94c2684595a8eb2ce5';
$nativeSha = '23b89050f061faddd80debce2fefc8dd356a0b49dffa44f79b2200cb5cbbef11';
$inlineSha = 'ffbb0efc6df54322e8b1d2452419798f6897d905df370fc004bd834f1934bef2';
$inlineWtSha = '9fe6a771a040d15508c15fbc1842946c4ccf1b5b0d8be2c010fd993d0204f50e';
$json = static fn ($value): string => json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
$decode = static fn (string $bytes): array => json_decode($bytes, true, flags: JSON_THROW_ON_ERROR);
$read = static function (string $path, string $sha): string {
    if (!is_file($path) || is_link($path) || !hash_equals($sha, hash_file('sha256', $path))) {
        throw new RuntimeException('M83 inline separation pinned input differs: '.$path);
    }
    $bytes = file_get_contents($path);
    if (!is_string($bytes) || !hash_equals($sha, hash('sha256', $bytes))) {
        throw new RuntimeException('M83 inline separation input changed while reading: '.$path);
    }
    return $bytes;
};
if (!is_dir($dir) || is_link($dir) || is_link($wt) || Package::VERSION !== '1.0.0'
    || [Package::SOURCE, Package::MASTER, Package::CHECKSUMS] !== [$oldSource, $oldMaster, $oldChecks]) {
    throw new RuntimeException('M83 proposal requires the exact unpublished native 1.0.0 predecessor.');
}
$freeze = $decode($read($dir.'/freeze-plan-v1.json', $freezeSha));
$baseline = $decode($read($dir.'/'.$baselineFile, $baselineSha));
if (($freeze['revision'] ?? null) !== '1.0.0' || count($freeze['outputs'] ?? []) !== 19
    || ($baseline['all_old_rows_and_timestamps_exact'] ?? null) !== true
    || ($baseline['independent_before_projection_exact'] ?? null) !== true
    || ($baseline['reviewed_protected_projection_exact'] ?? null) !== true
    || ($baseline['independent_before_sha256'] ?? null) !== 'd522a715b123748b84f52f076b156ccf5224892f138d5429062d9527a843e33e'
    || ($baseline['update_operations'] ?? null) !== 83 || ($baseline['question_family_writes'] ?? null) !== 0
    || ($baseline['sql_tables'] ?? null) !== 46 || count($baseline['sql_after'] ?? []) !== 46
    || count($baseline['new_practices'] ?? []) !== 1 || ($baseline['new_question_families'] ?? null) !== []
    || ($baseline['db_writes'] ?? null) !== 0 || ($baseline['http_requests'] ?? null) !== 0) {
    throw new RuntimeException('M83 inline separation lacks exact applied six-owner/46-table predecessor evidence.');
}
foreach ($freeze['outputs'] as $path => $bytes) {
    foreach ([$root, $wt] as $context) {
        if (!is_file($context.'/'.$path) || is_link($context.'/'.$path) || file_get_contents($context.'/'.$path) !== $bytes) {
            throw new RuntimeException('M83 original frozen artifact/definition changed: '.$context.'/'.$path);
        }
    }
}
foreach ([$root, $wt] as $context) {
    $read($context.'/app/Support/TheoryNativeContentRelease.php', $nativeSha);
    $read($context.'/app/Support/TheoryInlineHtml.php', $context === $root ? $inlineSha : $inlineWtSha);
}
if (str_replace("\r\n", "\n", file_get_contents($root.'/app/Support/TheoryInlineHtml.php'))
    !== str_replace("\r\n", "\n", file_get_contents($wt.'/app/Support/TheoryInlineHtml.php'))) {
    throw new RuntimeException('M83 unchanged native inline parser contexts differ beyond historical PHP EOL.');
}
$previousSource = $decode($read($wt.'/'.$oldSource, $sourceSha));
$previousMaster = $decode($read($wt.'/'.$oldMaster, $masterSha));
$checks = $decode($read($wt.'/'.$oldChecks, $checksSha));
foreach (['artifacts', 'active_definitions', 'author_inputs'] as $group) {
    foreach ($checks[$group] as $record) {
        foreach ([$root, $wt] as $context) { $read($context.'/'.$record['path'], $record['sha256']); }
    }
}
[$before, $loadedSource, $loadedMaster, $contract, $loadedChecks, $bindings, $navigation] = Package::load($wt);
if ($loadedSource !== $previousSource || $loadedMaster !== $previousMaster
    || ($loadedMaster['version'] ?? null) !== '1.0.0' || array_key_exists('author_revision', $loadedMaster)) {
    throw new RuntimeException('M83 exact active predecessor or revision marker differs.');
}
Package::validate($before, $previousSource, $previousMaster, $contract);
$release = new TheoryNativeContentRelease(Package::draftProfile($bindings['bindings']));
$master = $previousMaster;
$counts = [2, 1, 1, 1, 1, 1]; $changes = []; $total = 0;
$items = $master['lessons'][4]['blocks'][1]['data']['items'] ?? null;
if (!is_array($items) || count($items) !== 6) { throw new RuntimeException('M83 six exact imperative function cards differ.'); }
foreach ($counts as $i => $expectedCount) {
    $old = $items[$i]['subtitle'] ?? null;
    if (!is_string($old) || str_contains($old, '<br>') || substr_count($old, '</p><p>') !== $expectedCount
        || substr_count($old, '<span lang="en">') !== $expectedCount + 1) {
        throw new RuntimeException('M83 exact inline paragraph boundary differs: '.$i);
    }
    $new = str_replace('</p><p>', '</p><br><p>', $old, $actualCount);
    $oldInline = (string) TheoryInlineHtml::render($old);
    $newInline = (string) TheoryInlineHtml::render($new);
    if ($actualCount !== $expectedCount || strlen($new) - strlen($old) !== 4 * $expectedCount
        || strip_tags($new) !== strip_tags($old) || substr_count($newInline, '<br>') !== $expectedCount
        || str_replace('<br>', '', $newInline) !== $oldInline) {
        throw new RuntimeException('M83 supported inline rendering changed beyond exact separators: '.$i);
    }
    $master['lessons'][4]['blocks'][1]['data']['items'][$i]['subtitle'] = $new;
    $changes[] = ['pointer' => '/lessons/4/blocks/1/data/items/'.$i.'/subtitle', 'item_index' => $i,
        'before' => $old, 'after' => $new, 'before_sha256' => hash('sha256', $old), 'after_sha256' => hash('sha256', $new),
        'added_br' => $actualCount, 'added_utf8_bytes' => strlen($new) - strlen($old),
        'learning_text_exact' => true, 'before_native_inline_html' => $oldInline, 'after_native_inline_html' => $newInline];
    $total += $actualCount;
}
$master['author_revision'] = '1.0.1';
$source = $previousSource;
$source['targets'][4]['after'] = $release->definition($master['lessons'][4], $before['targets'][4]['before']);
$oldBody = $previousSource['targets'][4]['after']['page']['blocks'][1]['body'];
$newBody = $source['targets'][4]['after']['page']['blocks'][1]['body'];
$restoredSource = $source;
$restoredSource['targets'][4]['after']['page']['blocks'][1]['body'] = $oldBody;
$restoredMaster = $master; unset($restoredMaster['author_revision']);
foreach ($changes as $change) { $restoredMaster['lessons'][4]['blocks'][1]['data']['items'][$change['item_index']]['subtitle'] = $change['before']; }
if ($total !== 7 || $restoredSource !== $previousSource || $restoredMaster !== $previousMaster
    || strlen($oldBody) !== 1736 || strlen($newBody) !== 1764 || strip_tags($oldBody) !== strip_tags($newBody)
    || hash('sha256', $oldBody) !== '40bcc789a1e51293a8f8eb4077c30c0d30ff00501b813cc268e34d17976e70c1'
    || hash('sha256', $newBody) !== '12adb7840627d0c7e5aaa5c6b6d29395d8bd221e3d56f8adf1e79aa42b0f100c') {
    throw new RuntimeException('M83 seven-boundary/one-body exact transition differs.');
}
$release->validate($before, $source, $master, $contract);
Package::assertEditorial($before, $source, $master, $navigation);
App\Support\M83BuilderSelectionPresentation::validateAudit($decode($freeze['outputs'][App\Support\M83BuilderSelectionPresentation::AUDIT]), $source, $contract);
$components = $decode($freeze['outputs'][$oldComponents]); $changedComponents = 0;
foreach ($components as &$component) {
    if ($component['identity'] === Package::OWNERS[4] && $component['source_index'] === 1) {
        if ($component['body_sha256'] !== hash('sha256', $oldBody) || $component['type'] !== 'forms-grid'
            || $component['level'] !== null || $component['column'] !== 'left' || $component['detail_count'] !== 0) {
            throw new RuntimeException('M83 exact E component pin differs.');
        }
        $component['body_sha256'] = hash('sha256', $newBody); $changedComponents++;
    }
}
unset($component);
if ($changedComponents !== 1) { throw new RuntimeException('M83 component transition is not exactly one record.'); }
$markdown = $freeze['outputs'][$oldMarkdown];
if (substr_count($markdown, 'Авторська редакція1.0.0:') !== 1) { throw new RuntimeException('M83 readable author revision differs.'); }
$markdown = str_replace('Авторська редакція1.0.0:', 'Авторська редакція1.0.1:', $markdown);
$ePath = $source['targets'][4]['path']; $eBefore = $freeze['outputs'][$ePath]; $eAfter = $json($source['targets'][4]['after']);
$manifest = ['schema_version' => 1, 'package' => 'm83-b2-revision-basic-grammar', 'status' => 'frozen',
    'native_version' => '1.0.0', 'author_revision' => '1.0.1', 'kind' => 'finite data-only inline separation correction',
    'predecessor' => ['source' => $oldSource, 'source_sha256' => $sourceSha, 'master' => $oldMaster, 'master_sha256' => $masterSha,
        'checksums' => $oldChecks, 'checksums_sha256' => $checksSha, 'private_freeze_basename' => 'freeze-plan-v1.json',
        'private_freeze_sha256' => $freezeSha, 'independent_post_apply_basename' => $baselineFile, 'independent_post_apply_sha256' => $baselineSha],
    'successor' => ['source' => $nextSource, 'master' => $nextMaster, 'checksums' => $nextChecks, 'components' => $nextComponents,
        'readable' => $nextMarkdown, 'contract' => Package::CONTRACT, 'contract_sha256' => Package::CONTRACT_SHA],
    'owner' => Package::OWNERS[4], 'page_id' => 21, 'category_id' => 2, 'source_index' => 1,
    'row' => ['id' => 1153, 'uuid' => '2c280a48-644e-5876-8435-bf975022255b', 'locale' => 'uk', 'sort_order' => 2,
        'type' => 'forms-grid', 'column' => 'left', 'heading' => null, 'css_class' => null, 'level' => null],
    'before_body_sha256' => hash('sha256', $oldBody), 'after_body_sha256' => hash('sha256', $newBody),
    'before_body_bytes' => strlen($oldBody), 'after_body_bytes' => strlen($newBody), 'changes' => $changes,
    'added_br' => 7, 'added_body_utf8_bytes' => 28, 'bilingual_pairs' => 13, 'learning_text_exact' => true,
    'sql_updates' => 1, 'sql_inserts' => 0, 'question_family_writes' => 0, 'visual_source_changes' => 0,
    'renderer_changes' => 0, 'formatter_changes' => 0, 'native_protocol_changes' => 0,
    'unchanged' => ['native_version_and_markers', 'contract', 'native_bindings', 'widget_audit', 'task_map', 'editorial', 'coverage',
        'all_60_own_graded_controls', 'five_demos', '30_widget_questions', 'all_other_71_native_block_bodies', 'all_metadata_and_timestamps'],
    'author_input_policy' => 'Original v1 author parts remain immutable. This canonical exact manifest supersedes only the six listed E subtitle fields.',
    'acceptance_policy' => 'Source-only proposal; fresh proof, one-body preview/backup/apply, no-op, independent postconditions and actual browser acceptance remain required.'];
$outputs = [$nextSource => $json($source), $nextMaster => $json($master), $nextComponents => $json($components),
    $nextMarkdown => $markdown, $manifestPath => $json($manifest)];
$repaths = [$oldSource => $nextSource, $oldMaster => $nextMaster, $oldComponents => $nextComponents, $oldMarkdown => $nextMarkdown];
foreach ($checks['artifacts'] as &$record) {
    $record['path'] = $repaths[$record['path']] ?? $record['path'];
    if (isset($outputs[$record['path']])) { $record['sha256'] = hash('sha256', $outputs[$record['path']]); $record['bytes'] = strlen($outputs[$record['path']]); }
}
unset($record);
$checks['artifacts'][] = ['path' => $manifestPath, 'sha256' => hash('sha256', $outputs[$manifestPath]), 'bytes' => strlen($outputs[$manifestPath])];
$checks['author_revision'] = '1.0.1';
$checks['inline_separation_revision'] = ['manifest' => $manifestPath, 'predecessor_checksums_sha256' => $checksSha];
foreach ($checks['active_definitions'] as &$record) {
    if ($record['path'] === $ePath) { $record['sha256'] = hash('sha256', $eAfter); $record['bytes'] = strlen($eAfter); }
}
unset($record);
$outputs[$nextChecks] = $json($checks);
foreach ($outputs + [$ePath => $eAfter] as $path => $bytes) {
    if (!mb_check_encoding($bytes, 'UTF-8') || str_contains($bytes, "\r") || str_starts_with($bytes, "\xEF\xBB\xBF")
        || !str_ends_with($bytes, "\n") || str_ends_with($bytes, "\n\n") || preg_match('/[\t ]+$/m', $bytes)) {
        throw new RuntimeException('M83 inline separation UTF8/LF/single-EOF policy differs: '.$path);
    }
    if (str_ends_with($path, '.json')) { $decode($bytes); }
    if ($path !== $ePath) {
        foreach ([$root, $wt] as $context) {
            if (file_exists($context.'/'.$path) || is_link($context.'/'.$path)) { throw new RuntimeException('M83 successor path already exists: '.$path); }
        }
    }
}
$nextPackage = ['VERSION' => '1.0.0', 'author_revision' => '1.0.1',
    'SOURCE' => $nextSource, 'SOURCE_SHA' => hash('sha256', $outputs[$nextSource]),
    'MASTER' => $nextMaster, 'MASTER_SHA' => hash('sha256', $outputs[$nextMaster]),
    'CHECKSUMS' => $nextChecks, 'CHECKSUMS_SHA' => hash('sha256', $outputs[$nextChecks])];
$replacements = [];
foreach (['SOURCE', 'SOURCE_SHA', 'MASTER', 'MASTER_SHA', 'CHECKSUMS', 'CHECKSUMS_SHA'] as $constant) {
    $old = ['SOURCE' => $oldSource, 'SOURCE_SHA' => $sourceSha, 'MASTER' => $oldMaster, 'MASTER_SHA' => $masterSha,
        'CHECKSUMS' => $oldChecks, 'CHECKSUMS_SHA' => $checksSha][$constant];
    $replacements[] = ['path' => 'app/Support/M83B2RevisionBasicGrammar.php',
        'before' => "public const ".$constant." = '".$old."';", 'after' => "public const ".$constant." = '".$nextPackage[$constant]."';"];
}
$plan = ['schema_version' => 1, 'native_version' => '1.0.0', 'author_revision' => '1.0.1',
    'kind' => 'pure proposed exact successor; not materialized or SQL applied',
    'predecessor_private_plan_sha256' => $freezeSha, 'independent_after_file' => $baselineFile, 'independent_after_sha256' => $baselineSha,
    'outputs' => $outputs, 'definition_update' => ['path' => $ePath, 'before_sha256' => hash('sha256', $eBefore), 'before_bytes' => $eBefore,
        'after_sha256' => hash('sha256', $eAfter), 'after_bytes' => $eAfter],
    'next_package' => $nextPackage, 'literal_constant_replacements' => $replacements,
    'manifest_path' => $manifestPath, 'manifest_sha256' => hash('sha256', $outputs[$manifestPath]),
    'component_path' => $nextComponents, 'component_sha256' => hash('sha256', $outputs[$nextComponents]),
    'immutable_predecessor_outputs' => array_map(static fn ($bytes) => hash('sha256', $bytes), $freeze['outputs']),
    'proof' => ['six_field_changes' => 6, 'added_br' => 7, 'added_body_utf8_bytes' => 28, 'bilingual_pairs' => 13,
        'learning_text_exact' => true, 'all_other_source_fields_exact' => true, 'all_native_practice_and_widget_bindings_exact' => true],
    'db_connections' => 0, 'db_writes' => 0, 'http_requests' => 0, 'active_source_writes' => 0];
if (count($outputs) !== 6 || $checks['version'] !== '1.0.0') { throw new RuntimeException('M83 exact six successor artifacts/native version differs.'); }
$file = $dir.'/revision-plan-v1.0.1.json'; $bytes = $json($plan);
if (file_exists($file) || is_link($file)) { throw new RuntimeException('M83 exclusive revision proposal already exists.'); }
$handle = fopen($file, 'x');
if (!$handle) { throw new RuntimeException('M83 exclusive revision proposal unavailable.'); }
try {
    if (fwrite($handle, $bytes) !== strlen($bytes) || !fflush($handle)) { throw new RuntimeException('M83 revision proposal incomplete.'); }
} finally { fclose($handle); }
echo $json(['file' => basename($file), 'sha256' => hash('sha256', $bytes), 'bytes' => strlen($bytes),
    'new_public_outputs' => count($outputs), 'definition_updates' => 1, 'planned_body_updates' => 1,
    'manifest_path' => $manifestPath, 'manifest_sha256' => $plan['manifest_sha256'], 'next_package' => $nextPackage,
    'proof' => $plan['proof'], 'db_connections' => 0, 'db_writes' => 0, 'http_requests' => 0, 'active_source_writes' => 0]);
