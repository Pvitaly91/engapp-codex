<?php

namespace App\Support\PackageSeed;

use App\Support\ReleaseCheck\AbstractJsonPackageReleaseCheckService;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

abstract class AbstractJsonPackageFolderDestroyFilesService extends AbstractJsonPackageReleaseCheckService
{
    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function run(string $targetInput, array $options = []): array
    {
        $resolvedOptions = $this->normalizeOptions($options);
        $planResult = $this->planFolder($targetInput, [
            'mode' => 'destroy-files',
            'strict' => false,
        ]);
        $result = $this->resultTemplate($targetInput, $resolvedOptions, $planResult);
        $result['preflight']['packages'] = $this->initialPreflightPackages(
            (array) ($result['plan']['packages'] ?? [])
        );
        $result['execution']['packages'] = $this->initialExecutionPackages(
            (array) ($result['plan']['packages'] ?? [])
        );

        if (is_array($result['plan']['error'] ?? null)) {
            $result['error'] = $result['plan']['error'];

            return $this->finalizeResult($result);
        }

        $result = $this->runPreflight($result, $resolvedOptions);

        if (is_array($result['error'] ?? null)) {
            return $this->finalizeResult($result);
        }

        if ((int) ($result['preflight']['summary']['candidates'] ?? 0) === 0) {
            return $this->finalizeResult($result);
        }

        if (! $resolvedOptions['dry_run'] && ! $resolvedOptions['force']) {
            $result['error'] = $this->forceRequiredError();

            return $this->finalizeResult($result);
        }

        if ($resolvedOptions['dry_run']) {
            return $this->finalizeResult($result);
        }

        return $this->finalizeResult($this->runExecution($result, $resolvedOptions));
    }

    /**
     * @param  array<string, mixed>  $result
     */
    public function writeReport(array $result): string
    {
        $scopeRelativePath = (string) ($result['scope']['resolved_root_relative_path'] ?? 'scope');
        $scopeName = basename(str_replace('\\', '/', $scopeRelativePath));
        $runType = (bool) ($result['execution']['dry_run'] ?? false) ? 'dry-run' : 'live';
        $suffix = (bool) ($result['execution']['remove_empty_dirs'] ?? false) ? '-prune' : '';
        $hash = substr(sha1($scopeRelativePath . '|' . $runType . '|' . $suffix), 0, 8);
        $fileName = Str::slug($scopeName !== '' ? $scopeName : 'scope')
            . '-destroy-files-'
            . $runType
            . $suffix
            . '-'
            . $hash
            . '.md';
        $relativePath = trim($this->reportDirectory(), '/') . '/' . $fileName;

        Storage::disk('local')->put($relativePath, $this->renderMarkdownReport($result));

        return $relativePath;
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    protected function normalizeOptions(array $options): array
    {
        return [
            'dry_run' => (bool) ($options['dry_run'] ?? false),
            'force' => (bool) ($options['force'] ?? false),
            'remove_empty_dirs' => (bool) ($options['remove_empty_dirs'] ?? false),
            'strict' => (bool) ($options['strict'] ?? false),
        ];
    }

    /**
     * @param  array<string, mixed>  $options
     * @param  array<string, mixed>  $planResult
     * @return array<string, mixed>
     */
    protected function resultTemplate(string $targetInput, array $options, array $planResult): array
    {
        $planScope = (array) ($planResult['scope'] ?? []);

        return [
            'scope' => [
                'input' => trim($targetInput),
                'resolved_root_absolute_path' => $planScope['resolved_root_absolute_path'] ?? null,
                'resolved_root_relative_path' => $planScope['resolved_root_relative_path'] ?? null,
                'single_package' => (bool) ($planScope['single_package'] ?? false),
                'mode' => (string) ($planScope['mode'] ?? 'destroy-files'),
                'strict' => (bool) $options['strict'],
            ],
            'plan' => [
                'summary' => (array) ($planResult['summary'] ?? $this->defaultPlanSummary()),
                'packages' => array_values((array) ($planResult['packages'] ?? [])),
                'error' => is_array($planResult['error'] ?? null) ? $planResult['error'] : null,
            ],
            'preflight' => [
                'executed' => false,
                'packages' => [],
                'summary' => $this->defaultPreflightSummary(),
            ],
            'execution' => [
                'dry_run' => (bool) $options['dry_run'],
                'force' => (bool) $options['force'],
                'remove_empty_dirs' => (bool) $options['remove_empty_dirs'],
                'fail_fast' => true,
                'package_atomic' => false,
                'folder_transactional' => false,
                'started' => 0,
                'completed' => 0,
                'succeeded' => 0,
                'failed' => 0,
                'stopped_on' => null,
                'packages' => [],
            ],
            'artifacts' => [
                'report_path' => null,
            ],
            'error' => null,
        ];
    }

    /**
     * @return array<string, int>
     */
    protected function defaultPlanSummary(): array
    {
        return [
            'total_packages' => 0,
            'seed_candidates' => 0,
            'refresh_candidates' => 0,
            'unseed_candidates' => 0,
            'destroy_files_candidates' => 0,
            'destroy_candidates' => 0,
            'skipped' => 0,
            'blocked' => 0,
            'warnings' => 0,
        ];
    }

    /**
     * @return array<string, int>
     */
    protected function defaultPreflightSummary(): array
    {
        return [
            'candidates' => 0,
            'ok' => 0,
            'warn' => 0,
            'fail' => 0,
            'skipped' => 0,
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $packages
     * @return array<int, array<string, mixed>>
     */
    protected function initialPreflightPackages(array $packages): array
    {
        return array_values(array_map(function (array $package): array {
            $action = (string) ($package['recommended_action'] ?? 'skip');

            return [
                'relative_path' => (string) ($package['relative_path'] ?? ''),
                'action' => $action,
                'status' => $action === 'destroy_files' ? 'pending' : 'skip',
                'delete_set' => [],
                'dir_prune_set' => [],
                'warnings' => array_values(array_map(
                    static fn ($warning): string => trim((string) $warning),
                    (array) ($package['warnings'] ?? [])
                )),
                'failures' => [],
            ];
        }, $packages));
    }

    /**
     * @param  array<int, array<string, mixed>>  $packages
     * @return array<int, array<string, mixed>>
     */
    protected function initialExecutionPackages(array $packages): array
    {
        return array_values(array_map(function (array $package): array {
            $action = (string) ($package['recommended_action'] ?? 'skip');

            return [
                'relative_path' => (string) ($package['relative_path'] ?? ''),
                'action' => $action,
                'executed' => false,
                'status' => match ($action) {
                    'skip' => 'skipped',
                    'blocked' => 'blocked',
                    default => 'pending',
                },
                'deleted_files' => [],
                'deleted_dirs' => [],
                'failed_paths' => [],
            ];
        }, $packages));
    }

    /**
     * @param  array<string, mixed>  $result
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    protected function runPreflight(array $result, array $options): array
    {
        $result['preflight']['executed'] = true;

        foreach ((array) ($result['plan']['packages'] ?? []) as $index => $package) {
            $action = (string) ($package['recommended_action'] ?? 'skip');

            if ($action !== 'destroy_files') {
                $result['preflight']['summary']['skipped']++;

                continue;
            }

            $result['preflight']['summary']['candidates']++;
            $preflight = $this->buildPreflightPackage($package, (array) ($result['scope'] ?? []), $options);
            $status = (string) ($preflight['status'] ?? 'fail');

            $result['preflight']['packages'][$index] = array_merge(
                (array) ($result['preflight']['packages'][$index] ?? []),
                $preflight
            );
            $result['preflight']['summary'][$status === 'skip' ? 'skipped' : $status]++;
        }

        $this->applySharedDeleteSetFailures($result);

        if ((int) ($result['preflight']['summary']['fail'] ?? 0) > 0) {
            $failedPackages = collect((array) ($result['preflight']['packages'] ?? []))
                ->filter(static fn (array $package): bool => ($package['status'] ?? null) === 'fail')
                ->pluck('relative_path')
                ->map(static fn ($path): string => trim((string) $path))
                ->filter()
                ->values()
                ->all();

            $result['execution']['stopped_on'] = $failedPackages[0] ?? null;
            $result['error'] = [
                'stage' => 'preflight',
                'reason' => 'preflight_failed',
                'message' => 'Folder file destroy preflight failed; live execution did not start.',
                'packages' => $failedPackages,
            ];

            return $result;
        }

        $blockedPackages = collect((array) ($result['plan']['packages'] ?? []))
            ->filter(static fn (array $package): bool => ($package['recommended_action'] ?? null) === 'blocked')
            ->pluck('relative_path')
            ->map(static fn ($path): string => trim((string) $path))
            ->filter()
            ->values()
            ->all();

        if ($blockedPackages !== []) {
            $result['execution']['stopped_on'] = $blockedPackages[0] ?? null;
            $result['error'] = [
                'stage' => 'planning',
                'reason' => 'blocked_packages',
                'message' => 'Planner found blocked packages; folder file destroy aborted before live deletion.',
                'packages' => $blockedPackages,
            ];

            return $result;
        }

        if ($options['strict'] ?? false) {
            $warningPackages = collect((array) ($result['preflight']['packages'] ?? []))
                ->filter(static fn (array $package): bool => ((array) ($package['warnings'] ?? [])) !== [])
                ->pluck('relative_path')
                ->map(static fn ($path): string => trim((string) $path))
                ->filter()
                ->values()
                ->all();

            if ($warningPackages !== []) {
                $result['execution']['stopped_on'] = $warningPackages[0] ?? null;
                $result['error'] = [
                    'stage' => 'preflight',
                    'reason' => 'warnings_are_fatal',
                    'message' => 'Folder file destroy returned warnings and --strict is enabled.',
                    'packages' => $warningPackages,
                ];
            }
        }

        return $result;
    }

    /**
     * @param  array<string, mixed>  $package
     * @param  array<string, mixed>  $scope
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    protected function buildPreflightPackage(array $package, array $scope, array $options): array
    {
        $packageRootAbsolutePath = $this->packageRootAbsolutePath($package);
        $scopeRootAbsolutePath = $this->normalizePath((string) ($scope['resolved_root_absolute_path'] ?? ''));
        $warnings = collect((array) ($package['warnings'] ?? []))
            ->map(static fn ($warning): string => trim((string) $warning))
            ->filter();
        $failures = collect();
        $deleteSet = [];
        $deleteSetAbsolute = [];

        foreach ($this->canonicalFileDescriptors($package) as $descriptor) {
            $absolutePath = $this->normalizePath((string) ($descriptor['absolute_path'] ?? ''));

            if ($absolutePath === '') {
                continue;
            }

            if (! $this->isPathWithinRoot($absolutePath, $packageRootAbsolutePath) && $absolutePath !== $this->loaderAbsolutePath($package)) {
                $failures->push(sprintf(
                    'Computed package file falls outside the resolved package root: %s.',
                    (string) ($this->relativePath($absolutePath) ?? $absolutePath)
                ));

                continue;
            }

            if (! $this->pathAllowedForScope($absolutePath, $scope, $package)) {
                $failures->push(sprintf(
                    'Computed package file falls outside the resolved subtree: %s.',
                    (string) ($this->relativePath($absolutePath) ?? $absolutePath)
                ));

                continue;
            }

            $existingPath = $this->existingComparablePath($absolutePath);

            if ($existingPath === null) {
                $warnings->push(sprintf(
                    'Expected package file is already missing: %s.',
                    (string) ($this->relativePath($absolutePath) ?? $absolutePath)
                ));

                continue;
            }

            if (File::isDirectory($existingPath)) {
                $failures->push(sprintf(
                    'Expected package file path resolves to a directory: %s.',
                    (string) ($this->relativePath($existingPath) ?? $existingPath)
                ));

                continue;
            }

            if (! $this->pathAllowedForScope($existingPath, $scope, $package)) {
                $failures->push(sprintf(
                    'Resolved package file leaves the allowed subtree: %s.',
                    (string) ($this->relativePath($existingPath) ?? $existingPath)
                ));

                continue;
            }

            $relativePath = (string) ($this->relativePath($existingPath) ?? $existingPath);
            $deleteSet[] = $relativePath;
            $deleteSetAbsolute[] = $existingPath;
        }

        $dirPruneSetAbsolute = $this->dirPruneCandidates($package, $scope, $options);
        $dirPruneSet = array_values(array_map(
            fn (string $path): string => (string) ($this->relativePath($path) ?? $path),
            $dirPruneSetAbsolute
        ));

        foreach ($this->additionalPreflightWarnings($package, $scope, $options) as $warning) {
            $normalizedWarning = trim((string) $warning);

            if ($normalizedWarning !== '') {
                $warnings->push($normalizedWarning);
            }
        }

        foreach ($this->additionalPreflightFailures($package, $scope, $options) as $failure) {
            $normalizedFailure = trim((string) $failure);

            if ($normalizedFailure !== '') {
                $failures->push($normalizedFailure);
            }
        }

        return [
            'relative_path' => (string) ($package['relative_path'] ?? ''),
            'action' => 'destroy_files',
            'status' => $failures->isNotEmpty() ? 'fail' : ($warnings->isNotEmpty() ? 'warn' : 'ok'),
            'delete_set' => array_values(array_unique($deleteSet)),
            'dir_prune_set' => $dirPruneSet,
            'warnings' => $warnings
                ->unique()
                ->values()
                ->all(),
            'failures' => $failures
                ->unique()
                ->values()
                ->all(),
            '_delete_set_absolute' => array_values(array_unique($deleteSetAbsolute)),
            '_dir_prune_set_absolute' => array_values(array_unique($dirPruneSetAbsolute)),
        ];
    }

    /**
     * @param  array<string, mixed>  $result
     */
    protected function applySharedDeleteSetFailures(array &$result): void
    {
        $this->applySharedDeleteSetFailuresToPackages(
            $result['preflight']['packages'],
            $result['preflight']['summary']
        );
    }

    /**
     * @param  array<int, array<string, mixed>>  $packages
     * @param  array<string, int>  $summary
     */
    protected function applySharedDeleteSetFailuresToPackages(array &$packages, array &$summary): void
    {
        $pathOwners = [];

        foreach ($packages as $index => $package) {
            if (($package['action'] ?? null) !== 'destroy_files') {
                continue;
            }

            foreach ((array) ($package['_delete_set_absolute'] ?? []) as $absolutePath) {
                $normalizedPath = $this->normalizePath((string) $absolutePath);

                if ($normalizedPath === '') {
                    continue;
                }

                $pathOwners[$normalizedPath] ??= [];
                $pathOwners[$normalizedPath][] = $index;
            }
        }

        foreach ($pathOwners as $absolutePath => $ownerIndexes) {
            if (count($ownerIndexes) <= 1) {
                continue;
            }

            foreach ($ownerIndexes as $ownerIndex) {
                $package = (array) ($packages[$ownerIndex] ?? []);
                $currentStatus = (string) ($package['status'] ?? 'fail');

                if (in_array($currentStatus, ['ok', 'warn'], true)) {
                    $summary[$currentStatus]--;
                    $summary['fail']++;
                }

                $failures = collect((array) ($package['failures'] ?? []))
                    ->push(sprintf(
                        'Computed delete set includes a file shared with another package in the same scope: %s.',
                        (string) ($this->relativePath($absolutePath) ?? $absolutePath)
                    ))
                    ->unique()
                    ->values()
                    ->all();

                $packages[$ownerIndex]['status'] = 'fail';
                $packages[$ownerIndex]['failures'] = $failures;
            }
        }
    }

    /**
     * @param  array<string, mixed>  $result
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    protected function runExecution(array $result, array $options): array
    {
        foreach ((array) ($result['plan']['packages'] ?? []) as $index => $package) {
            if (($package['recommended_action'] ?? null) !== 'destroy_files') {
                continue;
            }

            $result['execution']['started']++;
            $result['execution']['packages'][$index]['executed'] = true;
            $packageExecution = $this->executePackageDeletion(
                (array) ($result['preflight']['packages'][$index] ?? []),
                (array) ($result['scope'] ?? []),
                $options
            );
            $result['execution']['completed']++;
            $result['execution']['packages'][$index] = array_merge(
                (array) ($result['execution']['packages'][$index] ?? []),
                $packageExecution
            );

            if (($packageExecution['status'] ?? null) === 'ok') {
                $result['execution']['succeeded']++;

                continue;
            }

            $result['execution']['failed']++;
            $result['execution']['stopped_on'] = (string) ($package['relative_path'] ?? null);
            $result['error'] = [
                'stage' => 'execution',
                'reason' => 'package_failed',
                'message' => 'Folder file destroy stopped on the first package failure.',
                'package' => (string) ($package['relative_path'] ?? ''),
                'failed_paths' => (array) ($packageExecution['failed_paths'] ?? []),
            ];

            break;
        }

        return $result;
    }

    /**
     * @param  array<string, mixed>  $preflightPackage
     * @param  array<string, mixed>  $scope
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    protected function executePackageDeletion(array $preflightPackage, array $scope, array $options): array
    {
        $deletedFiles = [];
        $deletedDirs = [];
        $failedPaths = [];
        $scopeRootAbsolutePath = $this->normalizePath((string) ($scope['resolved_root_absolute_path'] ?? ''));

        foreach ((array) ($preflightPackage['_delete_set_absolute'] ?? []) as $absolutePath) {
            $normalizedPath = $this->normalizePath((string) $absolutePath);

            if ($normalizedPath === '' || ! $this->pathAllowedForScope($normalizedPath, $scope, $preflightPackage)) {
                $failedPaths[] = (string) ($this->relativePath($normalizedPath) ?? $normalizedPath);

                break;
            }

            if (! File::exists($normalizedPath) || File::isDirectory($normalizedPath)) {
                $failedPaths[] = (string) ($this->relativePath($normalizedPath) ?? $normalizedPath);

                break;
            }

            try {
                if (! File::delete($normalizedPath)) {
                    $failedPaths[] = (string) ($this->relativePath($normalizedPath) ?? $normalizedPath);

                    break;
                }
            } catch (Throwable) {
                $failedPaths[] = (string) ($this->relativePath($normalizedPath) ?? $normalizedPath);

                break;
            }

            $deletedFiles[] = (string) ($this->relativePath($normalizedPath) ?? $normalizedPath);
        }

        if ($failedPaths === [] && ($options['remove_empty_dirs'] ?? false)) {
            foreach ((array) ($preflightPackage['_dir_prune_set_absolute'] ?? []) as $absolutePath) {
                $normalizedPath = $this->normalizePath((string) $absolutePath);

                if (
                    $normalizedPath === ''
                    || ! File::isDirectory($normalizedPath)
                    || ! $this->isPathWithinRoot($normalizedPath, $scopeRootAbsolutePath)
                    || $normalizedPath === $this->rootAbsolutePath()
                ) {
                    continue;
                }

                if (! $this->directoryIsEmpty($normalizedPath)) {
                    continue;
                }

                if (! @rmdir($normalizedPath)) {
                    $failedPaths[] = (string) ($this->relativePath($normalizedPath) ?? $normalizedPath);

                    break;
                }

                $deletedDirs[] = (string) ($this->relativePath($normalizedPath) ?? $normalizedPath);
            }
        }

        return [
            'status' => $failedPaths === [] ? 'ok' : 'failed',
            'deleted_files' => $deletedFiles,
            'deleted_dirs' => $deletedDirs,
            'failed_paths' => $failedPaths,
        ];
    }

    /**
     * @param  array<string, mixed>  $package
     * @return array<int, array<string, mixed>>
     */
    protected function canonicalFileDescriptors(array $package): array
    {
        $packageRootAbsolutePath = $this->packageRootAbsolutePath($package);
        $descriptors = [
            [
                'absolute_path' => $this->loaderAbsolutePath($package),
            ],
            [
                'absolute_path' => $this->normalizePath($packageRootAbsolutePath . '/' . basename($packageRootAbsolutePath) . '.php'),
            ],
            [
                'absolute_path' => $this->normalizePath($packageRootAbsolutePath . '/definition.json'),
            ],
        ];

        foreach ($this->expectedLocales() as $locale) {
            $descriptors[] = [
                'absolute_path' => $this->normalizePath($packageRootAbsolutePath . '/localizations/' . strtolower($locale) . '.json'),
            ];
        }

        return $descriptors;
    }

    /**
     * @param  array<string, mixed>  $package
     * @param  array<string, mixed>  $scope
     * @param  array<string, mixed>  $options
     * @return list<string>
     */
    protected function dirPruneCandidates(array $package, array $scope, array $options): array
    {
        if (! ($options['remove_empty_dirs'] ?? false)) {
            return [];
        }

        $scopeRootAbsolutePath = $this->normalizePath((string) ($scope['resolved_root_absolute_path'] ?? ''));
        $packageRootAbsolutePath = $this->packageRootAbsolutePath($package);
        $rootAbsolutePath = $this->rootAbsolutePath();
        $candidates = collect([
            $this->normalizePath($packageRootAbsolutePath . '/localizations'),
            $packageRootAbsolutePath,
        ]);
        $current = $this->normalizePath(dirname($packageRootAbsolutePath));

        while (
            $current !== ''
            && $current !== '.'
            && $this->isPathWithinRoot($current, $scopeRootAbsolutePath)
            && $current !== $rootAbsolutePath
        ) {
            $candidates->push($current);

            if ($current === $scopeRootAbsolutePath) {
                break;
            }

            $parent = $this->normalizePath(dirname($current));

            if ($parent === $current) {
                break;
            }

            $current = $parent;
        }

        return $candidates
            ->filter(fn (string $path): bool => $path !== $rootAbsolutePath && $this->isPathWithinRoot($path, $scopeRootAbsolutePath))
            ->unique()
            ->sortByDesc(static fn (string $path): int => substr_count($path, '/'))
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $package
     * @return list<string>
     */
    protected function additionalPreflightWarnings(array $package, array $scope, array $options): array
    {
        return [];
    }

    /**
     * @param  array<string, mixed>  $package
     * @return list<string>
     */
    protected function additionalPreflightFailures(array $package, array $scope, array $options): array
    {
        return [];
    }

    /**
     * @param  array<string, mixed>  $package
     */
    protected function packageRootAbsolutePath(array $package): string
    {
        $relativePath = trim((string) ($package['relative_path'] ?? ''));

        if ($relativePath === '') {
            throw new RuntimeException('Planner package is missing relative_path.');
        }

        return $this->normalizePath(base_path(str_replace('/', DIRECTORY_SEPARATOR, $relativePath)));
    }

    /**
     * @param  array<string, mixed>  $package
     */
    protected function loaderAbsolutePath(array $package): string
    {
        $packageRootAbsolutePath = $this->packageRootAbsolutePath($package);
        $className = basename($packageRootAbsolutePath);

        return $this->normalizePath(dirname($packageRootAbsolutePath) . '/' . $className . '.php');
    }

    protected function existingComparablePath(string $absolutePath): ?string
    {
        $normalizedPath = $this->normalizePath($absolutePath);

        if (! File::exists($normalizedPath)) {
            return null;
        }

        $realPath = realpath($normalizedPath);

        return $this->normalizePath($realPath !== false ? $realPath : $normalizedPath);
    }

    protected function directoryIsEmpty(string $absolutePath): bool
    {
        if (! File::isDirectory($absolutePath)) {
            return false;
        }

        $entries = @scandir($absolutePath);

        if ($entries === false) {
            return false;
        }

        foreach ($entries as $entry) {
            if (! in_array($entry, ['.', '..'], true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  array<string, mixed>  $scope
     * @param  array<string, mixed>  $package
     */
    protected function pathAllowedForScope(string $absolutePath, array $scope, array $package): bool
    {
        $normalizedPath = $this->normalizePath($absolutePath);
        $scopeRootAbsolutePath = $this->normalizePath((string) ($scope['resolved_root_absolute_path'] ?? ''));

        if ($normalizedPath === '' || $scopeRootAbsolutePath === '') {
            return false;
        }

        if ($this->isPathWithinRoot($normalizedPath, $scopeRootAbsolutePath)) {
            return true;
        }

        return (bool) ($scope['single_package'] ?? false)
            && $normalizedPath === $this->loaderAbsolutePath($package);
    }

    /**
     * @return array<string, mixed>
     */
    protected function forceRequiredError(): array
    {
        return [
            'stage' => 'safety',
            'reason' => 'force_required',
            'message' => 'Live folder file destroy requires --force.',
        ];
    }

    /**
     * @param  array<string, mixed>  $result
     */
    protected function renderMarkdownReport(array $result): string
    {
        $lines = [
            '# JSON Folder File Destroy',
            '',
            '- Target: `' . (string) ($result['scope']['input'] ?? '') . '`',
            '- Scope Root: `' . (string) ($result['scope']['resolved_root_relative_path'] ?? '') . '`',
            '- Single Package: `' . $this->boolString((bool) ($result['scope']['single_package'] ?? false)) . '`',
            '- Mode: `' . (string) ($result['scope']['mode'] ?? 'destroy-files') . '`',
            '- Dry Run: `' . $this->boolString((bool) ($result['execution']['dry_run'] ?? false)) . '`',
            '- Force: `' . $this->boolString((bool) ($result['execution']['force'] ?? false)) . '`',
            '- Remove Empty Dirs: `' . $this->boolString((bool) ($result['execution']['remove_empty_dirs'] ?? false)) . '`',
            '- Strict: `' . $this->boolString((bool) ($result['scope']['strict'] ?? false)) . '`',
            '',
            '## Plan Summary',
            '',
            '- Total Packages: ' . (int) ($result['plan']['summary']['total_packages'] ?? 0),
            '- Destroy File Candidates: ' . (int) ($result['plan']['summary']['destroy_files_candidates'] ?? 0),
            '- Skipped: ' . (int) ($result['plan']['summary']['skipped'] ?? 0),
            '- Blocked: ' . (int) ($result['plan']['summary']['blocked'] ?? 0),
            '- Warnings: ' . (int) ($result['plan']['summary']['warnings'] ?? 0),
            '',
            '## Preflight Summary',
            '',
            '- Candidates: ' . (int) ($result['preflight']['summary']['candidates'] ?? 0),
            '- OK: ' . (int) ($result['preflight']['summary']['ok'] ?? 0),
            '- Warn: ' . (int) ($result['preflight']['summary']['warn'] ?? 0),
            '- Fail: ' . (int) ($result['preflight']['summary']['fail'] ?? 0),
            '- Skipped: ' . (int) ($result['preflight']['summary']['skipped'] ?? 0),
            '',
            '## Packages',
            '',
        ];

        foreach ((array) ($result['plan']['packages'] ?? []) as $index => $package) {
            $preflight = (array) (($result['preflight']['packages'][$index] ?? []) ?: []);
            $execution = (array) (($result['execution']['packages'][$index] ?? []) ?: []);

            $lines[] = '- `' . (string) ($package['relative_path'] ?? '') . '`'
                . ' | action=`' . (string) ($package['recommended_action'] ?? 'skip') . '`'
                . ' | preflight=`' . (string) ($preflight['status'] ?? 'skip') . '`'
                . ' | execution=`' . (string) ($execution['status'] ?? 'pending') . '`'
                . ' | delete_set=' . implode(', ', (array) ($preflight['delete_set'] ?? []));
        }

        $lines[] = '';
        $lines[] = '## Execution Summary';
        $lines[] = '';
        $lines[] = '- Started: ' . (int) ($result['execution']['started'] ?? 0);
        $lines[] = '- Completed: ' . (int) ($result['execution']['completed'] ?? 0);
        $lines[] = '- Succeeded: ' . (int) ($result['execution']['succeeded'] ?? 0);
        $lines[] = '- Failed: ' . (int) ($result['execution']['failed'] ?? 0);
        $lines[] = '- Stopped On: `' . (string) ($result['execution']['stopped_on'] ?? '') . '`';

        if (is_array($result['error'] ?? null)) {
            $lines[] = '';
            $lines[] = '## Error';
            $lines[] = '';
            $lines[] = '- Stage: `' . (string) ($result['error']['stage'] ?? 'execution') . '`';
            $lines[] = '- Message: ' . (string) ($result['error']['message'] ?? 'Unknown error.');
        }

        return implode(PHP_EOL, $lines) . PHP_EOL;
    }

    protected function boolString(bool $value): string
    {
        return $value ? 'true' : 'false';
    }

    /**
     * @param  array<string, mixed>  $result
     * @return array<string, mixed>
     */
    protected function finalizeResult(array $result): array
    {
        $result['preflight']['packages'] = array_values(array_map(function (array $package): array {
            return collect($package)
                ->reject(static fn (mixed $value, string $key): bool => Str::startsWith($key, '_'))
                ->all();
        }, (array) ($result['preflight']['packages'] ?? [])));

        return $result;
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    abstract protected function planFolder(string $targetInput, array $options): array;

    abstract protected function reportDirectory(): string;
}
