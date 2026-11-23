<?php

declare(strict_types=1);

namespace App\Support;

require __DIR__.'/ubuntu-native-source-probe.php';

// Deliberately no Laravel bootstrap, DB, or real FPM session. Only the
// namespaced hooks and finite frame matching are under test here.
final class TheoryNativeContentRelease
{
    public static function load(string $path): array
    {
        $raw = file_get_contents($path);

        return [hash(algo: 'sha256', data: $raw), hash_file(algo: 'sha256', filename: $path)];
    }

    public static function verifiedArtifactBytes(string $path): string
    {
        return hash('sha256', file_get_contents($path));
    }
}

final class ValidatedNativeContentSources
{
    public static function read(string $path): string
    {
        return TheoryNativeContentRelease::verifiedArtifactBytes($path);
    }
}

function check(bool $condition, string $name): void
{
    if (!$condition) {
        throw new \RuntimeException($name);
    }
}

$root = realpath(__DIR__.'/../..');
$path = $root.'/database/content-patches/m90-past-perfect-future-simple-basics.v1.json';
$expected = \hash_file('sha256', $path);
check($expected !== false, 'fixture exists');
check(TheoryNativeContentRelease::load($path) === [$expected, $expected], 'disabled parity');
check(Perf31NativeSourceProbe::snapshot()['counts'] === [], 'disabled is not measured');
Perf31NativeSourceProbe::enable($root);
check(TheoryNativeContentRelease::load($path) === [$expected, $expected], 'instrumented parity');
check(ValidatedNativeContentSources::read($path) === $expected, 'read-through parity');
$beforeUnmatched = Perf31NativeSourceProbe::snapshot();
check(hash_file('sha256', $path) === $expected, 'unmatched caller parity');
check(file_get_contents(filename: $path, offset: 2, length: 3) === \file_get_contents($path, false, null, 2, 3), 'named arguments');
check(Perf31NativeSourceProbe::snapshot() === $beforeUnmatched, 'unmatched callers are not measured');
check($beforeUnmatched['complete'], 'capture complete');
$recorded = $beforeUnmatched['counts']['M90'];
check($recorded['cold_load']['file_read']['bytes'] === filesize($path), 'counted file bytes');
check($recorded['cold_load']['sha256_raw_bytes']['calls'] === 1, 'cold raw SHA counted');
check($recorded['cold_load']['sha256_file']['calls'] === 1, 'file SHA counted');
check($recorded['memo_raw_reverify']['sha256_raw_bytes']['calls'] === 1, 'memo re-verification counted separately');
fwrite(STDOUT, "PERF-31 native probe contract PASS (isolated PHP CLI)\n");
