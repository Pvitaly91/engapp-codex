<?php
declare(strict_types=1);

// File-only, exclusive pre-edit checkpoint; never boots the application or touches a DB.
require dirname(__DIR__, 2).'/vendor/autoload.php';
$root = dirname(__DIR__, 2);
$directory = $root.'/storage/app/finite-builder-word-slots';
if (!is_dir($directory)) { mkdir($directory, 0700, true); }
$banks = [];
foreach (App\Support\FinitePracticeQualityCatalogs::CATALOGS as $class) {
    foreach (array_keys($class::TOPICS) as $topic) {
        $mapping = $class::mapping($topic);
        $definition = $class::compile($topic, 'builder');
        $banks[] = ['catalog' => $class, 'topic' => $topic, 'mapping' => $mapping,
            'definition' => $definition,
            'definition_bytes' => file_get_contents($root.'/'.$mapping['builder']['definition']),
            'source_bytes' => file_get_contents($root.'/'.$mapping['author']),
            'standard_bytes' => file_get_contents($root.'/'.$mapping['standard']['definition'])];
    }
}
$path = $directory.'/baseline.json';
$handle = fopen($path, 'x');
if (!$handle) { throw new RuntimeException('Baseline already exists; refusing overwrite.'); }
$bytes = json_encode(['schema_version' => 1, 'banks' => $banks], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
if (fwrite($handle, $bytes) !== strlen($bytes)) { throw new RuntimeException('Incomplete checkpoint.'); }
fclose($handle);
echo json_encode(['banks' => count($banks), 'sha256' => hash_file('sha256', $path), 'database_writes' => 0])."\n";
