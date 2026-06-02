<?php

namespace Tests\Unit;

use App\Services\PageV3PromptGenerator\PageV3ChangedPackagesApplyService;
use App\Services\PageV3PromptGenerator\PageV3ChangedPackagesPlanService;
use App\Services\PageV3PromptGenerator\PageV3DeletedPackageCleanupService;
use App\Services\PageV3PromptGenerator\PageV3PackageRefreshService;
use App\Services\PageV3PromptGenerator\PageV3PackageSeedService;
use Mockery;
use Tests\TestCase;

class PageV3ChangedPackagesApplyServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_live_run_reuses_planner_order_for_deleted_cleanup_then_upsert_present(): void
    {
        $planner = Mockery::mock(PageV3ChangedPackagesPlanService::class);
        $cleanup = Mockery::mock(PageV3DeletedPackageCleanupService::class);
        $seed = Mockery::mock(PageV3PackageSeedService::class);
        $refresh = Mockery::mock(PageV3PackageRefreshService::class);
        $cleanupCalls = [];
        $seedCalls = [];
        $refreshCalls = [];
        $packages = [
            $this->deletedPackage('database/seeders/Page_V3/Tests/Unit/Changed/PageSeeder', 'page', 'Database\\Seeders\\Page_V3\\Tests\\Unit\\Changed\\PageSeeder'),
            $this->deletedPackage('database/seeders/Page_V3/Tests/Unit/Changed/CategorySeeder', 'category', 'Database\\Seeders\\Page_V3\\Tests\\Unit\\Changed\\CategorySeeder'),
            $this->currentPackage('database/seeders/Page_V3/Tests/Unit/Changed/CategoryCurrentSeeder', 'category', 'seed', 'not_seeded', 'Database\\Seeders\\Page_V3\\Tests\\Unit\\Changed\\CategoryCurrentSeeder'),
            $this->currentPackage('database/seeders/Page_V3/Tests/Unit/Changed/PageCurrentSeeder', 'page', 'refresh', 'seeded', 'Database\\Seeders\\Page_V3\\Tests\\Unit\\Changed\\PageCurrentSeeder'),
        ];

        $planner->shouldReceive('run')
            ->once()
            ->andReturn($this->plannerResult($packages));

        $cleanup->shouldReceive('runPackageRecord')
            ->times(4)
            ->andReturnUsing(function (array $package, array $options) use (&$cleanupCalls): array {
                $cleanupCalls[] = [
                    'package' => $package['package_key'],
                    'dry_run' => $options['dry_run'] ?? null,
                    'additional_cleanup_classes' => $options['additional_cleanup_classes'] ?? [],
                ];

                return $this->deletedCleanupResult((bool) ($options['dry_run'] ?? false));
            });

        $seed->shouldReceive('run')
            ->twice()
            ->andReturnUsing(function (string $target, array $options) use (&$seedCalls): array {
                $seedCalls[] = [
                    'target' => $target,
                    'dry_run' => $options['dry_run'] ?? null,
                ];

                return $this->seedResult((bool) ($options['dry_run'] ?? false));
            });

        $refresh->shouldReceive('run')
            ->twice()
            ->andReturnUsing(function (string $target, array $options) use (&$refreshCalls): array {
                $refreshCalls[] = [
                    'target' => $target,
                    'dry_run' => $options['dry_run'] ?? null,
                    'force' => $options['force'] ?? null,
                ];

                return $this->refreshResult((bool) ($options['dry_run'] ?? false));
            });

        $result = (new PageV3ChangedPackagesApplyService($planner, $cleanup, $seed, $refresh))->run('scope-target', [
            'force' => true,
            'skip_release_check' => true,
        ]);

        $this->assertNull($result['error']);
        $this->assertSame(4, $result['preflight']['summary']['candidates']);
        $this->assertSame(4, $result['preflight']['summary']['ok']);
        $this->assertSame([
            'database/seeders/Page_V3/Tests/Unit/Changed/PageSeeder',
            'database/seeders/Page_V3/Tests/Unit/Changed/CategorySeeder',
            'database/seeders/Page_V3/Tests/Unit/Changed/PageSeeder',
            'database/seeders/Page_V3/Tests/Unit/Changed/CategorySeeder',
        ], array_column($cleanupCalls, 'package'));
        $this->assertSame([], $cleanupCalls[0]['additional_cleanup_classes']);
        $this->assertSame(['Database\\Seeders\\Page_V3\\Tests\\Unit\\Changed\\PageSeeder'], $cleanupCalls[1]['additional_cleanup_classes']);
        $this->assertSame('database/seeders/Page_V3/Tests/Unit/Changed/CategoryCurrentSeeder/definition.json', $seedCalls[0]['target']);
        $this->assertSame('database/seeders/Page_V3/Tests/Unit/Changed/PageCurrentSeeder/definition.json', $refreshCalls[0]['target']);
        $this->assertSame(2, $result['execution']['cleanup_deleted']['succeeded']);
        $this->assertSame(2, $result['execution']['upsert_present']['succeeded']);
    }

    public function test_cleanup_failure_prevents_page_upsert_phase(): void
    {
        $planner = Mockery::mock(PageV3ChangedPackagesPlanService::class);
        $cleanup = Mockery::mock(PageV3DeletedPackageCleanupService::class);
        $seed = Mockery::mock(PageV3PackageSeedService::class);
        $refresh = Mockery::mock(PageV3PackageRefreshService::class);
        $packages = [
            $this->deletedPackage('database/seeders/Page_V3/Tests/Unit/Changed/PageSeeder', 'page', 'Database\\Seeders\\Page_V3\\Tests\\Unit\\Changed\\PageSeeder'),
            $this->currentPackage('database/seeders/Page_V3/Tests/Unit/Changed/PageCurrentSeeder', 'page', 'seed', 'not_seeded', 'Database\\Seeders\\Page_V3\\Tests\\Unit\\Changed\\PageCurrentSeeder'),
        ];

        $planner->shouldReceive('run')
            ->once()
            ->andReturn($this->plannerResult($packages));

        $cleanup->shouldReceive('runPackageRecord')
            ->twice()
            ->andReturnUsing(function (array $package, array $options): array {
                if (($options['dry_run'] ?? false) === true) {
                    return $this->deletedCleanupResult(true);
                }

                return $this->deletedCleanupFailureResult();
            });

        $seed->shouldReceive('run')
            ->once()
            ->andReturn($this->seedResult(true));

        $refresh->shouldNotReceive('run');

        $result = (new PageV3ChangedPackagesApplyService($planner, $cleanup, $seed, $refresh))->run('scope-target', [
            'force' => true,
            'skip_release_check' => true,
        ]);

        $this->assertSame('cleanup_deleted_failed', $result['error']['reason']);
        $this->assertSame('cleanup_deleted', $result['error']['phase']);
        $this->assertSame(0, $result['execution']['upsert_present']['started']);
        $this->assertSame('pending', $result['execution']['upsert_present']['packages'][0]['status']);
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
                'resolved_root_absolute_path' => base_path('database/seeders/Page_V3/Tests/Unit/Changed'),
                'resolved_root_relative_path' => 'database/seeders/Page_V3/Tests/Unit/Changed',
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
    private function deletedPackage(string $relativePath, string $packageType, string $resolvedSeederClass): array
    {
        return [
            'package_key' => $relativePath,
            'package_type' => $packageType,
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
    private function currentPackage(string $relativePath, string $packageType, string $action, string $state, string $resolvedSeederClass): array
    {
        return [
            'package_key' => $relativePath,
            'package_type' => $packageType,
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
    private function deletedCleanupResult(bool $dryRun): array
    {
        return [
            'ownership' => [
                'seed_run_present' => true,
                'package_present_in_db' => true,
            ],
            'impact' => [
                'warnings' => [],
                'counts' => ['Page' => 1],
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
    private function deletedCleanupFailureResult(): array
    {
        return [
            'ownership' => [
                'seed_run_present' => true,
                'package_present_in_db' => true,
            ],
            'impact' => [
                'warnings' => [],
                'counts' => ['Page' => 1],
            ],
            'result' => [
                'deleted' => false,
                'rolled_back' => false,
                'seed_run_removed' => false,
            ],
            'error' => [
                'stage' => 'deleted_cleanup',
                'message' => 'Cleanup failed.',
            ],
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
}
