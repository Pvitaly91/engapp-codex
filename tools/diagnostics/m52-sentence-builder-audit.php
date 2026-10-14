<?php

/** Explicit read-only existing registry gate with an exclusive private JSON receipt. */
if (PHP_SAPI !== 'cli' || count($argv) !== 2 || !in_array($argv[1], ['m52', 'registry'], true)) { throw new RuntimeException('Explicit M52 audit scope required.'); }
$root = 'D:/DEV/htdocs/gramlyze.loc';
if (strtolower(str_replace('\\', '/', (string) realpath(dirname(__DIR__, 2)))) !== strtolower($root)) { throw new RuntimeException('M52 audit ROOT differs.'); }
require $root.'/vendor/autoload.php'; $app = require $root.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
App\Services\M52LocalTargetGuard::assertConfiguredDatabase(Illuminate\Support\Facades\DB::connection());
$options = ['--target' => 'gramlyze.loc'];
if ($argv[1] === 'm52') { $options['--source'] = ['m52-passive-basics.v1.json']; }
$exit = Illuminate\Support\Facades\Artisan::call('theory:audit-sentence-builders', $options);
$output = Illuminate\Support\Facades\Artisan::output();
$data = json_decode($output, true, flags: JSON_THROW_ON_ERROR);
if ($exit !== 0 || ($data['failed'] ?? null) !== 0 || ($data['writes'] ?? null) !== 0
    || ($argv[1] === 'm52' && ($data['targets'] ?? null) !== 3)) { throw new RuntimeException('M52 sentence-builder gate did not pass; no writes.'); }
$file = $root.'/storage/app/seo-m52-local/'.$argv[1].'-sentence-builders-v1.json';
if (is_link($file) || file_exists($file)) { throw new RuntimeException('M52 audit receipt exists; no overwrite.'); }
$bytes = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
$handle = fopen($file, 'x'); if (!$handle) { throw new RuntimeException('M52 audit receipt unavailable.'); }
try { if (fwrite($handle, $bytes) !== strlen($bytes) || !fflush($handle)) { throw new RuntimeException('M52 audit receipt incomplete.'); } } finally { fclose($handle); }
echo json_encode(['scope' => $argv[1], 'targets' => $data['targets'], 'failed' => $data['failed'], 'writes' => 0,
    'file' => basename($file), 'sha256' => hash('sha256', $bytes)], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR)."\n";
