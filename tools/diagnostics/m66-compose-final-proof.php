<?php

/** Explicit private read-only proof composition; never an apply plan, bootstrap, HTTP or SQL. */
if (PHP_SAPI !== 'cli' || count($argv) !== 1) { throw new RuntimeException('M66 explicit final composition only.'); }
$root = 'D:/DEV/htdocs/gramlyze.loc';
if (strcasecmp(str_replace('\\', '/', (string) realpath(dirname(__DIR__, 2))), $root) !== 0) {
    throw new RuntimeException('M66 composition must use served ROOT.');
}
require $root.'/vendor/autoload.php';
$dir = $root.'/storage/app/seo-m66-local/';
$read = static function (string $name, string $sha) use ($dir): array {
    $path = $dir.$name;
    if (is_link($path) || !is_file($path) || !hash_equals($sha, hash_file('sha256', $path))) {
        throw new RuntimeException('M66 composition immutable input differs.');
    }
    return json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
};
$o = $read('preview-v2.json', '93da4a63ff7b0d6e9b1256900d3c44235f66e7bdd06c61f0c71f21e62c65895c');
$c = $read('correction-preview-v1.json', '6e683d3b46fc25636403d2208a959fd72075b4271e26e3c1a5462a283ab8ecf7');
$final = $read('final-readonly-v2.json', 'eea6f70711d0606a114b2043034e45c49d38150f4cb35ce23374c7e639d1d155');
[$before, $f, $master, $contract] = App\Support\M66VerbPatternsCorePackage::load($root);
App\Support\M66VerbPatternsCorePackage::validate($before, $f, $master, $contract);
if ($o['sha256'] !== 'f24005c56c9dd820232327e7a634cfaa0f1b0e465236f1783486a82b908d03ca'
    || $c['sha256'] !== '27a0c17432dd018bc6acf1b5201199974af49bc10c58e8695ae1e160622cd3b8'
    || $c['state'] !== 'before' || count($c['updates']) !== 1 || $c['inserts'] !== []
    || $o['connection'] !== $c['connection'] || $o['protected'] !== $c['protected']
    || ($final['all_old_rows_and_timestamps_exact'] ?? null) !== true
    || ($final['independent_before_projection_exact'] ?? null) !== true) {
    throw new RuntimeException('M66 revision chain scope differs.');
}
$change = $c['updates'][0]; $expected = $f['targets'][0]['after']['page']['blocks'][1]['body'];
if ($change['table'] !== 'text_blocks' || $change['id'] !== 5757
    || $change['uuid'] !== App\Services\M66FormulaCorrectionPatch::TARGET_UUID
    || $change['seeder'] !== App\Support\M66VerbPatternsCorePackage::OWNERS[0]
    || array_keys($change['before']) !== ['body'] || $change['after'] !== ['body' => $expected]
    || $expected !== str_replace(App\Services\M66FormulaCorrectionPatch::OLD_FORMULA,
        App\Services\M66FormulaCorrectionPatch::NEW_FORMULA, $change['before']['body'])) {
    throw new RuntimeException('M66 exact one-body revision differs.');
}
$a = $o; unset($a['sha256']); $count = 0;
foreach ($a['updates'] as &$u) {
    if ($u['table'] === 'text_blocks' && $u['id'] === 5757) {
        if ($u['after'] !== $change['before']) { throw new RuntimeException('M66 correction does not follow actual initial apply.'); }
        $u['after'] = $change['after']; $u['after_sha256'] = App\Services\M26ContentPatch::digest($u['after']); ++$count;
    }
}
unset($u);
if ($count !== 1) { throw new RuntimeException('M66 aggregate has no unique approved update.'); }
$a['patch'] = 'm66-v102-readonly-aggregate-projection-not-an-apply-plan';
$a['projection_kind'] = 'Read-only composition of original actual BEFORE preview and exact follow-up correction. NEVER APPLY this aggregate.';
$a['sources'] = $c['sources'];
foreach (['preview-v2.json', 'correction-preview-v1.json', 'final-readonly-v2.json'] as $name) {
    $a['sources']['private:'.$name] = hash_file('sha256', $dir.$name);
}
ksort($a['sources']); $a['sha256'] = App\Services\M26ContentPatch::digest($a);
$path = $dir.'aggregate-final-v102.json';
if (is_link($path) || file_exists($path)) { throw new RuntimeException('M66 composition receipt exists; no overwrite.'); }
$bytes = json_encode($a, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
$handle = fopen($path, 'x');
try {
    if (!$handle || fwrite($handle, $bytes) !== strlen($bytes) || !fflush($handle)) {
        throw new RuntimeException('M66 composition incomplete.');
    }
} finally { if (is_resource($handle)) { fclose($handle); } }
echo json_encode(['file' => basename($path), 'sha256' => hash('sha256', $bytes), 'reviewed_digest' => $a['sha256'],
    'updates' => count($a['updates']), 'inserts' => count($a['inserts']), 'correction_rows' => 1,
    'can_apply' => false, 'db_writes' => 0, 'http_requests' => 0], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR)."\n";
