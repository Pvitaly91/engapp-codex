<?php

namespace Tests\Unit;

use App\Services\V3PromptGenerator\V3FolderPlanService;
use App\Services\V3PromptGenerator\V3FolderUnseedService;
use App\Services\V3PromptGenerator\V3PackageUnseedService;
use Mockery;
use Tests\TestCase;

class V3FolderUnseedServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_it_preflights_all_candidates_before_live_execution_and_keeps_planner_order(): void
    {
        $calls = [];
        $planner = Mockery::mock(V3FolderPlanService::class);
        $unseed = Mockery::mock(V3PackageUnseedService::class);

        $planner->shouldReceive('run')
            ->once()
            ->with('scope-target', Mockery::on(
                fn (array $options): bool => ($options['mode'] ?? null) === 'unseed' && ($options['strict'] ?? null) === false
            ))
            ->andReturn($this->plannerResult([
                $this->planPackage('database/seeders/V3/Tests/Unit/BetaSeeder'),
                $this->planPackage('database/seeders/V3/Tests/Unit/AlphaSeeder'),
                $this->planPackage('database/seeders/V3/Tests/Unit/GammaSeeder', 'skip', 'not_seeded'),
            ]));

        $unseed->shouldReceive('run')
            ->times(4)
            ->andReturnUsing(function (string $target, array $options) use (&$calls): array {
                $calls[] = (($options['dry_run'] ?? false) ? 'dry' : 'live') . ':' . $target;

                return [
                    'ownership' => [
                        'seed_run_present' => true,
                        'package_present_in_db' => true,
                    ],
                    'impact' => [
                        'counts' => ['Question' => 1],
                        'warnings' => [],
                    ],
                    'result' => [
                        'deleted' => true,
                        'rolled_back' => (bool) ($options['dry_run'] ?? false),
                        'seed_run_removed' => ! ($options['dry_run'] ?? false),
                    ],
                    'error' => null,
                ];
            });

        $result = (new V3FolderUnseedService($planner, $unseed))->run('scope-target', [
            'force' => true,
        ]);

        $this->assertNull($result['error']);
        $this->assertSame([
            'dry:database/seeders/V3/Tests/Unit/BetaSeeder/definition.json',
            'dry:database/seeders/V3/Tests/Unit/AlphaSeeder/definition.json',
            'live:database/seeders/V3/Tests/Unit/BetaSeeder/definition.json',
            'live:database/seeders/V3/Tests/Unit/AlphaSeeder/definition.json',
        ], $calls);
        $this->assertSame(2, $result['preflight']['summary']['candidates']);
        $this->assertSame(2, $result['preflight']['summary']['ok']);
        $this->assertSame(1, $result['preflight']['summary']['skipped']);
        $this->assertSame(2, $result['execution']['started']);
        $this->assertSame(2, $result['execution']['completed']);
        $this->assertSame(2, $result['execution']['succeeded']);
        $this->assertSame('ok', $result['execution']['packages'][0]['status']);
        $this->assertSame('ok', $result['execution']['packages'][1]['status']);
        $this->assertSame('skipped', $result['execution']['packages'][2]['status']);
    }

    public function test_preflight_failure_aborts_before_any_live_unseed_runs(): void
    {
        $calls = [];
        $planner = Mockery::mock(V3FolderPlanService::class);
        $unseed = Mockery::mock(V3PackageUnseedService::class);

        $planner->shouldReceive('run')
            ->once()
            ->andReturn($this->plannerResult([
                $this->planPackage('database/seeders/V3/Tests/Unit/BetaSeeder'),
                $this->planPackage('database/seeders/V3/Tests/Unit/AlphaSeeder'),
            ]));

        $unseed->shouldReceive('run')
            ->twice()
            ->andReturnUsing(function (string $target, array $options) use (&$calls): array {
                $calls[] = (($options['dry_run'] ?? false) ? 'dry' : 'live') . ':' . $target;

                if (str_contains($target, 'BetaSeeder')) {
                    return [
                        'impact' => [
                            'counts' => ['Question' => 1],
                            'warnings' => [],
                        ],
                        'result' => [
                            'deleted' => false,
                            'rolled_back' => true,
                            'seed_run_removed' => false,
                        ],
                        'error' => [
                            'stage' => 'dependency_guard',
                            'reason' => 'external_saved_tests_reference_package',
                            'message' => 'Blocked by dependency guard.',
                        ],
                    ];
                }

                return [
                    'ownership' => [
                        'seed_run_present' => true,
                        'package_present_in_db' => true,
                    ],
                    'impact' => [
                        'counts' => ['Question' => 1],
                        'warnings' => [],
                    ],
                    'result' => [
                        'deleted' => true,
                        'rolled_back' => true,
                        'seed_run_removed' => false,
                    ],
                    'error' => null,
                ];
            });

        $result = (new V3FolderUnseedService($planner, $unseed))->run('scope-target', [
            'force' => true,
        ]);

        $this->assertSame([
            'dry:database/seeders/V3/Tests/Unit/BetaSeeder/definition.json',
            'dry:database/seeders/V3/Tests/Unit/AlphaSeeder/definition.json',
        ], $calls);
        $this->assertSame('preflight', $result['error']['stage']);
        $this->assertSame('preflight_failed', $result['error']['reason']);
        $this->assertSame('database/seeders/V3/Tests/Unit/BetaSeeder', $result['error']['package']);
        $this->assertSame(0, $result['execution']['started']);
        $this->assertSame(1, $result['preflight']['summary']['fail']);
        $this->assertSame(1, $result['preflight']['summary']['ok']);
    }

    public function test_blocked_packages_stay_non_executed_while_clean_candidates_can_run(): void
    {
        $calls = [];
        $planner = Mockery::mock(V3FolderPlanService::class);
        $unseed = Mockery::mock(V3PackageUnseedService::class);

        $planner->shouldReceive('run')
            ->once()
            ->andReturn($this->plannerResult([
                $this->planPackage('database/seeders/V3/Tests/Unit/BrokenSeeder', 'blocked', 'broken_disk_package', [
                    'warnings' => ['Missing definition.json.'],
                ]),
                $this->planPackage('database/seeders/V3/Tests/Unit/AlphaSeeder'),
            ]));

        $unseed->shouldReceive('run')
            ->twice()
            ->andReturnUsing(function (string $target, array $options) use (&$calls): array {
                $calls[] = (($options['dry_run'] ?? false) ? 'dry' : 'live') . ':' . $target;

                return [
                    'ownership' => [
                        'seed_run_present' => true,
                        'package_present_in_db' => true,
                    ],
                    'impact' => [
                        'counts' => ['Question' => 1],
                        'warnings' => [],
                    ],
                    'result' => [
                        'deleted' => true,
                        'rolled_back' => (bool) ($options['dry_run'] ?? false),
                        'seed_run_removed' => ! ($options['dry_run'] ?? false),
                    ],
                    'error' => null,
                ];
            });

        $result = (new V3FolderUnseedService($planner, $unseed))->run('scope-target', [
            'force' => true,
        ]);

        $this->assertNull($result['error']);
        $this->assertSame([
            'dry:database/seeders/V3/Tests/Unit/AlphaSeeder/definition.json',
            'live:database/seeders/V3/Tests/Unit/AlphaSeeder/definition.json',
        ], $calls);
        $this->assertSame('skip', $result['preflight']['packages'][0]['status']);
        $this->assertSame('blocked', $result['execution']['packages'][0]['status']);
        $this->assertSame('ok', $result['execution']['packages'][1]['status']);
    }

    /**
     * @param  array<int, array<string, mixed>>  $packages
     * @return array<string, mixed>
     */
    private function plannerResult(array $packages): array
    {
        return [
            'scope' => [
                'input' => 'scope-target',
                'resolved_root_absolute_path' => base_path('database/seeders/V3/Tests/Unit'),
                'resolved_root_relative_path' => 'database/seeders/V3/Tests/Unit',
                'single_package' => false,
                'mode' => 'unseed',
            ],
            'summary' => [
                'total_packages' => count($packages),
                'seed_candidates' => 0,
                'refresh_candidates' => 0,
                'unseed_candidates' => count(array_filter(
                    $packages,
                    fn (array $package): bool => ($package['recommended_action'] ?? null) === 'unseed'
                )),
                'skipped' => count(array_filter(
                    $packages,
                    fn (array $package): bool => ($package['recommended_action'] ?? null) === 'skip'
                )),
                'blocked' => count(array_filter(
                    $packages,
                    fn (array $package): bool => ($package['recommended_action'] ?? null) === 'blocked'
                )),
                'warnings' => count(array_filter(
                    $packages,
                    fn (array $package): bool => (array) ($package['warnings'] ?? []) !== []
                )),
            ],
            'packages' => $packages,
            'error' => null,
        ];
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function planPackage(
        string $relativePath,
        string $action = 'unseed',
        string $state = 'seeded',
        array $overrides = [],
    ): array {
        $className = class_basename(str_replace('/', '\\', $relativePath));

        return array_merge([
            'relative_path' => $relativePath,
            'definition_relative_path' => $relativePath . '/definition.json',
            'resolved_seeder_class' => 'Database\\Seeders\\V3\\Tests\\Unit\\' . $className,
            'package_type' => 'v3_test',
            'package_state' => $state,
            'recommended_action' => $action,
            'warnings' => [],
        ], $overrides);
    }
}
