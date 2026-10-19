<?php

/**
 * Explicit filesystem-only evidence for the post-apply M54 token-case formatter.
 * No autoloader/application bootstrap, PDO/SQL, HTTP, renderer invocation or apply.
 * php tools/diagnostics/m54-presentation-sources.php presentation-sources-v1.json
 */
set_exception_handler(static function (Throwable $error): void {
    fwrite(STDERR, $error instanceof RuntimeException
        ? 'M54 presentation audit: '.$error->getMessage()."\n"
        : 'M54 presentation audit incomplete; no successful receipt.'."\n");
    exit(1);
});
$name = $argv[1] ?? '';
if (PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows' || count($argv) !== 2
    || preg_match('/^presentation-sources-v[1-9][0-9]*\.json$/D', $name) !== 1) {
    throw new RuntimeException('Explicit Windows CLI and a new private presentation-sources-vN.json basename required.');
}
$root = 'D:/DEV/htdocs/gramlyze.loc';
$wt = 'C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc';
$beforeSha = 'a0fc78b98a12e8af17c56d3f4fde8dccf95031d6de2a5a4c8081afeba15edb64';
$syncSha = '1111f0107be49e29ac118d913f5e208aa9995a2c4ec4a0e3004633342a946139';
$writerSha = 'a3981d2d0f8046ef8f7a3e373b5348ac43c8b9d9227a3fce85c6c55878d418d0';
$auditSha = '2ae1efb300d3fccc719a7a7ddd4865738a33cf60df40720c54fa9c86d1d0034c';
$rendererBeforeSha = '8783db815ab2d07beb5c1e6e8cdba95ce7e6b4583bb4dc91744889a3d84dba11';
$rendererAfterSha = '47dada869deba7dc457e2b4b346e86d9ce9be3d482cc450502cbf46acb5303d0';
$helperSha = '68734166024d4c3f65b6ebb585a9f26f396f7d79204d0c4f9a7ff99d2164a1bb';
$normal = static function (string $path): string {
    $real = realpath($path);
    if ($real === false) { throw new RuntimeException('Required reviewed filesystem path is unavailable.'); }
    return rtrim(strtolower(str_replace('\\', '/', $real)), '/');
};
if ($normal(dirname(__DIR__, 2)) !== $normal($root)) {
    throw new RuntimeException('Presentation audit must inspect the actual served ROOT.');
}
$dir = $root.'/storage/app/seo-m54-local';
if (!is_dir($dir) || is_link($dir) || $normal($dir) !== $normal($root).'/storage/app/seo-m54-local'
    || file_exists($dir.'/'.$name) || is_link($dir.'/'.$name)) {
    throw new RuntimeException('Private presentation receipt path is unsafe or already exists.');
}
$read = static function (string $path) use ($normal, $root, $wt): string {
    if (!is_file($path) || is_link($path)) { throw new RuntimeException('Reviewed source/evidence is unavailable or linked.'); }
    $resolved = $normal($path);
    if (!str_starts_with($resolved, $normal($root).'/') && !str_starts_with($resolved, $normal($wt).'/')) {
        throw new RuntimeException('Reviewed source/evidence resolves outside the finite ROOT/worktree scope.');
    }
    $bytes = file_get_contents($path);
    if (!is_string($bytes)) { throw new RuntimeException('Reviewed filesystem input cannot be read.'); }
    return $bytes;
};
$verify = static function (string $path, string $sha) use ($read): string {
    if (preg_match('/^[a-f0-9]{64}$/D', $sha) !== 1) { throw new RuntimeException('Presentation source is not yet reviewed/pinned.'); }
    $bytes = $read($path);
    if (!hash_equals($sha, hash('sha256', $bytes))) { throw new RuntimeException('Reviewed filesystem bytes changed: '.basename($path)); }
    return $bytes;
};
foreach ([$beforeSha, $syncSha, $writerSha, $auditSha, $rendererBeforeSha, $rendererAfterSha, $helperSha] as $sha) {
    if (preg_match('/^[a-f0-9]{64}$/D', $sha) !== 1) { throw new RuntimeException('Every finite presentation source pin must be reviewed before running this audit.'); }
}
$before = json_decode($verify($dir.'/before-v1.json', $beforeSha), true, flags: JSON_THROW_ON_ERROR);
$sync = json_decode($verify($dir.'/source-sync-v1.json', $syncSha), true, flags: JSON_THROW_ON_ERROR);
if (($before['base_sha'] ?? null) !== 'f4e1c0d94a590d630582e2bb613cbdc93654fc4a'
    || ($before['served_root'] ?? null) !== $root || ($before['source_worktree'] ?? null) !== $wt
    || ($sync['base_sha'] ?? null) !== $before['base_sha'] || ($sync['served_root'] ?? null) !== $root
    || ($sync['source_worktree'] ?? null) !== $wt
    || ($sync['visual_before'] ?? null) !== ['file' => 'before-v1.json', 'sha256' => $beforeSha]
    || ($sync['db_writes'] ?? null) !== 0 || ($sync['http_requests'] ?? null) !== 0
    || empty($before['visual_hashes']) || empty($before['frozen_hashes'])) {
    throw new RuntimeException('Independent BEFORE or immutable writer source-sync identity differs.');
}
$paths = [
    'app/Support/TheoryContentRenderer.php' => $rendererAfterSha,
    'app/Support/M54BuilderTokenPresentation.php' => $helperSha,
    'app/Services/M54ContentPatch.php' => $writerSha,
    'docs/content/m54-passive-patterns-bank-audit.v1.json' => $auditSha,
];
$facts = [];
foreach ($paths as $path => $sha) {
    $rootBytes = $verify($root.'/'.$path, $sha); $wtBytes = $verify($wt.'/'.$path, $sha);
    if ($rootBytes !== $wtBytes) { throw new RuntimeException('Reviewed presentation source ROOT/worktree parity differs.'); }
    if ($path === 'app/Support/TheoryContentRenderer.php') {
        $eol = str_contains($rootBytes, "\r\n") ? "\r\n" : "\n";
        $oldCall = '                $bindings = M53BuilderHintPresentation::bindings($bindings);'.$eol;
        $newCall = '                $bindings = M54BuilderTokenPresentation::bindings($bindings);'.$eol;
        if (substr_count($rootBytes, $newCall) !== 1 || substr_count($rootBytes, $oldCall.$newCall) !== 1
            || !hash_equals($rendererBeforeSha, hash('sha256', str_replace($newCall, '', $rootBytes)))) {
            throw new RuntimeException('Renderer is not precisely the one reviewed hook after M53 with exact BEFORE reconstruction.');
        }
    }
    $facts[] = ['path' => $path, 'root_sha256' => $sha, 'worktree_sha256' => $sha];
}
$writerRecords = array_values(array_filter($sync['files'] ?? [], static fn ($record) => ($record['path'] ?? null) === 'app/Services/M54ContentPatch.php'));
if (count($writerRecords) !== 1 || ($writerRecords[0]['root_after_sha256'] ?? null) !== $writerSha
    || ($writerRecords[0]['worktree_sha256'] ?? null) !== $writerSha) {
    throw new RuntimeException('The completed DB writer bytes differ from immutable source-sync-v1.');
}
$checkInventory = static function (array $hashes) use ($verify, $root): void {
    foreach ($hashes as $path => $sha) {
        if (!is_string($path) || preg_match('~^[A-Za-z0-9_./-]+$~D', $path) !== 1
            || str_starts_with($path, '/') || in_array('..', explode('/', $path), true)) {
            throw new RuntimeException('Protected inventory path is outside the reviewed relative source scope.');
        }
        $verify($root.'/'.$path, $sha);
    }
};
$checkInventory($before['visual_hashes']); $checkInventory($before['frozen_hashes']);
// Keep every immutable SQL-writer guard intact; the new formatter is not a writer source refresh.
foreach ($paths as $path => $sha) { $verify($root.'/'.$path, $sha); $verify($wt.'/'.$path, $sha); }
$verify($dir.'/before-v1.json', $beforeSha); $verify($dir.'/source-sync-v1.json', $syncSha);
$receipt = ['schema_version' => 1, 'checked_at' => gmdate('c'), 'base_sha' => $before['base_sha'],
    'served_root' => $root, 'source_worktree' => $wt, 'independent_before_sha256' => $beforeSha,
    'writer_source_sync_file' => 'source-sync-v1.json', 'writer_source_sync_sha256' => $syncSha,
    'writer_bytes_unchanged' => true, 'renderer_before_sha256' => $rendererBeforeSha,
    'exact_one_hook_before_reconstruction' => true, 'presentation_sources' => $facts,
    'visual_files_unchanged' => count($before['visual_hashes']), 'frozen_files_unchanged' => count($before['frozen_hashes']),
    'application_bootstrap' => false, 'db_access' => false, 'http_requests' => 0, 'db_writes' => 0,
    'scope' => 'Filesystem proof only. No new content apply or writer/preview/backup/guard replacement. Runtime formatter behavior and SQL preservation are separately tested.'];
$bytes = json_encode($receipt, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
$handle = fopen($dir.'/'.$name, 'x');
if ($handle === false) { throw new RuntimeException('Exclusive presentation receipt creation failed.'); }
try {
    if (fwrite($handle, $bytes) !== strlen($bytes) || !fflush($handle)) { throw new RuntimeException('Presentation receipt write incomplete.'); }
} finally { fclose($handle); }
echo json_encode(['file' => $name, 'sha256' => hash('sha256', $bytes), 'writer_bytes_unchanged' => true,
    'exact_one_hook_before_reconstruction' => true, 'visual_files_unchanged' => count($before['visual_hashes']),
    'frozen_files_unchanged' => count($before['frozen_hashes']), 'db_access' => false, 'db_writes' => 0, 'http_requests' => 0],
    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
