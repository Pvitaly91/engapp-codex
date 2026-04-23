<?php

namespace App\Support\PackageSeed;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

abstract class AbstractJsonPackageChangedApplyService
{
    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function run(?string $targetInput = null, array $options = []): array
    {
        $resolvedOptions = $this->normalizeOptions($options);
        $normalizedTarget = trim((string) ($targetInput ?? ''));
        $planResult = $this->planChanged($normalizedTarget !== '' ? $normalizedTarget : null, [
            'base' => $resolvedOptions['base'],
            'head' => $resolvedOptions['head'],
            'staged' => $resolvedOptions['staged'],
            'working_tree' => $resolvedOptions['working_tree'],
            'include_untracked' => $resolvedOptions['include_untracked'],
            'with_release_check' => $resolvedOptions['with_release_check'],
            'check_profile' => $resolvedOptions['check_profile'],
            'strict' => $resolvedOptions['strict'],
        ]);
        $result = $this->resultTemplate($normalizedTarget, $resolvedOptions, $planResult);

        if (is_array($result['plan']['error'] ?? null)) {
            $result['error'] = $result['plan']['error'];

            return $result;
        }

        $result = $this->runPreflight($result, $resolvedOptions);

        if (is_array($result['error'] ?? null)) {
            return $result;
        }

        if (! $resolvedOptions['dry_run'] && ! $resolvedOptions['force']) {
            $result['error'] = [
                'stage' => 'safety',
                'reason' => 'force_required',
                'message' => 'Live changed-package apply requires --force.',
            ];

            return $result;
        }

        if ($resolvedOptions['dry_run']) {
            return $result;
        }

        $result = $this->runCleanupDeletedPhase($result, $resolvedOptions);

        if (is_array($result['error'] ?? null)) {
            return $result;
        }

        return $this->runUpsertPresentPhase($result, $resolvedOptions);
    }

    /**
     * @param  array<string, mixed>  $result
     */
    public function writeReport(array $result): string
    {
        $scopeRelativePath = (string) ($result['scope']['resolved_root_relative_path'] ?? 'scope');
        $scopeName = basename(str_replace('\\', '/', $scopeRelativePath));
        $mode = (string) ($result['diff']['mode'] ?? 'working-tree');
        $runType = (bool) ($result['execution']['dry_run'] ?? false) ? 'dry-run' : 'live';
        $hash = substr(sha1($scopeRelativePath . '|' . $mode . '|' . $runType), 0, 8);
        $fileName = Str::slug($scopeName !== '' ? $scopeName : 'scope')
            . '-changed-apply-'
            . Str::slug($mode)
            . '-'
            . $runType
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
        $checkProfile = strtolower(trim((string) ($options['check_profile'] ?? 'release')));

        if (! in_array($checkProfile, ['scaffold', 'release'], true)) {
            throw new RuntimeException('Unsupported release-check profile. Use scaffold or release.');
        }

        return [
            'base' => trim((string) ($options['base'] ?? '')),
            'head' => trim((string) ($options['head'] ?? '')),
            'staged' => (bool) ($options['staged'] ?? false),
            'working_tree' => (bool) ($options['working_tree'] ?? false),
            'include_untracked' => (bool) ($options['include_untracked'] ?? false),
            'dry_run' => (bool) ($options['dry_run'] ?? false),
            'force' => (bool) ($options['force'] ?? false),
            'with_release_check' => (bool) ($options['with_release_check'] ?? false),
            'skip_release_check' => (bool) ($options['skip_release_check'] ?? false),
            'check_profile' => $checkProfile,
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
        $planPackages = array_values((array) ($planResult['packages'] ?? []));
        $planPhases = (array) ($planResult['phases'] ?? []);

        return [
            'diff' => [
                'mode' => (string) ($planResult['diff']['mode'] ?? 'working_tree'),
                'base' => $planResult['diff']['base'] ?? ($options['base'] !== '' ? $options['base'] : null),
                'head' => $planResult['diff']['head'] ?? ($options['head'] !== '' ? $options['head'] : null),
                'include_untracked' => (bool) ($planResult['diff']['include_untracked'] ?? $options['include_untracked']),
            ],
            'scope' => [
                'input' => $targetInput,
                'resolved_root_absolute_path' => $planScope['resolved_root_absolute_path'] ?? null,
                'resolved_root_relative_path' => $planScope['resolved_root_relative_path'] ?? null,
                'single_package' => (bool) ($planScope['single_package'] ?? false),
                'with_release_check' => (bool) $options['with_release_check'],
                'skip_release_check' => (bool) $options['skip_release_check'],
                'check_profile' => (string) $options['check_profile'],
                'strict' => (bool) $options['strict'],
            ],
            'plan' => [
                'summary' => (array) ($planResult['summary'] ?? $this->defaultPlanSummary()),
                'phases' => [
                    'cleanup_deleted' => array_values((array) ($planPhases['cleanup_deleted'] ?? [])),
                    'upsert_present' => array_values((array) ($planPhases['upsert_present'] ?? [])),
                ],
                'packages' => $planPackages,
                'error' => is_array($planResult['error'] ?? null) ? $planResult['error'] : null,
            ],
            'preflight' => [
                'executed' => false,
                'summary' => $this->defaultPreflightSummary(),
                'packages' => $this->initialPreflightPackages($planPackages),
            ],
            'execution' => [
                'dry_run' => (bool) $options['dry_run'],
                'force' => (bool) $options['force'],
                'fail_fast' => true,
                'folder_transactional' => false,
                'cleanup_deleted' => $this->initialPhaseExecution(
                    array_values((array) ($planPhases['cleanup_deleted'] ?? []))
                ),
                'upsert_present' => $this->initialPhaseExecution(
                    array_values((array) ($planPhases['upsert_present'] ?? []))
                ),
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
            'changed_packages' => 0,
            'seed_candidates' => 0,
            'refresh_candidates' => 0,
            'deleted_cleanup_candidates' => 0,
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
                'package_key' => (string) ($package['package_key'] ?? $this->packageKey($package)),
                'relative_path' => $this->packagePath($package),
                'phase' => (string) ($package['recommended_phase'] ?? 'none'),
                'action' => $action,
                'status' => in_array($action, ['seed', 'refresh', 'unseed_deleted'], true) ? 'pending' : 'skip',
                'warnings' => [],
                'service_result' => null,
            ];
        }, $packages));
    }

    /**
     * @param  array<int, array<string, mixed>>  $packages
     * @return array<string, mixed>
     */
    protected function initialPhaseExecution(array $packages): array
    {
        return [
            'started' => 0,
            'completed' => 0,
            'succeeded' => 0,
            'failed' => 0,
            'stopped_on' => null,
            'packages' => array_values(array_map(function (array $package): array {
                return [
                    'package_key' => (string) ($package['package_key'] ?? $this->packageKey($package)),
                    'relative_path' => $this->packagePath($package),
                    'action' => (string) ($package['recommended_action'] ?? 'skip'),
                    'executed' => false,
                    'status' => 'pending',
                    'seed_run_removed' => false,
                    'seed_run_logged' => false,
                    'service_result' => null,
                ];
            }, $packages)),
        ];
    }

    /**
     * @param  array<string, mixed>  $result
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    protected function runPreflight(array $result, array $options): array
    {
        $result['preflight']['executed'] = true;
        $packages = (array) ($result['plan']['packages'] ?? []);

        foreach ($packages as $index => $package) {
            $action = (string) ($package['recommended_action'] ?? 'skip');

            if (! in_array($action, ['seed', 'refresh', 'unseed_deleted'], true)) {
                $result['preflight']['summary']['skipped']++;

                continue;
            }

            $result['preflight']['summary']['candidates']++;

            try {
                $serviceResult = $this->preflightServiceResult($package, $packages, $index, $options);
            } catch (Throwable $exception) {
                $serviceResult = [
                    'error' => [
                        'stage' => 'preflight',
                        'message' => $exception->getMessage(),
                        'exception_class' => $exception::class,
                    ],
                ];
            }

            $warnings = $this->serviceWarnings($action, $serviceResult);
            $status = is_array($serviceResult['error'] ?? null)
                ? 'fail'
                : ($warnings === [] ? 'ok' : 'warn');

            $result['preflight']['packages'][$index]['status'] = $status;
            $result['preflight']['packages'][$index]['warnings'] = $warnings;
            $result['preflight']['packages'][$index]['service_result'] = $serviceResult;
            $result['preflight']['summary'][$status]++;
        }

        $blockedPackages = array_values(array_filter(
            $packages,
            static fn (array $package): bool => ($package['recommended_action'] ?? null) === 'blocked'
        ));
        $failedPreflight = array_values(array_filter(
            (array) ($result['preflight']['packages'] ?? []),
            static fn (array $package): bool => ($package['status'] ?? null) === 'fail'
        ));
        $warnPreflight = array_values(array_filter(
            (array) ($result['preflight']['packages'] ?? []),
            static fn (array $package): bool => ($package['status'] ?? null) === 'warn'
        ));

        if ($failedPreflight !== []) {
            $result['error'] = [
                'stage' => 'preflight',
                'reason' => 'preflight_failed',
                'message' => 'Changed-package apply preflight failed; live execution did not start.',
                'package' => (string) ($failedPreflight[0]['relative_path'] ?? $failedPreflight[0]['package_key'] ?? ''),
                'phase' => (string) ($failedPreflight[0]['phase'] ?? 'unknown'),
                'service_error' => $failedPreflight[0]['service_result']['error'] ?? null,
            ];

            return $result;
        }

        if ($blockedPackages !== []) {
            $result['error'] = [
                'stage' => 'planning',
                'reason' => 'blocked_packages',
                'message' => 'Changed-package planner found blocked packages; live execution did not start.',
                'packages' => array_values(array_map(
                    fn (array $package): string => $this->packagePath($package),
                    $blockedPackages
                )),
            ];

            return $result;
        }

        if (($options['strict'] ?? false) && $warnPreflight !== []) {
            $result['error'] = [
                'stage' => 'preflight',
                'reason' => 'warnings_are_fatal',
                'message' => 'Changed-package preflight returned warnings and --strict is enabled.',
                'packages' => array_values(array_map(
                    static fn (array $package): string => (string) ($package['relative_path'] ?? $package['package_key'] ?? ''),
                    $warnPreflight
                )),
            ];
        }

        return $result;
    }

    /**
     * @param  array<string, mixed>  $result
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    protected function runCleanupDeletedPhase(array $result, array $options): array
    {
        $packages = array_values((array) ($result['plan']['phases']['cleanup_deleted'] ?? []));

        foreach ($packages as $index => $package) {
            $result['execution']['cleanup_deleted']['started']++;

            try {
                $serviceResult = $this->cleanupDeletedPackage($package, array_merge($this->deletedCleanupOptions(
                    (array) ($result['plan']['packages'] ?? []),
                    $package,
                    $options
                ), [
                    'dry_run' => false,
                    'force' => true,
                    'strict' => (bool) ($options['strict'] ?? false),
                ]));
            } catch (Throwable $exception) {
                $serviceResult = [
                    'error' => [
                        'stage' => 'cleanup_deleted',
                        'message' => $exception->getMessage(),
                        'exception_class' => $exception::class,
                    ],
                ];
            }

            $phasePackage =& $result['execution']['cleanup_deleted']['packages'][$index];
            $phasePackage['executed'] = true;
            $phasePackage['service_result'] = $serviceResult;
            $phasePackage['seed_run_removed'] = (bool) ($serviceResult['result']['seed_run_removed'] ?? false);
            $result['execution']['cleanup_deleted']['completed']++;

            if ($this->deletedCleanupSucceeded($serviceResult)) {
                $phasePackage['status'] = 'ok';
                $result['execution']['cleanup_deleted']['succeeded']++;

                continue;
            }

            $phasePackage['status'] = 'failed';
            $result['execution']['cleanup_deleted']['failed']++;
            $result['execution']['cleanup_deleted']['stopped_on'] = $this->packagePath($package);
            $result['error'] = [
                'stage' => 'execution',
                'phase' => 'cleanup_deleted',
                'reason' => 'cleanup_deleted_failed',
                'message' => 'Changed-package apply stopped during deleted cleanup phase.',
                'package' => $this->packagePath($package),
                'service_error' => $serviceResult['error'] ?? null,
            ];

            break;
        }

        return $result;
    }

    /**
     * @param  array<string, mixed>  $result
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    protected function runUpsertPresentPhase(array $result, array $options): array
    {
        $packages = array_values((array) ($result['plan']['phases']['upsert_present'] ?? []));

        foreach ($packages as $index => $package) {
            $action = (string) ($package['recommended_action'] ?? 'skip');
            $result['execution']['upsert_present']['started']++;

            try {
                $serviceResult = $this->executeUpsertAction($action, $package, $options);
            } catch (Throwable $exception) {
                $serviceResult = [
                    'error' => [
                        'stage' => 'upsert_present',
                        'message' => $exception->getMessage(),
                        'exception_class' => $exception::class,
                    ],
                ];
            }

            $phasePackage =& $result['execution']['upsert_present']['packages'][$index];
            $phasePackage['executed'] = true;
            $phasePackage['service_result'] = $serviceResult;
            $phasePackage['seed_run_logged'] = (bool) ($serviceResult['result']['seed_run_logged'] ?? false);
            $result['execution']['upsert_present']['completed']++;

            if ($this->upsertSucceeded($action, $serviceResult)) {
                $phasePackage['status'] = 'ok';
                $result['execution']['upsert_present']['succeeded']++;

                continue;
            }

            $phasePackage['status'] = 'failed';
            $result['execution']['upsert_present']['failed']++;
            $result['execution']['upsert_present']['stopped_on'] = $this->packagePath($package);
            $result['error'] = [
                'stage' => 'execution',
                'phase' => 'upsert_present',
                'reason' => 'upsert_present_failed',
                'message' => 'Changed-package apply stopped during current-package upsert phase.',
                'package' => $this->packagePath($package),
                'action' => $action,
                'service_error' => $serviceResult['error'] ?? null,
            ];

            break;
        }

        return $result;
    }

    /**
     * @param  array<string, mixed>  $package
     * @param  array<int, array<string, mixed>>  $allPackages
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    protected function preflightServiceResult(array $package, array $allPackages, int $index, array $options): array
    {
        $action = (string) ($package['recommended_action'] ?? 'skip');

        return match ($action) {
            'unseed_deleted' => $this->cleanupDeletedPackage($package, array_merge($this->deletedCleanupOptions(
                $allPackages,
                $package,
                $options
            ), [
                'dry_run' => true,
                'force' => false,
                'strict' => false,
            ])),
            'seed' => $this->seedPackage($this->executionTarget($package), [
                'dry_run' => true,
                'skip_release_check' => (bool) ($options['skip_release_check'] ?? false),
                'check_profile' => (string) ($options['check_profile'] ?? 'release'),
                'strict' => false,
            ]),
            'refresh' => $this->refreshPackage($this->executionTarget($package), [
                'dry_run' => true,
                'skip_release_check' => (bool) ($options['skip_release_check'] ?? false),
                'check_profile' => (string) ($options['check_profile'] ?? 'release'),
                'strict' => false,
                'force' => false,
            ]),
            default => throw new RuntimeException(sprintf('Unsupported changed-package preflight action [%s].', $action)),
        };
    }

    /**
     * @param  array<string, mixed>  $package
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    protected function executeUpsertAction(string $action, array $package, array $options): array
    {
        return match ($action) {
            'seed' => $this->seedPackage($this->executionTarget($package), [
                'dry_run' => false,
                'skip_release_check' => (bool) ($options['skip_release_check'] ?? false),
                'check_profile' => (string) ($options['check_profile'] ?? 'release'),
                'strict' => (bool) ($options['strict'] ?? false),
            ]),
            'refresh' => $this->refreshPackage($this->executionTarget($package), [
                'dry_run' => false,
                'force' => true,
                'skip_release_check' => (bool) ($options['skip_release_check'] ?? false),
                'check_profile' => (string) ($options['check_profile'] ?? 'release'),
                'strict' => (bool) ($options['strict'] ?? false),
            ]),
            default => throw new RuntimeException(sprintf('Unsupported changed-package upsert action [%s].', $action)),
        };
    }

    /**
     * @param  array<string, mixed>  $package
     * @return list<string>
     */
    protected function serviceWarnings(string $action, array $serviceResult): array
    {
        $warnings = collect();

        if ($action === 'unseed_deleted') {
            $warnings = $warnings->merge((array) ($serviceResult['impact']['warnings'] ?? []));
        }

        if (in_array($action, ['seed', 'refresh'], true)) {
            $releaseWarnings = (int) ($serviceResult['preflight']['summary']['check_counts']['warn'] ?? 0);

            if ($releaseWarnings > 0) {
                $warnings->push(sprintf('Release-check returned %d warning(s).', $releaseWarnings));
            }
        }

        if ($action === 'refresh') {
            $warnings = $warnings->merge((array) ($serviceResult['warnings'] ?? []));
        }

        return $warnings
            ->map(static fn ($warning): string => trim((string) $warning))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    protected function deletedCleanupSucceeded(array $serviceResult): bool
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

    protected function upsertSucceeded(string $action, array $serviceResult): bool
    {
        if (is_array($serviceResult['error'] ?? null)) {
            return false;
        }

        return match ($action) {
            'seed' => (bool) ($serviceResult['result']['seeded'] ?? false),
            'refresh' => (bool) ($serviceResult['result']['refreshed'] ?? false),
            default => false,
        };
    }

    /**
     * @param  array<string, mixed>  $package
     */
    protected function executionTarget(array $package): string
    {
        $target = trim((string) ($package['current_relative_path'] ?? '') ?: (string) ($package['definition_relative_path'] ?? ''));

        if ($target === '') {
            throw new RuntimeException(sprintf(
                'Changed-package planner did not provide an executable current-package target for [%s].',
                $this->packagePath($package)
            ));
        }

        if (! str_ends_with(strtolower($target), '/definition.json')) {
            $target = rtrim($target, '/') . '/definition.json';
        }

        return $target;
    }

    /**
     * @param  array<int, array<string, mixed>>  $allPackages
     * @param  array<string, mixed>  $package
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    protected function deletedCleanupOptions(array $allPackages, array $package, array $options): array
    {
        $currentIndex = array_search((string) ($package['package_key'] ?? $this->packageKey($package)), array_map(
            fn (array $candidate): string => (string) ($candidate['package_key'] ?? $this->packageKey($candidate)),
            $allPackages
        ), true);

        if (! is_int($currentIndex) || $currentIndex <= 0) {
            return [];
        }

        $previousCleanupClasses = collect(array_slice($allPackages, 0, $currentIndex))
            ->filter(static fn (array $candidate): bool => ($candidate['recommended_action'] ?? null) === 'unseed_deleted')
            ->map(static fn (array $candidate): string => trim((string) ($candidate['resolved_seeder_class'] ?? '')))
            ->filter()
            ->unique()
            ->values()
            ->all();

        return $previousCleanupClasses === []
            ? []
            : ['additional_cleanup_classes' => $previousCleanupClasses];
    }

    /**
     * @param  array<string, mixed>  $package
     */
    protected function packagePath(array $package): string
    {
        return (string) (($package['current_relative_path'] ?? null)
            ?: ($package['historical_relative_path'] ?? null)
            ?: ($package['relative_path'] ?? null)
            ?: ($package['package_key'] ?? ''));
    }

    /**
     * @param  array<string, mixed>  $package
     */
    protected function packageKey(array $package): string
    {
        return (string) ($package['package_key'] ?? $this->packagePath($package));
    }

    /**
     * @param  array<string, mixed>  $result
     */
    protected function renderMarkdownReport(array $result): string
    {
        $lines = [
            '# Changed Package Apply',
            '',
            '- Diff Mode: `' . (string) ($result['diff']['mode'] ?? 'working_tree') . '`',
            '- Base: `' . (string) (($result['diff']['base'] ?? null) ?: '') . '`',
            '- Head: `' . (string) (($result['diff']['head'] ?? null) ?: '') . '`',
            '- Include Untracked: `' . (((bool) ($result['diff']['include_untracked'] ?? false)) ? 'true' : 'false') . '`',
            '- Scope Root: `' . (string) ($result['scope']['resolved_root_relative_path'] ?? '') . '`',
            '- Dry Run: `' . (((bool) ($result['execution']['dry_run'] ?? false)) ? 'true' : 'false') . '`',
            '- Force: `' . (((bool) ($result['execution']['force'] ?? false)) ? 'true' : 'false') . '`',
            '- Planner Release Check: `' . (((bool) ($result['scope']['with_release_check'] ?? false)) ? 'true' : 'false') . '`',
            '- Runtime Release Check Skipped: `' . (((bool) ($result['scope']['skip_release_check'] ?? false)) ? 'true' : 'false') . '`',
            '- Check Profile: `' . (string) ($result['scope']['check_profile'] ?? 'release') . '`',
            '- Strict: `' . (((bool) ($result['scope']['strict'] ?? false)) ? 'true' : 'false') . '`',
            '',
            '## Plan Summary',
            '',
            '- Changed Packages: ' . (int) ($result['plan']['summary']['changed_packages'] ?? 0),
            '- Deleted Cleanup Candidates: ' . (int) ($result['plan']['summary']['deleted_cleanup_candidates'] ?? 0),
            '- Seed Candidates: ' . (int) ($result['plan']['summary']['seed_candidates'] ?? 0),
            '- Refresh Candidates: ' . (int) ($result['plan']['summary']['refresh_candidates'] ?? 0),
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
            '## Deleted Packages Cleanup Phase',
            '',
        ];

        $cleanupPackages = (array) ($result['execution']['cleanup_deleted']['packages'] ?? []);

        if ($cleanupPackages === []) {
            $lines[] = '- None.';
        } else {
            foreach ($cleanupPackages as $package) {
                $lines[] = '- `' . (string) ($package['relative_path'] ?? '') . '`'
                    . ' | action=`' . (string) ($package['action'] ?? 'unseed_deleted') . '`'
                    . ' | status=`' . (string) ($package['status'] ?? 'pending') . '`'
                    . ' | seed_run_removed=`' . (((bool) ($package['seed_run_removed'] ?? false)) ? 'true' : 'false') . '`';
            }
        }

        $lines[] = '';
        $lines[] = '## Current Packages Upsert Phase';
        $lines[] = '';

        $upsertPackages = (array) ($result['execution']['upsert_present']['packages'] ?? []);

        if ($upsertPackages === []) {
            $lines[] = '- None.';
        } else {
            foreach ($upsertPackages as $package) {
                $lines[] = '- `' . (string) ($package['relative_path'] ?? '') . '`'
                    . ' | action=`' . (string) ($package['action'] ?? 'seed') . '`'
                    . ' | status=`' . (string) ($package['status'] ?? 'pending') . '`'
                    . ' | seed_run_logged=`' . (((bool) ($package['seed_run_logged'] ?? false)) ? 'true' : 'false') . '`';
            }
        }

        if (is_array($result['error'] ?? null)) {
            $lines[] = '';
            $lines[] = '## Error';
            $lines[] = '';
            $lines[] = '- Stage: `' . (string) ($result['error']['stage'] ?? 'execution') . '`';
            $lines[] = '- Message: ' . (string) ($result['error']['message'] ?? 'Unknown error.');
        }

        return implode(PHP_EOL, $lines) . PHP_EOL;
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    abstract protected function planChanged(?string $targetInput, array $options): array;

    /**
     * @param  array<string, mixed>  $package
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    abstract protected function cleanupDeletedPackage(array $package, array $options): array;

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    abstract protected function seedPackage(string $targetInput, array $options): array;

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    abstract protected function refreshPackage(string $targetInput, array $options): array;

    abstract protected function reportDirectory(): string;
}
