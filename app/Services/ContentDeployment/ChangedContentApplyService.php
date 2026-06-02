<?php

namespace App\Services\ContentDeployment;

use App\Services\PageV3PromptGenerator\PageV3DeletedPackageCleanupService;
use App\Services\PageV3PromptGenerator\PageV3PackageRefreshService;
use App\Services\PageV3PromptGenerator\PageV3PackageSeedService;
use App\Services\V3PromptGenerator\V3DeletedPackageCleanupService;
use App\Services\V3PromptGenerator\V3PackageRefreshService;
use App\Services\V3PromptGenerator\V3PackageSeedService;
use App\Support\PackageSeed\AbstractCrossDomainChangedContentService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class ChangedContentApplyService extends AbstractCrossDomainChangedContentService
{
    public function __construct(
        private readonly ChangedContentPlanService $changedContentPlanService,
        private readonly V3DeletedPackageCleanupService $v3DeletedPackageCleanupService,
        private readonly PageV3DeletedPackageCleanupService $pageV3DeletedPackageCleanupService,
        private readonly V3PackageSeedService $v3PackageSeedService,
        private readonly PageV3PackageSeedService $pageV3PackageSeedService,
        private readonly V3PackageRefreshService $v3PackageRefreshService,
        private readonly PageV3PackageRefreshService $pageV3PackageRefreshService,
        private readonly ?ContentSyncStateService $contentSyncStateService = null,
    ) {
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function run(?string $targetInput = null, array $options = []): array
    {
        $resolvedOptions = $this->normalizeOptions($options);
        $this->heartbeat($resolvedOptions);
        $normalizedTarget = trim((string) ($targetInput ?? ''));
        $planResult = $this->changedContentPlanService->run($normalizedTarget !== '' ? $normalizedTarget : null, [
            'domains' => $resolvedOptions['domains'],
            'base' => $resolvedOptions['base'],
            'base_refs_by_domain' => $resolvedOptions['base_refs_by_domain'],
            'head' => $resolvedOptions['head'],
            'staged' => $resolvedOptions['staged'],
            'working_tree' => $resolvedOptions['working_tree'],
            'include_untracked' => $resolvedOptions['include_untracked'],
            'with_release_check' => $resolvedOptions['with_release_check'],
            'check_profile' => $resolvedOptions['check_profile'],
            'strict' => false,
        ]);
        $result = $this->resultTemplate($normalizedTarget, $resolvedOptions, $planResult);

        if (is_array($planResult['error'] ?? null)) {
            $result['error'] = $planResult['error'];

            return $this->finalizeSyncState($result, $resolvedOptions);
        }

        $result = $this->runPreflight($result, $resolvedOptions);

        if (is_array($result['error'] ?? null)) {
            return $this->finalizeSyncState($result, $resolvedOptions);
        }

        if (! $resolvedOptions['dry_run'] && ! $resolvedOptions['force']) {
            $result['error'] = [
                'stage' => 'safety',
                'reason' => 'force_required',
                'message' => 'Live changed-content apply requires --force.',
            ];

            return $this->finalizeSyncState($result, $resolvedOptions);
        }

        if ($resolvedOptions['dry_run']) {
            return $this->finalizeSyncState($result, $resolvedOptions);
        }

        $result = $this->runCleanupDeletedPhase($result, $resolvedOptions);

        if (is_array($result['error'] ?? null)) {
            return $this->finalizeSyncState($result, $resolvedOptions);
        }

        return $this->finalizeSyncState(
            $this->runUpsertPresentPhase($result, $resolvedOptions),
            $resolvedOptions
        );
    }

    /**
     * @param  array<string, mixed>  $result
     */
    public function writeReport(array $result): string
    {
        $domains = collect((array) ($result['scope']['domains'] ?? []))
            ->map(fn ($domain): string => Str::slug((string) $domain))
            ->implode('-');
        $mode = (string) ($result['diff']['mode'] ?? 'working_tree');
        $runType = (bool) ($result['execution']['dry_run'] ?? false) ? 'dry-run' : 'live';
        $hash = substr(sha1(json_encode([
            $result['scope']['input'] ?? null,
            $result['scope']['domains'] ?? [],
            $result['diff']['base'] ?? null,
            $result['diff']['base_refs_by_domain'] ?? [],
            $result['diff']['head'] ?? null,
            $result['diff']['include_untracked'] ?? false,
            $runType,
        ])), 0, 8);
        $fileName = ($domains !== '' ? $domains : 'content')
            . '-changed-apply-'
            . Str::slug($mode)
            . '-'
            . $runType
            . '-'
            . $hash
            . '.md';
        $relativePath = 'content-changed-apply-reports/' . $fileName;

        Storage::disk('local')->put($relativePath, $this->renderMarkdownReport($result));

        return $relativePath;
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    private function normalizeOptions(array $options): array
    {
        $checkProfile = strtolower(trim((string) ($options['check_profile'] ?? 'release')));

        if (! in_array($checkProfile, ['scaffold', 'release'], true)) {
            throw new RuntimeException('Unsupported release-check profile. Use scaffold or release.');
        }

        return [
            'domains' => $this->normalizeDomainsOption($options['domains'] ?? null),
            'base' => trim((string) ($options['base'] ?? '')),
            'base_refs_by_domain' => $this->normalizeBaseRefsByDomain(
                is_array($options['base_refs_by_domain'] ?? null) ? $options['base_refs_by_domain'] : null
            ),
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
            'track_sync_state' => ! array_key_exists('track_sync_state', $options)
                || (bool) $options['track_sync_state'],
            'heartbeat' => is_callable($options['heartbeat'] ?? null) ? $options['heartbeat'] : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $options
     * @param  array<string, mixed>  $planResult
     * @return array<string, mixed>
     */
    private function resultTemplate(string $targetInput, array $options, array $planResult): array
    {
        $planPackages = array_values((array) ($planResult['packages'] ?? []));
        $planPhases = (array) ($planResult['phases'] ?? []);

        return [
            'diff' => [
                'mode' => (string) ($planResult['diff']['mode'] ?? 'working_tree'),
                'base' => $planResult['diff']['base'] ?? ($options['base'] !== '' ? $options['base'] : null),
                'base_refs_by_domain' => (array) ($planResult['diff']['base_refs_by_domain'] ?? $options['base_refs_by_domain']),
                'head' => $planResult['diff']['head'] ?? ($options['head'] !== '' ? $options['head'] : null),
                'include_untracked' => (bool) ($planResult['diff']['include_untracked'] ?? $options['include_untracked']),
            ],
            'scope' => [
                'input' => $targetInput !== '' ? $targetInput : null,
                'domains' => (array) ($planResult['scope']['domains'] ?? $options['domains']),
                'resolved_roots' => array_values((array) ($planResult['scope']['resolved_roots'] ?? [])),
                'single_package' => (bool) ($planResult['scope']['single_package'] ?? false),
                'with_release_check' => (bool) $options['with_release_check'],
                'skip_release_check' => (bool) $options['skip_release_check'],
                'check_profile' => (string) $options['check_profile'],
                'strict' => (bool) $options['strict'],
            ],
            'plan' => [
                'summary' => (array) ($planResult['summary'] ?? $this->emptyPlanSummary()),
                'domains' => (array) ($planResult['domains'] ?? []),
                'phases' => [
                    'cleanup_deleted' => array_values((array) ($planPhases['cleanup_deleted'] ?? [])),
                    'upsert_present' => array_values((array) ($planPhases['upsert_present'] ?? [])),
                ],
                'packages' => $planPackages,
                'error' => is_array($planResult['error'] ?? null) ? $planResult['error'] : null,
            ],
            'preflight' => [
                'executed' => false,
                'summary' => $this->emptyPreflightSummary(),
                'packages' => $this->initialPreflightPackages($planPackages),
            ],
            'execution' => [
                'dry_run' => (bool) $options['dry_run'],
                'force' => (bool) $options['force'],
                'fail_fast' => true,
                'scope_transactional' => false,
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
    private function emptyPlanSummary(): array
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
    private function emptyPreflightSummary(): array
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
     * @param  list<array<string, mixed>>  $packages
     * @return list<array<string, mixed>>
     */
    private function initialPreflightPackages(array $packages): array
    {
        return array_values(array_map(function (array $package): array {
            $action = (string) ($package['recommended_action'] ?? 'skip');

            return [
                'domain' => (string) ($package['domain'] ?? 'unknown'),
                'package_key' => $this->packageKey($package),
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
     * @param  list<array<string, mixed>>  $packages
     * @return array<string, mixed>
     */
    private function initialPhaseExecution(array $packages): array
    {
        return [
            'started' => 0,
            'completed' => 0,
            'succeeded' => 0,
            'failed' => 0,
            'stopped_on' => null,
            'packages' => array_values(array_map(function (array $package): array {
                return [
                    'domain' => (string) ($package['domain'] ?? 'unknown'),
                    'package_key' => $this->packageKey($package),
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
    private function runPreflight(array $result, array $options): array
    {
        $this->heartbeat($options);
        $result['preflight']['executed'] = true;
        $packages = (array) ($result['plan']['packages'] ?? []);

        foreach ($packages as $index => $package) {
            $this->heartbeat($options);
            $package = (array) $package;
            $action = (string) ($package['recommended_action'] ?? 'skip');

            if (! in_array($action, ['seed', 'refresh', 'unseed_deleted'], true)) {
                $result['preflight']['summary']['skipped']++;

                continue;
            }

            $result['preflight']['summary']['candidates']++;

            try {
                $serviceResult = $this->preflightServiceResult($package, $packages, $options);
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

        $failedPreflight = array_values(array_filter(
            (array) ($result['preflight']['packages'] ?? []),
            static fn (array $package): bool => ($package['status'] ?? null) === 'fail'
        ));

        if ($failedPreflight !== []) {
            $result['error'] = [
                'stage' => 'preflight',
                'reason' => 'preflight_failed',
                'message' => 'Changed-content apply preflight failed; live execution did not start.',
                'package' => (string) ($failedPreflight[0]['relative_path'] ?? $failedPreflight[0]['package_key'] ?? ''),
                'phase' => (string) ($failedPreflight[0]['phase'] ?? 'unknown'),
                'domain' => (string) ($failedPreflight[0]['domain'] ?? 'unknown'),
                'service_error' => $failedPreflight[0]['service_result']['error'] ?? null,
            ];

            return $result;
        }

        $blockedPackages = array_values(array_filter(
            $packages,
            static fn (array $package): bool => ($package['recommended_action'] ?? null) === 'blocked'
        ));

        if ($blockedPackages !== []) {
            $result['error'] = [
                'stage' => 'planning',
                'reason' => 'blocked_packages',
                'message' => 'Changed-content planner found blocked packages; live execution did not start.',
                'packages' => array_values(array_map(
                    fn (array $package): string => '[' . (string) ($package['domain'] ?? 'unknown') . '] ' . $this->packagePath($package),
                    $blockedPackages
                )),
            ];

            return $result;
        }

        if ($options['strict']) {
            $warningPackages = array_values(array_filter(
                $packages,
                fn (array $package): bool => $this->packageHasPlannerWarning($package)
            ));
            $preflightWarnings = array_values(array_filter(
                (array) ($result['preflight']['packages'] ?? []),
                static fn (array $package): bool => ($package['status'] ?? null) === 'warn'
            ));

            if ($warningPackages !== [] || $preflightWarnings !== []) {
                $result['error'] = [
                    'stage' => 'preflight',
                    'reason' => 'warnings_are_fatal',
                    'message' => 'Changed-content planner or preflight returned warnings and --strict is enabled.',
                    'packages' => array_values(array_unique(array_merge(
                        array_map(
                            fn (array $package): string => '[' . (string) ($package['domain'] ?? 'unknown') . '] ' . $this->packagePath($package),
                            $warningPackages
                        ),
                        array_map(
                            static fn (array $package): string => '[' . (string) ($package['domain'] ?? 'unknown') . '] ' . (string) ($package['relative_path'] ?? $package['package_key'] ?? ''),
                            $preflightWarnings
                        )
                    ))),
                ];
            }
        }

        return $result;
    }

    /**
     * @param  array<string, mixed>  $package
     * @param  list<array<string, mixed>>  $allPackages
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    private function preflightServiceResult(array $package, array $allPackages, array $options): array
    {
        $action = (string) ($package['recommended_action'] ?? 'skip');

        return match ($action) {
            'unseed_deleted' => $this->cleanupDeletedPackage($package, array_merge(
                $this->deletedCleanupOptions($allPackages, $package),
                [
                    'dry_run' => true,
                    'force' => false,
                    'strict' => false,
                ]
            )),
            'seed' => $this->seedPackage($package, [
                'dry_run' => true,
                'skip_release_check' => (bool) ($options['skip_release_check'] ?? false),
                'check_profile' => (string) ($options['check_profile'] ?? 'release'),
                'strict' => false,
            ]),
            'refresh' => $this->refreshPackage($package, [
                'dry_run' => true,
                'force' => false,
                'skip_release_check' => (bool) ($options['skip_release_check'] ?? false),
                'check_profile' => (string) ($options['check_profile'] ?? 'release'),
                'strict' => false,
            ]),
            default => throw new RuntimeException(sprintf('Unsupported changed-content preflight action [%s].', $action)),
        };
    }

    /**
     * @param  array<string, mixed>  $result
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    private function runCleanupDeletedPhase(array $result, array $options): array
    {
        $this->heartbeat($options);
        $packages = array_values((array) ($result['plan']['phases']['cleanup_deleted'] ?? []));

        foreach ($packages as $index => $package) {
            $this->heartbeat($options);
            $package = (array) $package;
            $result['execution']['cleanup_deleted']['started']++;

            try {
                $serviceResult = $this->cleanupDeletedPackage($package, array_merge(
                    $this->deletedCleanupOptions((array) ($result['plan']['packages'] ?? []), $package),
                    [
                        'dry_run' => false,
                        'force' => true,
                        'strict' => (bool) ($options['strict'] ?? false),
                    ]
                ));
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
            $result['execution']['cleanup_deleted']['stopped_on'] = '[' . (string) ($package['domain'] ?? 'unknown') . '] ' . $this->packagePath($package);
            $result['error'] = [
                'stage' => 'execution',
                'phase' => 'cleanup_deleted',
                'reason' => 'cleanup_deleted_failed',
                'message' => 'Changed-content apply stopped during deleted cleanup phase.',
                'package' => $this->packagePath($package),
                'domain' => (string) ($package['domain'] ?? 'unknown'),
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
    private function runUpsertPresentPhase(array $result, array $options): array
    {
        $this->heartbeat($options);
        $packages = array_values((array) ($result['plan']['phases']['upsert_present'] ?? []));

        foreach ($packages as $index => $package) {
            $this->heartbeat($options);
            $package = (array) $package;
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
            $result['execution']['upsert_present']['stopped_on'] = '[' . (string) ($package['domain'] ?? 'unknown') . '] ' . $this->packagePath($package);
            $result['error'] = [
                'stage' => 'execution',
                'phase' => 'upsert_present',
                'reason' => 'upsert_present_failed',
                'message' => 'Changed-content apply stopped during current-package upsert phase.',
                'package' => $this->packagePath($package),
                'domain' => (string) ($package['domain'] ?? 'unknown'),
                'action' => $action,
                'service_error' => $serviceResult['error'] ?? null,
            ];

            break;
        }

        return $result;
    }

    /**
     * @param  array<string, mixed>  $package
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    private function executeUpsertAction(string $action, array $package, array $options): array
    {
        return match ($action) {
            'seed' => $this->seedPackage($package, [
                'dry_run' => false,
                'skip_release_check' => (bool) ($options['skip_release_check'] ?? false),
                'check_profile' => (string) ($options['check_profile'] ?? 'release'),
                'strict' => (bool) ($options['strict'] ?? false),
            ]),
            'refresh' => $this->refreshPackage($package, [
                'dry_run' => false,
                'force' => true,
                'skip_release_check' => (bool) ($options['skip_release_check'] ?? false),
                'check_profile' => (string) ($options['check_profile'] ?? 'release'),
                'strict' => (bool) ($options['strict'] ?? false),
            ]),
            default => throw new RuntimeException(sprintf('Unsupported changed-content upsert action [%s].', $action)),
        };
    }

    /**
     * @param  array<string, mixed>  $package
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    private function cleanupDeletedPackage(array $package, array $options): array
    {
        return match ((string) ($package['domain'] ?? 'unknown')) {
            'v3' => $this->v3DeletedPackageCleanupService->runPackageRecord($package, $options),
            'page-v3' => $this->pageV3DeletedPackageCleanupService->runPackageRecord($package, $options),
            default => throw new RuntimeException(sprintf(
                'Unsupported content domain [%s] for deleted cleanup.',
                (string) ($package['domain'] ?? 'unknown')
            )),
        };
    }

    /**
     * @param  array<string, mixed>  $package
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    private function seedPackage(array $package, array $options): array
    {
        $target = $this->executionTarget($package);

        return match ((string) ($package['domain'] ?? 'unknown')) {
            'v3' => $this->v3PackageSeedService->run($target, $options),
            'page-v3' => $this->pageV3PackageSeedService->run($target, $options),
            default => throw new RuntimeException(sprintf(
                'Unsupported content domain [%s] for package seed.',
                (string) ($package['domain'] ?? 'unknown')
            )),
        };
    }

    /**
     * @param  array<string, mixed>  $package
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    private function refreshPackage(array $package, array $options): array
    {
        $target = $this->executionTarget($package);

        return match ((string) ($package['domain'] ?? 'unknown')) {
            'v3' => $this->v3PackageRefreshService->run($target, $options),
            'page-v3' => $this->pageV3PackageRefreshService->run($target, $options),
            default => throw new RuntimeException(sprintf(
                'Unsupported content domain [%s] for package refresh.',
                (string) ($package['domain'] ?? 'unknown')
            )),
        };
    }

    /**
     * @param  list<array<string, mixed>>  $allPackages
     * @param  array<string, mixed>  $package
     * @return array<string, mixed>
     */
    private function deletedCleanupOptions(array $allPackages, array $package): array
    {
        $currentKey = $this->packageKey($package);
        $currentIndex = array_search($currentKey, array_map(
            fn (array $candidate): string => $this->packageKey($candidate),
            $allPackages
        ), true);

        if (! is_int($currentIndex) || $currentIndex <= 0) {
            return [];
        }

        $previousCleanupClasses = collect(array_slice($allPackages, 0, $currentIndex))
            ->filter(fn (array $candidate): bool => ($candidate['recommended_action'] ?? null) === 'unseed_deleted')
            ->filter(fn (array $candidate): bool => ($candidate['domain'] ?? null) === ($package['domain'] ?? null))
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
    private function executionTarget(array $package): string
    {
        $target = trim((string) ($package['current_relative_path'] ?? '') ?: (string) ($package['definition_relative_path'] ?? ''));

        if ($target === '') {
            throw new RuntimeException(sprintf(
                'Changed-content planner did not provide an executable current-package target for [%s].',
                $this->packagePath($package)
            ));
        }

        if (! str_ends_with(strtolower($target), '/definition.json')) {
            $target = rtrim($target, '/') . '/definition.json';
        }

        return $target;
    }

    /**
     * @return list<string>
     */
    private function serviceWarnings(string $action, array $serviceResult): array
    {
        $warnings = collect();

        if ($action === 'unseed_deleted') {
            $warnings = $warnings->merge((array) ($serviceResult['impact']['warnings'] ?? []));
        }

        if (in_array($action, ['seed', 'refresh'], true)) {
            $releaseWarnings = (int) ($serviceResult['preflight']['summary']['check_counts']['warn'] ?? 0);
            $releaseFailures = (int) ($serviceResult['preflight']['summary']['check_counts']['fail'] ?? 0);

            if ($releaseWarnings > 0) {
                $warnings->push(sprintf('Release-check returned %d warning(s).', $releaseWarnings));
            }

            if ($releaseFailures > 0) {
                $warnings->push(sprintf('Release-check returned %d failure(s).', $releaseFailures));
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

    private function deletedCleanupSucceeded(array $serviceResult): bool
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

    private function upsertSucceeded(string $action, array $serviceResult): bool
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
     * @param  array<string, mixed>  $result
     */
    private function renderMarkdownReport(array $result): string
    {
        $lines = [
            '# Changed Content Apply',
            '',
            '- Diff Mode: `' . (string) ($result['diff']['mode'] ?? 'working_tree') . '`',
            '- Base: `' . (string) (($result['diff']['base'] ?? null) ?: '') . '`',
            '- Base Refs By Domain: `' . json_encode((array) ($result['diff']['base_refs_by_domain'] ?? []), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '`',
            '- Head: `' . (string) (($result['diff']['head'] ?? null) ?: '') . '`',
            '- Include Untracked: `' . (((bool) ($result['diff']['include_untracked'] ?? false)) ? 'true' : 'false') . '`',
            '- Domains: `' . implode(', ', (array) ($result['scope']['domains'] ?? [])) . '`',
            '- Dry Run: `' . (((bool) ($result['execution']['dry_run'] ?? false)) ? 'true' : 'false') . '`',
            '- Force: `' . (((bool) ($result['execution']['force'] ?? false)) ? 'true' : 'false') . '`',
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
                $lines[] = '- [' . (string) ($package['domain'] ?? 'unknown') . '] `' . (string) ($package['relative_path'] ?? '') . '`'
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
                $lines[] = '- [' . (string) ($package['domain'] ?? 'unknown') . '] `' . (string) ($package['relative_path'] ?? '') . '`'
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
     * @param  array<string, mixed>  $result
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    private function finalizeSyncState(array $result, array $options): array
    {
        $this->heartbeat($options);

        if (! $this->shouldTrackSyncState($result, $options)) {
            return $result;
        }

        $domains = (array) ($result['scope']['domains'] ?? []);
        $headRef = trim((string) ($result['diff']['head'] ?? ''));
        $baseRefsByDomain = $this->resolvedBaseRefsByDomain($result, $domains);
        $meta = $this->syncAttemptMeta($result);

        if ($options['dry_run']) {
            $this->contentSyncStateService?->recordDryRun($domains, $baseRefsByDomain, $headRef, $meta);

            return $result;
        }

        if (is_array($result['error'] ?? null)) {
            $this->contentSyncStateService?->recordFailure($domains, $baseRefsByDomain, $headRef, $meta);

            return $result;
        }

        $this->contentSyncStateService?->recordSuccess($domains, $baseRefsByDomain, $headRef, $meta);

        return $result;
    }

    /**
     * @param  array<string, mixed>  $result
     * @param  array<string, mixed>  $options
     */
    private function shouldTrackSyncState(array $result, array $options): bool
    {
        if ($this->contentSyncStateService === null) {
            return false;
        }

        if (! (bool) ($options['track_sync_state'] ?? true)) {
            return false;
        }

        if ((string) ($result['diff']['mode'] ?? 'working_tree') !== 'refs') {
            return false;
        }

        if (trim((string) ($result['diff']['head'] ?? '')) === '') {
            return false;
        }

        $baseRefsByDomain = (array) ($result['diff']['base_refs_by_domain'] ?? []);
        $singleBase = trim((string) ($result['diff']['base'] ?? ''));

        return $baseRefsByDomain !== [] || $singleBase !== '';
    }

    /**
     * @param  array<string, mixed>  $result
     * @param  list<string>  $domains
     * @return array<string, string|null>
     */
    private function resolvedBaseRefsByDomain(array $result, array $domains): array
    {
        $diffBaseRefs = (array) ($result['diff']['base_refs_by_domain'] ?? []);
        $singleBase = trim((string) ($result['diff']['base'] ?? ''));
        $resolved = [];

        foreach ($domains as $domain) {
            $baseRef = trim((string) ($diffBaseRefs[$domain] ?? $singleBase));
            $resolved[$domain] = $baseRef !== '' ? $baseRef : null;
        }

        return $resolved;
    }

    /**
     * @param  array<string, mixed>  $result
     * @return array<string, mixed>
     */
    private function syncAttemptMeta(array $result): array
    {
        return [
            'diff_mode' => (string) ($result['diff']['mode'] ?? 'refs'),
            'stage' => (string) (($result['error']['stage'] ?? null) ?: 'completed'),
            'phase' => (string) (($result['error']['phase'] ?? null) ?: ''),
            'reason' => (string) (($result['error']['reason'] ?? null) ?: ''),
            'message' => (string) (($result['error']['message'] ?? null) ?: ''),
            'package' => (string) (($result['error']['package'] ?? null) ?: ''),
            'plan_summary' => (array) ($result['plan']['summary'] ?? []),
            'preflight_summary' => (array) ($result['preflight']['summary'] ?? []),
        ];
    }

    /**
     * @param  array<string, mixed>  $options
     */
    private function heartbeat(array $options): void
    {
        $heartbeat = $options['heartbeat'] ?? null;

        if (is_callable($heartbeat)) {
            $heartbeat();
        }
    }
}
