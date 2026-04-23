<?php

namespace App\Support\PackageSeed;

use App\Support\ReleaseCheck\AbstractJsonPackageReleaseCheckService;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

abstract class AbstractJsonPackageChangedPlanService extends AbstractJsonPackageReleaseCheckService
{
    public function __construct(
        protected readonly GitPackageDiffService $gitPackageDiffService,
    ) {
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function run(?string $targetInput = null, array $options = []): array
    {
        $resolvedOptions = $this->normalizeChangedPlanOptions($options);

        try {
            $scope = $this->resolveChangedPlanningScope((string) ($targetInput ?? ''));
        } catch (Throwable $exception) {
            return $this->errorResult(
                [
                    'input' => trim((string) ($targetInput ?? '')),
                    'resolved_root_absolute_path' => null,
                    'resolved_root_relative_path' => null,
                    'single_package' => false,
                ],
                $resolvedOptions,
                'target_resolution',
                $exception
            );
        }

        $result = $this->resultTemplate($scope, $resolvedOptions);

        try {
            $diff = $this->gitPackageDiffService->collect(
                (string) ($scope['resolved_root_relative_path'] ?? $this->packageRootRelativePath()),
                $resolvedOptions
            );
        } catch (Throwable $exception) {
            $result['error'] = [
                'stage' => 'git_diff',
                'message' => $exception->getMessage(),
                'exception_class' => $exception::class,
            ];

            return $result;
        }

        $result['diff'] = [
            'mode' => (string) ($diff['mode'] ?? 'working_tree'),
            'base' => $diff['base'] ?? null,
            'head' => $diff['head'] ?? null,
            'include_untracked' => (bool) ($diff['include_untracked'] ?? false),
        ];

        $aggregated = $this->aggregateChangedPackages((array) ($diff['entries'] ?? []));
        $packages = [];

        foreach ((array) ($aggregated['deleted'] ?? []) as $packageRootRelativePath => $change) {
            $packages[] = $this->buildDeletedPackageRecord(
                (string) $packageRootRelativePath,
                (array) $change,
                (string) ($diff['historical_ref'] ?? 'HEAD')
            );
        }

        foreach ((array) ($aggregated['current'] ?? []) as $packageRootRelativePath => $change) {
            $packages[] = $this->buildCurrentPackageRecord(
                (string) $packageRootRelativePath,
                (array) $change,
                $resolvedOptions
            );
        }

        $result['packages'] = $this->sortPackages($packages);
        $result['phases'] = $this->buildPhases($result['packages']);
        $result['summary'] = $this->buildSummary($result['packages']);
        $result['error'] = $this->strictFailureIfNeeded($result['packages'], $resolvedOptions);

        return $result;
    }

    /**
     * @param  array<string, mixed>  $result
     */
    public function writeReport(array $result): string
    {
        $scopeRelativePath = (string) ($result['scope']['resolved_root_relative_path'] ?? $this->packageRootRelativePath());
        $scopeName = basename(str_replace('\\', '/', $scopeRelativePath));
        $mode = (string) ($result['diff']['mode'] ?? 'working-tree');
        $base = trim((string) ($result['diff']['base'] ?? ''));
        $head = trim((string) ($result['diff']['head'] ?? ''));
        $suffix = (bool) ($result['diff']['include_untracked'] ?? false) ? '-untracked' : '';
        $hash = substr(sha1($scopeRelativePath . '|' . $mode . '|' . $base . '|' . $head . '|' . $suffix), 0, 8);
        $fileName = Str::slug($scopeName !== '' ? $scopeName : 'scope')
            . '-changed-'
            . Str::slug($mode)
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
    protected function normalizeChangedPlanOptions(array $options): array
    {
        return [
            'base' => trim((string) ($options['base'] ?? '')),
            'head' => trim((string) ($options['head'] ?? '')),
            'staged' => (bool) ($options['staged'] ?? false),
            'working_tree' => (bool) ($options['working_tree'] ?? false),
            'include_untracked' => (bool) ($options['include_untracked'] ?? false),
            'with_release_check' => (bool) ($options['with_release_check'] ?? false),
            'check_profile' => $this->normalizeProfile((string) ($options['check_profile'] ?? 'release')),
            'strict' => (bool) ($options['strict'] ?? false),
        ];
    }

    /**
     * @param  array<string, mixed>  $scope
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    protected function resultTemplate(array $scope, array $options): array
    {
        return [
            'diff' => [
                'mode' => 'working_tree',
                'base' => $options['base'] !== '' ? $options['base'] : null,
                'head' => $options['head'] !== '' ? $options['head'] : null,
                'include_untracked' => (bool) ($options['include_untracked'] ?? false),
            ],
            'scope' => $scope,
            'summary' => [
                'changed_packages' => 0,
                'seed_candidates' => 0,
                'refresh_candidates' => 0,
                'deleted_cleanup_candidates' => 0,
                'skipped' => 0,
                'blocked' => 0,
                'warnings' => 0,
            ],
            'phases' => [
                'cleanup_deleted' => [],
                'upsert_present' => [],
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
    protected function resolveChangedPlanningScope(string $targetInput): array
    {
        $rootAbsolutePath = $this->rootAbsolutePath();
        $normalizedTarget = trim($targetInput);

        if ($normalizedTarget === '') {
            return [
                'input' => $this->packageRootRelativePath(),
                'resolved_root_absolute_path' => $rootAbsolutePath,
                'resolved_root_relative_path' => $this->packageRootRelativePath(),
                'single_package' => false,
            ];
        }

        $absoluteInputPath = $this->resolveAbsolutePathAllowMissing($normalizedTarget);
        $this->assertPathWithinRoot($absoluteInputPath, $rootAbsolutePath);

        if (File::exists($absoluteInputPath)) {
            $singlePackage = ! File::isDirectory($absoluteInputPath)
                || $this->directoryLooksLikePackageRoot($absoluteInputPath);
            $resolvedRootAbsolutePath = $singlePackage
                ? $this->resolvePackageRootAbsolutePath($absoluteInputPath)
                : $absoluteInputPath;
        } else {
            $singlePackage = $this->pathLooksLikeSinglePackageTarget($absoluteInputPath);
            $resolvedRootAbsolutePath = $singlePackage
                ? $this->resolvePackageRootFromPotentialPath($absoluteInputPath)
                : $absoluteInputPath;
        }

        $this->assertPathWithinRoot($resolvedRootAbsolutePath, $rootAbsolutePath);

        return [
            'input' => $normalizedTarget,
            'resolved_root_absolute_path' => $resolvedRootAbsolutePath,
            'resolved_root_relative_path' => (string) ($this->relativePath($resolvedRootAbsolutePath) ?? $resolvedRootAbsolutePath),
            'single_package' => $singlePackage,
        ];
    }

    protected function resolveAbsolutePathAllowMissing(string $input): string
    {
        $candidatePath = $this->isAbsolutePath($input)
            ? $input
            : base_path($input);
        $normalizedCandidatePath = $this->normalizePath($candidatePath);

        if (File::exists($normalizedCandidatePath)) {
            $realPath = realpath($normalizedCandidatePath);

            return $this->normalizePath($realPath !== false ? $realPath : $normalizedCandidatePath);
        }

        return $normalizedCandidatePath;
    }

    protected function pathLooksLikeSinglePackageTarget(string $absoluteInputPath): bool
    {
        $extension = strtolower(pathinfo($absoluteInputPath, PATHINFO_EXTENSION));

        if ($extension === 'json') {
            return strtolower(basename($absoluteInputPath)) === 'definition.json';
        }

        if ($extension === 'php') {
            return true;
        }

        return Str::endsWith(basename($absoluteInputPath), 'Seeder');
    }

    protected function resolvePackageRootFromPotentialPath(string $absoluteInputPath): string
    {
        if (File::isDirectory($absoluteInputPath)) {
            return $absoluteInputPath;
        }

        $extension = strtolower(pathinfo($absoluteInputPath, PATHINFO_EXTENSION));

        if ($extension === 'json' && strtolower(basename($absoluteInputPath)) === 'definition.json') {
            return $this->normalizePath(dirname($absoluteInputPath));
        }

        if ($extension === 'php') {
            $className = pathinfo($absoluteInputPath, PATHINFO_FILENAME);
            $parentDirectory = $this->normalizePath(dirname($absoluteInputPath));
            $parentName = basename($parentDirectory);

            if ($parentName === $className) {
                return $parentDirectory;
            }

            return $this->normalizePath($parentDirectory . DIRECTORY_SEPARATOR . $className);
        }

        return $absoluteInputPath;
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
     * @param  array<int, array<string, mixed>>  $entries
     * @return array<string, array<string, array<string, mixed>>>
     */
    protected function aggregateChangedPackages(array $entries): array
    {
        $current = [];
        $deleted = [];

        foreach ($entries as $entry) {
            $status = strtoupper((string) ($entry['status'] ?? ''));
            $oldPath = trim((string) ($entry['old_path'] ?? ''));
            $newPath = trim((string) ($entry['new_path'] ?? ''));
            $oldPackageRoot = $oldPath !== '' ? $this->canonicalPackageRootFromChangedPath($oldPath) : null;
            $newPackageRoot = $newPath !== '' ? $this->canonicalPackageRootFromChangedPath($newPath) : null;

            if (in_array($status, ['R', 'C'], true)) {
                if ($oldPackageRoot !== null && $newPackageRoot !== null && $oldPackageRoot === $newPackageRoot) {
                    $this->registerCurrentAggregate($current, $newPackageRoot, 'modified', $entry, $oldPackageRoot);

                    continue;
                }

                if ($oldPackageRoot !== null) {
                    if ($this->currentPackageExistsOnDisk($oldPackageRoot)) {
                        $this->registerCurrentAggregate($current, $oldPackageRoot, 'modified', $entry, $oldPackageRoot);
                    } else {
                        $this->registerDeletedAggregate($deleted, $oldPackageRoot, 'deleted', $entry);
                    }
                }

                if ($newPackageRoot !== null) {
                    $this->registerCurrentAggregate($current, $newPackageRoot, 'added', $entry, $oldPackageRoot);
                }

                continue;
            }

            $packageRoot = $newPackageRoot ?? $oldPackageRoot;

            if ($packageRoot === null) {
                continue;
            }

            if ($status === 'D' && ! $this->currentPackageExistsOnDisk($packageRoot)) {
                $this->registerDeletedAggregate($deleted, $packageRoot, 'deleted', $entry);

                continue;
            }

            $this->registerCurrentAggregate(
                $current,
                $packageRoot,
                $status === 'A' ? 'added' : 'modified',
                $entry,
                $oldPackageRoot
            );
        }

        return [
            'current' => $current,
            'deleted' => $deleted,
        ];
    }

    protected function canonicalPackageRootFromChangedPath(string $relativePath): ?string
    {
        $normalizedPath = trim(str_replace('\\', '/', $relativePath), '/');
        $rootRelativePath = trim($this->packageRootRelativePath(), '/');

        if (
            $normalizedPath === ''
            || ! Str::startsWith($normalizedPath, $rootRelativePath . '/')
            && $normalizedPath !== $rootRelativePath
        ) {
            return null;
        }

        if (strtolower(basename($normalizedPath)) === 'definition.json') {
            return trim(dirname($normalizedPath), '/');
        }

        if (preg_match('#/localizations/[^/]+\.json$#i', $normalizedPath) === 1) {
            return trim(dirname(dirname($normalizedPath)), '/');
        }

        if (strtolower(pathinfo($normalizedPath, PATHINFO_EXTENSION)) !== 'php') {
            return null;
        }

        $className = pathinfo($normalizedPath, PATHINFO_FILENAME);
        $parentDirectory = trim(dirname($normalizedPath), '/');

        if ($className === '') {
            return null;
        }

        if (basename($parentDirectory) === $className) {
            return $parentDirectory;
        }

        return trim($parentDirectory . '/' . $className, '/');
    }

    protected function currentPackageExistsOnDisk(string $packageRootRelativePath): bool
    {
        $packageRootAbsolutePath = base_path(str_replace('/', DIRECTORY_SEPARATOR, $packageRootRelativePath));
        $className = basename($packageRootRelativePath);

        return File::isDirectory($packageRootAbsolutePath)
            || File::exists($packageRootAbsolutePath . DIRECTORY_SEPARATOR . 'definition.json')
            || File::exists($packageRootAbsolutePath . DIRECTORY_SEPARATOR . $className . '.php')
            || File::exists(dirname($packageRootAbsolutePath) . DIRECTORY_SEPARATOR . $className . '.php');
    }

    /**
     * @param  array<string, array<string, mixed>>  $aggregates
     * @param  array<string, mixed>  $entry
     */
    protected function registerCurrentAggregate(
        array &$aggregates,
        string $packageRootRelativePath,
        string $signalType,
        array $entry,
        ?string $historicalPackageRoot = null,
    ): void {
        if (! isset($aggregates[$packageRootRelativePath])) {
            $aggregates[$packageRootRelativePath] = [
                'package_key' => $packageRootRelativePath,
                'current_relative_path' => $packageRootRelativePath,
                'historical_relative_path' => $historicalPackageRoot !== $packageRootRelativePath ? $historicalPackageRoot : null,
                'signal_types' => [],
                'paths' => [],
            ];
        }

        $aggregates[$packageRootRelativePath]['signal_types'][] = $signalType;
        $aggregates[$packageRootRelativePath]['paths'][] = array_values(array_filter([
            (string) ($entry['old_path'] ?? ''),
            (string) ($entry['new_path'] ?? ''),
        ]));

        if ($historicalPackageRoot !== null && $historicalPackageRoot !== $packageRootRelativePath) {
            $aggregates[$packageRootRelativePath]['historical_relative_path'] = $historicalPackageRoot;
        }
    }

    /**
     * @param  array<string, array<string, mixed>>  $aggregates
     * @param  array<string, mixed>  $entry
     */
    protected function registerDeletedAggregate(
        array &$aggregates,
        string $packageRootRelativePath,
        string $signalType,
        array $entry,
    ): void {
        if (! isset($aggregates[$packageRootRelativePath])) {
            $aggregates[$packageRootRelativePath] = [
                'package_key' => $packageRootRelativePath,
                'historical_relative_path' => $packageRootRelativePath,
                'signal_types' => [],
                'paths' => [],
            ];
        }

        $aggregates[$packageRootRelativePath]['signal_types'][] = $signalType;
        $aggregates[$packageRootRelativePath]['paths'][] = array_values(array_filter([
            (string) ($entry['old_path'] ?? ''),
            (string) ($entry['new_path'] ?? ''),
        ]));
    }

    /**
     * @param  array<string, mixed>  $change
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    protected function buildCurrentPackageRecord(
        string $packageRootRelativePath,
        array $change,
        array $options,
    ): array {
        $releaseCheck = $this->defaultReleaseCheck();

        try {
            $plan = $this->planCurrentPackage($packageRootRelativePath);
            $package = collect((array) ($plan['packages'] ?? []))->first();

            if (! is_array($package)) {
                throw new \RuntimeException('Current package planner did not return a package record.');
            }

            if (
                ($options['with_release_check'] ?? false)
                && in_array((string) ($package['recommended_action'] ?? 'skip'), ['seed', 'refresh'], true)
            ) {
                $releaseCheck = $this->buildReleaseCheckSummary(
                    (string) ($package['definition_relative_path'] ?? $packageRootRelativePath),
                    (string) ($options['check_profile'] ?? 'release')
                );
            }

            return [
                'package_key' => (string) ($package['relative_path'] ?? $packageRootRelativePath),
                'package_type' => (string) ($package['package_type'] ?? 'unknown'),
                'change_type' => $this->normalizeChangeType((array) ($change['signal_types'] ?? []), false),
                'current_relative_path' => (string) ($package['relative_path'] ?? $packageRootRelativePath),
                'historical_relative_path' => $change['historical_relative_path'] ?? null,
                'current_on_disk' => true,
                'historical_metadata_available' => false,
                'resolved_seeder_class' => $this->nullableString($package['resolved_seeder_class'] ?? null),
                'seed_run_present' => (bool) ($package['seed_run_present'] ?? false),
                'package_present_in_db' => (bool) ($package['package_present_in_db'] ?? false),
                'package_state' => (string) ($package['package_state'] ?? 'blocked'),
                'recommended_phase' => $this->recommendedPhase((string) ($package['recommended_action'] ?? 'skip')),
                'recommended_action' => (string) ($package['recommended_action'] ?? 'blocked'),
                'release_check' => $releaseCheck,
                'historical_definition_summary' => null,
                'warnings' => array_values(array_unique(array_filter(array_map(
                    static fn ($warning): string => trim((string) $warning),
                    (array) ($package['warnings'] ?? [])
                )))),
                'next_step_hint' => $this->currentPackageNextStepHint($package),
            ];
        } catch (Throwable $exception) {
            return [
                'package_key' => $packageRootRelativePath,
                'package_type' => 'unknown',
                'change_type' => $this->normalizeChangeType((array) ($change['signal_types'] ?? []), false),
                'current_relative_path' => $packageRootRelativePath,
                'historical_relative_path' => $change['historical_relative_path'] ?? null,
                'current_on_disk' => $this->currentPackageExistsOnDisk($packageRootRelativePath),
                'historical_metadata_available' => false,
                'resolved_seeder_class' => null,
                'seed_run_present' => false,
                'package_present_in_db' => false,
                'package_state' => 'blocked',
                'recommended_phase' => 'none',
                'recommended_action' => 'blocked',
                'release_check' => $releaseCheck,
                'historical_definition_summary' => null,
                'warnings' => ['Current package planning failed: ' . $exception->getMessage()],
                'next_step_hint' => 'Fix the current package state before applying changed-package lifecycle commands.',
            ];
        }
    }

    /**
     * @param  array<string, mixed>  $change
     * @return array<string, mixed>
     */
    protected function buildDeletedPackageRecord(
        string $packageRootRelativePath,
        array $change,
        string $historicalRef,
    ): array {
        $metadata = $this->deletedPackageMetadata($packageRootRelativePath, $historicalRef);
        $ownership = $this->deletedPackageOwnership($metadata);
        $warnings = collect((array) ($metadata['warnings'] ?? []))
            ->merge((array) ($ownership['warnings'] ?? []))
            ->map(static fn ($warning): string => trim((string) $warning))
            ->filter()
            ->unique()
            ->values()
            ->all();
        $packageState = $this->deletedPackageState($metadata, $ownership);
        $recommendedAction = $this->deletedPackageRecommendedAction($packageState, $metadata, $ownership);

        return [
            'package_key' => $packageRootRelativePath,
            'package_type' => (string) ($metadata['package_type'] ?? 'unknown'),
            'change_type' => $this->normalizeChangeType((array) ($change['signal_types'] ?? []), true),
            'current_relative_path' => null,
            'historical_relative_path' => (string) ($change['historical_relative_path'] ?? $packageRootRelativePath),
            'current_on_disk' => false,
            'historical_metadata_available' => (bool) ($metadata['available'] ?? false),
            'resolved_seeder_class' => $this->nullableString($metadata['resolved_seeder_class'] ?? null),
            'seed_run_present' => (bool) ($ownership['seed_run_present'] ?? false),
            'package_present_in_db' => (bool) ($ownership['package_present_in_db'] ?? false),
            'package_state' => $packageState,
            'recommended_phase' => $recommendedAction === 'unseed_deleted' ? 'cleanup_deleted' : 'none',
            'recommended_action' => $recommendedAction,
            'release_check' => $this->defaultReleaseCheck(),
            'historical_definition_summary' => (array) ($metadata['summary'] ?? []),
            'warnings' => $warnings,
            'next_step_hint' => $this->deletedPackageNextStepHint($recommendedAction, $metadata),
        ];
    }

    /**
     * @param  array<int, string>  $signalTypes
     */
    protected function normalizeChangeType(array $signalTypes, bool $deleted): string
    {
        $normalized = array_values(array_unique(array_filter(array_map(
            static fn ($signal): string => strtolower(trim((string) $signal)),
            $signalTypes
        ))));

        if ($normalized === []) {
            return $deleted ? 'deleted' : 'modified';
        }

        if (count($normalized) === 1) {
            return match ($normalized[0]) {
                'added' => 'added',
                'deleted' => 'deleted',
                default => 'modified',
            };
        }

        return 'mixed';
    }

    /**
     * @param  array<string, mixed>  $metadata
     * @param  array<string, mixed>  $ownership
     */
    protected function deletedPackageState(array $metadata, array $ownership): string
    {
        if (! ($metadata['available'] ?? false) || ! ($metadata['safe'] ?? false)) {
            return 'blocked';
        }

        $seedRunPresent = (bool) ($ownership['seed_run_present'] ?? false);
        $packagePresent = (bool) ($ownership['package_present_in_db'] ?? false);

        if ($seedRunPresent && ! $packagePresent) {
            return 'seed_run_only';
        }

        if (! $seedRunPresent && $packagePresent) {
            return 'db_only_without_seed_run';
        }

        return 'deleted_from_disk';
    }

    /**
     * @param  array<string, mixed>  $metadata
     * @param  array<string, mixed>  $ownership
     */
    protected function deletedPackageRecommendedAction(
        string $packageState,
        array $metadata,
        array $ownership,
    ): string {
        if (! ($metadata['available'] ?? false) || ! ($metadata['safe'] ?? false)) {
            return 'blocked';
        }

        if (in_array($packageState, ['seed_run_only', 'db_only_without_seed_run', 'blocked'], true)) {
            return 'blocked';
        }

        return ((bool) ($ownership['seed_run_present'] ?? false) || (bool) ($ownership['package_present_in_db'] ?? false))
            ? 'unseed_deleted'
            : 'skip';
    }

    protected function recommendedPhase(string $recommendedAction): string
    {
        return match ($recommendedAction) {
            'seed', 'refresh' => 'upsert_present',
            'unseed_deleted' => 'cleanup_deleted',
            default => 'none',
        };
    }

    /**
     * @param  array<string, mixed>  $package
     */
    protected function currentPackageNextStepHint(array $package): ?string
    {
        $command = trim((string) ($package['next_step_command'] ?? ''));

        if ($command !== '') {
            return $command;
        }

        return match ((string) ($package['recommended_action'] ?? 'skip')) {
            'blocked' => 'Fix the current package consistency issues before running seed or refresh.',
            'skip' => 'No lifecycle action is required for this current package.',
            default => null,
        };
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    protected function deletedPackageNextStepHint(string $recommendedAction, array $metadata): string
    {
        return match ($recommendedAction) {
            'unseed_deleted' => 'Deleted package still has ownership in the database; plan a deleted-package cleanup step before deploy/apply.',
            'skip' => 'Deleted package is already absent from disk and DB; no lifecycle action is required.',
            default => (bool) ($metadata['available'] ?? false)
                ? 'Deleted package metadata or ownership is inconsistent; manual review is required before cleanup.'
                : 'Historical package metadata is unavailable; manual review is required before cleanup.',
        };
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
     * @return array<int, array<string, mixed>>
     */
    protected function sortPackages(array $packages): array
    {
        $cleanupDeleted = array_values(array_filter(
            $packages,
            static fn (array $package): bool => ($package['recommended_phase'] ?? null) === 'cleanup_deleted'
        ));
        $upsertPresent = array_values(array_filter(
            $packages,
            static fn (array $package): bool => ($package['recommended_phase'] ?? null) === 'upsert_present'
        ));
        $none = array_values(array_filter(
            $packages,
            static fn (array $package): bool => ($package['recommended_phase'] ?? null) === 'none'
        ));

        usort($cleanupDeleted, fn (array $left, array $right): int => $this->cleanupPhaseComparator($left, $right));
        usort($upsertPresent, fn (array $left, array $right): int => $this->upsertPhaseComparator($left, $right));
        usort($none, fn (array $left, array $right): int => strcmp($this->packageSortPath($left), $this->packageSortPath($right)));

        return array_values(array_merge($cleanupDeleted, $upsertPresent, $none));
    }

    protected function packageSortPath(array $package): string
    {
        return (string) (($package['current_relative_path'] ?? null)
            ?: ($package['historical_relative_path'] ?? null)
            ?: ($package['package_key'] ?? ''));
    }

    /**
     * @param  array<int, array<string, mixed>>  $packages
     * @return array<string, array<int, array<string, mixed>>>
     */
    protected function buildPhases(array $packages): array
    {
        return [
            'cleanup_deleted' => array_values(array_filter(
                $packages,
                static fn (array $package): bool => ($package['recommended_phase'] ?? null) === 'cleanup_deleted'
            )),
            'upsert_present' => array_values(array_filter(
                $packages,
                static fn (array $package): bool => ($package['recommended_phase'] ?? null) === 'upsert_present'
            )),
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $packages
     * @return array<string, int>
     */
    protected function buildSummary(array $packages): array
    {
        return [
            'changed_packages' => count($packages),
            'seed_candidates' => count(array_filter(
                $packages,
                static fn (array $package): bool => ($package['recommended_action'] ?? null) === 'seed'
            )),
            'refresh_candidates' => count(array_filter(
                $packages,
                static fn (array $package): bool => ($package['recommended_action'] ?? null) === 'refresh'
            )),
            'deleted_cleanup_candidates' => count(array_filter(
                $packages,
                static fn (array $package): bool => ($package['recommended_action'] ?? null) === 'unseed_deleted'
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
                'message' => 'Changed-package planner found blocked packages and --strict is enabled.',
                'packages' => array_values(array_map(
                    fn (array $package): string => $this->packageSortPath($package),
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
                'message' => 'Changed-package planner returned warnings and --strict is enabled.',
                'packages' => array_values(array_map(
                    fn (array $package): string => $this->packageSortPath($package),
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
                    'message' => 'Changed-package release-check results returned warnings or failures and --strict is enabled.',
                    'packages' => array_values(array_map(
                        fn (array $package): string => $this->packageSortPath($package),
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
            '# Changed Package Plan',
            '',
            '- Diff Mode: `' . (string) ($result['diff']['mode'] ?? 'working_tree') . '`',
            '- Base: `' . (string) (($result['diff']['base'] ?? null) ?: '') . '`',
            '- Head: `' . (string) (($result['diff']['head'] ?? null) ?: '') . '`',
            '- Include Untracked: `' . (((bool) ($result['diff']['include_untracked'] ?? false)) ? 'true' : 'false') . '`',
            '- Scope Root: `' . (string) ($result['scope']['resolved_root_relative_path'] ?? '') . '`',
            '- Single Package: `' . (((bool) ($result['scope']['single_package'] ?? false)) ? 'true' : 'false') . '`',
            '',
            '## Summary',
            '',
            '- Changed Packages: ' . (int) ($result['summary']['changed_packages'] ?? 0),
            '- Seed Candidates: ' . (int) ($result['summary']['seed_candidates'] ?? 0),
            '- Refresh Candidates: ' . (int) ($result['summary']['refresh_candidates'] ?? 0),
            '- Deleted Cleanup Candidates: ' . (int) ($result['summary']['deleted_cleanup_candidates'] ?? 0),
            '- Skipped: ' . (int) ($result['summary']['skipped'] ?? 0),
            '- Blocked: ' . (int) ($result['summary']['blocked'] ?? 0),
            '- Warnings: ' . (int) ($result['summary']['warnings'] ?? 0),
            '',
            '## Deleted Packages Cleanup Phase',
            '',
        ];

        $cleanupDeleted = (array) ($result['phases']['cleanup_deleted'] ?? []);

        if ($cleanupDeleted === []) {
            $lines[] = '- None.';
        } else {
            foreach ($cleanupDeleted as $package) {
                $lines[] = '- `' . (string) ($package['historical_relative_path'] ?? $package['package_key'] ?? '') . '`'
                    . ' | type=`' . (string) ($package['package_type'] ?? 'unknown') . '`'
                    . ' | change=`' . (string) ($package['change_type'] ?? 'deleted') . '`'
                    . ' | state=`' . (string) ($package['package_state'] ?? 'deleted_from_disk') . '`'
                    . ' | action=`' . (string) ($package['recommended_action'] ?? 'skip') . '`';
            }
        }

        $lines[] = '';
        $lines[] = '## Current Packages Upsert Phase';
        $lines[] = '';

        $upsertPresent = (array) ($result['phases']['upsert_present'] ?? []);

        if ($upsertPresent === []) {
            $lines[] = '- None.';
        } else {
            foreach ($upsertPresent as $package) {
                $lines[] = '- `' . (string) ($package['current_relative_path'] ?? $package['package_key'] ?? '') . '`'
                    . ' | type=`' . (string) ($package['package_type'] ?? 'unknown') . '`'
                    . ' | change=`' . (string) ($package['change_type'] ?? 'modified') . '`'
                    . ' | state=`' . (string) ($package['package_state'] ?? 'unknown') . '`'
                    . ' | action=`' . (string) ($package['recommended_action'] ?? 'skip') . '`'
                    . ' | release=`' . (string) ($package['release_check']['status'] ?? 'skipped') . '`';
            }
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
     * @param  array<string, mixed>  $scope
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    protected function errorResult(
        array $scope,
        array $options,
        string $stage,
        Throwable $exception,
    ): array {
        $result = $this->resultTemplate($scope, $options);
        $result['error'] = [
            'stage' => $stage,
            'message' => $exception->getMessage(),
            'exception_class' => $exception::class,
        ];

        return $result;
    }

    protected function nullableString(mixed $value): ?string
    {
        $resolved = trim((string) $value);

        return $resolved !== '' ? $resolved : null;
    }

    /**
     * @return array<string, mixed>
     */
    abstract protected function planCurrentPackage(string $targetInput): array;

    /**
     * @return array<string, mixed>
     */
    abstract protected function deletedPackageMetadata(string $packageRootRelativePath, string $historicalRef): array;

    /**
     * @param  array<string, mixed>  $metadata
     * @return array<string, mixed>
     */
    abstract protected function deletedPackageOwnership(array $metadata): array;

    /**
     * @return array<string, mixed>
     */
    abstract protected function releaseCheckReport(string $targetInput, string $profile): array;

    /**
     * @param  array<string, mixed>  $left
     * @param  array<string, mixed>  $right
     */
    abstract protected function cleanupPhaseComparator(array $left, array $right): int;

    /**
     * @param  array<string, mixed>  $left
     * @param  array<string, mixed>  $right
     */
    abstract protected function upsertPhaseComparator(array $left, array $right): int;
}
