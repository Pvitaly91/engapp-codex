<?php

// Read-only source inspection and exclusive private evidence. Never edits ROOT/WT sources or boots Laravel.
if (PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows' || !in_array($argv[1] ?? '', ['--capture', '--review'], true) || count($argv) !== 2) { exit(1); }
$source = dirname(__DIR__, 2); $root = 'D:/DEV/htdocs/gramlyze.loc'; $directory = $root.'/storage/app/seo-m43-local';
require $source.'/vendor/autoload.php';
use App\Services\M43ContentPatch as Patch;
if (!is_dir($directory) || is_link($directory)) { throw new RuntimeException('M43 private evidence directory unavailable.'); }
$exclusive = static function (string $path, string $bytes): void {
    if (!is_dir(dirname($path)) && !mkdir(dirname($path), 0700, true)) { throw new RuntimeException('M43 evidence directory creation failed.'); }
    if (is_link(dirname($path)) || is_link($path)) { throw new RuntimeException('M43 redirected evidence path.'); }
    $file = fopen($path, 'xb'); if (!$file) { throw new RuntimeException('M43 exclusive source evidence already exists.'); }
    try { if (fwrite($file, $bytes) !== strlen($bytes) || !fflush($file)) { throw new RuntimeException('Incomplete M43 source evidence.'); } }
    finally { fclose($file); }
};
if ($argv[1] === '--capture') {
    $pending = [];
    foreach (Patch::SHARED_VIEWS as $index => $path) {
        if (!is_file($root.'/'.$path) || !is_file($source.'/'.$path) || is_link($root.'/'.$path) || is_link($source.'/'.$path)) {
            throw new RuntimeException('Missing/linked M43 shared source.');
        }
        $git = new Symfony\Component\Process\Process(['git', '-c', 'safe.directory='.$source, '-c', 'core.bare=false', 'show', Patch::ACCEPTED_BASE.':'.$path], $source);
        $git->mustRun();
        $pending['m43-shared-base-v1-'.$index.'.bin'] = $git->getOutput();
        $pending['source-sync-before-v1/'.$path] = file_get_contents($root.'/'.$path);
    }
    foreach ($pending as $basename => $bytes) {
        if (file_exists($directory.'/'.$basename)) { throw new RuntimeException('M43 capture already exists; no overwrite.'); }
    }
    foreach ($pending as $basename => $bytes) { $exclusive($directory.'/'.$basename, $bytes); }
    echo json_encode(['captured' => count(Patch::SHARED_VIEWS), 'base_sha' => Patch::ACCEPTED_BASE, 'source_writes' => false, 'db_access' => false])."\n";
    exit;
}
$files = [];
foreach (Patch::SHARED_VIEWS as $index => $path) {
    $projection = Patch::sharedSourceProjection($source, $directory, $index, $path);
    if (!is_file($root.'/'.$path) || is_link($root.'/'.$path) || file_get_contents($root.'/'.$path) !== $projection['bytes']) {
        throw new RuntimeException('M43 ROOT has not received exactly the reviewed hunks, or unrelated ROOT changes differ: '.$path);
    }
    $files[] = $projection['record'];
}
$review = ['version' => 1, 'base_sha' => Patch::ACCEPTED_BASE, 'files' => $files];
$exclusive($directory.'/'.Patch::SHARED_REVIEW, json_encode($review, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n");
Patch::reviewedSharedSources($source, $directory);
echo json_encode(['reviewed' => count($files), 'foreign_root_bytes_preserved' => true, 'source_writes' => false, 'db_access' => false])."\n";
