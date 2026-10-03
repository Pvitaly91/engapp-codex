<?php

// Independent saved SELECT evidence comparison; no .env, kernel boot or DB access.
if (PHP_SAPI !== 'cli') { exit(1); }
$source = dirname(__DIR__, 2); $dir = 'D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m32-local';
require $source.'/vendor/autoload.php';
$app = new Illuminate\Foundation\Application($source);
$read = static fn ($path) => json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
$name = $argv[1] ?? '';
if (!preg_match('/^m32-after-[a-z0-9-]+\.json$/D', $name)) { throw new RuntimeException('Invalid after evidence basename.'); }
$old = $read($dir.'/m32-bank-inventory-v1.json'); $new = $read($dir.'/'.$name);
$preview = $read($dir.'/m32-preview-v2.json');
$digest = $preview['sha256']; unset($preview['sha256']);
if (!hash_equals($digest, App\Services\M26ContentPatch::digest($preview))) { throw new RuntimeException('Saved reviewed preview digest differs.'); }
[, $package] = App\Support\M32FormalEnglishPackage::load($source);
$assert = static function ($a, $b, $why): void { if ($a !== $b) { throw new RuntimeException($why); } };
$assert($new['target'], $old['target'], 'Physical target changed.');
$assert($new['linked_banks'], $old['linked_banks'], 'Linked bank IDs changed.');
$assert($new['regressions'], $old['regressions'], 'M26-M31 owner/author payload changed.');
if (isset($new['non_target_text_blocks'])) {
    $assert($new['non_target_text_blocks'], $preview['protected']['text_blocks'], 'Any non-M32 text block changed, including M26.');
} elseif (str_starts_with($name, 'm32-after-browser-')) { throw new RuntimeException('Final non-target block fingerprint missing.'); }
foreach ($new['fingerprints'] as $table => $fp) {
    if ($table !== 'text_blocks') { $assert($fp, $old['fingerprints'][$table], 'Unrelated table changed: '.$table); }
}
$inserted = array_sum(array_map(fn ($t) => count($t['after']['page']['blocks']) - 2, $package['targets']));
$assert($new['fingerprints']['text_blocks']['count'], $old['fingerprints']['text_blocks']['count'] + $inserted, 'Unexpected block count.');
foreach ($package['targets'] as $i => $t) {
    $before = $old['targets'][$i]; $after = $new['targets'][$i];
    foreach (['identity', 'slug', 'page_id', 'category_id', 'page_sha256', 'linked_bank_groups', 'global_bank_levels', 'linked_bank_ids'] as $f) {
        $assert($after[$f], $before[$f], 'Protected owner field changed: '.$f);
    }
    $assert(count($after['blocks']), count($t['after']['page']['blocks']) + 1, 'Exact subtitle/hero/native row count differs.');
    foreach ($t['after']['page']['blocks'] as $j => $b) {
        $uuid = App\Support\M26DetailPackage::uuid($t['identity'], $b, $j + 1);
        $matches = array_values(array_filter($after['blocks'], fn ($r) => $r['uuid'] === $uuid));
        $assert(count($matches), 1, 'Missing/duplicate native UUID.');
        $r = $matches[0];
        $assert([$r['type'], $r['locale'], $r['order'], $r['body_sha256']], [$b['type'], 'uk', $j + 1, hash('sha256', $b['body'])], 'Actual DB body/type/locale/order differs.');
    }
    foreach ([0, 1] as $j) { $assert($after['blocks'][$j], $before['blocks'][$j], 'Subtitle/hero record changed.'); }
    $assert($after['blocks'][2]['id'], $before['blocks'][2]['id'], 'Original box ID changed.');
    $assert($after['blocks'][2]['uuid'], $before['blocks'][2]['uuid'], 'Original box UUID changed.');
}
echo json_encode(['pass' => true, 'evidence' => $name, 'updated' => 3, 'inserted' => $inserted, 'deleted' => 0,
    'text_blocks_before' => $old['fingerprints']['text_blocks']['count'], 'text_blocks_after' => $new['fingerprints']['text_blocks']['count'],
    'unchanged_tables' => count($new['fingerprints']) - 1, 'unchanged_regression_pages' => count($new['regressions']), 'exact_actual_db_bodies' => true], JSON_THROW_ON_ERROR)."\n";
