<?php

namespace App\Support\PackageSeed;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

abstract class AbstractJsonPackageFolderDestroyService extends AbstractJsonPackageFolderDestroyFilesService
{
    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function run(string $targetInput, array $options = []): array
    {
        $resolvedOptions = $this->normalizeOptions($options);
        $planResult = $this->planFolder($targetInput, [
            'mode' => 'destroy',
            'strict' => false,
        ]);
        $result = $this->resultTemplate($targetInput, $resolvedOptions, $planResult);

        if (is_array($result['plan']['error'] ?? null)) {
            $result['error'] = $result['plan']['error'];

            return $this->finalizeDestroyResult($result);
        }

        $result = $this->runPreflight($result, $resolvedOptions);

        if (is_array($result['error'] ?? null)) {
            return $this->finalizeDestroyResult($result);
        }

        if ((int) ($result['plan']['summary']['destroy_candidates'] ?? 0) === 0) {
            return $this->finalizeDestroyResult($result);
        }

        if (! $resolvedOptions['dry_run'] && ! $resolvedOptions['force']) {
            $result['error'] = $this->forceRequiredError();

            return $this->finalizeDestroyResult($result);
        }

        if ($resolvedOptions['dry_run']) {
            return $this->finalizeDestroyResult($result);
        }

        $result = $this->runDbPhase($result, $resolvedOptions);

        if (is_array($result['error'] ?? null)) {
            return $this->finalizeDestroyResult($result);
        }

        return $this->finalizeDestroyResult($this->runFilePhase($result, $resolvedOptions));
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
            . '-destroy-'
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
     * @param  array<string, mixed>  $planResult
     * @return array<string, mixed>
     */
    protected function resultTemplate(string $targetInput, array $options, array $planResult): array
    {
        $planScope = (array) ($planResult['scope'] ?? []);
        $planPackages = array_values((array) ($planResult['packages'] ?? []));

        return [
            'scope' => [
                'input' => trim($targetInput),
                'resolved_root_absolute_path' => $planScope['resolved_root_absolute_path'] ?? null,
                'resolved_root_relative_path' => $planScope['resolved_root_relative_path'] ?? null,
                'single_package' => (bool) ($planScope['single_package'] ?? false),
                'mode' => (string) ($planScope['mode'] ?? 'destroy'),
                'strict' => (bool) $options['strict'],
            ],
            'plan' => [
                'summary' => (array) ($planResult['summary'] ?? $this->defaultPlanSummary()),
                'packages' => $planPackages,
                'error' => is_array($planResult['error'] ?? null) ? $planResult['error'] : null,
            ],
            'preflight' => [
                'executed' => false,
                'db_packages' => $this->initialDbPreflightPackages($planPackages),
                'file_packages' => $this->initialFilePreflightPackages($planPackages),
                'summary' => $this->defaultPreflightSummary(),
            ],
            'execution' => [
                'dry_run' => (bool) $options['dry_run'],
                'force' => (bool) $options['force'],
                'remove_empty_dirs' => (bool) $options['remove_empty_dirs'],
                'fail_fast' => true,
                'folder_transactional' => false,
                'db_phase' => [
                    'started' => 0,
                    'completed' => 0,
                    'succeeded' => 0,
                    'failed' => 0,
                    'stopped_on' => null,
                    'packages' => $this->initialDbExecutionPackages($planPackages),
                ],
                'file_phase' => [
                    'started' => 0,
                    'completed' => 0,
                    'succeeded' => 0,
                    'failed' => 0,
                    'stopped_on' => null,
                    'packages' => $this->initialFileExecutionPackages($planPackages),
                ],
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
            'db_candidates' => 0,
            'file_candidates' => 0,
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
    protected function initialDbPreflightPackages(array $packages): array
    {
        return array_values(array_map(function (array $package): array {
            return [
                'relative_path' => (string) ($package['relative_path'] ?? ''),
                'required' => (bool) ($package['needs_unseed'] ?? false),
                'status' => (bool) ($package['needs_unseed'] ?? false) ? 'pending' : 'skip',
                'ownership' => [
                    'seed_run_present' => (bool) ($package['seed_run_present'] ?? false),
                    'package_present_in_db' => (bool) ($package['package_present_in_db'] ?? false),
                ],
                'impact_counts' => [],
                'warnings' => [],
                'error' => null,
            ];
        }, $packages));
    }

    /**
     * @param  array<int, array<string, mixed>>  $packages
     * @return array<int, array<string, mixed>>
     */
    protected function initialFilePreflightPackages(array $packages): array
    {
        return array_values(array_map(function (array $package): array {
            return [
                'relative_path' => (string) ($package['relative_path'] ?? ''),
                'action' => (bool) ($package['needs_file_destroy'] ?? false) ? 'destroy_files' : 'skip',
                'required' => (bool) ($package['needs_file_destroy'] ?? false),
                'status' => (bool) ($package['needs_file_destroy'] ?? false) ? 'pending' : 'skip',
                'delete_set' => [],
                'dir_prune_set' => [],
                'warnings' => [],
                'failures' => [],
            ];
        }, $packages));
    }

    /**
     * @param  array<int, array<string, mixed>>  $packages
     * @return array<int, array<string, mixed>>
     */
    protected function initialDbExecutionPackages(array $packages): array
    {
        return array_values(array_map(function (array $package): array {
            return [
                'relative_path' => (string) ($package['relative_path'] ?? ''),
                'required' => (bool) ($package['needs_unseed'] ?? false),
                'executed' => false,
                'status' => (bool) ($package['needs_unseed'] ?? false) ? 'pending' : 'skipped',
                'deleted' => false,
                'seed_run_removed' => false,
                'impact_counts' => [],
                'warnings' => [],
                'error' => null,
            ];
        }, $packages));
    }

    /**
     * @param  array<int, array<string, mixed>>  $packages
     * @return array<int, array<string, mixed>>
     */
    protected function initialFileExecutionPackages(array $packages): array
    {
        return array_values(array_map(function (array $package): array {
            return [
                'relative_path' => (string) ($package['relative_path'] ?? ''),
                'required' => (bool) ($package['needs_file_destroy'] ?? false),
                'executed' => false,
                'status' => (bool) ($package['needs_file_destroy'] ?? false) ? 'pending' : 'skipped',
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
            if ((bool) ($package['needs_unseed'] ?? false)) {
                $result['preflight']['db_packages'][$index] = $this->buildDbPreflightPackage(
                    $package,
                    (array) ($result['plan']['packages'] ?? []),
                    $index
                );
            }

            if ((bool) ($package['needs_file_destroy'] ?? false)) {
                $result['preflight']['file_packages'][$index] = array_merge(
                    (array) ($result['preflight']['file_packages'][$index] ?? []),
                    $this->buildPreflightPackage($package, (array) ($result['scope'] ?? []), $options)
                );
            }
        }

        $fileSummaryScratch = $this->defaultPreflightSummary();
        $this->applySharedDeleteSetFailuresToPackages(
            $result['preflight']['file_packages'],
            $fileSummaryScratch
        );
        $result['preflight']['summary'] = $this->rebuildPreflightSummary($result);

        if ((int) ($result['preflight']['summary']['fail'] ?? 0) > 0) {
            $failedPackages = $this->aggregatePreflightPackagesByStatus($result, 'fail');

            $result['error'] = [
                'stage' => 'preflight',
                'reason' => 'preflight_failed',
                'message' => 'Folder destroy preflight failed; live execution did not start.',
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
            $result['error'] = [
                'stage' => 'planning',
                'reason' => 'blocked_packages',
                'message' => 'Planner found blocked packages; combined folder destroy aborted before live execution.',
                'packages' => $blockedPackages,
            ];

            return $result;
        }

        if ($options['strict'] ?? false) {
            $warningPackages = $this->aggregatePreflightPackagesByStatus($result, 'warn');

            if ($warningPackages !== []) {
                $result['error'] = [
                    'stage' => 'preflight',
                    'reason' => 'warnings_are_fatal',
                    'message' => 'Folder destroy preflight returned warnings and --strict is enabled.',
                    'packages' => $warningPackages,
                ];
            }
        }

        return $result;
    }

    /**
     * @param  array<string, mixed>  $package
     * @param  array<int, array<string, mixed>>  $packages
     * @return array<string, mixed>
     */
    protected function buildDbPreflightPackage(array $package, array $packages, int $currentIndex): array
    {
        try {
            $serviceResult = $this->unseedPackage($this->dbExecutionTarget($package), [
                'dry_run' => true,
                'force' => false,
                'strict' => false,
                'additional_cleanup_classes' => $this->additionalCleanupClassesForPackage($packages, $currentIndex),
            ]);
        } catch (Throwable $exception) {
            $serviceResult = [
                'ownership' => [
                    'seed_run_present' => (bool) ($package['seed_run_present'] ?? false),
                    'package_present_in_db' => (bool) ($package['package_present_in_db'] ?? false),
                ],
                'impact' => [
                    'counts' => [],
                    'warnings' => [],
                ],
                'error' => [
                    'stage' => 'preflight',
                    'message' => $exception->getMessage(),
                    'exception_class' => $exception::class,
                ],
            ];
        }

        return [
            'relative_path' => (string) ($package['relative_path'] ?? ''),
            'required' => true,
            'status' => $this->dbPreflightStatus($serviceResult),
            'ownership' => [
                'seed_run_present' => (bool) ($serviceResult['ownership']['seed_run_present'] ?? false),
                'package_present_in_db' => (bool) ($serviceResult['ownership']['package_present_in_db'] ?? false),
            ],
            'impact_counts' => (array) ($serviceResult['impact']['counts'] ?? []),
            'warnings' => array_values(array_map(
                static fn ($warning): string => trim((string) $warning),
                (array) ($serviceResult['impact']['warnings'] ?? [])
            )),
            'error' => is_array($serviceResult['error'] ?? null) ? $serviceResult['error'] : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $serviceResult
     */
    protected function dbPreflightStatus(array $serviceResult): string
    {
        if (is_array($serviceResult['error'] ?? null)) {
            return 'fail';
        }

        return ((array) ($serviceResult['impact']['warnings'] ?? [])) === [] ? 'ok' : 'warn';
    }

    /**
     * @param  array<string, mixed>  $package
     */
    protected function dbExecutionTarget(array $package): string
    {
        $target = trim((string) ($package['relative_path'] ?? ''));

        if ($target === '') {
            throw new \RuntimeException('Planner package is missing relative_path for DB destroy execution.');
        }

        return $target;
    }

    /**
     * @param  array<string, mixed>  $result
     * @return array<string, int>
     */
    protected function rebuildPreflightSummary(array $result): array
    {
        $summary = $this->defaultPreflightSummary();

        foreach ((array) ($result['plan']['packages'] ?? []) as $index => $package) {
            if (($package['recommended_action'] ?? null) !== 'destroy') {
                $summary['skipped']++;

                continue;
            }

            $summary['candidates']++;

            if ((bool) ($package['needs_unseed'] ?? false)) {
                $summary['db_candidates']++;
            }

            if ((bool) ($package['needs_file_destroy'] ?? false)) {
                $summary['file_candidates']++;
            }

            $status = $this->aggregatePreflightStatus(
                $package,
                (array) ($result['preflight']['db_packages'][$index] ?? []),
                (array) ($result['preflight']['file_packages'][$index] ?? [])
            );

            $summary[$status]++;
        }

        return $summary;
    }

    /**
     * @param  array<string, mixed>  $package
     * @param  array<string, mixed>  $dbPreflight
     * @param  array<string, mixed>  $filePreflight
     */
    protected function aggregatePreflightStatus(array $package, array $dbPreflight, array $filePreflight): string
    {
        $statuses = collect([
            (bool) ($package['needs_unseed'] ?? false) ? ($dbPreflight['status'] ?? 'skip') : 'skip',
            (bool) ($package['needs_file_destroy'] ?? false) ? ($filePreflight['status'] ?? 'skip') : 'skip',
        ]);
        $warnings = $this->packageWarningsForDestroyResult($package, $dbPreflight, $filePreflight);

        if ($statuses->contains(static fn ($status): bool => $status === 'fail')) {
            return 'fail';
        }

        if ($warnings !== []) {
            return 'warn';
        }

        return 'ok';
    }

    /**
     * @param  array<string, mixed>  $result
     * @return list<string>
     */
    protected function aggregatePreflightPackagesByStatus(array $result, string $status): array
    {
        return collect((array) ($result['plan']['packages'] ?? []))
            ->values()
            ->filter(function (array $package, int $index) use ($result, $status): bool {
                if (($package['recommended_action'] ?? null) !== 'destroy') {
                    return false;
                }

                return $this->aggregatePreflightStatus(
                    $package,
                    (array) ($result['preflight']['db_packages'][$index] ?? []),
                    (array) ($result['preflight']['file_packages'][$index] ?? [])
                ) === $status;
            })
            ->pluck('relative_path')
            ->map(static fn ($path): string => trim((string) $path))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $package
     * @param  array<string, mixed>  $dbPhase
     * @param  array<string, mixed>  $filePhase
     * @return list<string>
     */
    protected function packageWarningsForDestroyResult(array $package, array $dbPhase, array $filePhase): array
    {
        return collect((array) ($package['warnings'] ?? []))
            ->merge((array) ($dbPhase['warnings'] ?? []))
            ->merge((array) ($filePhase['warnings'] ?? []))
            ->map(static fn ($warning): string => trim((string) $warning))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $result
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    protected function runDbPhase(array $result, array $options): array
    {
        foreach ((array) ($result['plan']['packages'] ?? []) as $index => $package) {
            if (! ($package['needs_unseed'] ?? false)) {
                continue;
            }

            $result['execution']['db_phase']['started']++;
            $result['execution']['db_phase']['packages'][$index]['executed'] = true;

            try {
                $serviceResult = $this->unseedPackage($this->dbExecutionTarget($package), [
                    'dry_run' => false,
                    'force' => true,
                    'strict' => false,
                    'additional_cleanup_classes' => $this->additionalCleanupClassesForPackage(
                        (array) ($result['plan']['packages'] ?? []),
                        $index
                    ),
                ]);
            } catch (Throwable $exception) {
                $serviceResult = [
                    'impact' => [
                        'counts' => [],
                        'warnings' => [],
                    ],
                    'result' => [
                        'deleted' => false,
                        'rolled_back' => false,
                        'seed_run_removed' => false,
                    ],
                    'error' => [
                        'stage' => 'execution',
                        'message' => $exception->getMessage(),
                        'exception_class' => $exception::class,
                    ],
                ];
            }

            $result['execution']['db_phase']['completed']++;
            $result['execution']['db_phase']['packages'][$index] = [
                'relative_path' => (string) ($package['relative_path'] ?? ''),
                'required' => true,
                'executed' => true,
                'status' => $this->dbServiceSucceeded($serviceResult) ? 'ok' : 'failed',
                'deleted' => (bool) ($serviceResult['result']['deleted'] ?? false),
                'seed_run_removed' => (bool) ($serviceResult['result']['seed_run_removed'] ?? false),
                'impact_counts' => (array) ($serviceResult['impact']['counts'] ?? []),
                'warnings' => array_values(array_map(
                    static fn ($warning): string => trim((string) $warning),
                    (array) ($serviceResult['impact']['warnings'] ?? [])
                )),
                'error' => is_array($serviceResult['error'] ?? null) ? $serviceResult['error'] : null,
            ];

            if ($this->dbServiceSucceeded($serviceResult)) {
                $result['execution']['db_phase']['succeeded']++;

                continue;
            }

            $result['execution']['db_phase']['failed']++;
            $result['execution']['db_phase']['stopped_on'] = (string) ($package['relative_path'] ?? '');
            $result['error'] = [
                'stage' => 'execution',
                'reason' => 'db_phase_failed',
                'message' => 'Folder destroy stopped during DB phase; file phase did not start.',
                'package' => (string) ($package['relative_path'] ?? ''),
                'service_error' => $serviceResult['error'] ?? null,
            ];

            break;
        }

        return $result;
    }

    /**
     * @param  array<string, mixed>  $serviceResult
     */
    protected function dbServiceSucceeded(array $serviceResult): bool
    {
        if (is_array($serviceResult['error'] ?? null)) {
            return false;
        }

        if ((bool) ($serviceResult['result']['deleted'] ?? false)) {
            return true;
        }

        if ((bool) ($serviceResult['result']['seed_run_removed'] ?? false)) {
            return true;
        }

        return ! (bool) ($serviceResult['ownership']['seed_run_present'] ?? true)
            && ! (bool) ($serviceResult['ownership']['package_present_in_db'] ?? true);
    }

    /**
     * @param  array<string, mixed>  $result
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    protected function runFilePhase(array $result, array $options): array
    {
        foreach ((array) ($result['plan']['packages'] ?? []) as $index => $package) {
            if (! ($package['needs_file_destroy'] ?? false)) {
                continue;
            }

            $result['execution']['file_phase']['started']++;
            $result['execution']['file_phase']['packages'][$index]['executed'] = true;
            $packageExecution = $this->executePackageDeletion(
                (array) ($result['preflight']['file_packages'][$index] ?? []),
                (array) ($result['scope'] ?? []),
                $options
            );
            $result['execution']['file_phase']['completed']++;
            $result['execution']['file_phase']['packages'][$index] = array_merge(
                (array) ($result['execution']['file_phase']['packages'][$index] ?? []),
                $packageExecution
            );

            if (($packageExecution['status'] ?? null) === 'ok') {
                $result['execution']['file_phase']['succeeded']++;

                continue;
            }

            $result['execution']['file_phase']['failed']++;
            $result['execution']['file_phase']['stopped_on'] = (string) ($package['relative_path'] ?? '');
            $result['error'] = [
                'stage' => 'execution',
                'reason' => 'file_phase_failed',
                'message' => 'Folder destroy stopped during file phase after DB phase completed.',
                'package' => (string) ($package['relative_path'] ?? ''),
                'failed_paths' => (array) ($packageExecution['failed_paths'] ?? []),
            ];

            break;
        }

        return $result;
    }

    /**
     * @param  array<int, array<string, mixed>>  $packages
     * @return list<string>
     */
    protected function additionalCleanupClassesForPackage(array $packages, int $currentIndex): array
    {
        return collect(array_slice($packages, 0, $currentIndex))
            ->filter(static fn (array $package): bool => (bool) ($package['needs_unseed'] ?? false))
            ->map(static fn (array $package): string => trim((string) ($package['resolved_seeder_class'] ?? '')))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    protected function forceRequiredError(): array
    {
        return [
            'stage' => 'safety',
            'reason' => 'force_required',
            'message' => 'Live folder destroy requires --force.',
        ];
    }

    /**
     * @param  array<string, mixed>  $result
     */
    protected function renderMarkdownReport(array $result): string
    {
        $lines = [
            '# JSON Folder Destroy',
            '',
            '- Target: `' . (string) ($result['scope']['input'] ?? '') . '`',
            '- Scope Root: `' . (string) ($result['scope']['resolved_root_relative_path'] ?? '') . '`',
            '- Single Package: `' . $this->boolString((bool) ($result['scope']['single_package'] ?? false)) . '`',
            '- Mode: `' . (string) ($result['scope']['mode'] ?? 'destroy') . '`',
            '- Dry Run: `' . $this->boolString((bool) ($result['execution']['dry_run'] ?? false)) . '`',
            '- Force: `' . $this->boolString((bool) ($result['execution']['force'] ?? false)) . '`',
            '- Remove Empty Dirs: `' . $this->boolString((bool) ($result['execution']['remove_empty_dirs'] ?? false)) . '`',
            '- Strict: `' . $this->boolString((bool) ($result['scope']['strict'] ?? false)) . '`',
            '',
            '## Plan Summary',
            '',
            '- Total Packages: ' . (int) ($result['plan']['summary']['total_packages'] ?? 0),
            '- Destroy Candidates: ' . (int) ($result['plan']['summary']['destroy_candidates'] ?? 0),
            '- Skipped: ' . (int) ($result['plan']['summary']['skipped'] ?? 0),
            '- Blocked: ' . (int) ($result['plan']['summary']['blocked'] ?? 0),
            '- Warnings: ' . (int) ($result['plan']['summary']['warnings'] ?? 0),
            '',
            '## Preflight Summary',
            '',
            '- Candidates: ' . (int) ($result['preflight']['summary']['candidates'] ?? 0),
            '- DB Candidates: ' . (int) ($result['preflight']['summary']['db_candidates'] ?? 0),
            '- File Candidates: ' . (int) ($result['preflight']['summary']['file_candidates'] ?? 0),
            '- OK: ' . (int) ($result['preflight']['summary']['ok'] ?? 0),
            '- Warn: ' . (int) ($result['preflight']['summary']['warn'] ?? 0),
            '- Fail: ' . (int) ($result['preflight']['summary']['fail'] ?? 0),
            '- Skipped: ' . (int) ($result['preflight']['summary']['skipped'] ?? 0),
            '',
            '## Packages',
            '',
        ];

        foreach ((array) ($result['plan']['packages'] ?? []) as $index => $package) {
            $dbPreflight = (array) (($result['preflight']['db_packages'][$index] ?? []) ?: []);
            $filePreflight = (array) (($result['preflight']['file_packages'][$index] ?? []) ?: []);
            $dbExecution = (array) (($result['execution']['db_phase']['packages'][$index] ?? []) ?: []);
            $fileExecution = (array) (($result['execution']['file_phase']['packages'][$index] ?? []) ?: []);

            $lines[] = '- `' . (string) ($package['relative_path'] ?? '') . '`'
                . ' | action=`' . (string) ($package['recommended_action'] ?? 'skip') . '`'
                . ' | needs_unseed=`' . $this->boolString((bool) ($package['needs_unseed'] ?? false)) . '`'
                . ' | needs_file_destroy=`' . $this->boolString((bool) ($package['needs_file_destroy'] ?? false)) . '`'
                . ' | db_preflight=`' . (string) ($dbPreflight['status'] ?? 'skip') . '`'
                . ' | file_preflight=`' . (string) ($filePreflight['status'] ?? 'skip') . '`'
                . ' | db_execution=`' . (string) ($dbExecution['status'] ?? 'pending') . '`'
                . ' | file_execution=`' . (string) ($fileExecution['status'] ?? 'pending') . '`';
        }

        $lines[] = '';
        $lines[] = '## DB Phase';
        $lines[] = '';
        $lines[] = '- Started: ' . (int) ($result['execution']['db_phase']['started'] ?? 0);
        $lines[] = '- Completed: ' . (int) ($result['execution']['db_phase']['completed'] ?? 0);
        $lines[] = '- Succeeded: ' . (int) ($result['execution']['db_phase']['succeeded'] ?? 0);
        $lines[] = '- Failed: ' . (int) ($result['execution']['db_phase']['failed'] ?? 0);
        $lines[] = '- Stopped On: `' . (string) ($result['execution']['db_phase']['stopped_on'] ?? '') . '`';
        $lines[] = '';
        $lines[] = '## File Phase';
        $lines[] = '';
        $lines[] = '- Started: ' . (int) ($result['execution']['file_phase']['started'] ?? 0);
        $lines[] = '- Completed: ' . (int) ($result['execution']['file_phase']['completed'] ?? 0);
        $lines[] = '- Succeeded: ' . (int) ($result['execution']['file_phase']['succeeded'] ?? 0);
        $lines[] = '- Failed: ' . (int) ($result['execution']['file_phase']['failed'] ?? 0);
        $lines[] = '- Stopped On: `' . (string) ($result['execution']['file_phase']['stopped_on'] ?? '') . '`';

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
    protected function finalizeDestroyResult(array $result): array
    {
        $result['preflight']['file_packages'] = array_values(array_map(function (array $package): array {
            return collect($package)
                ->reject(static fn (mixed $value, string $key): bool => Str::startsWith($key, '_'))
                ->all();
        }, (array) ($result['preflight']['file_packages'] ?? [])));

        return $result;
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    abstract protected function unseedPackage(string $targetInput, array $options): array;
}
