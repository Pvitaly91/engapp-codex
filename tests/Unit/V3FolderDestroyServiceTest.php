<?php

namespace Tests\Unit;

use App\Services\V3PromptGenerator\V3FolderDestroyService;
use App\Services\V3PromptGenerator\V3FolderPlanService;
use App\Services\V3PromptGenerator\V3PackageUnseedService;
use Illuminate\Support\Facades\File;
use Mockery;
use Tests\TestCase;

class V3FolderDestroyServiceTest extends TestCase
{
    /**
     * @var array<int, string>
     */
    private array $cleanupPaths = [];

    protected function tearDown(): void
    {
        foreach (array_reverse(array_unique($this->cleanupPaths)) as $path) {
            if (File::isDirectory($path)) {
                File::deleteDirectory($path);

                continue;
            }

            File::delete($path);
        }

        Mockery::close();

        parent::tearDown();
    }

    public function test_it_preflights_and_executes_combined_v3_destroy_in_planner_order(): void
    {
        $scopeRelativePath = 'database/seeders/V3/Tests/Unit/V3DestroyScope';
        $beta = $this->createPackageFiles($scopeRelativePath . '/BetaSeeder');
        $alpha = $this->createPackageFiles($scopeRelativePath . '/AlphaSeeder');
        $gamma = $this->createPackageFiles($scopeRelativePath . '/GammaSeeder');

        $planner = Mockery::mock(V3FolderPlanService::class);
        $planner->shouldReceive('run')
            ->once()
            ->with('scope-target', Mockery::on(
                fn (array $options): bool => ($options['mode'] ?? null) === 'destroy' && ($options['strict'] ?? null) === false
            ))
            ->andReturn($this->plannerResult($scopeRelativePath, [
                $this->planPackage($beta, true, true),
                $this->planPackage($alpha, false, true),
                $this->planPackage($gamma, false, false, 'skip'),
            ]));

        $packageUnseedService = Mockery::mock(V3PackageUnseedService::class);
        $packageUnseedService->shouldReceive('run')
            ->once()
            ->with($beta['relative'], Mockery::on(fn (array $options): bool => (bool) ($options['dry_run'] ?? false)))
            ->andReturn($this->packageServiceResult(true, false, ['Question' => 2]));
        $packageUnseedService->shouldReceive('run')
            ->once()
            ->with($beta['relative'], Mockery::on(fn (array $options): bool => ! (bool) ($options['dry_run'] ?? true) && (bool) ($options['force'] ?? false)))
            ->andReturn($this->packageServiceResult(false, true, ['Question' => 2]));

        $result = (new V3FolderDestroyService($planner, $packageUnseedService))->run('scope-target', [
            'force' => true,
            'remove_empty_dirs' => true,
        ]);

        $this->assertNull($result['error']);
        $this->assertSame(2, $result['plan']['summary']['destroy_candidates']);
        $this->assertSame(2, $result['preflight']['summary']['candidates']);
        $this->assertSame(1, $result['execution']['db_phase']['started']);
        $this->assertSame(1, $result['execution']['db_phase']['succeeded']);
        $this->assertTrue((bool) $result['execution']['db_phase']['packages'][0]['seed_run_removed']);
        $this->assertSame(2, $result['execution']['file_phase']['started']);
        $this->assertSame(2, $result['execution']['file_phase']['succeeded']);
        $this->assertFalse(File::exists($beta['loader']));
        $this->assertFalse(File::isDirectory($beta['package']));
        $this->assertFalse(File::exists($alpha['loader']));
        $this->assertFalse(File::isDirectory($alpha['package']));
        $this->assertTrue(File::exists($gamma['loader']));
        $this->assertTrue(File::isDirectory($gamma['package']));
    }

    public function test_preflight_failure_blocks_all_live_v3_destroy_writes(): void
    {
        $scopeRelativePath = 'database/seeders/V3/Tests/Unit/V3DestroyPreflight';
        $alpha = $this->createPackageFiles($scopeRelativePath . '/AlphaSeeder');

        $planner = Mockery::mock(V3FolderPlanService::class);
        $planner->shouldReceive('run')
            ->once()
            ->andReturn($this->plannerResult($scopeRelativePath, [
                $this->planPackage($alpha, true, true),
            ]));

        $packageUnseedService = Mockery::mock(V3PackageUnseedService::class);
        $packageUnseedService->shouldReceive('run')
            ->once()
            ->andReturn([
                'ownership' => [
                    'seed_run_present' => true,
                    'package_present_in_db' => true,
                ],
                'impact' => [
                    'counts' => [],
                    'warnings' => [],
                ],
                'error' => [
                    'stage' => 'preflight',
                    'message' => 'Dependency guard failed.',
                ],
            ]);

        $result = (new V3FolderDestroyService($planner, $packageUnseedService))->run('scope-target', [
            'force' => true,
        ]);

        $this->assertSame('preflight', $result['error']['stage']);
        $this->assertSame('preflight_failed', $result['error']['reason']);
        $this->assertSame(0, $result['execution']['db_phase']['started']);
        $this->assertSame(0, $result['execution']['file_phase']['started']);
        $this->assertTrue(File::exists($alpha['loader']));
        $this->assertTrue(File::isDirectory($alpha['package']));
    }

    public function test_db_phase_failure_prevents_v3_file_phase_from_starting(): void
    {
        $scopeRelativePath = 'database/seeders/V3/Tests/Unit/V3DestroyDbFail';
        $alpha = $this->createPackageFiles($scopeRelativePath . '/AlphaSeeder');
        $beta = $this->createPackageFiles($scopeRelativePath . '/BetaSeeder');

        $planner = Mockery::mock(V3FolderPlanService::class);
        $planner->shouldReceive('run')
            ->once()
            ->andReturn($this->plannerResult($scopeRelativePath, [
                $this->planPackage($beta, true, true),
                $this->planPackage($alpha, false, true),
            ]));

        $packageUnseedService = Mockery::mock(V3PackageUnseedService::class);
        $packageUnseedService->shouldReceive('run')
            ->once()
            ->with($beta['relative'], Mockery::on(fn (array $options): bool => (bool) ($options['dry_run'] ?? false)))
            ->andReturn($this->packageServiceResult(true, false, ['Question' => 1]));
        $packageUnseedService->shouldReceive('run')
            ->once()
            ->with($beta['relative'], Mockery::on(fn (array $options): bool => ! (bool) ($options['dry_run'] ?? true)))
            ->andReturn([
                'ownership' => [
                    'seed_run_present' => true,
                    'package_present_in_db' => true,
                ],
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
                    'message' => 'DB delete failed.',
                ],
            ]);

        $result = (new V3FolderDestroyService($planner, $packageUnseedService))->run('scope-target', [
            'force' => true,
        ]);

        $this->assertSame('db_phase_failed', $result['error']['reason']);
        $this->assertSame(1, $result['execution']['db_phase']['failed']);
        $this->assertSame(0, $result['execution']['file_phase']['started']);
        $this->assertTrue(File::exists($beta['loader']));
        $this->assertTrue(File::exists($alpha['loader']));
    }

    public function test_file_phase_failure_is_reported_after_successful_v3_db_phase(): void
    {
        $scopeRelativePath = 'database/seeders/V3/Tests/Unit/V3DestroyFileFail';
        $alpha = $this->createPackageFiles($scopeRelativePath . '/AlphaSeeder');
        $beta = $this->createPackageFiles($scopeRelativePath . '/BetaSeeder');
        $gamma = $this->createPackageFiles($scopeRelativePath . '/GammaSeeder');

        $planner = Mockery::mock(V3FolderPlanService::class);
        $planner->shouldReceive('run')
            ->once()
            ->andReturn($this->plannerResult($scopeRelativePath, [
                $this->planPackage($gamma, true, true),
                $this->planPackage($beta, true, true),
                $this->planPackage($alpha, false, true),
            ]));

        $packageUnseedService = Mockery::mock(V3PackageUnseedService::class);
        $packageUnseedService->shouldReceive('run')
            ->twice()
            ->with(Mockery::any(), Mockery::on(fn (array $options): bool => (bool) ($options['dry_run'] ?? false)))
            ->andReturn($this->packageServiceResult(true, false, ['Question' => 1]));
        $packageUnseedService->shouldReceive('run')
            ->twice()
            ->with(Mockery::any(), Mockery::on(fn (array $options): bool => ! (bool) ($options['dry_run'] ?? true)))
            ->andReturn($this->packageServiceResult(false, true, ['Question' => 1]));

        $service = Mockery::mock(V3FolderDestroyService::class, [$planner, $packageUnseedService])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $service->shouldReceive('executePackageDeletion')
            ->times(2)
            ->andReturnUsing(function (array $package): array {
                if (str_contains((string) ($package['relative_path'] ?? ''), 'BetaSeeder')) {
                    return [
                        'status' => 'failed',
                        'deleted_files' => ['database/seeders/V3/Tests/Unit/V3DestroyFileFail/BetaSeeder/definition.json'],
                        'deleted_dirs' => [],
                        'failed_paths' => ['database/seeders/V3/Tests/Unit/V3DestroyFileFail/BetaSeeder/BetaSeeder.php'],
                    ];
                }

                return [
                    'status' => 'ok',
                    'deleted_files' => (array) ($package['delete_set'] ?? []),
                    'deleted_dirs' => [],
                    'failed_paths' => [],
                ];
            });

        $result = $service->run('scope-target', [
            'force' => true,
        ]);

        $this->assertSame('file_phase_failed', $result['error']['reason']);
        $this->assertSame(2, $result['execution']['db_phase']['succeeded']);
        $this->assertSame(2, $result['execution']['file_phase']['completed']);
        $this->assertSame('ok', $result['execution']['file_phase']['packages'][0]['status']);
        $this->assertSame('failed', $result['execution']['file_phase']['packages'][1]['status']);
        $this->assertSame('pending', $result['execution']['file_phase']['packages'][2]['status']);
    }

    /**
     * @return array<string, mixed>
     */
    private function plannerResult(string $scopeRelativePath, array $packages): array
    {
        return [
            'scope' => [
                'input' => 'scope-target',
                'resolved_root_absolute_path' => base_path(str_replace('/', DIRECTORY_SEPARATOR, $scopeRelativePath)),
                'resolved_root_relative_path' => $scopeRelativePath,
                'single_package' => false,
                'mode' => 'destroy',
            ],
            'summary' => [
                'total_packages' => count($packages),
                'seed_candidates' => 0,
                'refresh_candidates' => 0,
                'unseed_candidates' => 0,
                'destroy_files_candidates' => 0,
                'destroy_candidates' => count(array_filter(
                    $packages,
                    fn (array $package): bool => ($package['recommended_action'] ?? null) === 'destroy'
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
     * @param  array<string, string>  $packagePaths
     * @return array<string, mixed>
     */
    private function planPackage(
        array $packagePaths,
        bool $needsUnseed,
        bool $needsFileDestroy,
        string $action = 'destroy',
        array $warnings = [],
    ): array {
        $relativePath = $packagePaths['relative'];
        $className = class_basename(str_replace('/', '\\', $relativePath));

        return [
            'relative_path' => $relativePath,
            'definition_relative_path' => $relativePath . '/definition.json',
            'resolved_seeder_class' => 'Database\\Seeders\\V3\\Tests\\Unit\\' . $className,
            'package_type' => 'v3_test',
            'package_state' => $needsUnseed ? 'seeded' : 'not_seeded',
            'recommended_action' => $action,
            'needs_unseed' => $needsUnseed,
            'needs_file_destroy' => $needsFileDestroy,
            'seed_run_present' => $needsUnseed,
            'package_present_in_db' => $needsUnseed,
            'warnings' => $warnings,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function packageServiceResult(bool $dryRun, bool $seedRunRemoved, array $counts): array
    {
        return [
            'ownership' => [
                'seed_run_present' => true,
                'package_present_in_db' => true,
            ],
            'impact' => [
                'counts' => $counts,
                'warnings' => [],
            ],
            'result' => [
                'deleted' => $counts !== [],
                'rolled_back' => $dryRun,
                'seed_run_removed' => $seedRunRemoved,
            ],
            'error' => null,
        ];
    }

    /**
     * @return array<string, string>
     */
    private function createPackageFiles(string $packageRelativePath): array
    {
        $packageAbsolutePath = base_path(str_replace('/', DIRECTORY_SEPARATOR, $packageRelativePath));
        $className = basename($packageRelativePath);
        $loaderAbsolutePath = dirname($packageAbsolutePath) . DIRECTORY_SEPARATOR . $className . '.php';
        $localizationsPath = $packageAbsolutePath . DIRECTORY_SEPARATOR . 'localizations';

        File::ensureDirectoryExists($localizationsPath);
        File::put($loaderAbsolutePath, "<?php\n");
        File::put($packageAbsolutePath . DIRECTORY_SEPARATOR . $className . '.php', "<?php\n");
        File::put($packageAbsolutePath . DIRECTORY_SEPARATOR . 'definition.json', "{}\n");
        File::put($localizationsPath . DIRECTORY_SEPARATOR . 'uk.json', "{}\n");
        File::put($localizationsPath . DIRECTORY_SEPARATOR . 'en.json', "{}\n");
        File::put($localizationsPath . DIRECTORY_SEPARATOR . 'pl.json', "{}\n");

        $this->cleanupPaths[] = $loaderAbsolutePath;
        $this->cleanupPaths[] = $packageAbsolutePath;

        return [
            'relative' => $packageRelativePath,
            'package' => $packageAbsolutePath,
            'loader' => $loaderAbsolutePath,
        ];
    }
}
