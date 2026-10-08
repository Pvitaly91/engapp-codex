<?php

// Independent source-derived expectations BEFORE apply; no kernel, HTTP or DB.
if (PHP_SAPI !== 'cli' || count($argv) !== 1) { exit(1); }
$root = dirname(__DIR__, 2); $directory = 'D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m44-local';
require $root.'/vendor/autoload.php';
$masterBytes = file_get_contents($root.'/docs/content/m44-authored-future-forms.v1.0.0.json');
if (hash('sha256', $masterBytes) !== App\Support\M44AuthoredFutureFormsPackage::MASTER_SHA) { throw new RuntimeException('Frozen master changed.'); }
$master = json_decode($masterBytes, true, flags: JSON_THROW_ON_ERROR);
$http = json_decode(file_get_contents($directory.'/http-before-v1/manifest.json'), true, flags: JSON_THROW_ON_ERROR);
$inventory = json_decode(file_get_contents($directory.'/m44-before-v1.json'), true, flags: JSON_THROW_ON_ERROR);
$rows = [];
foreach ($master['lessons'] as $i => $lesson) {
    $found = array_values(array_filter($http['rows'], fn ($r) => $r['route'] === $lesson['theory_path']));
    if (count($found) !== 1 || $found[0]['status'] !== 200 || isset($found[0]['error'])) { throw new RuntimeException('Exact target BEFORE missing.'); }
    $old = $found[0]; $meta = $old['metadata'];
    $category = $inventory['targets'][$i]['category_chain'];
    $expected = App\Support\PageMetadata::theory($lesson['title'], end($category)['title'], $lesson['subtitle'], $lesson['identity'], 'uk');
    if ($meta['title'] !== $expected['title']) { throw new RuntimeException('M44 must preserve title identity.'); }
    foreach (['description', 'ogDescription', 'twitterDescription'] as $key) { $meta[$key] = $expected['description']; }
    if (count($old['jsonLd']) !== 1) { throw new RuntimeException('One JSON-LD document expected.'); }
    $jsonLd = $old['jsonLd'][0]; $count = 0;
    foreach ($jsonLd['@graph'] as &$node) if (($node['@type'] ?? null) === 'LearningResource') {
        $node['description'] = Illuminate\Support\Str::limit($lesson['subtitle'], 300, '…'); $count++;
    } unset($node);
    if ($count !== 1) { throw new RuntimeException('One LearningResource expected.'); }
    $rows[] = ['path' => $lesson['theory_path'], 'before_metadata' => $old['metadata'], 'after_metadata' => $meta,
        'after_json_ld' => $jsonLd, 'x_robots_tag' => $old['xRobotsTag']];
}
$result = ['at' => gmdate('c'), 'master_sha256' => hash('sha256', $masterBytes), 'before_http' => 'http-before-v1/manifest.json',
    'allowed_changes' => ['description', 'ogDescription', 'twitterDescription', 'LearningResource.description'], 'rows' => $rows,
    'db_access' => false, 'http_access' => false, 'generated_from_after' => false];
$handle = fopen($directory.'/metadata-expectations-before-v1.json', 'xb');
try { $bytes = json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
    if (!$handle || fwrite($handle, $bytes) !== strlen($bytes) || !fflush($handle)) { throw new RuntimeException('Exclusive metadata evidence failed.'); }
} finally { if ($handle) { fclose($handle); } }
echo json_encode(['file' => 'metadata-expectations-before-v1.json', 'sha256' => hash('sha256', $bytes), 'targets' => 3, 'before_apply' => true])."\n";
