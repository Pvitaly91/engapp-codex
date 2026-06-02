<?php

namespace Tests\Unit;

use App\Services\PageV3PromptGenerator\PageV3FolderDestroyFilesService;
use App\Services\PageV3PromptGenerator\PageV3FolderPlanService;
use Illuminate\Support\Facades\File;
use Mockery;
use Tests\TestCase;

class PageV3FolderDestroyFilesServiceTest extends TestCase
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

    public function test_it_preflights_and_deletes_page_before_category_files(): void
    {
        $scopeRelativePath = 'database/seeders/Page_V3/Tests/Unit/PageDestroyFilesScope';
        $page = $this->createPackageFiles($scopeRelativePath . '/PageSeeder');
        $category = $this->createPackageFiles($scopeRelativePath . '/CategorySeeder');

        $planner = Mockery::mock(PageV3FolderPlanService::class);
        $planner->shouldReceive('run')
            ->once()
            ->andReturn($this->plannerResult($scopeRelativePath, [
                $this->planPackage($page, 'page'),
                $this->planPackage($category, 'category'),
            ]));

        $result = (new PageV3FolderDestroyFilesService($planner))->run('scope-target', [
            'force' => true,
            'remove_empty_dirs' => true,
        ]);

        $this->assertNull($result['error']);
        $this->assertSame(2, $result['preflight']['summary']['ok']);
        $this->assertSame(2, $result['execution']['succeeded']);
        $this->assertSame('ok', $result['execution']['packages'][0]['status']);
        $this->assertSame('ok', $result['execution']['packages'][1]['status']);
        $this->assertFalse(File::exists($page['loader']));
        $this->assertFalse(File::isDirectory($page['package']));
        $this->assertFalse(File::exists($category['loader']));
        $this->assertFalse(File::isDirectory($category['package']));
    }

    public function test_blocked_category_prevents_any_live_page_v3_file_destroy(): void
    {
        $scopeRelativePath = 'database/seeders/Page_V3/Tests/Unit/PageDestroyFilesBlocked';
        $page = $this->createPackageFiles($scopeRelativePath . '/PageSeeder');
        $category = $this->createPackageFiles($scopeRelativePath . '/CategorySeeder');

        $planner = Mockery::mock(PageV3FolderPlanService::class);
        $planner->shouldReceive('run')
            ->once()
            ->andReturn($this->plannerResult($scopeRelativePath, [
                $this->planPackage($page, 'page'),
                $this->planPackage($category, 'category', 'blocked', 'not_seeded', [
                    'warnings' => ['Sibling Page_V3 packages outside the current scope still reference category slug [blocked].'],
                ]),
            ]));

        $result = (new PageV3FolderDestroyFilesService($planner))->run('scope-target', [
            'force' => true,
        ]);

        $this->assertSame('planning', $result['error']['stage']);
        $this->assertSame('blocked_packages', $result['error']['reason']);
        $this->assertSame(1, $result['preflight']['summary']['candidates']);
        $this->assertSame(0, $result['execution']['started']);
        $this->assertTrue(File::exists($page['loader']));
        $this->assertTrue(File::exists($category['loader']));
    }

    public function test_live_execution_stops_after_first_page_v3_package_failure_and_marks_remaining_pending(): void
    {
        $scopeRelativePath = 'database/seeders/Page_V3/Tests/Unit/PageDestroyFilesFailFast';
        $page = $this->createPackageFiles($scopeRelativePath . '/PageSeeder');
        $category = $this->createPackageFiles($scopeRelativePath . '/CategorySeeder');
        $late = $this->createPackageFiles($scopeRelativePath . '/LateSeeder');

        $planner = Mockery::mock(PageV3FolderPlanService::class);
        $planner->shouldReceive('run')
            ->once()
            ->andReturn($this->plannerResult($scopeRelativePath, [
                $this->planPackage($page, 'page'),
                $this->planPackage($category, 'category'),
                $this->planPackage($late, 'page'),
            ]));

        $service = Mockery::mock(PageV3FolderDestroyFilesService::class, [$planner])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $service->shouldReceive('executePackageDeletion')
            ->twice()
            ->andReturnUsing(function (array $package, array $scope = [], array $options = []): array {
                if (str_contains((string) ($package['relative_path'] ?? ''), 'CategorySeeder')) {
                    return [
                        'status' => 'failed',
                        'deleted_files' => ['database/seeders/Page_V3/Tests/Unit/PageDestroyFilesFailFast/CategorySeeder/definition.json'],
                        'deleted_dirs' => [],
                        'failed_paths' => ['database/seeders/Page_V3/Tests/Unit/PageDestroyFilesFailFast/CategorySeeder/CategorySeeder.php'],
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

        $this->assertSame('execution', $result['error']['stage']);
        $this->assertSame('package_failed', $result['error']['reason']);
        $this->assertSame(2, $result['execution']['completed']);
        $this->assertSame('ok', $result['execution']['packages'][0]['status']);
        $this->assertSame('failed', $result['execution']['packages'][1]['status']);
        $this->assertSame('pending', $result['execution']['packages'][2]['status']);
        $this->assertSame(
            'database/seeders/Page_V3/Tests/Unit/PageDestroyFilesFailFast/CategorySeeder',
            $result['execution']['stopped_on']
        );
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
                'mode' => 'destroy-files',
            ],
            'summary' => [
                'total_packages' => count($packages),
                'seed_candidates' => 0,
                'refresh_candidates' => 0,
                'unseed_candidates' => 0,
                'destroy_files_candidates' => count(array_filter(
                    $packages,
                    fn (array $package): bool => ($package['recommended_action'] ?? null) === 'destroy_files'
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
     * @param  array<string, mixed>  $packagePaths
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function planPackage(
        array $packagePaths,
        string $packageType,
        string $action = 'destroy_files',
        string $state = 'not_seeded',
        array $overrides = [],
    ): array {
        $relativePath = $packagePaths['relative'];
        $className = class_basename(str_replace('/', '\\', $relativePath));

        return array_merge([
            'relative_path' => $relativePath,
            'definition_relative_path' => $relativePath . '/definition.json',
            'resolved_seeder_class' => 'Database\\Seeders\\Page_V3\\Tests\\Unit\\' . $className,
            'package_type' => $packageType,
            'package_state' => $state,
            'recommended_action' => $action,
            'warnings' => [],
        ], $overrides);
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
