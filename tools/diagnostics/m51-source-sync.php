<?php

/** Filesystem-only exclusive receipt after main's reviewed, exact-hunk source transfer. No DB/HTTP/bootstrap. */
if (PHP_SAPI !== 'cli' || count($argv) !== 1) { throw new RuntimeException('Explicit M51 source sync receipt only.'); }
$root = 'D:/DEV/htdocs/gramlyze.loc';
$wt = 'C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc';
$normal = static fn ($p) => strtolower(str_replace('\\', '/', (string) realpath($p)));
if ($normal(dirname(__DIR__, 2)) !== $normal($root)) { throw new RuntimeException('M51 receipt must inspect served ROOT.'); }
require $root.'/vendor/autoload.php';
[$beforeSource, $patch, $master] = App\Support\M51ModalMeaningsPackage::load($root);
App\Support\M51ModalMeaningsPackage::validate($beforeSource, $patch, $master);
App\Support\M51QuestionSupplement::load($root);
$private = $root.'/storage/app/seo-m51-local';
$beforeFile = $private.'/'.App\Services\M51ContentPatch::BEFORE_FILE;
if (hash_file('sha256', $beforeFile) !== App\Services\M51ContentPatch::BEFORE_SHA) { throw new RuntimeException('M51 independent BEFORE differs.'); }
$before = json_decode(file_get_contents($beforeFile), true, flags: JSON_THROW_ON_ERROR);
$files = [];
foreach (App\Services\M51ContentPatch::syncPaths($patch) as $path) {
    foreach ([$root, $wt] as $dir) { if (!is_file($dir.'/'.$path) || is_link($dir.'/'.$path)) { throw new RuntimeException('M51 source missing/linked: '.$path); } }
    $a = file_get_contents($root.'/'.$path); $b = file_get_contents($wt.'/'.$path);
    if ($path === App\Services\M51ContentPatch::CONTROLLER_PATH) {
        foreach (['root' => $a, 'worktree' => $b] as $copy => $after) {
            $original = $private.'/controller-before-v1-'.$copy.'.bin';
            $sha = $copy === 'root' ? App\Services\M51ContentPatch::CONTROLLER_BEFORE_SHA : App\Services\M51ContentPatch::CONTROLLER_WT_BEFORE_SHA;
            if (hash_file('sha256', $original) !== $sha || $after !== App\Services\M51ContentPatch::approvedControllerAfter(file_get_contents($original))) { throw new RuntimeException('M51 controller transfer changed foreign bytes.'); }
        }
    } elseif ($a !== $b && !(str_ends_with($path, '.php') && str_replace("\r\n", "\n", $a) === str_replace("\r\n", "\n", $b))) { throw new RuntimeException('M51 finite source mirror differs: '.$path); }
    $files[] = ['path' => $path, 'root_after_sha256' => hash('sha256', $a), 'worktree_sha256' => hash('sha256', $b)];
}
foreach ([$before['visual_hashes'], $before['frozen_hashes']] as $hashes) {
    foreach ($hashes as $path => $sha) { if (hash_file('sha256', $root.'/'.$path) !== $sha) { throw new RuntimeException('M51 protected visual/frozen source differs: '.$path); } }
}
$receipt = ['base_sha' => App\Support\M51ModalMeaningsPackage::BASE_SHA, 'source_worktree' => $wt, 'served_root' => $root,
    'visual_before' => ['file' => App\Services\M51ContentPatch::BEFORE_FILE, 'sha256' => App\Services\M51ContentPatch::BEFORE_SHA],
    'files' => $files, 'created_at' => gmdate('c'), 'db_writes' => 0, 'http_requests' => 0];
$bytes = json_encode($receipt, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
$path = $private.'/'.App\Services\M51ContentPatch::SYNC_RECEIPT;
if (file_exists($path) || is_link($path)) { throw new RuntimeException('M51 source sync receipt already exists; no overwrite.'); }
$handle = fopen($path, 'x');
if (!$handle) { throw new RuntimeException('M51 receipt cannot be created exclusively.'); }
try { if (fwrite($handle, $bytes) !== strlen($bytes) || !fflush($handle)) { throw new RuntimeException('M51 receipt write incomplete.'); } } finally { fclose($handle); }
echo json_encode(['file' => basename($path), 'sha256' => hash('sha256', $bytes), 'paths' => count($files),
    'visual_unchanged' => count($before['visual_hashes']), 'frozen_unchanged' => count($before['frozen_hashes'])], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR)."\n";
