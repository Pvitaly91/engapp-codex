<?php

/**
 * Explicit M51 private proof lifecycle, never a route installer or content writer.
 * prepare
 * cleanup local-proof-<nonce>.json <proof-sha256>
 * Main installs/removes the separately reviewed GET-only nonce route manually.
 * No implicit HTTP/startup integration, seeding, progress writes or server edits.
 */
set_exception_handler(static function (Throwable $error): void {
    $safe = $error instanceof RuntimeException && !$error instanceof PDOException
        ? $error->getMessage() : get_class($error).' (proof lifecycle incomplete; no successful receipt)';
    fwrite(STDERR, 'M51 runtime proof: '.$safe."\n"); exit(1);
});
$mode = $argv[1] ?? '';
$proofName = $argv[2] ?? '';
$expectedProofSha = $argv[3] ?? '';
if (PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows'
    || !in_array($mode, ['prepare', 'cleanup'], true)
    || ($mode === 'prepare' && count($argv) !== 2)
    || ($mode === 'cleanup' && (count($argv) !== 4
        || preg_match('/^local-proof-[a-f0-9]{32}\.json$/D', $proofName) !== 1
        || preg_match('/^[a-f0-9]{64}$/D', $expectedProofSha) !== 1))) {
    throw new RuntimeException('M51 requires an explicit prepare/cleanup invocation with scoped proof identity.');
}
$root = 'D:/DEV/htdocs/gramlyze.loc';
$normal = static function (string $path): string {
    $real = realpath($path);
    if ($real === false) { throw new RuntimeException('M51 required local path is unavailable.'); }
    return rtrim(strtolower(str_replace('\\', '/', $real)), '/');
};
if ($normal(dirname(__DIR__, 2)) !== $normal($root)) {
    throw new RuntimeException('M51 proof must run from the actual served ROOT, not another worktree.');
}
require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if ($normal(base_path()) !== $normal($root) || $normal(app()->environmentPath()) !== $normal($root)) {
    throw new RuntimeException('M51 proof application/environment ROOT differs from the served target.');
}
$dir = App\Services\M51LocalTargetGuard::privateDirectory();
if (is_link($dir) || (!is_dir($dir) && !mkdir($dir, 0700, true))
    || $normal($dir) !== $normal($root).'/storage/app/seo-m51-local') {
    throw new RuntimeException('M51 private proof directory is unsafe or unavailable.');
}
$json = static fn ($value): string => json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
$read = static function (string $path): string {
    if (!is_file($path) || is_link($path)) { throw new RuntimeException('M51 proof source is unavailable or linked.'); }
    $bytes = file_get_contents($path);
    if (!is_string($bytes)) { throw new RuntimeException('M51 proof source cannot be read.'); }
    return $bytes;
};
$exclusive = static function (string $path, string $bytes): void {
    if (file_exists($path) || is_link($path)) { throw new RuntimeException('M51 proof receipt collision; no overwrite.'); }
    $handle = fopen($path, 'x');
    if ($handle === false) { throw new RuntimeException('M51 proof receipt cannot be created exclusively.'); }
    try {
        if (fwrite($handle, $bytes) !== strlen($bytes) || !fflush($handle)) {
            throw new RuntimeException('M51 proof receipt write is incomplete.');
        }
    } finally { fclose($handle); }
};
$physicalPaths = [
    'C:/Program Files/xampp/apache/conf/httpd.conf',
    'C:/Program Files/xampp/apache/conf/extra/httpd-vhosts.conf',
    'C:/Program Files/xampp/apache/conf/extra/gramlyze-fastcgi.conf',
    $root.'/public/index.php',
];
$physicalHashes = static function () use ($physicalPaths, $read): array {
    $out = [];
    foreach ($physicalPaths as $path) { $out[$path] = hash('sha256', $read($path)); }
    return $out;
};
$apiPath = $root.'/routes/api.php';
$routesBefore = $read($apiPath);
$hashesBefore = $physicalHashes();
$db = Illuminate\Support\Facades\DB::connection();
App\Services\M51LocalTargetGuard::assertConfiguredDatabase($db); // Pure M49 policy, before PDO.
$guard = new App\Services\M51LocalTargetGuard;
$physical = $guard->physicalEvidence(); // Existing fixed M11 Windows process/vhost/listener inspection.
$db->statement('SET TRANSACTION READ ONLY'); $db->beginTransaction();
try {
    $server = (array) $db->selectOne('SELECT DATABASE() AS db, @@port AS port, @@pid_file AS pid_file, @@datadir AS datadir, @@version_compile_os AS os');
    foreach (['pid_file', 'datadir'] as $key) {
        if (!is_string($server[$key] ?? null) || preg_match('~^[A-Za-z]:/~D', str_replace('\\', '/', $server[$key])) !== 1) {
            throw new RuntimeException('M51 MySQL evidence path is not an absolute local Windows drive path.');
        }
    }
    $pidPath = $normal($server['pid_file']); $dataPath = $normal($server['datadir']);
    $pid = trim($read($server['pid_file']));
    if (($server['db'] ?? null) !== 'gr2' || (int) ($server['port'] ?? 0) !== 3306
        || !str_starts_with((string) ($server['os'] ?? ''), 'Win') || !ctype_digit($pid)
        || !str_starts_with($pidPath, $dataPath.'/') || str_starts_with($dataPath, '//')) {
        throw new RuntimeException('M51 runtime proof DB identity is not the inspected local Windows database.');
    }
    $guard->assertEvidence($physical, $normal($root), (int) $pid, 3306);
    $runtime = App\Services\M51LocalTargetGuard::publicIdentity($db, $root.'/public'); // Safe M49 allowlist only.
    if (array_keys($runtime) !== ['environment', 'site_mode', 'application_root', 'document_root', 'db_driver', 'db_host', 'db_port', 'database']
        || $runtime['application_root'] !== $normal($root) || $runtime['document_root'] !== $normal($root.'/public')
        || $runtime['db_driver'] !== 'mysql' || $runtime['db_port'] !== 3306 || $runtime['database'] !== 'gr2') {
        throw new RuntimeException('M51 public runtime identity differs from its safe local field contract.');
    }
    if ($mode === 'prepare') {
        if (str_contains($routesBefore, 'm51-target-')) {
            throw new RuntimeException('M51 proof route is already present; do not nest proof lifecycles.');
        }
        $nonce = bin2hex(random_bytes(16));
        $privateRuntimeProof = App\Services\M11LocalTargetGuard::runtimeProof($db, $nonce, $root.'/public');
        // runtimeProof hashes credentials/config internally; none of those values is serialized here.
    }
} finally { $db->rollBack(); }
if ($read($apiPath) !== $routesBefore || $physicalHashes() !== $hashesBefore) {
    throw new RuntimeException('M51 routes or physical configuration changed during read-only proof preparation.');
}
if ($mode === 'prepare') {
    $proofName = 'local-proof-'.$nonce.'.json'; $routesName = 'routes-before-'.$nonce.'.bin';
    $proof = ['target' => 'gramlyze.loc', 'nonce' => $nonce, 'created_at' => gmdate('c'),
        'scope' => App\Services\M51LocalTargetGuard::scope(), 'physical_hashes' => $hashesBefore,
        'physical' => $physical, 'runtime' => $runtime, 'runtime_proof' => $privateRuntimeProof,
        'routes_before_file' => $routesName, 'routes_before_sha256' => hash('sha256', $routesBefore),
        'route_installed' => false,
        'write_policy' => 'Exclusive private proof/snapshot only. DB SELECT/READ ONLY/ROLLBACK; no route installation, content/progress writes or server changes.'];
    App\Services\M51LocalTargetGuard::assertFreshProof($proof, $proofName);
    $exclusive($dir.'/'.$routesName, $routesBefore);
    $exclusive($dir.'/'.$proofName, $json($proof));
    App\Services\M51LocalTargetGuard::readFreshProof($proofName);
    echo $json(['nonce' => $nonce, 'file' => $proofName, 'sha256' => hash_file('sha256', $dir.'/'.$proofName)]);
    exit(0);
}

$proofPath = $dir.'/'.$proofName; $proofBytes = $read($proofPath);
if (!hash_equals($expectedProofSha, hash('sha256', $proofBytes))) {
    throw new RuntimeException('M51 cleanup immutable private proof hash differs.');
}
$proof = json_decode($proofBytes, true, flags: JSON_THROW_ON_ERROR);
$nonce = substr($proofName, strlen('local-proof-'), 32); $at = strtotime($proof['created_at'] ?? '');
if (!$at || $at > time() + 60 || ($proof['nonce'] ?? null) !== $nonce
    || ($proof['routes_before_file'] ?? null) !== 'routes-before-'.$nonce.'.bin'
    || preg_match('/^[a-f0-9]{64}$/D', $proof['routes_before_sha256'] ?? '') !== 1) {
    throw new RuntimeException('M51 cleanup proof/snapshot identity differs.');
}
// Cleanup is not authorization to apply. Permit cleanup of an expired proof without renewing it.
App\Services\M51LocalTargetGuard::assertFreshProof($proof, $proofName, $at);
$savedRoutes = $read($dir.'/'.$proof['routes_before_file']);
if (!hash_equals($proof['routes_before_sha256'], hash('sha256', $savedRoutes))
    || $savedRoutes !== $routesBefore || $hashesBefore !== $proof['physical_hashes']) {
    throw new RuntimeException('M51 routes/foreign bytes are not exactly restored or physical configuration changed.');
}
$cleanupName = 'cleanup-'.$nonce.'.json';
if (file_exists($dir.'/'.$cleanupName) || is_link($dir.'/'.$cleanupName)) {
    throw new RuntimeException('M51 cleanup receipt already exists; no overwrite.');
}
$url = 'http://gramlyze.loc/api/_local/m51-target-'.$nonce;
$curl = curl_init($url);
curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => false, CURLOPT_MAXREDIRS => 0,
    CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_TIMEOUT => 15, CURLOPT_PROXY => '',
    CURLOPT_RESOLVE => ['gramlyze.loc:80:127.0.0.1'], CURLOPT_HTTPHEADER => ['Accept: application/json']]);
try {
    $body = curl_exec($curl); $status = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
    if (!is_string($body) || $status !== 404 || curl_getinfo($curl, CURLINFO_PRIMARY_IP) !== '127.0.0.1'
        || curl_getinfo($curl, CURLINFO_EFFECTIVE_URL) !== $url || curl_getinfo($curl, CURLINFO_REDIRECT_COUNT) !== 0) {
        throw new RuntimeException('M51 removed nonce endpoint did not return an exact anonymous loopback 404.');
    }
} finally { curl_close($curl); }
if ($read($apiPath) !== $savedRoutes || $physicalHashes() !== $hashesBefore) {
    throw new RuntimeException('M51 routes or physical configuration changed during the cleanup GET.');
}
$receipt = ['target' => 'gramlyze.loc', 'nonce' => $nonce, 'checked_at' => gmdate('c'),
    'proof_file' => $proofName, 'proof_sha256' => $expectedProofSha, 'proof_created_at' => $proof['created_at'],
    'proof_renewed' => false, 'proof_fresh_for_apply_at_cleanup' => time() - $at <= 1800,
    'route_snapshot_file' => $proof['routes_before_file'], 'routes_before_sha256' => $proof['routes_before_sha256'],
    'routes_after_sha256' => hash('sha256', $savedRoutes), 'exact_route_bytes_restored' => true,
    'physical_hashes_unchanged' => true, 'removed_endpoint_status' => 404, 'redirects_followed' => 0,
    'physical' => $physical, 'runtime' => $runtime, 'route_writes' => 0, 'db_writes' => 0,
    'write_policy' => 'Exclusive private receipt only. No route removal performed by script; exact foreign-inclusive restoration and anonymous loopback GET 404 independently checked.'];
$exclusive($dir.'/'.$cleanupName, $json($receipt));
echo $json(['nonce' => $nonce, 'file' => $cleanupName, 'sha256' => hash_file('sha256', $dir.'/'.$cleanupName)]);
