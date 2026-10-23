<?php

/**
 * Explicit, locally reviewed one-body M83 correction; never invoked by HTTP/startup.
 * Preview:
 * php tools/diagnostics/m83-inline-separation-patch.php --mode=preview --plan=inline-preview-v1.json --database=gr2 --local-target=gramlyze.loc --local-proof=local-proof-<32hex>.json
 * Apply (only after reviewing the exclusive preview and its digest):
 * php tools/diagnostics/m83-inline-separation-patch.php --mode=apply --plan=inline-preview-v1.json --backup=inline-backup-v1.json --reviewed-digest=<64hex> --database=gr2 --local-target=gramlyze.loc --local-proof=local-proof-<32hex>.json
 * No restore, seeding, route installation, source edit, environment override or progress write.
 */
$failClosed = static function (Throwable $error): void {
    $safe = $error instanceof RuntimeException && !$error instanceof PDOException
        ? $error->getMessage() : get_class($error).' (M83 correction incomplete; no successful receipt)';
    fwrite(STDERR, 'M83 inline separation: '.$safe."\n"); exit(1);
};
set_exception_handler($failClosed);
$options = [];
foreach (array_slice($argv, 1) as $argument) {
    if (!is_string($argument) || preg_match('/^--(mode|plan|backup|reviewed-digest|database|local-target|local-proof)=(.+)$/D', $argument, $match) !== 1
        || array_key_exists($match[1], $options)) {
        throw new RuntimeException('M83 correction requires unique explicit allowlisted --name=value arguments.');
    }
    $options[$match[1]] = $match[2];
}
$mode = $options['mode'] ?? '';
if (PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows' || !in_array($mode, ['preview', 'apply'], true)
    || ($options['database'] ?? null) !== 'gr2' || ($options['local-target'] ?? null) !== 'gramlyze.loc'
    || preg_match('/^local-proof-[a-f0-9]{32}\.json$/D', $options['local-proof'] ?? '') !== 1
    || preg_match('/^[a-zA-Z0-9_-]+\.json$/D', $options['plan'] ?? '') !== 1
    || ($mode === 'preview' && (isset($options['backup']) || isset($options['reviewed-digest'])))
    || ($mode === 'apply' && (preg_match('/^[a-zA-Z0-9_-]+\.json$/D', $options['backup'] ?? '') !== 1
        || preg_match('/^[a-f0-9]{64}$/D', $options['reviewed-digest'] ?? '') !== 1
        || $options['backup'] === $options['plan']))) {
    throw new RuntimeException('M83 correction requires explicit local preview/apply, private basenames, database and fresh proof.');
}
$root = 'D:/DEV/htdocs/gramlyze.loc';
$normal = static function (string $path): string {
    $real = realpath($path);
    if ($real === false) { throw new RuntimeException('M83 correction required local path is unavailable.'); }
    return rtrim(strtolower(str_replace('\\', '/', $real)), '/');
};
if ($normal(dirname(__DIR__, 2)) !== $normal($root)) {
    throw new RuntimeException('M83 correction CLI must run from actual served ROOT, never another checkout.');
}
$private = $root.'/storage/app/seo-m83-local';
if (!is_dir($private) || is_link($private) || $normal($private) !== $normal($root).'/storage/app/seo-m83-local') {
    throw new RuntimeException('M83 correction private directory is unavailable or unsafe.');
}
$planPath = $private.'/'.$options['plan'];
if ($mode === 'apply') {
    if (!is_file($planPath) || is_link($planPath)) { throw new RuntimeException('M83 correction reviewed preview is unavailable.'); }
    $plan = json_decode(file_get_contents($planPath), true, flags: JSON_THROW_ON_ERROR);
    $digest = $plan['sha256'] ?? null;
    if (!is_string($digest) || !hash_equals($options['reviewed-digest'], $digest)) {
        throw new RuntimeException('M83 correction explicit reviewed preview digest differs.');
    }
}
require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php';
try {
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
} catch (Throwable $error) {
    $failClosed($error);
}
// Laravel installs its own exception handler during bootstrap; CLI failures must exit 1.
set_exception_handler($failClosed);
if ($normal(base_path()) !== $normal($root) || $normal(app()->environmentPath()) !== $normal($root)) {
    throw new RuntimeException('M83 correction application/environment ROOT differs from served target.');
}
$db = Illuminate\Support\Facades\DB::connection();
App\Services\M49LocalTargetGuard::assertConfiguredDatabase($db); // Pure target check before opening PDO.
$service = new App\Services\M83InlineSeparationPatch($db, $root.'/database', $private,
    $options['local-target'], $options['local-proof']);
if ($mode === 'preview') {
    $plan = $service->savePlan($planPath);
    $result = ['status' => 'preview', 'state' => $plan['state'], 'updated' => count($plan['updates']),
        'inserted' => count($plan['inserts']), 'owners' => count($plan['pages']),
        'protected_tables' => count($plan['protected']), 'source_files' => count($plan['sources']),
        'plan' => $options['plan'], 'sha256' => $plan['sha256']];
} else {
    $result = $service->apply($planPath, $private.'/'.$options['backup'], $options['database']);
    if (isset($result['backup'])) { $result['backup'] = basename($result['backup']); }
}
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
