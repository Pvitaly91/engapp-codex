<?php
// Explicit private M46 source-sync receipt; never queries or writes the database.
if (PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows' || count($argv) !== 1) { exit(1); }
$source = dirname(__DIR__, 2); $root = 'D:/DEV/htdocs/gramlyze.loc';
require $root.'/vendor/autoload.php';
require_once $source.'/app/Support/M46ConditionalsPackage.php';
require_once $source.'/app/Services/M46ContentPatch.php';
[, $package] = App\Support\M46ConditionalsPackage::load($source);
$files = [];
foreach (App\Services\M46ContentPatch::syncPaths($package) as $path) {
    $a = hash_file('sha256', $root.'/'.$path); $b = hash_file('sha256', $source.'/'.$path);
    $sameCode = str_ends_with($path, '.php')
        && str_replace("\r\n", "\n", file_get_contents($root.'/'.$path)) === str_replace("\r\n", "\n", file_get_contents($source.'/'.$path));
    if (!$a || !$b || ($a !== $b && !$sameCode)) { throw new RuntimeException('M46 source sync content differs: '.$path); }
    $files[] = ['path' => $path, 'root_after_sha256' => $a, 'worktree_sha256' => $b];
}
$before = $root.'/storage/app/seo-m46-local/'.App\Services\M46ContentPatch::BEFORE_FILE;
if (hash_file('sha256', $before) !== App\Services\M46ContentPatch::BEFORE_SHA) { throw new RuntimeException('M46 BEFORE changed.'); }
$receipt = ['base_sha' => App\Support\M46ConditionalsPackage::BASE_SHA, 'files' => $files,
    'visual_before' => ['file' => App\Services\M46ContentPatch::BEFORE_FILE, 'sha256' => App\Services\M46ContentPatch::BEFORE_SHA]];
$file = dirname($before).'/'.App\Services\M46ContentPatch::SYNC_RECEIPT;
$bytes = json_encode($receipt, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
$f = fopen($file, 'x');
if (!$f) { throw new RuntimeException('M46 source-sync receipt already exists; no overwrite.'); }
try { if (fwrite($f, $bytes) !== strlen($bytes) || !fflush($f)) { throw new RuntimeException('Incomplete M46 receipt.'); } }
finally { fclose($f); }
echo json_encode(['status' => 'source-sync-reviewed', 'files' => count($files), 'sha256' => hash_file('sha256', $file)], JSON_THROW_ON_ERROR)."\n";
