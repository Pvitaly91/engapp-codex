<?php

namespace Tests\Unit;

use App\Services\V3PromptGenerator\V3FolderApplyService;
use App\Services\V3PromptGenerator\V3FolderPlanService;
use App\Services\V3PromptGenerator\V3PackageRefreshService;
use App\Services\V3PromptGenerator\V3PackageSeedService;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class V3FolderApplyServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_sync_mode_executes_seed_and_refresh_candidates_in_planner_order(): void
    {
        $calls = [];
        $planner = Mockery::mock(V3FolderPlanService::class);
        $seed = Mockery::mock(V3PackageSeedService::class);
        $refresh = Mockery::mock(V3PackageRefreshService::class);

        $planner->shouldReceive('run')
            ->once()
            ->with('scope-target', Mockery::on(fn (array $options): bool => $options['mode'] === 'sync' && $options['strict'] === false))
            ->andReturn($this->plannerResult([
                $this->planPackage('database/seeders/V3/Tests/Unit/AlphaSeeder', 'seed'),
                $this->planPackage('database/seeders/V3/Tests/Unit/BetaSeeder', 'refresh', 'seeded'),
                $this->planPackage('database/seeders/V3/Tests/Unit/GammaSeeder', 'skip', 'seeded'),
            ]));

        $seed->shouldReceive('run')
            ->once()
            ->with('database/seeders/V3/Tests/Unit/AlphaSeeder/definition.json', Mockery::type('array'))
            ->andReturnUsing(function () use (&$calls): array {
                $calls[] = 'seed:Alpha';

                return [
                    'result' => [
                        'seeded' => true,
                        'rolled_back' => true,
                    ],
                    'error' => null,
                ];
            });

        $refresh->shouldReceive('run')
            ->once()
            ->with('database/seeders/V3/Tests/Unit/BetaSeeder/definition.json', Mockery::type('array'))
            ->andReturnUsing(function () use (&$calls): array {
                $calls[] = 'refresh:Beta';

                return [
                    'result' => [
                        'refreshed' => true,
                    ],
                    'error' => null,
                ];
            });

        $result = (new V3FolderApplyService($planner, $seed, $refresh))->run('scope-target', [
            'mode' => 'sync',
            'dry_run' => true,
            'skip_release_check' => true,
        ]);

        $this->assertNull($result['error']);
        $this->assertSame(['seed:Alpha', 'refresh:Beta'], $calls);
        $this->assertSame(2, $result['execution']['started']);
        $this->assertSame(2, $result['execution']['completed']);
        $this->assertSame(2, $result['execution']['succeeded']);
        $this->assertSame(0, $result['execution']['failed']);
        $this->assertSame('ok', $result['execution']['packages'][0]['status']);
        $this->assertSame('ok', $result['execution']['packages'][1]['status']);
        $this->assertSame('skipped', $result['execution']['packages'][2]['status']);
    }

    public function test_blocked_planner_state_aborts_before_any_writes(): void
    {
        $planner = Mockery::mock(V3FolderPlanService::class);
        $seed = Mockery::mock(V3PackageSeedService::class);
        $refresh = Mockery::mock(V3PackageRefreshService::class);

        $planner->shouldReceive('run')
            ->once()
            ->andReturn($this->plannerResult([
                $this->planPackage('database/seeders/V3/Tests/Unit/BlockedSeeder', 'blocked', 'db_only_without_seed_run'),
                $this->planPackage('database/seeders/V3/Tests/Unit/SkippedSeeder', 'skip', 'seeded'),
            ]));

        $seed->shouldNotReceive('run');
        $refresh->shouldNotReceive('run');

        $result = (new V3FolderApplyService($planner, $seed, $refresh))->run('scope-target', [
            'mode' => 'sync',
            'dry_run' => true,
        ]);

        $this->assertSame('blocked_packages', $result['error']['reason']);
        $this->assertSame('database/seeders/V3/Tests/Unit/BlockedSeeder', $result['execution']['stopped_on']);
        $this->assertSame(0, $result['execution']['started']);
        $this->assertSame('blocked', $result['execution']['packages'][0]['status']);
    }

    public function test_package_failure_stops_remaining_packages_and_keeps_pending_statuses(): void
    {
        $calls = [];
        $planner = Mockery::mock(V3FolderPlanService::class);
        $seed = Mockery::mock(V3PackageSeedService::class);
        $refresh = Mockery::mock(V3PackageRefreshService::class);

        $planner->shouldReceive('run')
            ->once()
            ->andReturn($this->plannerResult([
                $this->planPackage('database/seeders/V3/Tests/Unit/AlphaSeeder', 'seed'),
                $this->planPackage('database/seeders/V3/Tests/Unit/BetaSeeder', 'refresh', 'seeded'),
                $this->planPackage('database/seeders/V3/Tests/Unit/GammaSeeder', 'seed'),
            ]));

        $seed->shouldReceive('run')
            ->once()
            ->with('database/seeders/V3/Tests/Unit/AlphaSeeder/definition.json', Mockery::type('array'))
            ->andReturnUsing(function () use (&$calls): array {
                $calls[] = 'seed:Alpha';

                return [
                    'result' => [
                        'seeded' => true,
                    ],
                    'error' => null,
                ];
            });

        $refresh->shouldReceive('run')
            ->once()
            ->with('database/seeders/V3/Tests/Unit/BetaSeeder/definition.json', Mockery::type('array'))
            ->andReturnUsing(function () use (&$calls): array {
                $calls[] = 'refresh:Beta';

                return [
                    'result' => [
                        'refreshed' => false,
                    ],
                    'error' => [
                        'stage' => 'refresh',
                        'message' => 'Refresh failed.',
                    ],
                ];
            });

        $result = (new V3FolderApplyService($planner, $seed, $refresh))->run('scope-target', [
            'mode' => 'sync',
            'force' => true,
            'skip_release_check' => true,
        ]);

        $this->assertSame(['seed:Alpha', 'refresh:Beta'], $calls);
        $this->assertSame('package_failed', $result['error']['reason']);
        $this->assertSame('database/seeders/V3/Tests/Unit/BetaSeeder', $result['execution']['stopped_on']);
        $this->assertSame(2, $result['execution']['completed']);
        $this->assertSame('ok', $result['execution']['packages'][0]['status']);
        $this->assertSame('failed', $result['execution']['packages'][1]['status']);
        $this->assertSame('pending', $result['execution']['packages'][2]['status']);
    }

    public function test_it_writes_a_compact_report(): void
    {
        Storage::fake('local');
        $planner = Mockery::mock(V3FolderPlanService::class);
        $seed = Mockery::mock(V3PackageSeedService::class);
        $refresh = Mockery::mock(V3PackageRefreshService::class);

        $planner->shouldReceive('run')
            ->once()
            ->andReturn($this->plannerResult([
                $this->planPackage('database/seeders/V3/Tests/Unit/AlphaSeeder', 'skip', 'seeded'),
            ], [
                'resolved_root_relative_path' => 'database/seeders/V3/Tests/Unit',
                'single_package' => false,
            ]));

        $seed->shouldNotReceive('run');
        $refresh->shouldNotReceive('run');

        $service = new V3FolderApplyService($planner, $seed, $refresh);
        $result = $service->run('scope-target', [
            'mode' => 'missing',
            'dry_run' => true,
        ]);
        $path = $service->writeReport($result);

        Storage::disk('local')->assertExists($path);
        $this->assertStringStartsWith('folder-apply-reports/v3/', $path);
    }

    /**
     * @param  array<int, array<string, mixed>>  $packages
     * @param  array<string, mixed>  $scope
     * @return array<string, mixed>
     */
    private function plannerResult(array $packages, array $scope = []): array
    {
        $summary = [
            'total_packages' => count($packages),
            'seed_candidates' => count(array_filter($packages, fn (array $package): bool => ($package['recommended_action'] ?? null) === 'seed')),
            'refresh_candidates' => count(array_filter($packages, fn (array $package): bool => ($package['recommended_action'] ?? null) === 'refresh')),
            'skipped' => count(array_filter($packages, fn (array $package): bool => ($package['recommended_action'] ?? null) === 'skip')),
            'blocked' => count(array_filter($packages, fn (array $package): bool => ($package['recommended_action'] ?? null) === 'blocked')),
            'warnings' => count(array_filter($packages, fn (array $package): bool => (array) ($package['warnings'] ?? []) !== [])),
        ];

        return [
            'scope' => array_merge([
                'input' => 'scope-target',
                'resolved_root_absolute_path' => base_path('database/seeders/V3/Tests/Unit'),
                'resolved_root_relative_path' => 'database/seeders/V3/Tests/Unit',
                'single_package' => false,
                'mode' => 'sync',
            ], $scope),
            'summary' => $summary,
            'packages' => $packages,
            'error' => null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function planPackage(string $relativePath, string $action, string $state = 'not_seeded'): array
    {
        $className = class_basename(str_replace('/', '\\', $relativePath));

        return [
            'relative_path' => $relativePath,
            'definition_relative_path' => $relativePath . '/definition.json',
            'resolved_seeder_class' => 'Database\\Seeders\\V3\\Tests\\Unit\\' . $className,
            'package_type' => 'v3_test',
            'package_state' => $state,
            'recommended_action' => $action,
            'release_check' => [
                'executed' => false,
                'status' => 'skipped',
            ],
            'warnings' => [],
        ];
    }
}
