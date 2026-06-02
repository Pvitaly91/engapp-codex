<?php

namespace Tests\Unit;

use App\Services\V3PromptGenerator\V3FolderDestroyFilesService;
use App\Services\V3PromptGenerator\V3FolderPlanService;
use Illuminate\Support\Facades\File;
use Mockery;
use Tests\TestCase;

class V3FolderDestroyFilesServiceTest extends TestCase
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

    public function test_it_preflights_and_deletes_planner_ordered_v3_packages(): void
    {
        $scopeRelativePath = 'database/seeders/V3/Tests/Unit/V3DestroyFilesScope';
        $beta = $this->createPackageFiles($scopeRelativePath . '/BetaSeeder');
        $alpha = $this->createPackageFiles($scopeRelativePath . '/AlphaSeeder');
        $gamma = $this->createPackageFiles($scopeRelativePath . '/GammaSeeder');

        $planner = Mockery::mock(V3FolderPlanService::class);
        $planner->shouldReceive('run')
            ->once()
            ->with('scope-target', Mockery::on(
                fn (array $options): bool => ($options['mode'] ?? null) === 'destroy-files' && ($options['strict'] ?? null) === false
            ))
            ->andReturn($this->plannerResult($scopeRelativePath, [
                $this->planPackage($beta),
                $this->planPackage($alpha),
                $this->planPackage($gamma, 'skip'),
            ]));

        $result = (new V3FolderDestroyFilesService($planner))->run('scope-target', [
            'force' => true,
            'remove_empty_dirs' => true,
        ]);

        $this->assertNull($result['error']);
        $this->assertSame(2, $result['preflight']['summary']['candidates']);
        $this->assertSame(2, $result['execution']['started']);
        $this->assertSame(2, $result['execution']['succeeded']);
        $this->assertSame('ok', $result['execution']['packages'][0]['status']);
        $this->assertSame('ok', $result['execution']['packages'][1]['status']);
        $this->assertSame('skipped', $result['execution']['packages'][2]['status']);
        $this->assertFalse(File::exists($beta['loader']));
        $this->assertFalse(File::isDirectory($beta['package']));
        $this->assertFalse(File::exists($alpha['loader']));
        $this->assertFalse(File::isDirectory($alpha['package']));
        $this->assertTrue(File::exists($gamma['loader']));
        $this->assertTrue(File::isDirectory($gamma['package']));
    }

    public function test_preflight_failure_blocks_all_live_v3_file_deletes(): void
    {
        $scopeRelativePath = 'database/seeders/V3/Tests/Unit/V3DestroyFilesPreflight';
        $alpha = $this->createPackageFiles($scopeRelativePath . '/AlphaSeeder');

        $planner = Mockery::mock(V3FolderPlanService::class);
        $planner->shouldReceive('run')
            ->once()
            ->andReturn($this->plannerResult($scopeRelativePath, [
                $this->planPackage($alpha),
                $this->planPackage($alpha),
            ]));

        $result = (new V3FolderDestroyFilesService($planner))->run('scope-target', [
            'force' => true,
        ]);

        $this->assertSame('preflight', $result['error']['stage']);
        $this->assertSame('preflight_failed', $result['error']['reason']);
        $this->assertSame(0, $result['execution']['started']);
        $this->assertSame(2, $result['preflight']['summary']['fail']);
        $this->assertTrue(File::exists($alpha['loader']));
        $this->assertTrue(File::isDirectory($alpha['package']));
    }

    public function test_live_execution_stops_after_first_package_failure_and_marks_remaining_pending(): void
    {
        $scopeRelativePath = 'database/seeders/V3/Tests/Unit/V3DestroyFilesFailFast';
        $alpha = $this->createPackageFiles($scopeRelativePath . '/AlphaSeeder');
        $beta = $this->createPackageFiles($scopeRelativePath . '/BetaSeeder');
        $gamma = $this->createPackageFiles($scopeRelativePath . '/GammaSeeder');

        $planner = Mockery::mock(V3FolderPlanService::class);
        $planner->shouldReceive('run')
            ->once()
            ->andReturn($this->plannerResult($scopeRelativePath, [
                $this->planPackage($alpha),
                $this->planPackage($beta),
                $this->planPackage($gamma),
            ]));

        $service = Mockery::mock(V3FolderDestroyFilesService::class, [$planner])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $service->shouldReceive('executePackageDeletion')
            ->twice()
            ->andReturnUsing(function (array $package, array $scope = [], array $options = []): array {
                if (str_contains((string) ($package['relative_path'] ?? ''), 'BetaSeeder')) {
                    return [
                        'status' => 'failed',
                        'deleted_files' => ['database/seeders/V3/Tests/Unit/V3DestroyFilesFailFast/BetaSeeder/definition.json'],
                        'deleted_dirs' => [],
                        'failed_paths' => ['database/seeders/V3/Tests/Unit/V3DestroyFilesFailFast/BetaSeeder/BetaSeeder.php'],
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
            'database/seeders/V3/Tests/Unit/V3DestroyFilesFailFast/BetaSeeder',
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
     * @return array<string, mixed>
     */
    private function planPackage(array $packagePaths, string $action = 'destroy_files'): array
    {
        $relativePath = $packagePaths['relative'];
        $className = class_basename(str_replace('/', '\\', $relativePath));

        return [
            'relative_path' => $relativePath,
            'definition_relative_path' => $relativePath . '/definition.json',
            'resolved_seeder_class' => 'Database\\Seeders\\V3\\Tests\\Unit\\' . $className,
            'package_type' => 'v3_test',
            'package_state' => $action === 'skip' ? 'not_seeded' : 'not_seeded',
            'recommended_action' => $action,
            'warnings' => [],
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
