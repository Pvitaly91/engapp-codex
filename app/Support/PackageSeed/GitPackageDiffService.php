<?php

namespace App\Support\PackageSeed;

use Symfony\Component\Process\Process;

class GitPackageDiffService
{
    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function collect(string $scopeRelativePath, array $options = []): array
    {
        $resolvedOptions = $this->normalizeOptions($options);
        $this->resolveRepositoryRoot();

        $entries = match ($resolvedOptions['mode']) {
            'refs' => $this->parseNameStatusOutput(
                $this->runGitCommand([
                    'diff',
                    '--name-status',
                    '-z',
                    '--find-renames=50%',
                    $resolvedOptions['base'],
                    $resolvedOptions['head'],
                    '--',
                    $scopeRelativePath,
                ]),
                $resolvedOptions['historical_ref']
            ),
            'staged' => $this->parseNameStatusOutput(
                $this->runGitCommand([
                    'diff',
                    '--cached',
                    '--name-status',
                    '-z',
                    '--find-renames=50%',
                    '--',
                    $scopeRelativePath,
                ]),
                $resolvedOptions['historical_ref']
            ),
            default => $this->parseNameStatusOutput(
                $this->runGitCommand([
                    'diff',
                    '--name-status',
                    '-z',
                    '--find-renames=50%',
                    'HEAD',
                    '--',
                    $scopeRelativePath,
                ]),
                $resolvedOptions['historical_ref']
            ),
        };

        if ($resolvedOptions['include_untracked']) {
            $entries = array_merge(
                $entries,
                $this->parseUntrackedOutput($this->runGitCommand([
                    'ls-files',
                    '--others',
                    '--exclude-standard',
                    '-z',
                    '--',
                    $scopeRelativePath,
                ]))
            );
        }

        return [
            'mode' => $resolvedOptions['mode'],
            'base' => $resolvedOptions['base'],
            'head' => $resolvedOptions['head'],
            'historical_ref' => $resolvedOptions['historical_ref'],
            'include_untracked' => $resolvedOptions['include_untracked'],
            'entries' => array_values($entries),
        ];
    }

    public function showFile(string $ref, string $relativePath): ?string
    {
        $resolvedRef = trim($ref);
        $resolvedPath = $this->normalizeRelativePath($relativePath);

        if ($resolvedRef === '' || $resolvedPath === '') {
            return null;
        }

        $result = $this->runGitProcess(['show', $resolvedRef . ':' . $resolvedPath], true);

        if ($result['exit_code'] === 0) {
            return $result['stdout'];
        }

        $errorOutput = strtolower(trim($result['stderr']));

        if (
            str_contains($errorOutput, 'does not exist in')
            || str_contains($errorOutput, 'exists on disk, but not in')
            || str_contains($errorOutput, 'path \'' . strtolower($resolvedPath) . '\' does not exist')
        ) {
            return null;
        }

        throw new \RuntimeException(trim($result['stderr']) !== ''
            ? trim($result['stderr'])
            : sprintf('git show failed for [%s:%s].', $resolvedRef, $resolvedPath));
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    protected function normalizeOptions(array $options): array
    {
        $base = trim((string) ($options['base'] ?? ''));
        $head = trim((string) ($options['head'] ?? ''));
        $staged = (bool) ($options['staged'] ?? false);
        $workingTree = (bool) ($options['working_tree'] ?? false);
        $includeUntracked = (bool) ($options['include_untracked'] ?? false);
        $hasRefMode = $base !== '' || $head !== '';

        if ($head !== '' && $base === '') {
            throw new \RuntimeException('Changed-package planning requires --base when --head is provided.');
        }

        if ($base !== '' && $head === '') {
            $head = 'HEAD';
        }

        if ($staged && $workingTree) {
            throw new \RuntimeException('Changed-package planning cannot use --staged and --working-tree together.');
        }

        if ($hasRefMode && ($staged || $workingTree)) {
            throw new \RuntimeException('Changed-package planning cannot mix ref diff mode with --staged or --working-tree.');
        }

        $mode = match (true) {
            $hasRefMode => 'refs',
            $staged => 'staged',
            default => 'working_tree',
        };

        return [
            'mode' => $mode,
            'base' => $mode === 'refs' ? $base : null,
            'head' => $mode === 'refs' ? $head : ($mode === 'working_tree' ? 'HEAD' : null),
            'historical_ref' => $mode === 'refs' ? $base : 'HEAD',
            'include_untracked' => $includeUntracked,
        ];
    }

    protected function resolveRepositoryRoot(): string
    {
        $result = $this->runGitProcess(['rev-parse', '--show-toplevel'], true);

        if ($result['exit_code'] !== 0) {
            throw new \RuntimeException(trim($result['stderr']) !== ''
                ? trim($result['stderr'])
                : 'The current workspace is not an initialized git repository.');
        }

        return trim($result['stdout']);
    }

    protected function runGitCommand(array $arguments): string
    {
        $result = $this->runGitProcess($arguments, true);

        if ($result['exit_code'] !== 0) {
            throw new \RuntimeException(trim($result['stderr']) !== ''
                ? trim($result['stderr'])
                : 'git command failed.');
        }

        return $result['stdout'];
    }

    /**
     * @param  array<int, string>  $arguments
     * @return array{stdout:string,stderr:string,exit_code:int}
     */
    protected function runGitProcess(array $arguments, bool $captureBinary = false): array
    {
        $process = new Process(array_merge(['git'], $arguments), base_path());
        $process->setTimeout(30);
        $process->run();

        return [
            'stdout' => $captureBinary ? $process->getOutput() : trim($process->getOutput()),
            'stderr' => trim($process->getErrorOutput()),
            'exit_code' => (int) $process->getExitCode(),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function parseNameStatusOutput(string $output, ?string $historicalRef): array
    {
        $trimmedOutput = rtrim($output, "\0");

        if ($trimmedOutput === '') {
            return [];
        }

        $tokens = explode("\0", $trimmedOutput);
        $entries = [];
        $index = 0;

        while ($index < count($tokens)) {
            $rawStatus = (string) ($tokens[$index] ?? '');
            $index++;

            if ($rawStatus === '') {
                continue;
            }

            $status = strtoupper(substr($rawStatus, 0, 1));

            if (in_array($status, ['R', 'C'], true)) {
                $oldPath = $this->normalizeRelativePath((string) ($tokens[$index] ?? ''));
                $newPath = $this->normalizeRelativePath((string) ($tokens[$index + 1] ?? ''));
                $index += 2;

                $entries[] = [
                    'raw_status' => $rawStatus,
                    'status' => $status,
                    'old_path' => $oldPath !== '' ? $oldPath : null,
                    'new_path' => $newPath !== '' ? $newPath : null,
                    'historical_ref' => $historicalRef,
                    'untracked' => false,
                ];

                continue;
            }

            $path = $this->normalizeRelativePath((string) ($tokens[$index] ?? ''));
            $index++;

            $entries[] = [
                'raw_status' => $rawStatus,
                'status' => $status,
                'old_path' => $status === 'D' ? ($path !== '' ? $path : null) : null,
                'new_path' => $status === 'D' ? null : ($path !== '' ? $path : null),
                'historical_ref' => $historicalRef,
                'untracked' => false,
            ];
        }

        return array_values(array_filter($entries, static function (array $entry): bool {
            return trim((string) ($entry['old_path'] ?? '')) !== ''
                || trim((string) ($entry['new_path'] ?? '')) !== '';
        }));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function parseUntrackedOutput(string $output): array
    {
        $trimmedOutput = rtrim($output, "\0");

        if ($trimmedOutput === '') {
            return [];
        }

        $paths = array_values(array_filter(array_map(
            fn (string $path): string => $this->normalizeRelativePath($path),
            explode("\0", $trimmedOutput)
        )));

        return array_values(array_map(static fn (string $path): array => [
            'raw_status' => 'A',
            'status' => 'A',
            'old_path' => null,
            'new_path' => $path,
            'historical_ref' => null,
            'untracked' => true,
        ], array_unique($paths)));
    }

    protected function normalizeRelativePath(string $path): string
    {
        $normalized = str_replace('\\', '/', trim($path));
        $normalized = preg_replace('#/+#', '/', $normalized) ?? $normalized;

        return trim($normalized, '/');
    }
}
