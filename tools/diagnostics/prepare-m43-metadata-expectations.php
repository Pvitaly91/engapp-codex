<?php

// Independent pre-apply metadata expectations from frozen master and real guest BEFORE. No DB/HTTP/bootstrap.
if (PHP_SAPI !== 'cli' || count($argv) !== 2 || !preg_match('/^before-v[1-9][0-9]*-http\\.json$/D', $argv[1])) { exit(1); }
$source = dirname(__DIR__, 2); $directory = 'D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m43-local';
require $source.'/vendor/autoload.php';
$bytes = file_get_contents($source.'/docs/content/m43-authored-tense-usage.v1.0.0.json');
if (hash('sha256', $bytes) !== 'd9afc130ec223202ea83781147f9966855227d3194ccd7707c9b3c037b62e2f5') { throw new RuntimeException('Frozen master changed.'); }
$master = json_decode($bytes, true, flags: JSON_THROW_ON_ERROR);
$before = json_decode(file_get_contents($directory.'/'.$argv[1]), true, flags: JSON_THROW_ON_ERROR);
if ($before['pass'] !== true) { throw new RuntimeException('HTTP BEFORE incomplete.'); }
$rows = [];
foreach ($master['lessons'] as $lesson) {
    $matches = array_values(array_filter($before['rows'], fn ($r) => $r['path'] === $lesson['theory_path']));
    if (count($matches) !== 1) { throw new RuntimeException('Exact BEFORE target missing.'); }
    $old = $matches[0]; $meta = $old['meta'];
    $expected = App\Support\PageMetadata::theory($lesson['title'], 'Часи', $lesson['subtitle'], $lesson['identity'], 'uk');
    if ($expected['title'] !== $meta['title']) { throw new RuntimeException('M43 cannot change title identity.'); }
    foreach (['description', 'ogDescription', 'twitterDescription'] as $key) { $meta[$key] = $expected['description']; }
    if (count($old['jsonLd']) !== 1 || $old['jsonLd'][0]['valid'] !== true) { throw new RuntimeException('Exactly one valid JSON-LD script required.'); }
    $ld = $old['jsonLd'][0]['value']; $count = 0;
    foreach ($ld['@graph'] as &$node) {
        if (($node['@type'] ?? '') === 'LearningResource') {
            $node['description'] = Illuminate\Support\Str::limit($lesson['subtitle'], 300, '…'); $count++;
        }
    }
    unset($node);
    if ($count !== 1) { throw new RuntimeException('Exactly one learning resource expected.'); }
    $rows[] = ['path' => $lesson['theory_path'], 'before_meta' => $old['meta'], 'after_meta' => $meta,
        'after_json_ld_value' => $ld, 'x_robots' => $old['xRobots']];
}
$out = ['at' => gmdate('c'), 'master_sha256' => hash('sha256', $bytes), 'before_file' => $argv[1],
    'db_access' => false, 'http_access' => false, 'allowed_changes' => ['description', 'ogDescription', 'twitterDescription', 'LearningResource.description'], 'rows' => $rows];
$file = $directory.'/metadata-expectations-before-v1.json'; $h = fopen($file, 'x');
if (!$h) { throw new RuntimeException('Exclusive metadata expectation exists.'); }
try { $json = json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n"; if (fwrite($h, $json) !== strlen($json) || !fflush($h)) { throw new RuntimeException('Incomplete expectation.'); } } finally { fclose($h); }
echo json_encode(['file' => $file, 'sha256' => hash_file('sha256', $file), 'targets' => count($rows), 'before_apply' => true])."\n";
