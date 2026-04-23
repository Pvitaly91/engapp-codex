<?php

namespace Tests\Unit;

use App\Services\PageV3PromptGenerator\PageV3FolderDestroyService;
use App\Services\PageV3PromptGenerator\PageV3FolderPlanService;
use App\Services\PageV3PromptGenerator\PageV3PackageUnseedService;
use Illuminate\Support\Facades\File;
use Mockery;
use Tests\TestCase;

class PageV3FolderDestroyServiceTest extends TestCase
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

    public function test_it_preflights_and_executes_page_before_category_combined_destroy(): void
    {
        $scopeRelativePath = 'database/seeders/Page_V3/Tests/Unit/PageDestroyScope';
        $page = $this->createPackageFiles($scopeRelativePath . '/PageSeeder');
        $category = $this->createPackageFiles($scopeRelativePath . '/CategorySeeder');

        $planner = Mockery::mock(PageV3FolderPlanService::class);
        $planner->shouldReceive('run')
            ->once()
            ->andReturn($this->plannerResult($scopeRelativePath, [
                $this->planPackage($page, 'page', false, true),
                $this->planPackage($category, 'category', true, true),
            ]));

        $packageUnseedService = Mockery::mock(PageV3PackageUnseedService::class);
        $packageUnseedService->shouldReceive('run')
            ->once()
            ->with($category['relative'], Mockery::on(fn (array $options): bool => (bool) ($options['dry_run'] ?? false)))
            ->andReturn($this->packageServiceResult(true, false, ['PageCategory' => 1]));
        $packageUnseedService->shouldReceive('run')
            ->once()
            ->with($category['relative'], Mockery::on(fn (array $options): bool => ! (bool) ($options['dry_run'] ?? true)))
            ->andReturn($this->packageServiceResult(false, true, ['PageCategory' => 1]));

        $result = (new PageV3FolderDestroyService($planner, $packageUnseedService))->run('scope-target', [
            'force' => true,
            'remove_empty_dirs' => true,
        ]);

        $this->assertNull($result['error']);
        $this->assertSame(2, $result['plan']['summary']['destroy_candidates']);
        $this->assertSame(1, $result['execution']['db_phase']['succeeded']);
        $this->assertSame(2, $result['execution']['file_phase']['succeeded']);
        $this->assertFalse(File::exists($page['loader']));
        $this->assertFalse(File::isDirectory($page['package']));
        $this->assertFalse(File::exists($category['loader']));
        $this->assertFalse(File::isDirectory($category['package']));
    }

    public function test_blocked_page_v3_destroy_preflight_prevents_all_live_writes(): void
    {
        $scopeRelativePath = 'database/seeders/Page_V3/Tests/Unit/PageDestroyBlocked';
        $page = $this->createPackageFiles($scopeRelativePath . '/PageSeeder');
        $category = $this->createPackageFiles($scopeRelativePath . '/CategorySeeder');

        $planner = Mockery::mock(PageV3FolderPlanService::class);
        $planner->shouldReceive('run')
            ->once()
            ->andReturn($this->plannerResult($scopeRelativePath, [
                $this->planPackage($page, 'page', false, true),
                $this->planPackage($category, 'category', true, true, 'blocked', ['Sibling page packages remain out of scope.']),
            ]));

        $packageUnseedService = Mockery::mock(PageV3PackageUnseedService::class);

        $result = (new PageV3FolderDestroyService($planner, $packageUnseedService))->run('scope-target', [
            'force' => true,
        ]);

        $this->assertSame('blocked_packages', $result['error']['reason']);
        $this->assertSame(0, $result['execution']['db_phase']['started']);
        $this->assertSame(0, $result['execution']['file_phase']['started']);
        $this->assertTrue(File::exists($page['loader']));
        $this->assertTrue(File::exists($category['loader']));
    }

    public function test_db_phase_failure_prevents_page_v3_file_phase_from_starting(): void
    {
        $scopeRelativePath = 'database/seeders/Page_V3/Tests/Unit/PageDestroyDbFail';
        $page = $this->createPackageFiles($scopeRelativePath . '/PageSeeder');
        $category = $this->createPackageFiles($scopeRelativePath . '/CategorySeeder');

        $planner = Mockery::mock(PageV3FolderPlanService::class);
        $planner->shouldReceive('run')
            ->once()
            ->andReturn($this->plannerResult($scopeRelativePath, [
                $this->planPackage($page, 'page', false, true),
                $this->planPackage($category, 'category', true, true),
            ]));

        $packageUnseedService = Mockery::mock(PageV3PackageUnseedService::class);
        $packageUnseedService->shouldReceive('run')
            ->once()
            ->with($category['relative'], Mockery::on(fn (array $options): bool => (bool) ($options['dry_run'] ?? false)))
            ->andReturn($this->packageServiceResult(true, false, ['PageCategory' => 1]));
        $packageUnseedService->shouldReceive('run')
            ->once()
            ->with($category['relative'], Mockery::on(fn (array $options): bool => ! (bool) ($options['dry_run'] ?? true)))
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
                    'message' => 'Page dependency guard failed.',
                ],
            ]);

        $result = (new PageV3FolderDestroyService($planner, $packageUnseedService))->run('scope-target', [
            'force' => true,
        ]);

        $this->assertSame('db_phase_failed', $result['error']['reason']);
        $this->assertSame(0, $result['execution']['file_phase']['started']);
        $this->assertTrue(File::exists($page['loader']));
        $this->assertTrue(File::exists($category['loader']));
    }

    public function test_page_v3_file_phase_failure_is_reported_after_successful_db_phase(): void
    {
        $scopeRelativePath = 'database/seeders/Page_V3/Tests/Unit/PageDestroyFileFail';
        $page = $this->createPackageFiles($scopeRelativePath . '/PageSeeder');
        $category = $this->createPackageFiles($scopeRelativePath . '/CategorySeeder');
        $late = $this->createPackageFiles($scopeRelativePath . '/LateSeeder');

        $planner = Mockery::mock(PageV3FolderPlanService::class);
        $planner->shouldReceive('run')
            ->once()
            ->andReturn($this->plannerResult($scopeRelativePath, [
                $this->planPackage($page, 'page', false, true),
                $this->planPackage($category, 'category', true, true),
                $this->planPackage($late, 'page', false, true),
            ]));

        $packageUnseedService = Mockery::mock(PageV3PackageUnseedService::class);
        $packageUnseedService->shouldReceive('run')
            ->once()
            ->with($category['relative'], Mockery::on(fn (array $options): bool => (bool) ($options['dry_run'] ?? false)))
            ->andReturn($this->packageServiceResult(true, false, ['PageCategory' => 1]));
        $packageUnseedService->shouldReceive('run')
            ->once()
            ->with($category['relative'], Mockery::on(fn (array $options): bool => ! (bool) ($options['dry_run'] ?? true)))
            ->andReturn($this->packageServiceResult(false, true, ['PageCategory' => 1]));

        $service = Mockery::mock(PageV3FolderDestroyService::class, [$planner, $packageUnseedService])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $service->shouldReceive('executePackageDeletion')
            ->twice()
            ->andReturnUsing(function (array $package): array {
                if (str_contains((string) ($package['relative_path'] ?? ''), 'CategorySeeder')) {
                    return [
                        'status' => 'failed',
                        'deleted_files' => ['database/seeders/Page_V3/Tests/Unit/PageDestroyFileFail/CategorySeeder/definition.json'],
                        'deleted_dirs' => [],
                        'failed_paths' => ['database/seeders/Page_V3/Tests/Unit/PageDestroyFileFail/CategorySeeder/CategorySeeder.php'],
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
        $this->assertSame(1, $result['execution']['db_phase']['succeeded']);
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
        string $packageType,
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
            'resolved_seeder_class' => 'Database\\Seeders\\Page_V3\\Tests\\Unit\\' . $className,
            'package_type' => $packageType,
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
