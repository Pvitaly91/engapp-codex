<?php

/** Pure author/source projection and pre-apply metadata review; no database queries or source writes. */
if (PHP_SAPI !== 'cli' || count($argv) !== 2 || preg_match('/^author-projection-v[1-9][0-9]*\.json$/D', $argv[1]) !== 1) {
    throw new RuntimeException('Explicit private M53 author projection basename required.');
}
$root = 'D:/DEV/htdocs/gramlyze.loc'; $wt = 'C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc';
if (strcasecmp(str_replace('\\', '/', realpath(dirname(__DIR__, 2))), $root) !== 0) { throw new RuntimeException('Projection ROOT differs.'); }
require $root.'/vendor/autoload.php';
$beforePath = $root.'/storage/app/seo-m53-local/before-v1.json';
if (hash_file('sha256', $beforePath) !== '62612fa65e7ad3da6540de2e023b249727fc56db8a14033a92ef53e878785a33') {
    throw new RuntimeException('Independent M53 source/DB/HTTP capture changed.');
}
$before = json_decode(file_get_contents($beforePath), true, flags: JSON_THROW_ON_ERROR);
$master = json_decode(file_get_contents($wt.'/docs/content/m53-passive-constructions.v1.0.0.json'), true, flags: JSON_THROW_ON_ERROR);
// Frozen author fields are validated independently against their supported projection.
$contract = json_decode(file_get_contents($wt.'/docs/content/theory-sentence-builders/m53-passive-constructions.v1.json'), true, flags: JSON_THROW_ON_ERROR);
$base = ['schema_version' => 1, 'base_sha' => App\Support\M53PassiveConstructionsPackage::BASE_SHA];
$publicBefore = $base + ['content_type' => 'M53_source_before', 'targets' => []];
$patch = $base + ['content_type' => 'M53_Passive_Constructions', 'status' => 'frozen', 'targets' => []];
$metadata = []; $componentMap = []; $maxBody = 0;
foreach ($master['lessons'] as $i => $lesson) {
    $owner = $lesson['identity']; $old = $before['definitions'][$owner]; $state = $before['pages'][$owner];
    $after = App\Support\M53PassiveConstructionsPackage::definition($lesson, $old['definition']);
    $publicBefore['targets'][] = ['identity' => $owner, 'path' => $old['path'], 'before' => $old['definition']];
    $patch['targets'][] = ['identity' => $owner, 'slug' => $lesson['slug'], 'path' => $old['path'],
        'ancestry' => array_column($state['category_ancestry'], 'slug'),
        'local_url' => $state['target_route']['url'],
        'page_binding' => ['id' => $state['page']['id'], 'page_category_id' => $state['page']['page_category_id']],
        'block_keys' => array_column($lesson['blocks'], 'key'), 'after' => $after];
    $hero = json_decode($after['page']['blocks'][0]['body'], true, flags: JSON_THROW_ON_ERROR);
    $metadata[] = ['identity' => $owner, 'raw_page_title' => $state['page']['title'],
        'h1' => App\Support\M53PassiveConstructionsPackage::RENDERED_TITLES[$i],
        'expected_seo' => App\Support\PageMetadata::theory($state['page']['title'], $state['category_ancestry'][0]['title'],
            $hero['intro'], $owner, 'uk')];
    foreach ($after['page']['blocks'] as $n => $block) {
        App\Services\PracticeCompletionPatch::assertBodyCapacity($block['body'], (int) $before['body_capacity']['max_bytes']);
        $maxBody = max($maxBody, strlen($block['body']));
        $componentMap[] = ['identity' => $owner, 'source_index' => $n, 'key' => $lesson['blocks'][$n]['key'],
            'type' => $block['type'], 'level' => $block['level'] ?? null, 'body_sha256' => hash('sha256', $block['body']),
            'uuid' => App\Support\M26DetailPackage::uuid($owner, $block, $n + 1),
            'native_view' => App\Support\TheoryPresentation::nativeView($block['type']),
            'detail_owners' => array_values(array_filter($lesson['details'], static fn ($d) => $d['owner_block_key'] === $lesson['blocks'][$n]['key']))];
    }
}
App\Support\M53PassiveConstructionsPackage::validate($publicBefore, $patch, $master, $contract);
$out = ['schema_version' => 1, 'base_sha' => $base['base_sha'], 'source_before' => $publicBefore, 'patch' => $patch,
    'pre_apply_metadata' => $metadata, 'component_map' => $componentMap, 'largest_body_bytes' => $maxBody,
    'body_capacity_bytes' => (int) $before['body_capacity']['max_bytes'], 'db_writes' => 0];
$bytes = json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
$file = $root.'/storage/app/seo-m53-local/'.$argv[1];
if (is_link($file) || file_exists($file)) { throw new RuntimeException('Private author projection exists; no overwrite.'); }
$handle = fopen($file, 'x');
try { if (fwrite($handle, $bytes) !== strlen($bytes) || !fflush($handle)) { throw new RuntimeException('Incomplete private author projection.'); } }
finally { fclose($handle); }
echo json_encode(['status' => 'pure-author-projection', 'file' => $argv[1], 'sha256' => hash('sha256', $bytes),
    'largest_body_bytes' => $maxBody, 'pre_apply_metadata' => $metadata, 'db_writes' => 0],
    JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
