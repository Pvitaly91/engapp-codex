<?php

if (PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows') { exit(1); }
$root = 'D:/DEV/htdocs/gramlyze.loc'; $dir = $root.'/storage/app/ppc-quality-local';
if (!is_dir($dir) || is_link($dir)) { throw new RuntimeException('Missing private local directory.'); }
$nonce = bin2hex(random_bytes(16)); $bytes = file_get_contents($root.'/routes/api.php');
if (str_contains($bytes, 'ppc-quality-target-')) { throw new RuntimeException('Proof route already present.'); }
foreach (['routes-before-'.$nonce.'.bin' => $bytes, 'local-proof-'.$nonce.'.json' => json_encode(['target' => 'gramlyze.loc', 'nonce' => $nonce])] as $name => $value) {
    $f = fopen($dir.'/'.$name, 'x'); if (!$f) { throw new RuntimeException('Exclusive proof collision.'); }
    if (fwrite($f, $value) !== strlen($value) || !fflush($f)) { throw new RuntimeException('Proof write incomplete.'); } fclose($f);
}
echo json_encode(['nonce' => $nonce, 'proof' => 'local-proof-'.$nonce.'.json', 'routes_before_sha256' => hash('sha256', $bytes)])."\n";
