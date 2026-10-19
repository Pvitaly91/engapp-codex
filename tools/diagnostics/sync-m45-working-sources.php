<?php

// Deliberate source-only sync. No kernel, environment loading, HTTP, DB or Git mutations.
if (PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows') { exit(1); }
$mode = $argv[1] ?? ''; $name = $argv[2] ?? '';
if (!in_array($mode, ['--preview', '--apply-reviewed'], true)
    || !preg_match('/^source-sync-proposal-v[1-9][0-9]*\.json$/D', $name)
    || ($mode === '--preview' && count($argv) !== 3)
    || ($mode === '--apply-reviewed' && (count($argv) !== 5 || $argv[3] !== '--sha256' || !preg_match('/^[a-f0-9]{64}$/D', $argv[4])))) {
    throw new RuntimeException('Use --preview source-sync-proposal-vN.json, then --apply-reviewed that name --sha256 reviewed-proposal-SHA.');
}
$source = dirname(__DIR__, 2); $root = 'D:/DEV/htdocs/gramlyze.loc'; $directory = $root.'/storage/app/seo-m45-local';
require $source.'/vendor/autoload.php';
new Illuminate\Foundation\Application($source); // Path/container helpers only; never bootstrap a kernel.
use App\Services\M44ContentPatch as HunkProjection;
use App\Services\M45ContentPatch as Patch;
use App\Support\M45FutureComparisonsPackage as Package;
use Symfony\Component\Process\Process;

$normal = static fn (string $path): string => strtolower(str_replace('\\', '/', $path));
if ($normal((string) realpath($source)) === $normal((string) realpath($root)) || !is_dir($directory) || is_link($directory)) {
    throw new RuntimeException('M45 requires distinct WT/served ROOT and existing owned private evidence.');
}
$safe = static function (string $base, string $relative) use ($normal): string {
    if (!preg_match('~^[A-Za-z0-9_. /-]+$~D', $relative) || str_contains($relative, '//')
        || array_intersect(explode('/', $relative), ['', '.', '..', '.env', '.git', 'vendor', 'node_modules', 'build', 'cache'])) {
        throw new RuntimeException('M45 unsafe relative source/evidence path.');
    }
    $path = $base.'/'.$relative; $cursor = $path;
    while ($normal($cursor) !== $normal($base)) {
        if (is_link($cursor)) { throw new RuntimeException('M45 refuses linked source/evidence.'); }
        if (file_exists($cursor)) {
            $resolved = realpath($cursor);
            if ($resolved === false || !str_starts_with($normal($resolved), rtrim($normal((string) realpath($base)), '/').'/')) {
                throw new RuntimeException('M45 path leaves intended root.');
            }
        }
        $parent = dirname($cursor);
        if ($parent === $cursor) { throw new RuntimeException('M45 path containment failed.'); }
        $cursor = $parent;
    }
    return $path;
};
$read = static function (string $path): string {
    if (!is_file($path) || is_link($path)) { throw new RuntimeException('M45 missing/linked source or evidence: '.$path); }
    $bytes = file_get_contents($path);
    if (!is_string($bytes)) { throw new RuntimeException('M45 unreadable source/evidence.'); }
    return $bytes;
};
$exclusive = static function (string $path, string $bytes): void {
    if (!is_dir(dirname($path)) && !mkdir(dirname($path), 0700, true)) { throw new RuntimeException('M45 cannot create source/evidence parent.'); }
    $handle = fopen($path, 'xb');
    if (!$handle) { throw new RuntimeException('M45 exclusive file already exists.'); }
    try {
        if (fwrite($handle, $bytes) !== strlen($bytes) || !fflush($handle)) { throw new RuntimeException('M45 exclusive write incomplete.'); }
    } finally { fclose($handle); }
};
$json = static fn (array $value): string => json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";

[$before, $package] = Package::load($source); Package::validate($before, $package, $source);
$baselineBytes = $read($safe($directory, 'source-before-v1.json'));
if(hash('sha256',$baselineBytes)!=='f050d33fc844dad01125bd6268836836c8b8bf177bef37a8a7ebeed21c4a59e3'){
    throw new RuntimeException('M45 independently captured initial source inventory changed.');
}
$baseline = json_decode($baselineBytes, true, flags: JSON_THROW_ON_ERROR);
if (($baseline['schema'] ?? null) !== 'm45-source-inventory-v1' || ($baseline['base_sha'] ?? null) !== Patch::ACCEPTED_BASE
    || $normal($baseline['roots']['root']) !== $normal($root) || $normal($baseline['roots']['worktree']) !== $normal($source)) {
    throw new RuntimeException('M45 initial BEFORE source inventory identity differs.');
}
$backup = json_decode($read($safe($directory, 'source-sync-before-v1/manifest.json')), true, flags: JSON_THROW_ON_ERROR);
$definitions = array_column($before['targets'], 'before', 'path');
$targets = array_column($package['targets'], 'after', 'path');
if (($backup['schema'] ?? null) !== 'm45-source-backup-v1' || ($backup['base_sha'] ?? null) !== Patch::ACCEPTED_BASE
    || array_column($backup['files'] ?? [], 'path') !== [...Patch::SHARED_VIEWS, ...array_keys($definitions)]) {
    throw new RuntimeException('M45 exclusive ROOT-before backup scope differs.');
}
$initial = array_column($baseline['sources']['root'], null, 'path');
$backupFiles = array_column($backup['files'], null, 'path');
$explicit = [...Patch::SHARED_VIEWS, ...array_keys($targets)];
$paths = array_values(array_filter(Patch::sourcePaths($before, $package, $source),
    static fn (string $path): bool => str_contains($path, 'M45') || str_contains($path, 'm45') || in_array($path, $explicit, true)));
sort($paths); $pending = []; $records = [];

// Fresh full managed inventory also catches added/removed foreign files and protects configs.
$assertInventory = static function () use ($source, $baseline, $paths, $explicit): void {
    $program = 'process.stdout.write(JSON.stringify(require('.json_encode($source.'/tools/diagnostics/capture-m45-source-inventory.cjs').').capture()));';
    $process = new Process(['node', '-e', $program], $source); $process->setTimeout(600); $process->mustRun();
    $current = json_decode($process->getOutput(), true, flags: JSON_THROW_ON_ERROR);
    if ($current['schema'] !== $baseline['schema'] || $current['base_sha'] !== $baseline['base_sha'] || $current['roots'] !== $baseline['roots']
        || $current['protected_config'] !== $baseline['protected_config']) {
        throw new RuntimeException('M45 protected environment/config fingerprints changed since BEFORE.');
    }
    $owned = static fn (string $path): bool => $path==='.gitattributes' || in_array($path, $explicit, true) || preg_match(
        '~^(?:app/(?:Support|Services)/M45[^/]*\.php|app/Console/Commands/[^/]*M45[^/]*\.php|'
        .'resources/views/engram/theory/blocks-v3/m45-[^/]*\.blade\.php|public/js/m45-[^/]*\.js|'
        .'docs/(?:content|reports)/[^/]*m45[^/]*|database/content-patches/m45-[^/]*|'
        .'tests/(?:Feature|Unit|Browser)/[^/]*[mM]45[^/]*|tools/diagnostics/[^/]*m45[^/]*)$~D', $path) === 1;
    foreach (['root', 'worktree'] as $label) {
        $old = array_column($baseline['sources'][$label], null, 'path');
        $now = array_column($current['sources'][$label], null, 'path');
        foreach (array_unique([...array_keys($old), ...array_keys($now)]) as $path) {
            $left = $old[$path] ?? ['path' => $path, 'exists' => false, 'bytes' => 0, 'sha256' => null];
            $right = $now[$path] ?? ['path' => $path, 'exists' => false, 'bytes' => 0, 'sha256' => null];
            if ($left === $right) { continue; }
            if ($label === 'root' || !$owned($path)) { throw new RuntimeException('M45 non-target '.$label.' source drift: '.$path); }
        }
    }
};
$assertInventory();

foreach ($paths as $path) {
    $canonical = $safe($source, $path); $working = $safe($root, $path); $wt = $read($canonical);
    $old = file_exists($working) ? $read($working) : null; $new = $wt; $hunks = [];
    $sharedIndex = array_search($path, Patch::SHARED_VIEWS, true); $baseRecord = [];
    if ($sharedIndex !== false) {
        $record = $backupFiles[$path]; $baseName = 'm45-shared-base-v1-'.$sharedIndex.'.bin';
        if (($record['base_basename'] ?? null) !== $baseName || ($record['backup_basename'] ?? null) !== 'source-sync-before-v1/'.$path) {
            throw new RuntimeException('M45 shared evidence names differ.');
        }
        $base = $safe($directory, $baseName); $captured = $safe($directory, $record['backup_basename']);
        $baseBytes = $read($base); $capturedBytes = $read($captured);
        if (hash('sha256', $baseBytes) !== $record['base_sha256'] || hash('sha256', $capturedBytes) !== $record['root_before_sha256'] || $old !== $capturedBytes) {
            throw new RuntimeException('M45 captured shared ROOT/base bytes drifted.');
        }
        $git = new Process(['git', '-c', 'core.safecrlf=false', 'diff', '--no-index', '--no-color', '--no-ext-diff', '--ignore-cr-at-eol', '--unified=1', '--', $base, $canonical], $source);
        $git->run(); if (!in_array($git->getExitCode(), [0, 1], true)) { throw new RuntimeException('M45 read-only shared Git diff failed.'); }
        $hunk = null;
        foreach (explode("\n", str_replace("\r\n", "\n", $git->getOutput())) as $line) {
            if (str_starts_with($line, '@@ ')) { if ($hunk !== null) { $hunks[] = $hunk; } $hunk = ['before' => '', 'after' => '']; continue; }
            if ($hunk === null || $line === '') { continue; }
            $prefix = $line[0]; $text = substr($line, 1)."\n";
            if ($prefix === ' ') { $hunk['before'] .= $text; $hunk['after'] .= $text; }
            elseif ($prefix === '-') { $hunk['before'] .= $text; }
            elseif ($prefix === '+') { $hunk['after'] .= $text; }
            else { throw new RuntimeException('M45 unsupported shared diff, including missing final LF.'); }
        }
        if ($hunk !== null) { $hunks[] = $hunk; }
        if (HunkProjection::projectSharedFragments($baseBytes, $hunks) !== str_replace("\r\n", "\n", $wt)) {
            throw new RuntimeException('M45 hunks do not reconstruct exact WT bytes.');
        }
        $new = HunkProjection::projectSharedFragments($capturedBytes, $hunks, true);
        $baseRecord = ['base_basename' => $baseName, 'base_sha256' => hash('sha256', $baseBytes)];
    } elseif (isset($definitions[$path])) {
        if ($old === null || json_decode($old, true, flags: JSON_THROW_ON_ERROR) !== $definitions[$path]
            || json_decode($wt, true, flags: JSON_THROW_ON_ERROR) !== $targets[$path]
            || $old !== $read($safe($directory, 'source-sync-before-v1/'.$path))) {
            throw new RuntimeException('M45 ROOT definition is not captured exact BEFORE or WT is not exact projected AFTER: '.$path);
        }
    } elseif ($old !== null && $old !== $wt) {
        throw new RuntimeException('M45 refuses differing pre-existing own application source: '.$path);
    }
    $row = $initial[$path] ?? ['exists' => false, 'sha256' => null, 'bytes' => 0];
    if (($old === null && $row['exists']) || ($old !== null && (!$row['exists'] || hash('sha256', $old) !== $row['sha256'] || strlen($old) !== $row['bytes']))) {
        throw new RuntimeException('M45 ROOT candidate differs from initial raw BEFORE: '.$path);
    }
    $records[] = ['path' => $path, 'root_before_sha256' => $old === null ? null : hash('sha256', $old),
        'root_after_sha256' => hash('sha256', $new), 'worktree_sha256' => hash('sha256', $wt),
        'before_bytes' => $old === null ? 0 : strlen($old), 'after_bytes' => strlen($new),
        'mode' => $sharedIndex === false ? 'finite-sync' : 'shared-reviewed-hunks', 'hunks' => $hunks,
        'backup_basename' => isset($backupFiles[$path]) ? $backupFiles[$path]['backup_basename'] : null, ...$baseRecord];
    $pending[$path] = ['old' => $old, 'new' => $new, 'wt_sha256' => hash('sha256', $wt)];
}
$proposal = ['schema' => 'm45-reviewed-source-sync-v1', 'base_sha' => Patch::ACCEPTED_BASE,
    'roots' => ['root' => $root, 'worktree' => str_replace('\\', '/', $source)],
    'initial_inventory_sha256' => hash('sha256', $baselineBytes), 'master_sha256' => Package::MASTER_SHA,
    'package_sha256' => Package::SOURCE_SHA, 'files' => $records];
$proposalBytes = $json($proposal); $proposalPath = $safe($directory, $name);
if ($mode === '--preview') {
    $exclusive($proposalPath, $proposalBytes);
    echo $json(['status' => 'preview-no-source-writes', 'proposal' => $name, 'sha256' => hash('sha256', $proposalBytes),
        'source_writes' => false, 'db_access' => false, 'files' => $records]);
    exit;
}
if ($read($proposalPath) !== $proposalBytes || hash('sha256', $proposalBytes) !== $argv[4]) {
    throw new RuntimeException('M45 reviewed proposal/SHA is stale; no ROOT source writes.');
}
$receiptPath = $safe($directory, 'source-sync.json');
if (file_exists($receiptPath)) { throw new RuntimeException('M45 source sync receipt already exists; no automatic reapply or overwrite.'); }
// Recheck all protection and candidate bytes immediately before the first source write.
$assertInventory();
foreach ($pending as $path => $item) {
    $working = $safe($root, $path); $current = file_exists($working) ? $read($working) : null;
    if ($current !== $item['old'] || hash('sha256', $read($safe($source, $path))) !== $item['wt_sha256']) {
        throw new RuntimeException('M45 concurrent candidate edit; exclusive originals remain, no sync started.');
    }
}
foreach ($pending as $path => $item) {
    $working = $safe($root, $path);
    if ($item['old'] === null) { $exclusive($working, $item['new']); }
    elseif ($item['old'] !== $item['new'] && file_put_contents($working, $item['new'], LOCK_EX) !== strlen($item['new'])) {
        throw new RuntimeException('M45 source write incomplete; use preserved private backups for explicit recovery: '.$path);
    }
    if ($read($working) !== $item['new']) { throw new RuntimeException('M45 ROOT source byte postcondition failed: '.$path); }
}
$exclusive($receiptPath, $json([...$proposal, 'reviewed_proposal' => $name, 'reviewed_proposal_sha256' => $argv[4], 'synced_at' => gmdate('c')]));
echo $json(['status' => 'synced-reviewed-sources', 'receipt' => 'source-sync.json', 'sha256' => hash_file('sha256', $receiptPath),
    'files' => count($records), 'private_backup' => 'source-sync-before-v1', 'db_access' => false]);
