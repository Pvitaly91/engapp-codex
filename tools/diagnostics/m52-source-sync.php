<?php

/** Explicit filesystem-only receipt; actual transfer is separately reviewed by main. No DB/HTTP/bootstrap. */
if (PHP_SAPI !== 'cli' || count($argv) !== 1) { throw new RuntimeException('Explicit M52 source sync receipt only.'); }
$root = 'D:/DEV/htdocs/gramlyze.loc';
$wt = 'C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc';
$normal = static fn ($p) => strtolower(str_replace('\\', '/', (string) realpath($p)));
if ($normal(dirname(__DIR__, 2)) !== $normal($root)) { throw new RuntimeException('M52 receipt must inspect served ROOT.'); }
require $root.'/vendor/autoload.php';
[$beforeSource, $patch, $master, $contract] = App\Support\M52PassiveBasicsPackage::load($root);
App\Support\M52PassiveBasicsPackage::validate($beforeSource, $patch, $master, $contract);
$private = $root.'/storage/app/seo-m52-local';
$beforeFile = $private.'/'.App\Services\M52ContentPatch::BEFORE_FILE;
if (hash_file('sha256', $beforeFile) !== App\Services\M52ContentPatch::BEFORE_SHA) { throw new RuntimeException('M52 independent BEFORE differs.'); }
$before = json_decode(file_get_contents($beforeFile), true, flags: JSON_THROW_ON_ERROR);
$files = [];
foreach (App\Services\M52ContentPatch::syncPaths($patch, $contract) as $path) {
    foreach ([$root, $wt] as $dir) { if (!is_file($dir.'/'.$path) || is_link($dir.'/'.$path)) { throw new RuntimeException('M52 source missing/linked: '.$path); } }
    $a = file_get_contents($root.'/'.$path); $b = file_get_contents($wt.'/'.$path);
    if ($path === App\Services\M52ContentPatch::CONTROLLER_PATH) {
        if (hash('sha256', $a) !== App\Services\M52ContentPatch::CONTROLLER_ROOT_AFTER_SHA
            || hash('sha256', $b) !== App\Services\M52ContentPatch::CONTROLLER_WT_AFTER_SHA) { throw new RuntimeException('M52 exact reviewed controller bytes differ.'); }
    } elseif ($a !== $b && !(str_ends_with($path, '.php') && str_replace("\r\n", "\n", $a) === str_replace("\r\n", "\n", $b))) {
        throw new RuntimeException('M52 finite source mirror differs: '.$path);
    }
    $originalSha = $before['shared_source_hashes'][$path]['root'] ?? null;
    foreach ($before['definitions'] as $definition) {
        if ($definition['path'] === $path) { $originalSha = $definition['sha256']; }
    }
    $files[] = ['path' => $path, 'root_before_sha256' => $originalSha,
        'root_after_sha256' => hash('sha256', $a), 'worktree_sha256' => hash('sha256', $b)];
}
foreach ([$before['visual_hashes'], $before['frozen_hashes']] as $hashes) {
    foreach ($hashes as $path => $sha) { if (hash_file('sha256', $root.'/'.$path) !== $sha) { throw new RuntimeException('M52 protected visual/frozen source differs: '.$path); } }
}
$receipt = ['base_sha' => App\Support\M52PassiveBasicsPackage::BASE_SHA, 'source_worktree' => $wt, 'served_root' => $root,
    'visual_before' => ['file' => App\Services\M52ContentPatch::BEFORE_FILE, 'sha256' => App\Services\M52ContentPatch::BEFORE_SHA],
    'files' => $files, 'created_at' => gmdate('c'), 'db_writes' => 0, 'http_requests' => 0];
$bytes = json_encode($receipt, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
$path = $private.'/'.App\Services\M52ContentPatch::SYNC_RECEIPT;
if (file_exists($path) || is_link($path)) { throw new RuntimeException('M52 receipt exists; no overwrite.'); }
$handle = fopen($path, 'x'); if (!$handle) { throw new RuntimeException('M52 receipt unavailable.'); }
try { if (fwrite($handle, $bytes) !== strlen($bytes) || !fflush($handle)) { throw new RuntimeException('M52 receipt incomplete.'); } }
finally { fclose($handle); }
echo json_encode(['file' => basename($path), 'sha256' => hash('sha256', $bytes), 'paths' => count($files),
    'visual_unchanged' => count($before['visual_hashes']), 'frozen_unchanged' => count($before['frozen_hashes'])], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR)."\n";
