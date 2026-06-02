<?php

namespace Tests\Unit;

use App\Models\Question;
use App\Services\V3PromptGenerator\V3ChangedPackagesPlanService;
use App\Services\V3PromptGenerator\V3FolderPlanService;
use App\Services\V3PromptGenerator\V3ReleaseCheckService;
use App\Support\PackageSeed\GitPackageDiffService;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class V3ChangedPackagesPlanServiceTest extends TestCase
{
    use RebuildsComposeTestSchema;

    protected function setUp(): void
    {
        parent::setUp();

        $this->rebuildComposeTestSchema();
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_it_builds_v3_changed_plan_with_deleted_cleanup_and_upsert_present_phases(): void
    {
        $scope = 'database/seeders/V3/Tests/Unit/V3Changed';
        $deletedPath = $scope . '/GammaSeeder';
        $deletedSeederClass = 'Database\\Seeders\\V3\\Tests\\Unit\\V3Changed\\GammaSeeder';

        DB::table('seed_runs')->insert([
            'class_name' => $deletedSeederClass,
            'ran_at' => now(),
        ]);
        Question::query()->create([
            'uuid' => '11111111-1111-1111-1111-111111111111',
            'question' => 'Deleted package question',
            'difficulty' => 1,
            'level' => 'A1',
            'seeder' => $deletedSeederClass,
        ]);

        $diffService = Mockery::mock(GitPackageDiffService::class);
        $diffService->shouldReceive('collect')
            ->once()
            ->andReturn([
                'mode' => 'working_tree',
                'base' => null,
                'head' => 'HEAD',
                'historical_ref' => 'HEAD',
                'include_untracked' => true,
                'entries' => [
                    ['status' => 'A', 'old_path' => null, 'new_path' => $scope . '/AlphaSeeder/definition.json'],
                    ['status' => 'M', 'old_path' => null, 'new_path' => $scope . '/BetaSeeder/definition.json'],
                    ['status' => 'D', 'old_path' => $deletedPath . '/definition.json', 'new_path' => null],
                ],
            ]);
        $diffService->shouldReceive('showFile')
            ->once()
            ->with('HEAD', $deletedPath . '/definition.json')
            ->andReturn(json_encode([
                'seeder' => [
                    'class' => $deletedSeederClass,
                ],
                'saved_test' => [
                    'uuid' => 'gamma-test-uuid',
                    'slug' => 'gamma-test-slug',
                    'name' => 'Gamma Test',
                ],
                'questions' => [
                    ['uuid' => 'gamma-question', 'level' => 'A1'],
                ],
            ], JSON_THROW_ON_ERROR));

        $folderPlanService = Mockery::mock(V3FolderPlanService::class);
        $folderPlanService->shouldReceive('run')
            ->once()
            ->with($scope . '/AlphaSeeder', Mockery::type('array'))
            ->andReturn($this->singlePackagePlan(
                $scope . '/AlphaSeeder',
                'seed',
                'not_seeded',
                false,
                false,
                'Database\\Seeders\\V3\\Tests\\Unit\\V3Changed\\AlphaSeeder'
            ));
        $folderPlanService->shouldReceive('run')
            ->once()
            ->with($scope . '/BetaSeeder', Mockery::type('array'))
            ->andReturn($this->singlePackagePlan(
                $scope . '/BetaSeeder',
                'refresh',
                'seeded',
                true,
                true,
                'Database\\Seeders\\V3\\Tests\\Unit\\V3Changed\\BetaSeeder'
            ));

        $releaseCheckService = Mockery::mock(V3ReleaseCheckService::class);
        $releaseCheckService->shouldReceive('run')
            ->once()
            ->with($scope . '/AlphaSeeder/definition.json', 'release')
            ->andReturn($this->releaseCheckReport(2, 0, 0));
        $releaseCheckService->shouldReceive('run')
            ->once()
            ->with($scope . '/BetaSeeder/definition.json', 'release')
            ->andReturn($this->releaseCheckReport(1, 1, 0));

        $result = (new V3ChangedPackagesPlanService(
            $diffService,
            $folderPlanService,
            $releaseCheckService,
        ))->run($scope, [
            'include_untracked' => true,
            'with_release_check' => true,
        ]);

        $this->assertNull($result['error']);
        $this->assertSame(3, $result['summary']['changed_packages']);
        $this->assertSame(1, $result['summary']['seed_candidates']);
        $this->assertSame(1, $result['summary']['refresh_candidates']);
        $this->assertSame(1, $result['summary']['deleted_cleanup_candidates']);
        $this->assertSame($deletedPath, $result['packages'][0]['historical_relative_path']);
        $this->assertSame('unseed_deleted', $result['packages'][0]['recommended_action']);
        $this->assertTrue((bool) $result['packages'][0]['historical_metadata_available']);
        $this->assertSame('deleted_from_disk', $result['packages'][0]['package_state']);
        $this->assertSame($scope . '/AlphaSeeder', $result['packages'][1]['current_relative_path']);
        $this->assertSame('seed', $result['packages'][1]['recommended_action']);
        $this->assertSame('pass', $result['packages'][1]['release_check']['status']);
        $this->assertSame($scope . '/BetaSeeder', $result['packages'][2]['current_relative_path']);
        $this->assertSame('refresh', $result['packages'][2]['recommended_action']);
        $this->assertSame('warn', $result['packages'][2]['release_check']['status']);
        $this->assertSame([$deletedPath], array_column($result['phases']['cleanup_deleted'], 'historical_relative_path'));
        $this->assertSame(
            [$scope . '/AlphaSeeder', $scope . '/BetaSeeder'],
            array_column($result['phases']['upsert_present'], 'current_relative_path')
        );
    }

    public function test_deleted_v3_package_without_historical_metadata_is_blocked(): void
    {
        $scope = 'database/seeders/V3/Tests/Unit/V3ChangedBlocked';
        $deletedPath = $scope . '/GhostSeeder';

        $diffService = Mockery::mock(GitPackageDiffService::class);
        $diffService->shouldReceive('collect')
            ->once()
            ->andReturn([
                'mode' => 'working_tree',
                'base' => null,
                'head' => 'HEAD',
                'historical_ref' => 'HEAD',
                'include_untracked' => false,
                'entries' => [
                    ['status' => 'D', 'old_path' => $deletedPath . '/definition.json', 'new_path' => null],
                ],
            ]);
        $diffService->shouldReceive('showFile')
            ->once()
            ->with('HEAD', $deletedPath . '/definition.json')
            ->andReturnNull();

        $folderPlanService = Mockery::mock(V3FolderPlanService::class);
        $releaseCheckService = Mockery::mock(V3ReleaseCheckService::class);

        $result = (new V3ChangedPackagesPlanService(
            $diffService,
            $folderPlanService,
            $releaseCheckService,
        ))->run($scope);

        $this->assertNull($result['error']);
        $this->assertSame(1, $result['summary']['blocked']);
        $this->assertSame('blocked', $result['packages'][0]['recommended_action']);
        $this->assertFalse((bool) $result['packages'][0]['historical_metadata_available']);
        $this->assertSame('blocked', $result['packages'][0]['package_state']);
    }

    public function test_v3_cross_root_rename_becomes_deleted_and_added_records(): void
    {
        $scope = 'database/seeders/V3/Tests/Unit/V3ChangedRename';
        $oldPath = $scope . '/OldSeeder';
        $newPath = $scope . '/NewSeeder';

        $diffService = Mockery::mock(GitPackageDiffService::class);
        $diffService->shouldReceive('collect')
            ->once()
            ->andReturn([
                'mode' => 'refs',
                'base' => 'HEAD~1',
                'head' => 'HEAD',
                'historical_ref' => 'HEAD~1',
                'include_untracked' => false,
                'entries' => [
                    ['status' => 'R', 'old_path' => $oldPath . '/definition.json', 'new_path' => $newPath . '/definition.json'],
                ],
            ]);
        $diffService->shouldReceive('showFile')
            ->once()
            ->with('HEAD~1', $oldPath . '/definition.json')
            ->andReturn(json_encode([
                'saved_test' => [
                    'uuid' => 'old-test-uuid',
                    'slug' => 'old-test-slug',
                    'name' => 'Old Test',
                ],
                'questions' => [],
            ], JSON_THROW_ON_ERROR));

        $folderPlanService = Mockery::mock(V3FolderPlanService::class);
        $folderPlanService->shouldReceive('run')
            ->once()
            ->with($newPath, Mockery::type('array'))
            ->andReturn($this->singlePackagePlan(
                $newPath,
                'seed',
                'not_seeded',
                false,
                false,
                'Database\\Seeders\\V3\\Tests\\Unit\\V3ChangedRename\\NewSeeder'
            ));

        $releaseCheckService = Mockery::mock(V3ReleaseCheckService::class);

        $result = (new V3ChangedPackagesPlanService(
            $diffService,
            $folderPlanService,
            $releaseCheckService,
        ))->run($scope, [
            'base' => 'HEAD~1',
            'head' => 'HEAD',
        ]);

        $this->assertNull($result['error']);
        $this->assertCount(2, $result['packages']);
        $this->assertSame($newPath, $result['packages'][0]['current_relative_path']);
        $this->assertSame('seed', $result['packages'][0]['recommended_action']);
        $this->assertSame($oldPath, $result['packages'][1]['historical_relative_path']);
        $this->assertSame('skip', $result['packages'][1]['recommended_action']);
        $this->assertSame('deleted', $result['packages'][1]['change_type']);
    }

    /**
     * @return array<string, mixed>
     */
    private function singlePackagePlan(
        string $relativePath,
        string $action,
        string $state,
        bool $seedRunPresent,
        bool $packagePresentInDb,
        string $resolvedSeederClass,
    ): array {
        return [
            'scope' => [
                'input' => $relativePath,
                'resolved_root_absolute_path' => base_path(str_replace('/', DIRECTORY_SEPARATOR, $relativePath)),
                'resolved_root_relative_path' => $relativePath,
                'single_package' => true,
                'mode' => 'sync',
            ],
            'summary' => [
                'total_packages' => 1,
                'seed_candidates' => $action === 'seed' ? 1 : 0,
                'refresh_candidates' => $action === 'refresh' ? 1 : 0,
                'unseed_candidates' => 0,
                'destroy_files_candidates' => 0,
                'destroy_candidates' => 0,
                'skipped' => $action === 'skip' ? 1 : 0,
                'blocked' => $action === 'blocked' ? 1 : 0,
                'warnings' => 0,
            ],
            'packages' => [[
                'relative_path' => $relativePath,
                'definition_relative_path' => $relativePath . '/definition.json',
                'resolved_seeder_class' => $resolvedSeederClass,
                'package_type' => 'v3_test',
                'seed_run_present' => $seedRunPresent,
                'package_present_in_db' => $packagePresentInDb,
                'package_state' => $state,
                'recommended_action' => $action,
                'release_check' => [
                    'executed' => false,
                    'profile' => null,
                    'status' => 'skipped',
                    'summary' => ['pass' => 0, 'warn' => 0, 'fail' => 0],
                    'message' => null,
                ],
                'next_step_command' => $action === 'seed'
                    ? 'php artisan v3:seed-package ' . $relativePath . '/definition.json'
                    : ($action === 'refresh'
                        ? 'php artisan v3:refresh-package ' . $relativePath . '/definition.json --force'
                        : null),
                'warnings' => [],
            ]],
            'error' => null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function releaseCheckReport(int $pass, int $warn, int $fail): array
    {
        return [
            'summary' => [
                'check_counts' => [
                    'pass' => $pass,
                    'warn' => $warn,
                    'fail' => $fail,
                ],
            ],
        ];
    }
}
