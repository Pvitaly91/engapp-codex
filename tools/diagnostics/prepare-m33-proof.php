<?php

// Generate private nonce evidence and preserve raw routes bytes. No routes or DB writes.
if (PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows') { exit(1); }
$root = 'D:/DEV/htdocs/gramlyze.loc';
$dir = $root.'/storage/app/seo-m33-local';
if (!is_dir($dir) || is_link($dir)) { throw new RuntimeException('Private evidence directory must exist.'); }
$nonce = bin2hex(random_bytes(16));
$bytes = file_get_contents($root.'/routes/api.php');
if ($bytes === false || str_contains($bytes, 'm33-target-')) { throw new RuntimeException('Routes unavailable or M33 proof already present.'); }
$exclusive = static function (string $path, string $data): void {
    $f = fopen($path, 'x');
    if (!$f) { throw new RuntimeException('Exclusive evidence collision.'); }
    try { if (fwrite($f, $data) !== strlen($data) || !fflush($f)) { throw new RuntimeException('Incomplete evidence.'); } } finally { fclose($f); }
};
$exclusive($dir.'/routes-before-'.$nonce.'.bin', $bytes);
$proof = 'local-proof-'.$nonce.'.json';
$exclusive($dir.'/'.$proof, json_encode(['target' => 'gramlyze.loc', 'nonce' => $nonce], JSON_THROW_ON_ERROR));
echo json_encode(['proof' => $proof, 'nonce' => $nonce, 'routes_before_sha256' => hash('sha256', $bytes)], JSON_THROW_ON_ERROR)."\n";
