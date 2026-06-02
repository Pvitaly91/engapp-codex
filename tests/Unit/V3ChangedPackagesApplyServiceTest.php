<?php

namespace Tests\Unit;

use App\Services\V3PromptGenerator\V3ChangedPackagesApplyService;
use App\Services\V3PromptGenerator\V3ChangedPackagesPlanService;
use App\Services\V3PromptGenerator\V3DeletedPackageCleanupService;
use App\Services\V3PromptGenerator\V3PackageRefreshService;
use App\Services\V3PromptGenerator\V3PackageSeedService;
use Mockery;
use Tests\TestCase;

class V3ChangedPackagesApplyServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_dry_run_preflights_deleted_cleanup_and_current_upserts_without_live_writes(): void
    {
        $planner = Mockery::mock(V3ChangedPackagesPlanService::class);
        $cleanup = Mockery::mock(V3DeletedPackageCleanupService::class);
        $seed = Mockery::mock(V3PackageSeedService::class);
        $refresh = Mockery::mock(V3PackageRefreshService::class);
        $cleanupCalls = [];
        $seedCalls = [];
        $refreshCalls = [];
        $packages = [
            $this->deletedPackage('database/seeders/V3/Tests/Unit/Changed/GhostSeeder', 'Database\\Seeders\\V3\\Tests\\Unit\\Changed\\GhostSeeder'),
            $this->currentPackage('database/seeders/V3/Tests/Unit/Changed/AlphaSeeder', 'seed', 'not_seeded', 'Database\\Seeders\\V3\\Tests\\Unit\\Changed\\AlphaSeeder'),
            $this->currentPackage('database/seeders/V3/Tests/Unit/Changed/BetaSeeder', 'refresh', 'seeded', 'Database\\Seeders\\V3\\Tests\\Unit\\Changed\\BetaSeeder'),
        ];

        $planner->shouldReceive('run')
            ->once()
            ->with('scope-target', Mockery::on(fn (array $options): bool => ($options['with_release_check'] ?? null) === false))
            ->andReturn($this->plannerResult($packages));

        $cleanup->shouldReceive('runPackageRecord')
            ->once()
            ->andReturnUsing(function (array $package, array $options) use (&$cleanupCalls): array {
                $cleanupCalls[] = [
                    'package' => $package['package_key'],
                    'dry_run' => $options['dry_run'] ?? null,
                    'force' => $options['force'] ?? null,
                ];

                return $this->deletedCleanupResult(true);
            });

        $seed->shouldReceive('run')
            ->once()
            ->andReturnUsing(function (string $target, array $options) use (&$seedCalls): array {
                $seedCalls[] = [
                    'target' => $target,
                    'dry_run' => $options['dry_run'] ?? null,
                    'skip_release_check' => $options['skip_release_check'] ?? null,
                ];

                return $this->seedResult(true);
            });

        $refresh->shouldReceive('run')
            ->once()
            ->andReturnUsing(function (string $target, array $options) use (&$refreshCalls): array {
                $refreshCalls[] = [
                    'target' => $target,
                    'dry_run' => $options['dry_run'] ?? null,
                    'skip_release_check' => $options['skip_release_check'] ?? null,
                ];

                return $this->refreshResult(true);
            });

        $result = (new V3ChangedPackagesApplyService($planner, $cleanup, $seed, $refresh))->run('scope-target', [
            'dry_run' => true,
            'skip_release_check' => true,
        ]);

        $this->assertNull($result['error']);
        $this->assertTrue((bool) $result['preflight']['executed']);
        $this->assertSame(3, $result['preflight']['summary']['candidates']);
        $this->assertSame(3, $result['preflight']['summary']['ok']);
        $this->assertSame(0, $result['execution']['cleanup_deleted']['started']);
        $this->assertSame(0, $result['execution']['upsert_present']['started']);
        $this->assertSame([
            ['package' => 'database/seeders/V3/Tests/Unit/Changed/GhostSeeder', 'dry_run' => true, 'force' => false],
        ], $cleanupCalls);
        $this->assertSame([
            ['target' => 'database/seeders/V3/Tests/Unit/Changed/AlphaSeeder/definition.json', 'dry_run' => true, 'skip_release_check' => true],
        ], $seedCalls);
        $this->assertSame([
            ['target' => 'database/seeders/V3/Tests/Unit/Changed/BetaSeeder/definition.json', 'dry_run' => true, 'skip_release_check' => true],
        ], $refreshCalls);
    }

    public function test_cleanup_phase_failure_prevents_upsert_phase_after_full_scope_preflight(): void
    {
        $planner = Mockery::mock(V3ChangedPackagesPlanService::class);
        $cleanup = Mockery::mock(V3DeletedPackageCleanupService::class);
        $seed = Mockery::mock(V3PackageSeedService::class);
        $refresh = Mockery::mock(V3PackageRefreshService::class);
        $cleanupCalls = [];
        $seedCalls = [];
        $packages = [
            $this->deletedPackage('database/seeders/V3/Tests/Unit/Changed/GhostSeeder', 'Database\\Seeders\\V3\\Tests\\Unit\\Changed\\GhostSeeder'),
            $this->currentPackage('database/seeders/V3/Tests/Unit/Changed/AlphaSeeder', 'seed', 'not_seeded', 'Database\\Seeders\\V3\\Tests\\Unit\\Changed\\AlphaSeeder'),
        ];

        $planner->shouldReceive('run')
            ->once()
            ->andReturn($this->plannerResult($packages));

        $cleanup->shouldReceive('runPackageRecord')
            ->twice()
            ->andReturnUsing(function (array $package, array $options) use (&$cleanupCalls): array {
                $cleanupCalls[] = [
                    'package' => $package['package_key'],
                    'dry_run' => $options['dry_run'] ?? null,
                    'force' => $options['force'] ?? null,
                ];

                if (($options['dry_run'] ?? false) === true) {
                    return $this->deletedCleanupResult(true);
                }

                return $this->deletedCleanupResult(false, true);
            });

        $seed->shouldReceive('run')
            ->once()
            ->andReturnUsing(function (string $target, array $options) use (&$seedCalls): array {
                $seedCalls[] = [
                    'target' => $target,
                    'dry_run' => $options['dry_run'] ?? null,
                ];

                return $this->seedResult((bool) ($options['dry_run'] ?? false));
            });

        $refresh->shouldNotReceive('run');

        $result = (new V3ChangedPackagesApplyService($planner, $cleanup, $seed, $refresh))->run('scope-target', [
            'force' => true,
            'skip_release_check' => true,
        ]);

        $this->assertSame('cleanup_deleted', $result['error']['phase']);
        $this->assertSame('cleanup_deleted_failed', $result['error']['reason']);
        $this->assertSame(2, count($cleanupCalls));
        $this->assertSame([
            ['target' => 'database/seeders/V3/Tests/Unit/Changed/AlphaSeeder/definition.json', 'dry_run' => true],
        ], $seedCalls);
        $this->assertSame(0, $result['execution']['upsert_present']['started']);
        $this->assertSame('failed', $result['execution']['cleanup_deleted']['packages'][0]['status']);
        $this->assertSame('pending', $result['execution']['upsert_present']['packages'][0]['status']);
    }

    public function test_upsert_phase_failure_keeps_remaining_packages_pending(): void
    {
        $planner = Mockery::mock(V3ChangedPackagesPlanService::class);
        $cleanup = Mockery::mock(V3DeletedPackageCleanupService::class);
        $seed = Mockery::mock(V3PackageSeedService::class);
        $refresh = Mockery::mock(V3PackageRefreshService::class);
        $seedCalls = [];
        $refreshCalls = [];
        $packages = [
            $this->currentPackage('database/seeders/V3/Tests/Unit/Changed/AlphaSeeder', 'seed', 'not_seeded', 'Database\\Seeders\\V3\\Tests\\Unit\\Changed\\AlphaSeeder'),
            $this->currentPackage('database/seeders/V3/Tests/Unit/Changed/BetaSeeder', 'refresh', 'seeded', 'Database\\Seeders\\V3\\Tests\\Unit\\Changed\\BetaSeeder'),
            $this->currentPackage('database/seeders/V3/Tests/Unit/Changed/GammaSeeder', 'seed', 'not_seeded', 'Database\\Seeders\\V3\\Tests\\Unit\\Changed\\GammaSeeder'),
        ];

        $planner->shouldReceive('run')
            ->once()
            ->andReturn($this->plannerResult($packages));

        $cleanup->shouldNotReceive('runPackageRecord');

        $seed->shouldReceive('run')
            ->times(3)
            ->andReturnUsing(function (string $target, array $options) use (&$seedCalls): array {
                $seedCalls[] = [
                    'target' => $target,
                    'dry_run' => $options['dry_run'] ?? null,
                ];

                if (($options['dry_run'] ?? false) === true) {
                    return $this->seedResult(true);
                }

                return $this->seedResult(false);
            });

        $refresh->shouldReceive('run')
            ->twice()
            ->andReturnUsing(function (string $target, array $options) use (&$refreshCalls): array {
                $refreshCalls[] = [
                    'target' => $target,
                    'dry_run' => $options['dry_run'] ?? null,
                    'force' => $options['force'] ?? null,
                ];

                if (($options['dry_run'] ?? false) === true) {
                    return $this->refreshResult(true);
                }

                return $this->refreshFailureResult();
            });

        $result = (new V3ChangedPackagesApplyService($planner, $cleanup, $seed, $refresh))->run('scope-target', [
            'force' => true,
            'skip_release_check' => true,
        ]);

        $this->assertSame('upsert_present', $result['error']['phase']);
        $this->assertSame('upsert_present_failed', $result['error']['reason']);
        $this->assertCount(3, $seedCalls);
        $this->assertCount(2, $refreshCalls);
        $this->assertSame(2, $result['execution']['upsert_present']['completed']);
        $this->assertSame('ok', $result['execution']['upsert_present']['packages'][0]['status']);
        $this->assertSame('failed', $result['execution']['upsert_present']['packages'][1]['status']);
        $this->assertSame('pending', $result['execution']['upsert_present']['packages'][2]['status']);
    }

    /**
     * @param  array<int, array<string, mixed>>  $packages
     * @return array<string, mixed>
     */
    private function plannerResult(array $packages): array
    {
        return [
            'diff' => [
                'mode' => 'working_tree',
                'base' => null,
                'head' => 'HEAD',
                'include_untracked' => true,
            ],
            'scope' => [
                'input' => 'scope-target',
                'resolved_root_absolute_path' => base_path('database/seeders/V3/Tests/Unit/Changed'),
                'resolved_root_relative_path' => 'database/seeders/V3/Tests/Unit/Changed',
                'single_package' => false,
            ],
            'summary' => [
                'changed_packages' => count($packages),
                'seed_candidates' => count(array_filter($packages, fn (array $package): bool => ($package['recommended_action'] ?? null) === 'seed')),
                'refresh_candidates' => count(array_filter($packages, fn (array $package): bool => ($package['recommended_action'] ?? null) === 'refresh')),
                'deleted_cleanup_candidates' => count(array_filter($packages, fn (array $package): bool => ($package['recommended_action'] ?? null) === 'unseed_deleted')),
                'skipped' => count(array_filter($packages, fn (array $package): bool => ($package['recommended_action'] ?? null) === 'skip')),
                'blocked' => count(array_filter($packages, fn (array $package): bool => ($package['recommended_action'] ?? null) === 'blocked')),
                'warnings' => 0,
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
     * @return array<string, mixed>
     */
    private function deletedPackage(string $relativePath, string $resolvedSeederClass): array
    {
        return [
            'package_key' => $relativePath,
            'package_type' => 'v3_test',
            'change_type' => 'deleted',
            'current_relative_path' => null,
            'historical_relative_path' => $relativePath,
            'current_on_disk' => false,
            'historical_metadata_available' => true,
            'resolved_seeder_class' => $resolvedSeederClass,
            'package_state' => 'deleted_from_disk',
            'recommended_phase' => 'cleanup_deleted',
            'recommended_action' => 'unseed_deleted',
            'release_check' => ['executed' => false, 'status' => 'skipped'],
            'warnings' => [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function currentPackage(string $relativePath, string $action, string $state, string $resolvedSeederClass): array
    {
        return [
            'package_key' => $relativePath,
            'package_type' => 'v3_test',
            'change_type' => $action === 'seed' ? 'added' : 'modified',
            'current_relative_path' => $relativePath,
            'historical_relative_path' => null,
            'current_on_disk' => true,
            'historical_metadata_available' => false,
            'resolved_seeder_class' => $resolvedSeederClass,
            'package_state' => $state,
            'recommended_phase' => 'upsert_present',
            'recommended_action' => $action,
            'release_check' => ['executed' => false, 'status' => 'skipped'],
            'warnings' => [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function deletedCleanupResult(bool $dryRun, bool $failed = false): array
    {
        return [
            'ownership' => [
                'seed_run_present' => true,
                'package_present_in_db' => true,
            ],
            'impact' => [
                'warnings' => [],
                'counts' => ['Question' => 1],
            ],
            'result' => [
                'deleted' => ! $failed,
                'rolled_back' => $dryRun,
                'seed_run_removed' => ! $dryRun && ! $failed,
            ],
            'error' => $failed
                ? [
                    'stage' => 'deleted_cleanup',
                    'message' => 'Cleanup failed.',
                ]
                : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function seedResult(bool $dryRun): array
    {
        return [
            'mode' => [
                'release_check_skipped' => true,
            ],
            'preflight' => [
                'summary' => [
                    'check_counts' => [
                        'warn' => 0,
                    ],
                ],
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
            'mode' => [
                'release_check_skipped' => true,
            ],
            'preflight' => [
                'summary' => [
                    'check_counts' => [
                        'warn' => 0,
                    ],
                ],
            ],
            'warnings' => [],
            'result' => [
                'refreshed' => true,
                'seeded_after' => true,
                'seed_run_logged' => ! $dryRun,
            ],
            'error' => null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function refreshFailureResult(): array
    {
        return [
            'mode' => [
                'release_check_skipped' => true,
            ],
            'preflight' => [
                'summary' => [
                    'check_counts' => [
                        'warn' => 0,
                    ],
                ],
            ],
            'warnings' => [],
            'result' => [
                'refreshed' => false,
                'seeded_after' => false,
                'seed_run_logged' => false,
            ],
            'error' => [
                'stage' => 'refresh',
                'message' => 'Refresh failed.',
            ],
        ];
    }
}
