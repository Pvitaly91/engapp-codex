<?php

// Explicit private evidence preparation only: no route edit, HTTP, Laravel bootstrap or DB access.
if (PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows' || count($argv) !== 2) { exit(1); }
$source = dirname(__DIR__, 2);
require_once $source.'/app/Services/M11LocalTargetGuard.php';
require_once $source.'/app/Services/M44LocalTargetGuard.php';
use App\Services\M44LocalTargetGuard as Guard;

$physicalName = $argv[1];
$physical = Guard::loadPhysicalCapture($physicalName);
$directory = Guard::privateDirectory();
$nonce = bin2hex(random_bytes(16));
$routeFile = Guard::ROOT.'/routes/api.php';
if (!is_file($routeFile) || is_link($routeFile)) { throw new RuntimeException('M44 routes are unavailable or redirected.'); }
$bytes = file_get_contents($routeFile);
if ($bytes === false || str_contains($bytes, 'm44-target-')) { throw new RuntimeException('M44 proof route already present or routes unavailable.'); }
$exclusive = static function (string $file, string $data): void {
    $handle = fopen($file, 'x');
    if (!$handle) { throw new RuntimeException('M44 exclusive evidence collision.'); }
    try {
        if (fwrite($handle, $data) !== strlen($data) || !fflush($handle)) { throw new RuntimeException('Incomplete M44 evidence.'); }
    } finally { fclose($handle); }
};
$proof = ['target' => 'gramlyze.loc', 'nonce' => $nonce, 'created_at' => gmdate('c'), 'scope' => Guard::SCOPE,
    'physical_basename' => $physicalName, 'physical_sha256' => $physical['sha256'], 'routes_before_sha256' => hash('sha256', $bytes)];
$name = 'local-proof-'.$nonce.'.json';
Guard::assertProofMetadata($proof, $name);
$exclusive($directory.'/routes-before-'.$nonce.'.bin', $bytes);
$exclusive($directory.'/'.$name, json_encode($proof, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n");
echo json_encode(['proof' => $name, 'nonce' => $nonce, 'physical_basename' => $physicalName,
    'physical_sha256' => $physical['sha256'], 'routes_before_sha256' => $proof['routes_before_sha256'], 'scope' => Guard::SCOPE],
    JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
