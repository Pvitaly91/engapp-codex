<?php

// Deliberate, reviewed file sync only. No kernel, .env, DB, HTTP, Git mutation or server changes.
if (PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows') { exit(1); }
$mode = $argv[1] ?? ''; $name = $argv[2] ?? '';
if (!in_array($mode, ['--preview', '--apply-reviewed'], true)
    || !preg_match('/^source-sync-proposal-v[1-9][0-9]*\.json$/D', $name)
    || ($mode === '--preview' && count($argv) !== 3)
    || ($mode === '--apply-reviewed' && (count($argv) !== 5 || $argv[3] !== '--sha256' || !preg_match('/^[a-f0-9]{64}$/D', $argv[4])))) {
    throw new RuntimeException('Use --preview source-sync-proposal-vN.json; then --apply-reviewed that basename --sha256 reviewed-file-SHA.');
}
$source = dirname(__DIR__, 2); $root = 'D:/DEV/htdocs/gramlyze.loc'; $directory = $root.'/storage/app/seo-m43-local';
require $source.'/vendor/autoload.php';
$app = new Illuminate\Foundation\Application($source); // Paths only, never bootstrap.
use App\Services\M43ContentPatch as Patch;
use App\Support\M43AuthoredTenseUsagePackage as Package;
$normal = static fn (string $path): string => strtolower(str_replace('\\', '/', $path));
if ($normal((string) realpath($source)) === $normal((string) realpath($root)) || !is_dir($directory) || is_link($directory)) {
    throw new RuntimeException('M43 requires distinct worktree/served roots and the existing private directory.');
}
$safe = static function (string $base, string $relative) use ($normal): string {
    if (!preg_match('~^[A-Za-z0-9_. /-]+$~D', $relative) || str_contains($relative, '//')
        || array_intersect(explode('/', $relative), ['', '.', '..', '.env', '.git', 'vendor', 'node_modules', 'build', 'cache'])) {
        throw new RuntimeException('M43 unsafe relative source/evidence path.');
    }
    $path = $base.'/'.$relative; $cursor = $path;
    while ($normal($cursor) !== $normal($base)) {
        if (is_link($cursor)) { throw new RuntimeException('M43 refuses a linked path.'); }
        if (file_exists($cursor)) {
            $resolved = realpath($cursor);
            if ($resolved === false || !str_starts_with($normal($resolved), rtrim($normal((string) realpath($base)), '/').'/')) {
                throw new RuntimeException('M43 path leaves its intended root.');
            }
        }
        $parent = dirname($cursor);
        if ($parent === $cursor) { throw new RuntimeException('M43 path containment failed.'); }
        $cursor = $parent;
    }
    return $path;
};
$read = static function (string $path): string {
    if (!is_file($path) || is_link($path)) { throw new RuntimeException('M43 missing/linked source evidence.'); }
    $bytes = file_get_contents($path);
    if (!is_string($bytes)) { throw new RuntimeException('M43 unreadable source evidence.'); }
    return $bytes;
};
$exclusive = static function (string $path, string $bytes): void {
    if (!is_dir(dirname($path)) && !mkdir(dirname($path), 0700, true)) { throw new RuntimeException('M43 cannot create private source parent.'); }
    $handle = fopen($path, 'xb');
    if (!$handle) { throw new RuntimeException('M43 exclusive file already exists.'); }
    try {
        if (fwrite($handle, $bytes) !== strlen($bytes) || !fflush($handle)) { throw new RuntimeException('M43 exclusive source evidence incomplete.'); }
    } finally { fclose($handle); }
};
$json = static fn (array $value): string => json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
[$before, $package] = Package::load($source); Package::validate($before, $package, $source);
$baselinePath = $safe($directory, 'source-before-v1.json'); $baselineBytes = $read($baselinePath);
if (hash('sha256', $baselineBytes) !== '3f122ab8d26923d53f4ede05fe094435797f7439950733fd74a0eae5ea9eacfd') {
    throw new RuntimeException('M43 initial source BEFORE inventory changed.');
}
$baseline = json_decode($baselineBytes, true, flags: JSON_THROW_ON_ERROR);
if ($baseline['schema'] !== 'm43-source-inventory-v1' || $normal($baseline['roots']['root']) !== $normal($root)
    || $normal($baseline['roots']['worktree']) !== $normal($source)) { throw new RuntimeException('M43 source inventory identity differs.'); }
$initial = array_column($baseline['sources']['root'], null, 'path');
$definitions = array_column($before['targets'], 'before', 'path');
$targets = array_column($package['targets'], 'after', 'path');
$paths = Patch::syncPaths($before, $package, $source); $pending = []; $records = []; $shared = [];
$backupDirectory = $safe($directory, 'source-sync-before-v1');
if (!is_dir($backupDirectory) || is_link($backupDirectory) || file_exists($backupDirectory.'/manifest.json')) {
    throw new RuntimeException('M43 requires the six shared captures first and no completed source-sync manifest.');
}
// Resolve and validate every source BEFORE writing even a backup/proposal.
foreach ($paths as $path) {
    $canonical = $safe($source, $path); $working = $safe($root, $path); $new = $read($canonical);
    $old = file_exists($working) ? $read($working) : null;
    $sharedIndex = array_search($path, Patch::SHARED_VIEWS, true);
    if ($sharedIndex !== false) {
        $projection = Patch::sharedSourceProjection($source, $directory, $sharedIndex, $path);
        if ($old === null || hash('sha256', $old) !== $projection['record']['before_sha256']) {
            throw new RuntimeException('M43 ROOT shared bytes changed after exclusive capture: '.$path);
        }
        $new = $projection['bytes']; $shared[] = $projection['record'];
    } elseif (isset($definitions[$path])) {
        if ($old === null || json_decode($old, true, flags: JSON_THROW_ON_ERROR) !== $definitions[$path]
            || json_decode($new, true, flags: JSON_THROW_ON_ERROR) !== $targets[$path]) {
            throw new RuntimeException('M43 ROOT definition is not exact frozen BEFORE, or WT is not exact AFTER: '.$path);
        }
    } elseif (str_contains(strtolower($path), 'm43')) {
        if ($old !== null) { throw new RuntimeException('M43 first sync refuses any pre-existing new M43 source: '.$path); }
    } elseif ($old === null) {
        throw new RuntimeException('M43 unexpected absent non-M43 source.');
    }
    if ($old !== null) {
        $row = $initial[$path] ?? null;
        if (($row['exists'] ?? null) !== true || $row['sha256'] !== hash('sha256', $old) || $row['bytes'] !== strlen($old)) {
            throw new RuntimeException('M43 existing ROOT source differs from initial BEFORE inventory: '.$path);
        }
    } elseif (($initial[$path]['exists'] ?? false) !== false) {
        throw new RuntimeException('M43 original ROOT source was removed after BEFORE inventory.');
    }
    $records[] = ['path' => $path, 'before_sha256' => $old === null ? null : hash('sha256', $old),
        'after_sha256' => hash('sha256', $new), 'worktree_sha256' => hash_file('sha256', $canonical),
        'mode' => $sharedIndex === false ? 'finite-sync' : 'shared-reviewed-hunks'];
    $pending[$path] = ['old' => $old, 'new' => $new, 'shared' => $sharedIndex !== false];
}
// Preserve the code-owned six-shared order expected by the independent review.
$sharedByPath = array_column($shared, null, 'path');
$shared = array_map(fn ($path) => $sharedByPath[$path], Patch::SHARED_VIEWS);
$proposal = ['version' => 1, 'base_sha' => Patch::ACCEPTED_BASE, 'source_root' => $source, 'working_root' => $root,
    'initial_inventory_sha256' => hash('sha256', $baselineBytes), 'package_sha256' => Package::SOURCE_SHA,
    'records' => $records, 'shared_hunks' => $shared];
$proposalBytes = $json($proposal); $proposalPath = $safe($directory, $name);
if ($mode === '--preview') {
    $exclusive($proposalPath, $proposalBytes);
    echo $json(['status' => 'preview-no-source-writes', 'proposal' => $name, 'proposal_sha256' => hash('sha256', $proposalBytes),
        'source_writes' => false, 'db_access' => false, 'records' => $records, 'shared_hunks' => $shared]);
    exit;
}
$reviewed = $read($proposalPath);
if (hash('sha256', $reviewed) !== $argv[4] || $reviewed !== $proposalBytes) {
    throw new RuntimeException('M43 reviewed proposal/SHA is stale; no source writes.');
}
// Shared backups already exist; all other original files get exclusive backups.
foreach ($pending as $path => $item) {
    $backup = $safe($backupDirectory, $path);
    if ($item['shared']) {
        if ($read($backup) !== $item['old']) { throw new RuntimeException('M43 captured shared backup differs.'); }
    } elseif ($item['old'] !== null && file_exists($backup)) { throw new RuntimeException('M43 unexpected pre-existing source backup.'); }
}
foreach ($pending as $path => $item) {
    if (!$item['shared'] && $item['old'] !== null) { $exclusive($safe($backupDirectory, $path), $item['old']); }
}
$exclusive($safe($backupDirectory, 'manifest.json'), $json($records));
// Recheck every byte after backup, before the first application-source write.
foreach ($pending as $path => $item) {
    $working = $safe($root, $path); $current = file_exists($working) ? $read($working) : null;
    if ($current !== $item['old'] || hash_file('sha256', $safe($source, $path)) !== $records[array_search($path, $paths, true)]['worktree_sha256']) {
        throw new RuntimeException('M43 concurrent source edit; originals retained, no sync started.');
    }
}
foreach ($pending as $path => $item) {
    $working = $safe($root, $path);
    if ($item['old'] === null) { $exclusive($working, $item['new']); }
    elseif ($item['new'] !== $item['old'] && file_put_contents($working, $item['new'], LOCK_EX) !== strlen($item['new'])) {
        throw new RuntimeException('M43 source sync incomplete; exclusive backups remain for recovery: '.$path);
    }
    if ($read($working) !== $item['new']) { throw new RuntimeException('M43 source postcondition failed: '.$path); }
}
echo $json(['status' => 'synced-reviewed-sources', 'proposal' => $name, 'proposal_sha256' => $argv[4],
    'source_backup' => 'source-sync-before-v1', 'files' => count($records), 'db_access' => false, 'records' => $records]);
