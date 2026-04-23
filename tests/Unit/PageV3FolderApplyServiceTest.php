<?php

namespace Tests\Unit;

use App\Services\PageV3PromptGenerator\PageV3FolderApplyService;
use App\Services\PageV3PromptGenerator\PageV3FolderPlanService;
use App\Services\PageV3PromptGenerator\PageV3PackageRefreshService;
use App\Services\PageV3PromptGenerator\PageV3PackageSeedService;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class PageV3FolderApplyServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_sync_mode_executes_category_before_page_in_planner_order(): void
    {
        $calls = [];
        $planner = Mockery::mock(PageV3FolderPlanService::class);
        $seed = Mockery::mock(PageV3PackageSeedService::class);
        $refresh = Mockery::mock(PageV3PackageRefreshService::class);

        $planner->shouldReceive('run')
            ->once()
            ->with('scope-target', Mockery::on(fn (array $options): bool => $options['mode'] === 'sync'))
            ->andReturn($this->plannerResult([
                $this->planPackage('database/seeders/Page_V3/Tests/Unit/CategorySeeder', 'seed', 'category'),
                $this->planPackage('database/seeders/Page_V3/Tests/Unit/PageSeeder', 'refresh', 'page', 'seeded'),
            ]));

        $seed->shouldReceive('run')
            ->once()
            ->with('database/seeders/Page_V3/Tests/Unit/CategorySeeder/definition.json', Mockery::type('array'))
            ->andReturnUsing(function () use (&$calls): array {
                $calls[] = 'seed:Category';

                return [
                    'result' => [
                        'seeded' => true,
                    ],
                    'error' => null,
                ];
            });

        $refresh->shouldReceive('run')
            ->once()
            ->with('database/seeders/Page_V3/Tests/Unit/PageSeeder/definition.json', Mockery::type('array'))
            ->andReturnUsing(function () use (&$calls): array {
                $calls[] = 'refresh:Page';

                return [
                    'result' => [
                        'refreshed' => true,
                    ],
                    'error' => null,
                ];
            });

        $result = (new PageV3FolderApplyService($planner, $seed, $refresh))->run('scope-target', [
            'mode' => 'sync',
            'dry_run' => true,
            'skip_release_check' => true,
        ]);

        $this->assertNull($result['error']);
        $this->assertSame(['seed:Category', 'refresh:Page'], $calls);
        $this->assertSame('ok', $result['execution']['packages'][0]['status']);
        $this->assertSame('ok', $result['execution']['packages'][1]['status']);
    }

    public function test_blocked_planner_state_aborts_before_any_writes(): void
    {
        $planner = Mockery::mock(PageV3FolderPlanService::class);
        $seed = Mockery::mock(PageV3PackageSeedService::class);
        $refresh = Mockery::mock(PageV3PackageRefreshService::class);

        $planner->shouldReceive('run')
            ->once()
            ->andReturn($this->plannerResult([
                $this->planPackage('database/seeders/Page_V3/Tests/Unit/PageSeeder', 'blocked', 'page', 'broken_disk_package'),
            ]));

        $seed->shouldNotReceive('run');
        $refresh->shouldNotReceive('run');

        $result = (new PageV3FolderApplyService($planner, $seed, $refresh))->run('scope-target', [
            'mode' => 'sync',
            'dry_run' => true,
        ]);

        $this->assertSame('blocked_packages', $result['error']['reason']);
        $this->assertSame('blocked', $result['execution']['packages'][0]['status']);
    }

    public function test_package_failure_stops_remaining_packages_and_keeps_pending_statuses(): void
    {
        $calls = [];
        $planner = Mockery::mock(PageV3FolderPlanService::class);
        $seed = Mockery::mock(PageV3PackageSeedService::class);
        $refresh = Mockery::mock(PageV3PackageRefreshService::class);

        $planner->shouldReceive('run')
            ->once()
            ->andReturn($this->plannerResult([
                $this->planPackage('database/seeders/Page_V3/Tests/Unit/CategorySeeder', 'seed', 'category'),
                $this->planPackage('database/seeders/Page_V3/Tests/Unit/PageSeeder', 'refresh', 'page', 'seeded'),
                $this->planPackage('database/seeders/Page_V3/Tests/Unit/PageTwoSeeder', 'seed', 'page'),
            ]));

        $seed->shouldReceive('run')
            ->once()
            ->with('database/seeders/Page_V3/Tests/Unit/CategorySeeder/definition.json', Mockery::type('array'))
            ->andReturnUsing(function () use (&$calls): array {
                $calls[] = 'seed:Category';

                return [
                    'result' => [
                        'seeded' => true,
                    ],
                    'error' => null,
                ];
            });

        $refresh->shouldReceive('run')
            ->once()
            ->with('database/seeders/Page_V3/Tests/Unit/PageSeeder/definition.json', Mockery::type('array'))
            ->andReturnUsing(function () use (&$calls): array {
                $calls[] = 'refresh:Page';

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

        $result = (new PageV3FolderApplyService($planner, $seed, $refresh))->run('scope-target', [
            'mode' => 'sync',
            'force' => true,
            'skip_release_check' => true,
        ]);

        $this->assertSame(['seed:Category', 'refresh:Page'], $calls);
        $this->assertSame('package_failed', $result['error']['reason']);
        $this->assertSame('ok', $result['execution']['packages'][0]['status']);
        $this->assertSame('failed', $result['execution']['packages'][1]['status']);
        $this->assertSame('pending', $result['execution']['packages'][2]['status']);
    }

    public function test_it_writes_a_compact_report_with_scope_warning(): void
    {
        Storage::fake('local');
        $planner = Mockery::mock(PageV3FolderPlanService::class);
        $seed = Mockery::mock(PageV3PackageSeedService::class);
        $refresh = Mockery::mock(PageV3PackageRefreshService::class);

        $planner->shouldReceive('run')
            ->once()
            ->andReturn($this->plannerResult([
                array_merge($this->planPackage('database/seeders/Page_V3/Tests/Unit/PageSeeder', 'skip', 'page', 'seeded'), [
                    'warnings' => [
                        'Page package references category slug [outside-scope] outside the current scope; apply will not expand scope.',
                    ],
                ]),
            ]));

        $seed->shouldNotReceive('run');
        $refresh->shouldNotReceive('run');

        $service = new PageV3FolderApplyService($planner, $seed, $refresh);
        $result = $service->run('scope-target', [
            'mode' => 'refresh',
            'dry_run' => true,
        ]);
        $path = $service->writeReport($result);

        Storage::disk('local')->assertExists($path);
        $this->assertStringStartsWith('folder-apply-reports/page-v3/', $path);
    }

    /**
     * @param  array<int, array<string, mixed>>  $packages
     * @return array<string, mixed>
     */
    private function plannerResult(array $packages): array
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
            'scope' => [
                'input' => 'scope-target',
                'resolved_root_absolute_path' => base_path('database/seeders/Page_V3/Tests/Unit'),
                'resolved_root_relative_path' => 'database/seeders/Page_V3/Tests/Unit',
                'single_package' => false,
                'mode' => 'sync',
            ],
            'summary' => $summary,
            'packages' => $packages,
            'error' => null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function planPackage(
        string $relativePath,
        string $action,
        string $packageType,
        string $state = 'not_seeded',
    ): array {
        $className = class_basename(str_replace('/', '\\', $relativePath));

        return [
            'relative_path' => $relativePath,
            'definition_relative_path' => $relativePath . '/definition.json',
            'resolved_seeder_class' => 'Database\\Seeders\\Page_V3\\Tests\\Unit\\' . $className,
            'package_type' => $packageType,
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
