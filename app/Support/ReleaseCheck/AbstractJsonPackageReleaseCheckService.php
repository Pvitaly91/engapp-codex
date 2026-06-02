<?php

namespace App\Support\ReleaseCheck;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

abstract class AbstractJsonPackageReleaseCheckService
{
    protected const STATUS_PASS = 'pass';

    protected const STATUS_WARN = 'warn';

    protected const STATUS_FAIL = 'fail';

    abstract protected function packageRootRelativePath(): string;

    abstract protected function reportDirectory(): string;

    /**
     * @return array<int, string>
     */
    abstract protected function expectedLocales(): array;

    protected function normalizeProfile(string $profile): string
    {
        $normalized = strtolower(trim($profile));

        if (! in_array($normalized, ['scaffold', 'release'], true)) {
            throw new RuntimeException('Unsupported release-check profile. Use scaffold or release.');
        }

        return $normalized;
    }

    /**
     * @return array<string, mixed>
     */
    protected function resolvePackageTarget(string $input): array
    {
        $absoluteInputPath = $this->resolveExistingAbsolutePath($input);
        $rootAbsolutePath = $this->rootAbsolutePath();
        $this->assertPathWithinRoot($absoluteInputPath, $rootAbsolutePath);

        $packageRootAbsolutePath = $this->resolvePackageRootAbsolutePath($absoluteInputPath);
        $this->assertPathWithinRoot($packageRootAbsolutePath, $rootAbsolutePath);

        $className = basename($packageRootAbsolutePath);
        $loaderAbsolutePath = $this->normalizePath(dirname($packageRootAbsolutePath) . DIRECTORY_SEPARATOR . $className . '.php');
        $realSeederAbsolutePath = $this->normalizePath($packageRootAbsolutePath . DIRECTORY_SEPARATOR . $className . '.php');
        $definitionAbsolutePath = $this->normalizePath($packageRootAbsolutePath . DIRECTORY_SEPARATOR . 'definition.json');

        return [
            'input' => trim($input),
            'definition_absolute_path' => $definitionAbsolutePath,
            'definition_relative_path' => $this->relativePath($definitionAbsolutePath),
            'package_root_absolute_path' => $packageRootAbsolutePath,
            'package_root_relative_path' => $this->relativePath($packageRootAbsolutePath),
            'loader_absolute_path' => $loaderAbsolutePath,
            'real_seeder_absolute_path' => $realSeederAbsolutePath,
            'localizations' => $this->expectedLocalizationPaths($packageRootAbsolutePath),
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $checks
     * @return array<string, mixed>
     */
    protected function summarizeChecks(array $checks): array
    {
        $counts = [
            self::STATUS_PASS => 0,
            self::STATUS_WARN => 0,
            self::STATUS_FAIL => 0,
        ];

        foreach ($checks as $check) {
            $status = strtolower((string) ($check['status'] ?? self::STATUS_FAIL));

            if (! array_key_exists($status, $counts)) {
                $status = self::STATUS_FAIL;
            }

            $counts[$status]++;
        }

        return [
            'fully_valid' => $counts[self::STATUS_WARN] === 0 && $counts[self::STATUS_FAIL] === 0,
            'check_counts' => $counts,
        ];
    }

    /**
     * @param  array<string, mixed>  $report
     */
    public function writeReport(array $report): string
    {
        $packageRelativePath = (string) ($report['target']['package_root_relative_path'] ?? 'package');
        $packageName = basename(str_replace('\\', '/', $packageRelativePath));
        $hash = substr(sha1($packageRelativePath), 0, 8);
        $fileName = Str::slug($packageName !== '' ? $packageName : 'package')
            . '-'
            . (($report['profile'] ?? 'release') === 'scaffold' ? 'scaffold' : 'release')
            . '-'
            . $hash
            . '.md';
        $relativePath = trim($this->reportDirectory(), '/') . '/' . $fileName;

        Storage::disk('local')->put($relativePath, $this->renderMarkdownReport($report));

        return $relativePath;
    }

    /**
     * @param  array<string, mixed>  $report
     */
    protected function renderMarkdownReport(array $report): string
    {
        $lines = [
            '# JSON Package Release Check',
            '',
            '- Profile: `' . ($report['profile'] ?? 'release') . '`',
            '- Definition: `' . ($report['target']['definition_relative_path'] ?? '') . '`',
            '- Package Root: `' . ($report['target']['package_root_relative_path'] ?? '') . '`',
            '- Loader: `' . $this->relativePath((string) ($report['target']['loader_absolute_path'] ?? '')) . '`',
            '- Real Seeder: `' . $this->relativePath((string) ($report['target']['real_seeder_absolute_path'] ?? '')) . '`',
            '',
            '## Checks',
            '',
        ];

        foreach ((array) ($report['checks'] ?? []) as $check) {
            $meta = $this->formatMeta((array) ($check['meta'] ?? []));
            $lines[] = sprintf(
                '- [%s] %s%s',
                strtoupper((string) ($check['status'] ?? self::STATUS_FAIL)),
                (string) ($check['label'] ?? 'Unnamed check'),
                $meta !== '' ? ' — ' . $meta : ''
            );
        }

        $lines[] = '';
        $lines[] = '## Summary';
        $lines[] = '';
        $lines[] = sprintf(
            '- %d PASS / %d WARN / %d FAIL',
            (int) ($report['summary']['check_counts'][self::STATUS_PASS] ?? 0),
            (int) ($report['summary']['check_counts'][self::STATUS_WARN] ?? 0),
            (int) ($report['summary']['check_counts'][self::STATUS_FAIL] ?? 0)
        );

        return implode(PHP_EOL, $lines) . PHP_EOL;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    protected function loadJsonFile(string $absolutePath, string $label): array
    {
        if (! File::exists($absolutePath)) {
            return [
                'data' => null,
                'error' => $label . ' not found.',
            ];
        }

        try {
            $decoded = json_decode(File::get($absolutePath), true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $exception) {
            return [
                'data' => null,
                'error' => $label . ' contains invalid JSON: ' . $exception->getMessage(),
            ];
        }

        if (! is_array($decoded)) {
            return [
                'data' => null,
                'error' => $label . ' must decode to a JSON object.',
            ];
        }

        return [
            'data' => $decoded,
            'error' => null,
        ];
    }

    /**
     * @param  array<string, mixed>  $meta
     * @return array<string, mixed>
     */
    protected function makeCheck(string $status, string $code, string $label, array $meta = []): array
    {
        return [
            'status' => $status,
            'code' => $code,
            'label' => $label,
            'meta' => $meta,
        ];
    }

    protected function readinessStatus(bool $isReady, string $profile): string
    {
        if ($isReady) {
            return self::STATUS_PASS;
        }

        return $profile === 'scaffold' ? self::STATUS_WARN : self::STATUS_FAIL;
    }

    protected function rootAbsolutePath(): string
    {
        $path = base_path($this->packageRootRelativePath());

        return $this->normalizePath($path);
    }

    protected function relativePath(?string $absolutePath): ?string
    {
        $normalized = trim((string) $absolutePath);

        if ($normalized === '') {
            return null;
        }

        $projectRoot = $this->normalizePath(base_path());
        $path = $this->normalizePath($normalized);

        if ($path === $projectRoot) {
            return '.';
        }

        if (Str::startsWith($path, $projectRoot . '/')) {
            return Str::after($path, $projectRoot . '/');
        }

        return $path;
    }

    protected function normalizePath(string $path): string
    {
        $normalized = str_replace('\\', '/', $path);
        $normalized = preg_replace('#/+#', '/', $normalized) ?? $normalized;

        if (preg_match('#^[A-Za-z]:/$#', $normalized) === 1) {
            return strtoupper(substr($normalized, 0, 1)) . substr($normalized, 1);
        }

        return rtrim($normalized, '/');
    }

    protected function isPathWithinRoot(string $absolutePath, string $rootAbsolutePath): bool
    {
        $path = $this->normalizePath($absolutePath);
        $root = $this->normalizePath($rootAbsolutePath);

        return $path === $root || Str::startsWith($path, $root . '/');
    }

    protected function assertPathWithinRoot(string $absolutePath, string $rootAbsolutePath): void
    {
        if ($this->isPathWithinRoot($absolutePath, $rootAbsolutePath)) {
            return;
        }

        throw new RuntimeException(sprintf(
            'Target must stay inside %s.',
            $this->packageRootRelativePath()
        ));
    }

    protected function resolveExistingAbsolutePath(string $input): string
    {
        $normalizedInput = trim($input);

        if ($normalizedInput === '') {
            throw new RuntimeException('Target path is required.');
        }

        $candidatePath = $this->isAbsolutePath($normalizedInput)
            ? $normalizedInput
            : base_path($normalizedInput);
        $normalizedCandidatePath = $this->normalizePath($candidatePath);

        if (! File::exists($normalizedCandidatePath)) {
            throw new RuntimeException(sprintf(
                'Target [%s] does not exist.',
                $normalizedInput
            ));
        }

        $realPath = realpath($normalizedCandidatePath);

        return $this->normalizePath($realPath !== false ? $realPath : $normalizedCandidatePath);
    }

    protected function resolvePackageRootAbsolutePath(string $absoluteInputPath): string
    {
        if (File::isDirectory($absoluteInputPath)) {
            return $absoluteInputPath;
        }

        $extension = strtolower(pathinfo($absoluteInputPath, PATHINFO_EXTENSION));

        if ($extension === 'json' && strtolower(basename($absoluteInputPath)) === 'definition.json') {
            return $this->normalizePath(dirname($absoluteInputPath));
        }

        if ($extension !== 'php') {
            throw new RuntimeException(
                'Target must point to a package directory, definition.json, top-level loader stub PHP, or real seeder PHP.'
            );
        }

        $className = pathinfo($absoluteInputPath, PATHINFO_FILENAME);
        $parentDirectory = $this->normalizePath(dirname($absoluteInputPath));
        $parentName = basename($parentDirectory);

        if ($parentName === $className) {
            return $parentDirectory;
        }

        return $this->normalizePath($parentDirectory . DIRECTORY_SEPARATOR . $className);
    }

    protected function expectedLocalizationPaths(string $packageRootAbsolutePath): array
    {
        $packageRoot = $this->normalizePath($packageRootAbsolutePath);
        $paths = [];

        foreach ($this->expectedLocales() as $locale) {
            $paths[$locale] = $packageRoot . '/localizations/' . strtolower($locale) . '.json';
        }

        return $paths;
    }

    /**
     * @return array<int, array{path:string,value:string}>
     */
    protected function placeholderMatches(mixed $payload, string $path = 'root'): array
    {
        if (is_array($payload)) {
            $matches = [];

            foreach ($payload as $key => $value) {
                $matches = array_merge(
                    $matches,
                    $this->placeholderMatches($value, $path . '.' . (string) $key)
                );
            }

            return $matches;
        }

        if (! is_string($payload)) {
            return [];
        }

        if (! preg_match('/<\s*resolved[-_]|<\s*Resolved/', $payload)) {
            return [];
        }

        return [[
            'path' => $path,
            'value' => $payload,
        ]];
    }

    protected function isAbsolutePath(string $path): bool
    {
        return preg_match('#^[A-Za-z]:[\\\\/]#', $path) === 1
            || Str::startsWith($path, ['/', '\\\\']);
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    protected function formatMeta(array $meta): string
    {
        $parts = [];

        foreach ($meta as $key => $value) {
            if ($value === null || $value === [] || $value === '') {
                continue;
            }

            $parts[] = sprintf(
                '%s=%s',
                $key,
                is_scalar($value)
                    ? (string) $value
                    : json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
            );
        }

        return implode('; ', $parts);
    }
}
