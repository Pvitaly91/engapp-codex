<?php
declare(strict_types=1);

// Mechanical projection of finite, source-owned definitions only; no application/DB boot.
require dirname(__DIR__, 2).'/vendor/autoload.php';
$root = dirname(__DIR__, 2);
$mode = $argv[1] ?? '--check';
if (!in_array($mode, ['--check', '--write'], true) || count($argv) > 2) {
    throw new RuntimeException('Use --check or --write.');
}
$outputs = [];
foreach (App\Support\FinitePracticeQualityCatalogs::CATALOGS as $catalog) {
    foreach (array_keys($catalog::TOPICS) as $topic) {
        $mapping = $catalog::mapping($topic);
        foreach (['standard', 'builder'] as $kind) {
            $path = $mapping[$kind]['definition'];
            if (!str_starts_with($path, 'database/seeders/V3/') || str_contains($path, '..')
                || is_link($root.'/'.$path) || !is_file($root.'/'.$path)) {
                throw new RuntimeException('Existing finite definition path required.');
            }
            $compiled = $catalog::compile($topic, $kind);
            $existing = json_decode(file_get_contents($root.'/'.$path), true, flags: JSON_THROW_ON_ERROR);
            if ($existing['seeder']['class'] !== $compiled['seeder']['class']
                || $existing['compiled_from']['path'] !== $compiled['compiled_from']['path']) {
                throw new RuntimeException('Definition ownership differs.');
            }
            $outputs[$path] = json_encode($compiled, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
        }
    }
}
$changed = [];
foreach ($outputs as $path => $bytes) {
    if (file_get_contents($root.'/'.$path) === $bytes) { continue; }
    $changed[] = $path;
    if ($mode === '--write' && file_put_contents($root.'/'.$path, $bytes) !== strlen($bytes)) {
        throw new RuntimeException('Incomplete generated definition.');
    }
}
echo json_encode(['mode' => $mode, 'definitions' => count($outputs), 'different' => count($changed),
    'written' => $mode === '--write' ? count($changed) : 0, 'database_writes' => 0], JSON_THROW_ON_ERROR)."\n";
exit($mode === '--write' || $changed === [] ? 0 : 1);
