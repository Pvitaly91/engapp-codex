<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Optional PERF-31 FPM diagnosis only. Unqualified calls outside the narrow
 * native source boundary are passed through unchanged and uncounted.
 */
final class Perf31NativeSourceProbe
{
    private const ENGINE = 'App\Support\TheoryNativeContentRelease';
    private const MEMO = 'App\Support\ValidatedNativeContentSources';
    private const LABEL = 'M(?:5[3-9]|[678][0-9]|9[0-3])';
    private static string $root = '';
    private static bool $captureFailed = false;
    private static array $lastRead = [];
    private static array $metrics = [];

    public static function enable(string $realSourceRoot): void
    {
        self::$root = $realSourceRoot.'/';
        self::$lastRead = [];
        self::$metrics = [];
        self::$captureFailed = false;
    }

    public static function enabled(): bool
    {
        return self::$root !== '';
    }

    public static function family(string $filename): ?string
    {
        if (!self::enabled() || !str_starts_with($filename, self::$root)) {
            return null;
        }
        $relative = substr($filename, strlen(self::$root));
        if (preg_match('~^(?:database/content-patches|docs/content(?:/theory-sentence-builders)?)/'
            .'(m(?:5[3-9]|[678][0-9]|9[0-3]))-[a-z0-9.-]+[.]json$~D', $relative, $matches) !== 1) {
            return null;
        }
        return strtoupper($matches[1]);
    }

    /** Only exact engine frames and finite native release class families. */
    private static function caller(array $trace): ?array
    {
        $direct = $trace[1] ?? [];
        $class = $direct['class'] ?? '';
        $method = $direct['function'] ?? '';
        if ($class === self::ENGINE) {
            $load = false;
            $memo = false;
            foreach ($trace as $frame) {
                $type = $frame['class'] ?? '';
                $fn = $frame['function'] ?? '';
                $load = $load || ($type === self::ENGINE && $fn === 'load');
                $memo = $memo || ($type === self::MEMO && $fn === 'read');
            }
            // Without a load frame, verifiedArtifactBytes under the memo
            // coordinator is the known verifyAll closure, not cold validation.
            return ['engine', $method, $load ? 'cold_load'
                : ($method === 'verifiedArtifactBytes' && $memo ? 'memo_raw_reverify' : 'engine_other')];
        }
        if (preg_match('~^App\\\\Support\\\\('.self::LABEL.')[A-Z][A-Za-z0-9]+$~D', $class, $m) === 1
            && in_array($method, ['readFrozen', 'load', 'profile', 'validate'], true)) {
            return [$m[1], $method, 'package'];
        }
        return null;
    }

    private static function count(string $family, string $phase, string $kind, int $ns, int $bytes = 0): void
    {
        $counter = &self::$metrics[$family][$phase][$kind];
        if (!isset($counter)) {
            $counter = ['calls' => 0, 'ns' => 0, 'bytes' => 0];
        }
        ++$counter['calls'];
        $counter['ns'] += max($ns, 0);
        $counter['bytes'] += $bytes;
    }

    public static function observedFile(string $family, string $operation, array $trace, int $ns, ?int $bytes): void
    {
        try {
            $caller = self::caller($trace);
            if ($caller === null || ($caller[0] !== 'engine' && $caller[0] !== $family)) {
                return;
            }
            if ($operation === 'file_read') {
                // Engine code immediately hashes the returned source bytes.
                if ($caller[0] === 'engine' && in_array($caller[1], ['load', 'verifiedArtifactBytes'], true)) {
                    self::$lastRead = $bytes === null ? [] : ['caller' => $caller, 'family' => $family, 'bytes' => $bytes];
                }
                self::count($family, $caller[2], 'file_read', $ns, $bytes ?? 0);
            } elseif ($operation === 'sha256_file') {
                // PHP hash_file opens the file itself; no second read is made.
                self::count($family, $caller[2], 'sha256_file', $ns);
            }
        } catch (\Throwable) {
            self::$captureFailed = true;
        }
    }

    public static function observedHash(array $trace, int $ns, int $bytes): void
    {
        try {
            $caller = self::caller($trace);
            if ($caller === null) {
                return;
            }
            if ($caller[0] === 'engine' && in_array($caller[1], ['load', 'verifiedArtifactBytes'], true)) {
                $last = self::$lastRead;
                self::$lastRead = [];
                if (($last['caller'] ?? null) === $caller && ($last['bytes'] ?? null) === $bytes) {
                    self::count($last['family'], $caller[2], 'sha256_raw_bytes', $ns, $bytes);
                    return;
                }
                self::count('engine_unpaired', $caller[2], 'sha256_unpaired', $ns, $bytes);
            } elseif ($caller[1] === 'validate') {
                self::count($caller[0] === 'engine' ? 'engine_validation' : $caller[0], 'validate', 'sha256_validation', $ns, $bytes);
            }
        } catch (\Throwable) {
            self::$captureFailed = true;
        }
    }

    /** Count only key existence; never inspect cached source values. */
    public static function memoKeys(\Illuminate\Http\Request $request): array
    {
        $keys = $request->attributes->keys();
        $count = 0;
        foreach ($keys as $key) {
            if (!is_string($key) || !str_starts_with($key, self::MEMO.':')) {
                continue;
            }
            $suffix = substr($key, strlen(self::MEMO) + 1);
            if (preg_match('~^(?:standalone_test|canonical_theory):'.self::LABEL.'$~D', $suffix) === 1) {
                ++$count;
            }
        }
        return [
            'm84StandaloneExists' => in_array(self::ENGINE.'.standalone-word-order-sources', $keys, true),
            'm90StandaloneExists' => in_array(self::ENGINE.'.standalone-past-perfect-sources', $keys, true),
            'finiteNativeKeyCount' => $count,
        ];
    }

    public static function snapshot(): array
    {
        return ['complete' => !self::$captureFailed, 'counts' => self::$metrics,
            'scope' => 'native source reads and SHA only; not full request validation or dataset time'];
    }
}

function hash(string $algo, string $data, bool $binary = false, array $options = []): string
{
    if (!Perf31NativeSourceProbe::enabled() || strcasecmp($algo, 'sha256') !== 0) {
        return \hash($algo, $data, $binary, $options);
    }
    $start = hrtime(true);
    try {
        return \hash($algo, $data, $binary, $options);
    } finally {
        $elapsed = hrtime(true) - $start;
        Perf31NativeSourceProbe::observedHash(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 16), $elapsed, strlen($data));
    }
}

function hash_file(string $algo, string $filename, bool $binary = false, array $options = []): string|false
{
    $family = Perf31NativeSourceProbe::family($filename);
    if ($family === null || strcasecmp($algo, 'sha256') !== 0) {
        return \hash_file($algo, $filename, $binary, $options);
    }
    $start = hrtime(true);
    try {
        return \hash_file($algo, $filename, $binary, $options);
    } finally {
        $elapsed = hrtime(true) - $start;
        Perf31NativeSourceProbe::observedFile($family, 'sha256_file', debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 16), $elapsed, null);
    }
}

function file_get_contents(string $filename, bool $use_include_path = false, $context = null,
    int $offset = 0, ?int $length = null): string|false
{
    $family = Perf31NativeSourceProbe::family($filename);
    if ($family === null) {
        return \file_get_contents($filename, $use_include_path, $context, $offset, $length);
    }
    $start = hrtime(true);
    $result = false;
    try {
        $result = \file_get_contents($filename, $use_include_path, $context, $offset, $length);
        return $result;
    } finally {
        $elapsed = hrtime(true) - $start;
        Perf31NativeSourceProbe::observedFile($family, 'file_read', debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 16),
            $elapsed, is_string($result) ? strlen($result) : null);
    }
}
