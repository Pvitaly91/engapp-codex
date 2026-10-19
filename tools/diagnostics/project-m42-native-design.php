<?php

// Pure source-bound style metadata generation; no Laravel or database bootstrap.
require dirname(__DIR__, 2).'/vendor/autoload.php';
$root = dirname(__DIR__, 2);
$mapping = App\Support\M42NativeDesignPackage::build($root);
$bytes = App\Support\M42NativeDesignPackage::bytes($mapping);
$path = $root.'/'.App\Support\M42NativeDesignPackage::SOURCE;
if (($argv[1] ?? '--check') === '--write-new') {
    $handle = fopen($path, 'x');
    if ($handle === false) { throw new RuntimeException('M42 mapping exists; no overwrite.'); }
    fwrite($handle, $bytes); fclose($handle);
} elseif (($argv[1] ?? '') === '--replace-draft-sha') {
    if (($argv[2] ?? null) !== hash_file('sha256', $path)) { throw new RuntimeException('M42 unaccepted draft SHA differs; no replacement.'); }
    file_put_contents($path, $bytes);
} elseif (!is_file($path) || file_get_contents($path) !== $bytes) {
    throw new RuntimeException('M42 native mapping differs from the finite semantic source decisions.');
}
echo json_encode(['targets' => count($mapping['targets']), 'blocks' => array_sum(array_map(fn ($t) => count($t['blocks']), $mapping['targets'])),
    'sha256' => hash('sha256', $bytes)], JSON_THROW_ON_ERROR)."\n";
