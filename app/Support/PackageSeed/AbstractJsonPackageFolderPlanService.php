<?php

namespace App\Support\PackageSeed;

use App\Support\ReleaseCheck\AbstractJsonPackageReleaseCheckService;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

abstract class AbstractJsonPackageFolderPlanService extends AbstractJsonPackageReleaseCheckService
{
    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function run(string $targetInput, array $options = []): array
    {
        $resolvedOptions = $this->normalizePlanOptions($options);
        $scope = $this->resolvePlanningScope($targetInput, $resolvedOptions['mode']);
        $result = $this->resultTemplate($scope);
        $packageEntries = [];

        foreach ($this->discoverPackageCandidates($scope) as $candidate) {
            $inspection = $this->inspectPackageTarget((string) $candidate['inspection_target_absolute_path']);
            $packageState = $this->packageStateForMode($inspection, $resolvedOptions['mode']);
            $phaseMetadata = $this->phaseMetadataForMode($inspection, $packageState, $resolvedOptions['mode']);
            $recommendedAction = $this->recommendedAction($packageState, $resolvedOptions['mode'], $phaseMetadata);
            $releaseCheck = $resolvedOptions['with_release_check']
                ? $this->buildReleaseCheckSummary(
                    (string) $candidate['inspection_target_absolute_path'],
                    $resolvedOptions['check_profile']
                )
                : $this->defaultReleaseCheck();
            $warnings = $this->packageWarnings($inspection, $packageState, $resolvedOptions['mode'], $phaseMetadata);
            $nextStepCommand = $this->nextStepCommand($recommendedAction, (array) ($inspection['target'] ?? []), $resolvedOptions);

            $packageEntries[] = [
                'relative_path' => (string) (($inspection['target']['package_root_relative_path'] ?? null) ?: $candidate['relative_path']),
                'definition_relative_path' => (string) (($inspection['target']['definition_relative_path'] ?? null) ?: ''),
                'resolved_seeder_class' => (string) (($inspection['target']['resolved_seeder_class'] ?? null) ?: ''),
                'package_type' => (string) (($inspection['package_type'] ?? null) ?: 'unknown'),
                'definition_exists' => (bool) ($inspection['definition_exists'] ?? false),
                'loader_exists' => (bool) ($inspection['loader_exists'] ?? false),
                'real_seeder_exists' => (bool) ($inspection['real_seeder_exists'] ?? false),
                'seed_run_present' => (bool) ($inspection['ownership']['seed_run_present'] ?? false),
                'package_present_in_db' => (bool) ($inspection['ownership']['package_present_in_db'] ?? false),
                'package_state' => $packageState,
                'recommended_action' => $recommendedAction,
                'needs_unseed' => (bool) ($phaseMetadata['needs_unseed'] ?? false),
                'needs_file_destroy' => (bool) ($phaseMetadata['needs_file_destroy'] ?? false),
                'release_check' => $releaseCheck,
                'next_step_command' => $nextStepCommand,
                'warnings' => $warnings,
                '_definition_summary' => (array) ($inspection['definition_summary'] ?? []),
            ];
        }

        $packages = $this->sortPackages($packageEntries, $resolvedOptions['mode']);
        $packages = $this->augmentPackagesForScope($packages, $scope, $resolvedOptions);
        $result['packages'] = $this->finalizePackages($packages);
        $result['summary'] = $this->buildSummary($result['packages']);
        $result['error'] = $this->strictFailureIfNeeded($result['packages'], $resolvedOptions);

        return $result;
    }

    /**
     * @param  array<string, mixed>  $result
     */
    public function writeReport(array $result): string
    {
        $scopeRelativePath = (string) ($result['scope']['resolved_root_relative_path'] ?? 'scope');
        $scopeName = basename(str_replace('\\', '/', $scopeRelativePath));
        $mode = (string) ($result['scope']['mode'] ?? 'sync');
        $hash = substr(sha1($scopeRelativePath . '|' . $mode), 0, 8);
        $fileName = Str::slug($scopeName !== '' ? $scopeName : 'scope')
            . '-'
            . $mode
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
    protected function normalizePlanOptions(array $options): array
    {
        $mode = strtolower(trim((string) ($options['mode'] ?? 'sync')));

        if (! in_array($mode, ['missing', 'refresh', 'sync', 'unseed', 'destroy-files', 'destroy'], true)) {
            throw new \RuntimeException('Unsupported plan mode. Use missing, refresh, sync, unseed, destroy-files, or destroy.');
        }

        return [
            'mode' => $mode,
            'with_release_check' => (bool) ($options['with_release_check'] ?? false),
            'check_profile' => $this->normalizeProfile((string) ($options['check_profile'] ?? 'release')),
            'strict' => (bool) ($options['strict'] ?? false),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function resultTemplate(array $scope): array
    {
        return [
            'scope' => $scope,
            'summary' => [
                'total_packages' => 0,
                'seed_candidates' => 0,
                'refresh_candidates' => 0,
                'unseed_candidates' => 0,
                'destroy_files_candidates' => 0,
                'destroy_candidates' => 0,
                'skipped' => 0,
                'blocked' => 0,
                'warnings' => 0,
            ],
            'packages' => [],
            'artifacts' => [
                'report_path' => null,
            ],
            'error' => null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function resolvePlanningScope(string $targetInput, string $mode): array
    {
        $absoluteInputPath = $this->resolveExistingAbsolutePath($targetInput);
        $rootAbsolutePath = $this->rootAbsolutePath();
        $this->assertPathWithinRoot($absoluteInputPath, $rootAbsolutePath);

        $singlePackage = ! File::isDirectory($absoluteInputPath)
            || $this->directoryLooksLikePackageRoot($absoluteInputPath);
        $resolvedRootAbsolutePath = $singlePackage
            ? $this->resolvePackageRootAbsolutePath($absoluteInputPath)
            : $absoluteInputPath;

        $this->assertPathWithinRoot($resolvedRootAbsolutePath, $rootAbsolutePath);

        return [
            'input' => trim($targetInput),
            'resolved_root_absolute_path' => $resolvedRootAbsolutePath,
            'resolved_root_relative_path' => (string) ($this->relativePath($resolvedRootAbsolutePath) ?? $resolvedRootAbsolutePath),
            'single_package' => $singlePackage,
            'mode' => $mode,
        ];
    }

    protected function directoryLooksLikePackageRoot(string $absoluteDirectoryPath): bool
    {
        if (! File::isDirectory($absoluteDirectoryPath)) {
            return false;
        }

        $className = basename($absoluteDirectoryPath);

        if ($className === '') {
            return false;
        }

        $definitionPath = $this->normalizePath($absoluteDirectoryPath . '/definition.json');
        $realSeederPath = $this->normalizePath($absoluteDirectoryPath . '/' . $className . '.php');
        $loaderPath = $this->normalizePath(dirname($absoluteDirectoryPath) . '/' . $className . '.php');

        return File::exists($definitionPath)
            || File::exists($realSeederPath)
            || File::exists($loaderPath);
    }

    /**
     * @param  array<string, mixed>  $scope
     * @return array<int, array<string, mixed>>
     */
    protected function discoverPackageCandidates(array $scope): array
    {
        if ((bool) ($scope['single_package'] ?? false)) {
            $targetInput = trim((string) ($scope['input'] ?? ''));
            $absoluteTarget = $this->resolveExistingAbsolutePath($targetInput);

            return [[
                'package_root_absolute_path' => (string) ($scope['resolved_root_absolute_path'] ?? ''),
                'relative_path' => (string) ($scope['resolved_root_relative_path'] ?? ''),
                'inspection_target_absolute_path' => $absoluteTarget,
            ]];
        }

        $scopeRoot = (string) ($scope['resolved_root_absolute_path'] ?? '');
        $candidates = [];
        $directories = collect([$scopeRoot])
            ->merge(collect($this->allDirectoriesRecursive($scopeRoot))->map(
                fn ($path): string => $this->normalizePath((string) $path)
            ));

        foreach ($directories as $directoryPath) {
            $this->registerDirectoryCandidate($candidates, (string) $directoryPath, $scopeRoot);
        }

        foreach (File::allFiles($scopeRoot) as $file) {
            $this->registerFileCandidate($candidates, $this->normalizePath($file->getPathname()), $scopeRoot);
        }

        return collect($candidates)
            ->map(function (array $candidate) {
                $inspectionTarget = $this->preferredInspectionTarget($candidate);

                return [
                    'package_root_absolute_path' => (string) ($candidate['package_root_absolute_path'] ?? ''),
                    'relative_path' => (string) ($candidate['relative_path'] ?? ''),
                    'inspection_target_absolute_path' => $inspectionTarget,
                ];
            })
            ->filter(fn (array $candidate): bool => $candidate['inspection_target_absolute_path'] !== '')
            ->values()
            ->all();
    }

    /**
     * @param  array<string, array<string, mixed>>  $candidates
     */
    protected function registerDirectoryCandidate(array &$candidates, string $directoryPath, string $scopeRoot): void
    {
        if (! File::isDirectory($directoryPath)) {
            return;
        }

        $className = basename($directoryPath);

        if ($className === '') {
            return;
        }

        $definitionPath = $this->normalizePath($directoryPath . '/definition.json');
        $realSeederPath = $this->normalizePath($directoryPath . '/' . $className . '.php');
        $loaderPath = $this->normalizePath(dirname($directoryPath) . '/' . $className . '.php');

        if (! File::exists($definitionPath) && ! File::exists($realSeederPath) && ! File::exists($loaderPath)) {
            return;
        }

        $this->registerCandidateSignal($candidates, $directoryPath, $scopeRoot, 'package_dir', $directoryPath);

        if (File::exists($definitionPath)) {
            $this->registerCandidateSignal($candidates, $directoryPath, $scopeRoot, 'definition', $definitionPath);
        }

        if (File::exists($realSeederPath)) {
            $this->registerCandidateSignal($candidates, $directoryPath, $scopeRoot, 'real_seeder', $realSeederPath);
        }

        if (File::exists($loaderPath)) {
            $this->registerCandidateSignal($candidates, $directoryPath, $scopeRoot, 'loader', $loaderPath);
        }
    }

    /**
     * @param  array<string, array<string, mixed>>  $candidates
     */
    protected function registerFileCandidate(array &$candidates, string $filePath, string $scopeRoot): void
    {
        if (strtolower(pathinfo($filePath, PATHINFO_EXTENSION)) !== 'php') {
            return;
        }

        $className = pathinfo($filePath, PATHINFO_FILENAME);
        $parentDirectory = $this->normalizePath(dirname($filePath));

        if ($className === '') {
            return;
        }

        $packageRoot = basename($parentDirectory) === $className
            ? $parentDirectory
            : $this->normalizePath($parentDirectory . '/' . $className);
        $signalType = basename($parentDirectory) === $className ? 'real_seeder' : 'loader';

        $this->registerCandidateSignal($candidates, $packageRoot, $scopeRoot, $signalType, $filePath);
    }

    /**
     * @param  array<string, array<string, mixed>>  $candidates
     */
    protected function registerCandidateSignal(
        array &$candidates,
        string $packageRoot,
        string $scopeRoot,
        string $signalType,
        string $signalPath,
    ): void {
        $normalizedRoot = $this->normalizePath($packageRoot);
        $normalizedSignalPath = $this->normalizePath($signalPath);

        if (! $this->isPathWithinRoot($normalizedRoot, $scopeRoot)) {
            return;
        }

        if (! isset($candidates[$normalizedRoot])) {
            $candidates[$normalizedRoot] = [
                'package_root_absolute_path' => $normalizedRoot,
                'relative_path' => (string) ($this->relativePath($normalizedRoot) ?? $normalizedRoot),
                'signals' => [
                    'definition' => null,
                    'real_seeder' => null,
                    'package_dir' => null,
                    'loader' => null,
                ],
            ];
        }

        $candidates[$normalizedRoot]['signals'][$signalType] = $normalizedSignalPath;
    }

    /**
     * @param  array<string, mixed>  $candidate
     */
    protected function preferredInspectionTarget(array $candidate): string
    {
        $signals = (array) ($candidate['signals'] ?? []);

        foreach (['definition', 'real_seeder', 'package_dir', 'loader'] as $signal) {
            $path = trim((string) ($signals[$signal] ?? ''));

            if ($path !== '') {
                return $path;
            }
        }

        return '';
    }

    /**
     * @param  array<string, mixed>  $inspection
     */
    protected function classifyPackageState(array $inspection): string
    {
        $diskBroken = ! ($inspection['definition_exists'] ?? false)
            || ! ($inspection['loader_exists'] ?? false)
            || ! ($inspection['real_seeder_exists'] ?? false)
            || is_array($inspection['error'] ?? null);
        $seedRunPresent = (bool) ($inspection['ownership']['seed_run_present'] ?? false);
        $packagePresent = (bool) ($inspection['ownership']['package_present_in_db'] ?? false);

        if ($diskBroken) {
            return 'broken_disk_package';
        }

        if ($seedRunPresent && $packagePresent) {
            return 'seeded';
        }

        if (! $seedRunPresent && ! $packagePresent) {
            return 'not_seeded';
        }

        if ($seedRunPresent && ! $packagePresent) {
            return 'seed_run_only';
        }

        if (! $seedRunPresent && $packagePresent) {
            return 'db_only_without_seed_run';
        }

        return 'blocked';
    }

    /**
     * @param  array<string, mixed>  $inspection
     */
    protected function packageStateForMode(array $inspection, string $mode): string
    {
        if ($mode === 'destroy') {
            return $this->destroyModePackageState($inspection);
        }

        return $this->classifyPackageState($inspection);
    }

    /**
     * @param  array<string, mixed>  $inspection
     */
    protected function destroyModePackageState(array $inspection): string
    {
        $seedRunPresent = (bool) ($inspection['ownership']['seed_run_present'] ?? false);
        $packagePresent = (bool) ($inspection['ownership']['package_present_in_db'] ?? false);

        if ($seedRunPresent && $packagePresent) {
            return 'seeded';
        }

        if (! $seedRunPresent && ! $packagePresent) {
            return 'not_seeded';
        }

        if ($seedRunPresent && ! $packagePresent) {
            return 'seed_run_only';
        }

        if (! $seedRunPresent && $packagePresent) {
            return 'db_only_without_seed_run';
        }

        return 'blocked';
    }

    /**
     * @param  array<string, mixed>  $inspection
     * @return array<string, bool>
     */
    protected function phaseMetadataForMode(array $inspection, string $packageState, string $mode): array
    {
        if ($mode !== 'destroy') {
            return [
                'needs_unseed' => false,
                'needs_file_destroy' => false,
            ];
        }

        $filePresence = $this->destroyModeCoreFilePresence($inspection);

        return match ($packageState) {
            'seeded' => [
                'needs_unseed' => true,
                'needs_file_destroy' => $filePresence['complete'],
            ],
            'not_seeded' => [
                'needs_unseed' => false,
                'needs_file_destroy' => $filePresence['complete'],
            ],
            default => [
                'needs_unseed' => false,
                'needs_file_destroy' => false,
            ],
        };
    }

    /**
     * @param  array<string, mixed>  $inspection
     * @return array{complete:bool,any:bool}
     */
    protected function destroyModeCoreFilePresence(array $inspection): array
    {
        $coreFiles = [
            (bool) ($inspection['definition_exists'] ?? false),
            (bool) ($inspection['loader_exists'] ?? false),
            (bool) ($inspection['real_seeder_exists'] ?? false),
        ];

        return [
            'complete' => count(array_filter($coreFiles)) === count($coreFiles),
            'any' => in_array(true, $coreFiles, true),
        ];
    }

    /**
     * @param  array<string, bool>  $phaseMetadata
     */
    protected function recommendedAction(string $packageState, string $mode, array $phaseMetadata = []): string
    {
        if (in_array($packageState, ['broken_disk_package', 'seed_run_only', 'db_only_without_seed_run', 'blocked'], true)) {
            return 'blocked';
        }

        return match ($mode) {
            'missing' => $packageState === 'not_seeded' ? 'seed' : 'skip',
            'refresh' => $packageState === 'seeded' ? 'refresh' : 'skip',
            'unseed' => $packageState === 'seeded' ? 'unseed' : 'skip',
            'destroy-files' => match ($packageState) {
                'not_seeded' => 'destroy_files',
                'seeded' => 'blocked',
                default => 'blocked',
            },
            'destroy' => (($phaseMetadata['needs_unseed'] ?? false) || ($phaseMetadata['needs_file_destroy'] ?? false))
                ? 'destroy'
                : 'skip',
            'sync' => match ($packageState) {
                'seeded' => 'refresh',
                'not_seeded' => 'seed',
                default => 'blocked',
            },
            default => 'skip',
        };
    }

    /**
     * @param  array<string, mixed>  $inspection
     * @return list<string>
     */
    protected function packageWarnings(array $inspection, string $packageState, string $mode, array $phaseMetadata = []): array
    {
        $warnings = collect();

        if ($packageState === 'seed_run_only') {
            $warnings->push('Canonical seed_runs record exists, but package-owned database content is absent.');
        }

        if ($packageState === 'db_only_without_seed_run') {
            $warnings->push('Package-owned database content exists without a canonical seed_runs record.');
        }

        if ($packageState === 'broken_disk_package') {
            $warnings->push('Package files are incomplete or unreadable inside the resolved subtree.');

            if (! ($inspection['definition_exists'] ?? false)) {
                $warnings->push('Package definition.json is missing.');
            }

            if (! ($inspection['loader_exists'] ?? false)) {
                $warnings->push('Top-level loader stub PHP is missing.');
            }

            if (! ($inspection['real_seeder_exists'] ?? false)) {
                $warnings->push('Package-local real seeder PHP is missing.');
            }

            if (is_array($inspection['error'] ?? null)) {
                $warnings->push((string) ($inspection['error']['message'] ?? 'Package inspection failed.'));
            }
        }

        if ($mode === 'refresh' && $packageState === 'not_seeded') {
            $warnings->push('Package is not seeded; refresh mode will skip it.');
        }

        if ($mode === 'destroy-files' && $packageState === 'seeded') {
            $warnings->push('Package is still seeded in the database and cannot be file-destroyed.');
        }

        if (in_array($mode, ['destroy-files', 'destroy'], true) && (($phaseMetadata['needs_file_destroy'] ?? false) || $mode === 'destroy-files' && $packageState === 'not_seeded')) {
            foreach ((array) ($inspection['target']['localizations'] ?? []) as $locale => $absolutePath) {
                $normalizedPath = trim((string) $absolutePath);

                if ($normalizedPath === '' || File::exists($normalizedPath)) {
                    continue;
                }

                $warnings->push(sprintf(
                    'Expected package localization file is missing: %s.',
                    (string) ($this->relativePath($normalizedPath) ?? $normalizedPath)
                ));
            }
        }

        if ($mode === 'destroy') {
            $filePresence = $this->destroyModeCoreFilePresence($inspection);

            if ($packageState === 'seeded' && ! ($phaseMetadata['needs_file_destroy'] ?? false)) {
                $warnings->push('Package files are already missing or incomplete; destroy will run DB cleanup only.');
            }

            if ($packageState === 'not_seeded' && ! ($phaseMetadata['needs_file_destroy'] ?? false) && $filePresence['any']) {
                $warnings->push('Package files are already missing or incomplete; combined destroy will skip disk cleanup.');
            }
        }

        return $warnings
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $target
     * @param  array<string, mixed>  $options
     */
    protected function nextStepCommand(string $action, array $target, array $options): ?string
    {
        if (! in_array($action, ['seed', 'refresh', 'unseed'], true)) {
            return null;
        }

        $command = match ($action) {
            'seed' => $this->seedCommandName(),
            'refresh' => $this->refreshCommandName(),
            'unseed' => $this->unseedCommandName(),
            default => null,
        };
        $targetPath = trim((string) ($target['definition_relative_path'] ?? $target['package_root_relative_path'] ?? ''));

        if ($command === null || $targetPath === '') {
            return null;
        }

        $parts = [
            'php artisan ' . $command,
            $this->shellEscapeArgument($targetPath),
        ];

        if (in_array($action, ['refresh', 'unseed'], true)) {
            $parts[] = '--force';
        }

        if (($options['check_profile'] ?? 'release') !== 'release') {
            $parts[] = '--check-profile=' . (string) $options['check_profile'];
        }

        return implode(' ', $parts);
    }

    protected function shellEscapeArgument(string $value): string
    {
        return preg_match('/\s/', $value) === 1
            ? '"' . str_replace('"', '\"', $value) . '"'
            : $value;
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildReleaseCheckSummary(string $targetInput, string $profile): array
    {
        try {
            $report = $this->releaseCheckReport($targetInput, $profile);
            $counts = [
                'pass' => (int) ($report['summary']['check_counts']['pass'] ?? 0),
                'warn' => (int) ($report['summary']['check_counts']['warn'] ?? 0),
                'fail' => (int) ($report['summary']['check_counts']['fail'] ?? 0),
            ];
            $status = $counts['fail'] > 0
                ? 'fail'
                : ($counts['warn'] > 0 ? 'warn' : 'pass');

            return [
                'executed' => true,
                'profile' => $profile,
                'status' => $status,
                'summary' => $counts,
                'message' => null,
            ];
        } catch (Throwable $exception) {
            return [
                'executed' => true,
                'profile' => $profile,
                'status' => 'fail',
                'summary' => [
                    'pass' => 0,
                    'warn' => 0,
                    'fail' => 1,
                ],
                'message' => $exception->getMessage(),
            ];
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultReleaseCheck(): array
    {
        return [
            'executed' => false,
            'profile' => null,
            'status' => 'skipped',
            'summary' => [
                'pass' => 0,
                'warn' => 0,
                'fail' => 0,
            ],
            'message' => null,
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $packages
     * @param  array<string, mixed>  $scope
     * @param  array<string, mixed>  $options
     * @return array<int, array<string, mixed>>
     */
    protected function augmentPackagesForScope(array $packages, array $scope, array $options): array
    {
        return $packages;
    }

    /**
     * @param  array<int, array<string, mixed>>  $packages
     * @return array<int, array<string, mixed>>
     */
    protected function finalizePackages(array $packages): array
    {
        return array_values(array_map(function (array $package): array {
            return collect($package)
                ->reject(static fn (mixed $value, string $key): bool => Str::startsWith($key, '_'))
                ->all();
        }, $packages));
    }

    /**
     * @param  array<int, array<string, mixed>>  $packages
     * @return array<int, array<string, mixed>>
     */
    protected function sortPackages(array $packages, string $mode): array
    {
        return in_array($mode, ['unseed', 'destroy-files', 'destroy'], true)
            ? $this->sortPackagesForUnseed($packages)
            : $this->sortPackagesForApply($packages);
    }

    /**
     * @param  array<int, array<string, mixed>>  $packages
     * @return array<int, array<string, mixed>>
     */
    protected function sortPackagesForApply(array $packages): array
    {
        usort($packages, function (array $left, array $right): int {
            $leftWeight = $this->packageTypeWeight((string) ($left['package_type'] ?? 'unknown'));
            $rightWeight = $this->packageTypeWeight((string) ($right['package_type'] ?? 'unknown'));

            if ($leftWeight !== $rightWeight) {
                return $leftWeight <=> $rightWeight;
            }

            return strcmp(
                (string) ($left['relative_path'] ?? ''),
                (string) ($right['relative_path'] ?? '')
            );
        });

        return $packages;
    }

    /**
     * @param  array<int, array<string, mixed>>  $packages
     * @return array<int, array<string, mixed>>
     */
    protected function sortPackagesForUnseed(array $packages): array
    {
        return array_values(array_reverse($this->sortPackagesForApply($packages)));
    }

    /**
     * @param  array<int, array<string, mixed>>  $packages
     * @return array<string, int>
     */
    protected function buildSummary(array $packages): array
    {
        return [
            'total_packages' => count($packages),
            'seed_candidates' => count(array_filter(
                $packages,
                static fn (array $package): bool => ($package['recommended_action'] ?? null) === 'seed'
            )),
            'refresh_candidates' => count(array_filter(
                $packages,
                static fn (array $package): bool => ($package['recommended_action'] ?? null) === 'refresh'
            )),
            'unseed_candidates' => count(array_filter(
                $packages,
                static fn (array $package): bool => ($package['recommended_action'] ?? null) === 'unseed'
            )),
            'destroy_files_candidates' => count(array_filter(
                $packages,
                static fn (array $package): bool => ($package['recommended_action'] ?? null) === 'destroy_files'
            )),
            'destroy_candidates' => count(array_filter(
                $packages,
                static fn (array $package): bool => ($package['recommended_action'] ?? null) === 'destroy'
            )),
            'skipped' => count(array_filter(
                $packages,
                static fn (array $package): bool => ($package['recommended_action'] ?? null) === 'skip'
            )),
            'blocked' => count(array_filter(
                $packages,
                static fn (array $package): bool => ($package['recommended_action'] ?? null) === 'blocked'
            )),
            'warnings' => count(array_filter($packages, function (array $package): bool {
                $releaseStatus = (string) ($package['release_check']['status'] ?? 'skipped');

                return ((array) ($package['warnings'] ?? [])) !== []
                    || in_array($releaseStatus, ['warn', 'fail'], true);
            })),
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $packages
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>|null
     */
    protected function strictFailureIfNeeded(array $packages, array $options): ?array
    {
        if (! ($options['strict'] ?? false)) {
            return null;
        }

        $blockedPackages = array_values(array_filter(
            $packages,
            static fn (array $package): bool => ($package['recommended_action'] ?? null) === 'blocked'
        ));

        if ($blockedPackages !== []) {
            return [
                'stage' => 'planning',
                'reason' => 'blocked_packages',
                'message' => 'Planner found inconsistent or broken package states and --strict is enabled.',
                'packages' => array_values(array_map(
                    static fn (array $package): string => (string) ($package['relative_path'] ?? ''),
                    $blockedPackages
                )),
            ];
        }

        $warningPackages = array_values(array_filter($packages, static function (array $package): bool {
            return ((array) ($package['warnings'] ?? [])) !== [];
        }));

        if ($warningPackages !== []) {
            return [
                'stage' => 'planning',
                'reason' => 'warnings_are_fatal',
                'message' => 'Planner returned warnings and --strict is enabled.',
                'packages' => array_values(array_map(
                    static fn (array $package): string => (string) ($package['relative_path'] ?? ''),
                    $warningPackages
                )),
            ];
        }

        if ($options['with_release_check'] ?? false) {
            $releaseWarningPackages = array_values(array_filter($packages, static function (array $package): bool {
                return in_array((string) ($package['release_check']['status'] ?? 'skipped'), ['warn', 'fail'], true);
            }));

            if ($releaseWarningPackages !== []) {
                return [
                    'stage' => 'release_check',
                    'reason' => 'warnings_are_fatal',
                    'message' => 'Planner release-check results returned warnings or failures and --strict is enabled.',
                    'packages' => array_values(array_map(
                        static fn (array $package): string => (string) ($package['relative_path'] ?? ''),
                        $releaseWarningPackages
                    )),
                ];
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $result
     */
    protected function renderMarkdownReport(array $result): string
    {
        $lines = [
            '# JSON Folder Plan',
            '',
            '- Target: `' . (string) ($result['scope']['input'] ?? '') . '`',
            '- Scope Root: `' . (string) ($result['scope']['resolved_root_relative_path'] ?? '') . '`',
            '- Single Package: `' . (((bool) ($result['scope']['single_package'] ?? false)) ? 'true' : 'false') . '`',
            '- Mode: `' . (string) ($result['scope']['mode'] ?? 'sync') . '`',
            '',
            '## Summary',
            '',
            '- Total Packages: ' . (int) ($result['summary']['total_packages'] ?? 0),
            '- Seed Candidates: ' . (int) ($result['summary']['seed_candidates'] ?? 0),
            '- Refresh Candidates: ' . (int) ($result['summary']['refresh_candidates'] ?? 0),
            '- Unseed Candidates: ' . (int) ($result['summary']['unseed_candidates'] ?? 0),
            '- Destroy File Candidates: ' . (int) ($result['summary']['destroy_files_candidates'] ?? 0),
            '- Destroy Candidates: ' . (int) ($result['summary']['destroy_candidates'] ?? 0),
            '- Skipped: ' . (int) ($result['summary']['skipped'] ?? 0),
            '- Blocked: ' . (int) ($result['summary']['blocked'] ?? 0),
            '- Warnings: ' . (int) ($result['summary']['warnings'] ?? 0),
            '',
            '## Packages',
            '',
        ];

        foreach ((array) ($result['packages'] ?? []) as $package) {
            $lines[] = '- `' . (string) ($package['relative_path'] ?? '') . '`'
                . ' | type=`' . (string) ($package['package_type'] ?? 'unknown') . '`'
                . ' | state=`' . (string) ($package['package_state'] ?? 'unknown') . '`'
                . ' | action=`' . (string) ($package['recommended_action'] ?? 'skip') . '`'
                . ' | release=`' . (string) ($package['release_check']['status'] ?? 'skipped') . '`';
        }

        if (is_array($result['error'] ?? null)) {
            $lines[] = '';
            $lines[] = '## Error';
            $lines[] = '';
            $lines[] = '- Stage: `' . (string) ($result['error']['stage'] ?? 'planning') . '`';
            $lines[] = '- Message: ' . (string) ($result['error']['message'] ?? 'Unknown error.');
        }

        return implode(PHP_EOL, $lines) . PHP_EOL;
    }

    /**
     * @return array<int, string>
     */
    protected function allDirectoriesRecursive(string $rootPath): array
    {
        $directories = [];
        $pending = [$rootPath];

        while ($pending !== []) {
            $current = array_pop($pending);

            foreach (File::directories($current) as $directory) {
                $normalized = $this->normalizePath((string) $directory);
                $directories[] = $normalized;
                $pending[] = $normalized;
            }
        }

        return $directories;
    }

    /**
     * @return array<string, mixed>
     */
    abstract protected function inspectPackageTarget(string $targetInput): array;

    /**
     * @return array<string, mixed>
     */
    abstract protected function releaseCheckReport(string $targetInput, string $profile): array;

    abstract protected function seedCommandName(): string;

    abstract protected function refreshCommandName(): string;

    abstract protected function unseedCommandName(): string;

    abstract protected function packageTypeWeight(string $packageType): int;
}
