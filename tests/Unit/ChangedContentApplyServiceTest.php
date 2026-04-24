<?php

namespace Tests\Unit;

use App\Services\ContentDeployment\ChangedContentApplyService;
use App\Services\ContentDeployment\ChangedContentPlanService;
use App\Services\ContentDeployment\ContentSyncStateService;
use App\Services\PageV3PromptGenerator\PageV3DeletedPackageCleanupService;
use App\Services\PageV3PromptGenerator\PageV3PackageRefreshService;
use App\Services\PageV3PromptGenerator\PageV3PackageSeedService;
use App\Services\V3PromptGenerator\V3DeletedPackageCleanupService;
use App\Services\V3PromptGenerator\V3PackageRefreshService;
use App\Services\V3PromptGenerator\V3PackageSeedService;
use Mockery;
use Tests\TestCase;

class ChangedContentApplyServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_it_preflights_all_actionable_packages_and_executes_cross_domain_phases_in_safe_order(): void
    {
        $planner = Mockery::mock(ChangedContentPlanService::class);
        $v3Cleanup = Mockery::mock(V3DeletedPackageCleanupService::class);
        $pageCleanup = Mockery::mock(PageV3DeletedPackageCleanupService::class);
        $v3Seed = Mockery::mock(V3PackageSeedService::class);
        $pageSeed = Mockery::mock(PageV3PackageSeedService::class);
        $v3Refresh = Mockery::mock(V3PackageRefreshService::class);
        $pageRefresh = Mockery::mock(PageV3PackageRefreshService::class);
        $cleanupCalls = [];
        $seedCalls = [];
        $refreshCalls = [];
        $plan = $this->planResult([
            $this->deletedPackage('v3', 'database/seeders/V3/Tests/GhostSeeder', 'Database\\Seeders\\V3\\Tests\\GhostSeeder'),
            $this->deletedPackage('page-v3', 'database/seeders/Page_V3/Tests/PageSeeder', 'Database\\Seeders\\Page_V3\\Tests\\PageSeeder', 'page'),
            $this->deletedPackage('page-v3', 'database/seeders/Page_V3/Tests/CategorySeeder', 'Database\\Seeders\\Page_V3\\Tests\\CategorySeeder', 'category'),
            $this->currentPackage('page-v3', 'database/seeders/Page_V3/Tests/CategoryCurrentSeeder', 'seed', 'category'),
            $this->currentPackage('page-v3', 'database/seeders/Page_V3/Tests/PageCurrentSeeder', 'refresh', 'page'),
            $this->currentPackage('v3', 'database/seeders/V3/Tests/AlphaSeeder', 'seed', 'v3_test'),
        ]);

        $planner->shouldReceive('run')
            ->once()
            ->with(null, Mockery::on(fn (array $options): bool => ($options['strict'] ?? null) === false))
            ->andReturn($plan);

        $v3Cleanup->shouldReceive('runPackageRecord')
            ->twice()
            ->andReturnUsing(function (array $package, array $options) use (&$cleanupCalls): array {
                $cleanupCalls[] = [
                    'domain' => $package['domain'],
                    'package' => $package['package_key'],
                    'dry_run' => $options['dry_run'] ?? null,
                    'additional_cleanup_classes' => $options['additional_cleanup_classes'] ?? [],
                ];

                return $this->deletedCleanupResult((bool) ($options['dry_run'] ?? false));
            });

        $pageCleanup->shouldReceive('runPackageRecord')
            ->times(4)
            ->andReturnUsing(function (array $package, array $options) use (&$cleanupCalls): array {
                $cleanupCalls[] = [
                    'domain' => $package['domain'],
                    'package' => $package['package_key'],
                    'dry_run' => $options['dry_run'] ?? null,
                    'additional_cleanup_classes' => $options['additional_cleanup_classes'] ?? [],
                ];

                return $this->deletedCleanupResult((bool) ($options['dry_run'] ?? false));
            });

        $pageSeed->shouldReceive('run')
            ->twice()
            ->andReturnUsing(function (string $target, array $options) use (&$seedCalls): array {
                $seedCalls[] = [
                    'target' => $target,
                    'dry_run' => $options['dry_run'] ?? null,
                ];

                return $this->seedResult((bool) ($options['dry_run'] ?? false));
            });

        $v3Seed->shouldReceive('run')
            ->twice()
            ->andReturnUsing(function (string $target, array $options) use (&$seedCalls): array {
                $seedCalls[] = [
                    'target' => $target,
                    'dry_run' => $options['dry_run'] ?? null,
                ];

                return $this->seedResult((bool) ($options['dry_run'] ?? false));
            });

        $pageRefresh->shouldReceive('run')
            ->twice()
            ->andReturnUsing(function (string $target, array $options) use (&$refreshCalls): array {
                $refreshCalls[] = [
                    'target' => $target,
                    'dry_run' => $options['dry_run'] ?? null,
                ];

                return $this->refreshResult((bool) ($options['dry_run'] ?? false));
            });

        $v3Refresh->shouldNotReceive('run');

        $result = (new ChangedContentApplyService(
            $planner,
            $v3Cleanup,
            $pageCleanup,
            $v3Seed,
            $pageSeed,
            $v3Refresh,
            $pageRefresh
        ))->run(null, [
            'force' => true,
            'skip_release_check' => true,
        ]);

        $this->assertNull($result['error']);
        $this->assertTrue((bool) $result['preflight']['executed']);
        $this->assertSame(6, $result['preflight']['summary']['candidates']);
        $this->assertSame(6, $result['preflight']['summary']['ok']);
        $this->assertSame([
            'database/seeders/V3/Tests/GhostSeeder',
            'database/seeders/Page_V3/Tests/PageSeeder',
            'database/seeders/Page_V3/Tests/CategorySeeder',
        ], array_column($result['execution']['cleanup_deleted']['packages'], 'relative_path'));
        $this->assertSame([
            'database/seeders/Page_V3/Tests/CategoryCurrentSeeder',
            'database/seeders/Page_V3/Tests/PageCurrentSeeder',
            'database/seeders/V3/Tests/AlphaSeeder',
        ], array_column($result['execution']['upsert_present']['packages'], 'relative_path'));
        $this->assertSame([], $cleanupCalls[0]['additional_cleanup_classes']);
        $this->assertSame([], $cleanupCalls[1]['additional_cleanup_classes']);
        $this->assertSame(['Database\\Seeders\\Page_V3\\Tests\\PageSeeder'], $cleanupCalls[2]['additional_cleanup_classes']);
        $this->assertSame([], $cleanupCalls[3]['additional_cleanup_classes']);
        $this->assertSame([], $cleanupCalls[4]['additional_cleanup_classes']);
        $this->assertSame(['Database\\Seeders\\Page_V3\\Tests\\PageSeeder'], $cleanupCalls[5]['additional_cleanup_classes']);
        $this->assertSame([
            'database/seeders/Page_V3/Tests/CategoryCurrentSeeder/definition.json',
            'database/seeders/V3/Tests/AlphaSeeder/definition.json',
            'database/seeders/Page_V3/Tests/CategoryCurrentSeeder/definition.json',
            'database/seeders/V3/Tests/AlphaSeeder/definition.json',
        ], array_values(array_filter(array_column($seedCalls, 'target'))));
        $this->assertSame([
            'database/seeders/Page_V3/Tests/PageCurrentSeeder/definition.json',
            'database/seeders/Page_V3/Tests/PageCurrentSeeder/definition.json',
        ], array_column($refreshCalls, 'target'));
    }

    public function test_preflight_failure_blocks_all_live_writes_after_full_scope_collection(): void
    {
        $planner = Mockery::mock(ChangedContentPlanService::class);
        $v3Cleanup = Mockery::mock(V3DeletedPackageCleanupService::class);
        $pageCleanup = Mockery::mock(PageV3DeletedPackageCleanupService::class);
        $v3Seed = Mockery::mock(V3PackageSeedService::class);
        $pageSeed = Mockery::mock(PageV3PackageSeedService::class);
        $v3Refresh = Mockery::mock(V3PackageRefreshService::class);
        $pageRefresh = Mockery::mock(PageV3PackageRefreshService::class);
        $cleanupCalls = [];
        $seedCalls = [];
        $plan = $this->planResult([
            $this->deletedPackage('v3', 'database/seeders/V3/Tests/GhostSeeder', 'Database\\Seeders\\V3\\Tests\\GhostSeeder'),
            $this->currentPackage('page-v3', 'database/seeders/Page_V3/Tests/PageCurrentSeeder', 'seed', 'page'),
        ]);

        $planner->shouldReceive('run')->once()->andReturn($plan);
        $v3Cleanup->shouldReceive('runPackageRecord')
            ->once()
            ->andReturnUsing(function (array $package, array $options) use (&$cleanupCalls): array {
                $cleanupCalls[] = [$package['package_key'], $options['dry_run'] ?? null];

                return [
                    'ownership' => ['seed_run_present' => true, 'package_present_in_db' => true],
                    'impact' => ['warnings' => []],
                    'result' => ['deleted' => false, 'seed_run_removed' => false],
                    'error' => ['message' => 'cleanup preflight failed'],
                ];
            });
        $pageSeed->shouldReceive('run')
            ->once()
            ->andReturnUsing(function (string $target, array $options) use (&$seedCalls): array {
                $seedCalls[] = [$target, $options['dry_run'] ?? null];

                return $this->seedResult(true);
            });
        $pageCleanup->shouldNotReceive('runPackageRecord');
        $v3Seed->shouldNotReceive('run');
        $v3Refresh->shouldNotReceive('run');
        $pageRefresh->shouldNotReceive('run');

        $result = (new ChangedContentApplyService(
            $planner,
            $v3Cleanup,
            $pageCleanup,
            $v3Seed,
            $pageSeed,
            $v3Refresh,
            $pageRefresh
        ))->run(null, [
            'force' => true,
            'skip_release_check' => true,
        ]);

        $this->assertSame('preflight_failed', $result['error']['reason']);
        $this->assertSame(0, $result['execution']['cleanup_deleted']['started']);
        $this->assertSame(0, $result['execution']['upsert_present']['started']);
        $this->assertSame([['database/seeders/V3/Tests/GhostSeeder', true]], $cleanupCalls);
        $this->assertSame([['database/seeders/Page_V3/Tests/PageCurrentSeeder/definition.json', true]], $seedCalls);
    }

    public function test_cleanup_phase_failure_prevents_upsert_phase(): void
    {
        $planner = Mockery::mock(ChangedContentPlanService::class);
        $v3Cleanup = Mockery::mock(V3DeletedPackageCleanupService::class);
        $pageCleanup = Mockery::mock(PageV3DeletedPackageCleanupService::class);
        $v3Seed = Mockery::mock(V3PackageSeedService::class);
        $pageSeed = Mockery::mock(PageV3PackageSeedService::class);
        $v3Refresh = Mockery::mock(V3PackageRefreshService::class);
        $pageRefresh = Mockery::mock(PageV3PackageRefreshService::class);
        $plan = $this->planResult([
            $this->deletedPackage('v3', 'database/seeders/V3/Tests/GhostSeeder', 'Database\\Seeders\\V3\\Tests\\GhostSeeder'),
            $this->currentPackage('page-v3', 'database/seeders/Page_V3/Tests/PageCurrentSeeder', 'seed', 'page'),
        ]);

        $planner->shouldReceive('run')->once()->andReturn($plan);
        $v3Cleanup->shouldReceive('runPackageRecord')
            ->twice()
            ->andReturnUsing(fn (array $package, array $options): array => ($options['dry_run'] ?? false)
                ? $this->deletedCleanupResult(true)
                : [
                    'ownership' => ['seed_run_present' => true, 'package_present_in_db' => true],
                    'impact' => ['warnings' => []],
                    'result' => ['deleted' => false, 'seed_run_removed' => false],
                    'error' => ['message' => 'cleanup failed'],
                ]);
        $pageSeed->shouldReceive('run')->once()->andReturn($this->seedResult(true));
        $pageCleanup->shouldNotReceive('runPackageRecord');
        $v3Seed->shouldNotReceive('run');
        $v3Refresh->shouldNotReceive('run');
        $pageRefresh->shouldNotReceive('run');

        $result = (new ChangedContentApplyService(
            $planner,
            $v3Cleanup,
            $pageCleanup,
            $v3Seed,
            $pageSeed,
            $v3Refresh,
            $pageRefresh
        ))->run(null, [
            'force' => true,
            'skip_release_check' => true,
        ]);

        $this->assertSame('cleanup_deleted_failed', $result['error']['reason']);
        $this->assertSame('cleanup_deleted', $result['error']['phase']);
        $this->assertSame(0, $result['execution']['upsert_present']['started']);
        $this->assertSame('pending', $result['execution']['upsert_present']['packages'][0]['status']);
    }

    public function test_strict_warnings_are_fatal_after_full_scope_preflight(): void
    {
        $planner = Mockery::mock(ChangedContentPlanService::class);
        $v3Cleanup = Mockery::mock(V3DeletedPackageCleanupService::class);
        $pageCleanup = Mockery::mock(PageV3DeletedPackageCleanupService::class);
        $v3Seed = Mockery::mock(V3PackageSeedService::class);
        $pageSeed = Mockery::mock(PageV3PackageSeedService::class);
        $v3Refresh = Mockery::mock(V3PackageRefreshService::class);
        $pageRefresh = Mockery::mock(PageV3PackageRefreshService::class);
        $plan = $this->planResult([
            $this->currentPackage('v3', 'database/seeders/V3/Tests/AlphaSeeder', 'seed', 'v3_test', ['Planner warning']),
        ]);

        $planner->shouldReceive('run')->once()->andReturn($plan);
        $v3Seed->shouldReceive('run')->once()->andReturn($this->seedResult(true));
        $v3Cleanup->shouldNotReceive('runPackageRecord');
        $pageCleanup->shouldNotReceive('runPackageRecord');
        $pageSeed->shouldNotReceive('run');
        $v3Refresh->shouldNotReceive('run');
        $pageRefresh->shouldNotReceive('run');

        $result = (new ChangedContentApplyService(
            $planner,
            $v3Cleanup,
            $pageCleanup,
            $v3Seed,
            $pageSeed,
            $v3Refresh,
            $pageRefresh
        ))->run(null, [
            'dry_run' => true,
            'strict' => true,
        ]);

        $this->assertSame('warnings_are_fatal', $result['error']['reason']);
        $this->assertTrue((bool) $result['preflight']['executed']);
    }

    public function test_live_ref_based_apply_records_sync_state_success_without_changing_phase_order(): void
    {
        $planner = Mockery::mock(ChangedContentPlanService::class);
        $v3Cleanup = Mockery::mock(V3DeletedPackageCleanupService::class);
        $pageCleanup = Mockery::mock(PageV3DeletedPackageCleanupService::class);
        $v3Seed = Mockery::mock(V3PackageSeedService::class);
        $pageSeed = Mockery::mock(PageV3PackageSeedService::class);
        $v3Refresh = Mockery::mock(V3PackageRefreshService::class);
        $pageRefresh = Mockery::mock(PageV3PackageRefreshService::class);
        $syncStateService = Mockery::mock(ContentSyncStateService::class);
        $plan = $this->planResult([
            $this->currentPackage('page-v3', 'database/seeders/Page_V3/Tests/PageCurrentSeeder', 'seed', 'page'),
        ]);
        $plan['diff']['mode'] = 'refs';
        $plan['diff']['base'] = null;
        $plan['diff']['base_refs_by_domain'] = [
            'v3' => 'v3-base',
            'page-v3' => 'page-base',
        ];
        $plan['diff']['head'] = 'target-head';

        $planner->shouldReceive('run')
            ->once()
            ->with(null, Mockery::on(fn (array $options): bool => ($options['base_refs_by_domain']['v3'] ?? null) === 'v3-base' && ($options['base_refs_by_domain']['page-v3'] ?? null) === 'page-base'))
            ->andReturn($plan);
        $pageSeed->shouldReceive('run')->twice()->andReturn($this->seedResult(true), $this->seedResult(false));
        $syncStateService->shouldReceive('recordSuccess')
            ->once()
            ->with(
                ['v3', 'page-v3'],
                [
                    'v3' => 'v3-base',
                    'page-v3' => 'page-base',
                ],
                'target-head',
                Mockery::on(fn (array $meta): bool => ($meta['stage'] ?? null) === 'completed')
            );
        $v3Cleanup->shouldNotReceive('runPackageRecord');
        $pageCleanup->shouldNotReceive('runPackageRecord');
        $v3Seed->shouldNotReceive('run');
        $v3Refresh->shouldNotReceive('run');
        $pageRefresh->shouldNotReceive('run');

        $result = (new ChangedContentApplyService(
            $planner,
            $v3Cleanup,
            $pageCleanup,
            $v3Seed,
            $pageSeed,
            $v3Refresh,
            $pageRefresh,
            $syncStateService
        ))->run(null, [
            'base_refs_by_domain' => [
                'v3' => 'v3-base',
                'page-v3' => 'page-base',
            ],
            'head' => 'target-head',
            'force' => true,
            'skip_release_check' => true,
        ]);

        $this->assertNull($result['error']);
        $this->assertSame(['database/seeders/Page_V3/Tests/PageCurrentSeeder'], array_column($result['execution']['upsert_present']['packages'], 'relative_path'));
    }

    /**
     * @param  list<array<string, mixed>>  $packages
     * @return array<string, mixed>
     */
    private function planResult(array $packages): array
    {
        return [
            'diff' => [
                'mode' => 'working_tree',
                'base' => null,
                'base_refs_by_domain' => [],
                'head' => 'HEAD',
                'include_untracked' => true,
            ],
            'scope' => [
                'input' => null,
                'domains' => ['v3', 'page-v3'],
                'resolved_roots' => [
                    [
                        'domain' => 'v3',
                        'resolved_root_absolute_path' => base_path('database/seeders/V3'),
                        'resolved_root_relative_path' => 'database/seeders/V3',
                        'single_package' => false,
                    ],
                    [
                        'domain' => 'page-v3',
                        'resolved_root_absolute_path' => base_path('database/seeders/Page_V3'),
                        'resolved_root_relative_path' => 'database/seeders/Page_V3',
                        'single_package' => false,
                    ],
                ],
                'single_package' => false,
            ],
            'summary' => [
                'changed_packages' => count($packages),
                'seed_candidates' => count(array_filter($packages, fn (array $package): bool => ($package['recommended_action'] ?? null) === 'seed')),
                'refresh_candidates' => count(array_filter($packages, fn (array $package): bool => ($package['recommended_action'] ?? null) === 'refresh')),
                'deleted_cleanup_candidates' => count(array_filter($packages, fn (array $package): bool => ($package['recommended_action'] ?? null) === 'unseed_deleted')),
                'skipped' => 0,
                'blocked' => count(array_filter($packages, fn (array $package): bool => ($package['recommended_action'] ?? null) === 'blocked')),
                'warnings' => count(array_filter($packages, fn (array $package): bool => ($package['warnings'] ?? []) !== [])),
            ],
            'domains' => [
                'v3' => ['packages' => []],
                'page-v3' => ['packages' => []],
            ],
            'phases' => [
                'cleanup_deleted' => array_values(array_filter($packages, fn (array $package): bool => ($package['recommended_phase'] ?? null) === 'cleanup_deleted')),
                'upsert_present' => array_values(array_filter($packages, fn (array $package): bool => ($package['recommended_phase'] ?? null) === 'upsert_present')),
            ],
            'packages' => $packages,
            'artifacts' => [
                'report_path' => null,
            ],
            'error' => null,
        ];
    }

    /**
     * @param  list<string>  $warnings
     * @return array<string, mixed>
     */
    private function currentPackage(string $domain, string $path, string $action, string $type, array $warnings = []): array
    {
        return [
            'domain' => $domain,
            'package_key' => $path,
            'package_type' => $type,
            'change_type' => $action === 'seed' ? 'added' : 'modified',
            'current_relative_path' => $path,
            'historical_relative_path' => null,
            'current_on_disk' => true,
            'historical_metadata_available' => false,
            'resolved_seeder_class' => str_replace('/', '\\', $path),
            'package_state' => $action === 'seed' ? 'not_seeded' : 'seeded',
            'recommended_phase' => 'upsert_present',
            'recommended_action' => $action,
            'release_check' => [
                'executed' => false,
                'status' => 'skipped',
                'summary' => ['pass' => 0, 'warn' => 0, 'fail' => 0],
            ],
            'warnings' => $warnings,
        ];
    }

    private function deletedPackage(string $domain, string $path, string $seederClass, string $type = 'v3_test'): array
    {
        return [
            'domain' => $domain,
            'package_key' => $path,
            'package_type' => $type,
            'change_type' => 'deleted',
            'current_relative_path' => null,
            'historical_relative_path' => $path,
            'current_on_disk' => false,
            'historical_metadata_available' => true,
            'resolved_seeder_class' => $seederClass,
            'package_state' => 'deleted_from_disk',
            'recommended_phase' => 'cleanup_deleted',
            'recommended_action' => 'unseed_deleted',
            'release_check' => [
                'executed' => false,
                'status' => 'skipped',
                'summary' => ['pass' => 0, 'warn' => 0, 'fail' => 0],
            ],
            'warnings' => [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function deletedCleanupResult(bool $dryRun): array
    {
        return [
            'ownership' => [
                'seed_run_present' => true,
                'package_present_in_db' => true,
            ],
            'impact' => [
                'warnings' => [],
            ],
            'result' => [
                'deleted' => true,
                'rolled_back' => $dryRun,
                'seed_run_removed' => ! $dryRun,
            ],
            'error' => null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function seedResult(bool $dryRun): array
    {
        return [
            'preflight' => [
                'summary' => [
                    'check_counts' => [
                        'pass' => 1,
                        'warn' => 0,
                        'fail' => 0,
                    ],
                ],
            ],
            'mode' => [
                'release_check_skipped' => true,
            ],
            'result' => [
                'seeded' => true,
                'rolled_back' => $dryRun,
                'seed_run_logged' => ! $dryRun,
            ],
            'error' => null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function refreshResult(bool $dryRun): array
    {
        return [
            'preflight' => [
                'summary' => [
                    'check_counts' => [
                        'pass' => 1,
                        'warn' => 0,
                        'fail' => 0,
                    ],
                ],
            ],
            'warnings' => [],
            'result' => [
                'refreshed' => true,
                'rolled_back' => $dryRun,
                'seed_run_logged' => ! $dryRun,
            ],
            'error' => null,
        ];
    }
}
