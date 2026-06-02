<?php

namespace Tests\Unit;

use App\Models\Page;
use App\Models\PageCategory;
use App\Services\PageV3PromptGenerator\PageV3ChangedPackagesPlanService;
use App\Services\PageV3PromptGenerator\PageV3FolderPlanService;
use App\Services\PageV3PromptGenerator\PageV3ReleaseCheckService;
use App\Support\Database\JsonPageDefinitionIndex;
use App\Support\PackageSeed\GitPackageDiffService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class PageV3ChangedPackagesPlanServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->rebuildMinimalSchema();
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_it_orders_page_v3_deleted_cleanup_before_category_and_current_upsert_category_before_page(): void
    {
        $scope = 'database/seeders/Page_V3/Tests/Unit/PageChanged';
        $deletedPagePath = $scope . '/DeletedPageSeeder';
        $deletedCategoryPath = $scope . '/DeletedCategorySeeder';
        $currentCategoryPath = $scope . '/CurrentCategorySeeder';
        $currentPagePath = $scope . '/CurrentPageSeeder';
        $deletedPageSeederClass = 'Database\\Seeders\\Page_V3\\Tests\\Unit\\PageChanged\\DeletedPageSeeder';
        $deletedCategorySeederClass = 'Database\\Seeders\\Page_V3\\Tests\\Unit\\PageChanged\\DeletedCategorySeeder';

        DB::table('seed_runs')->insert([
            ['class_name' => $deletedPageSeederClass, 'ran_at' => now()],
            ['class_name' => $deletedCategorySeederClass, 'ran_at' => now()],
        ]);
        Page::query()->create([
            'slug' => 'deleted-page',
            'title' => 'Deleted Page',
            'seeder' => $deletedPageSeederClass,
        ]);
        PageCategory::query()->create([
            'title' => 'Deleted Category',
            'slug' => 'deleted-category',
            'seeder' => $deletedCategorySeederClass,
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
                    ['status' => 'D', 'old_path' => $deletedCategoryPath . '/definition.json', 'new_path' => null],
                    ['status' => 'D', 'old_path' => $deletedPagePath . '/definition.json', 'new_path' => null],
                    ['status' => 'A', 'old_path' => null, 'new_path' => $currentPagePath . '/definition.json'],
                    ['status' => 'A', 'old_path' => null, 'new_path' => $currentCategoryPath . '/definition.json'],
                ],
            ]);
        $diffService->shouldReceive('showFile')
            ->once()
            ->with('HEAD', $deletedCategoryPath . '/definition.json')
            ->andReturn(json_encode([
                'category' => [
                    'slug' => 'deleted-category',
                    'title' => 'Deleted Category',
                ],
                'seeder' => [
                    'class' => $deletedCategorySeederClass,
                ],
            ], JSON_THROW_ON_ERROR));
        $diffService->shouldReceive('showFile')
            ->once()
            ->with('HEAD', $deletedPagePath . '/definition.json')
            ->andReturn(json_encode([
                'slug' => 'deleted-page',
                'page' => [
                    'title' => 'Deleted Page',
                    'category' => [
                        'slug' => 'deleted-category',
                    ],
                ],
                'seeder' => [
                    'class' => $deletedPageSeederClass,
                ],
            ], JSON_THROW_ON_ERROR));

        $folderPlanService = Mockery::mock(PageV3FolderPlanService::class);
        $folderPlanService->shouldReceive('run')
            ->once()
            ->with($currentPagePath, Mockery::type('array'))
            ->andReturn($this->singlePackagePlan(
                $currentPagePath,
                'page',
                'seed',
                'not_seeded',
                false,
                false,
                'Database\\Seeders\\Page_V3\\Tests\\Unit\\PageChanged\\CurrentPageSeeder'
            ));
        $folderPlanService->shouldReceive('run')
            ->once()
            ->with($currentCategoryPath, Mockery::type('array'))
            ->andReturn($this->singlePackagePlan(
                $currentCategoryPath,
                'category',
                'seed',
                'not_seeded',
                false,
                false,
                'Database\\Seeders\\Page_V3\\Tests\\Unit\\PageChanged\\CurrentCategorySeeder'
            ));

        $releaseCheckService = Mockery::mock(PageV3ReleaseCheckService::class);

        $result = (new PageV3ChangedPackagesPlanService(
            $diffService,
            $folderPlanService,
            $releaseCheckService,
            app(\App\Modules\SeedRunsV2\Services\SeedRunsService::class),
            app(JsonPageDefinitionIndex::class),
        ))->run($scope, [
            'include_untracked' => true,
        ]);

        $this->assertNull($result['error']);
        $this->assertSame(4, $result['summary']['changed_packages']);
        $this->assertSame(
            [$deletedPagePath, $deletedCategoryPath],
            array_column($result['phases']['cleanup_deleted'], 'historical_relative_path')
        );
        $this->assertSame(
            [$currentCategoryPath, $currentPagePath],
            array_column($result['phases']['upsert_present'], 'current_relative_path')
        );
        $this->assertSame('page', $result['packages'][0]['package_type']);
        $this->assertSame('unseed_deleted', $result['packages'][0]['recommended_action']);
        $this->assertSame('category', $result['packages'][1]['package_type']);
        $this->assertSame('unseed_deleted', $result['packages'][1]['recommended_action']);
        $this->assertSame('category', $result['packages'][2]['package_type']);
        $this->assertSame('seed', $result['packages'][2]['recommended_action']);
        $this->assertSame('page', $result['packages'][3]['package_type']);
        $this->assertSame('seed', $result['packages'][3]['recommended_action']);
    }

    public function test_deleted_page_v3_package_without_historical_metadata_is_blocked(): void
    {
        $scope = 'database/seeders/Page_V3/Tests/Unit/PageChangedBlocked';
        $deletedPath = $scope . '/GhostPageSeeder';

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

        $folderPlanService = Mockery::mock(PageV3FolderPlanService::class);
        $releaseCheckService = Mockery::mock(PageV3ReleaseCheckService::class);

        $result = (new PageV3ChangedPackagesPlanService(
            $diffService,
            $folderPlanService,
            $releaseCheckService,
            app(\App\Modules\SeedRunsV2\Services\SeedRunsService::class),
            app(JsonPageDefinitionIndex::class),
        ))->run($scope);

        $this->assertNull($result['error']);
        $this->assertSame(1, $result['summary']['blocked']);
        $this->assertSame('blocked', $result['packages'][0]['recommended_action']);
        $this->assertFalse((bool) $result['packages'][0]['historical_metadata_available']);
        $this->assertSame('blocked', $result['packages'][0]['package_state']);
    }

    private function rebuildMinimalSchema(): void
    {
        Schema::disableForeignKeyConstraints();

        foreach ([
            'seed_runs',
            'tag_text_block',
            'page_category_tag',
            'page_tag',
            'text_blocks',
            'pages',
            'page_categories',
            'tags',
            'questions',
        ] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::enableForeignKeyConstraints();

        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('category')->nullable();
            $table->timestamps();
        });

        Schema::create('page_categories', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('language')->nullable();
            $table->string('type')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('seeder')->nullable();
            $table->timestamps();
        });

        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug');
            $table->string('title');
            $table->text('text')->nullable();
            $table->string('type')->nullable();
            $table->string('seeder')->nullable();
            $table->foreignId('page_category_id')->nullable();
            $table->timestamps();
            $table->index(['slug', 'type']);
            $table->index('seeder');
        });

        Schema::create('text_blocks', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->nullable()->unique();
            $table->foreignId('page_id')->nullable();
            $table->foreignId('page_category_id')->nullable();
            $table->string('locale', 8)->nullable();
            $table->string('type')->nullable();
            $table->string('column')->nullable();
            $table->string('heading')->nullable();
            $table->string('css_class')->nullable();
            $table->integer('sort_order')->default(0);
            $table->text('body')->nullable();
            $table->string('level')->nullable();
            $table->string('seeder')->nullable();
            $table->timestamps();
            $table->index('seeder');
        });

        Schema::create('page_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tag_id')->constrained('tags')->cascadeOnDelete();
            $table->foreignId('page_id')->constrained('pages')->cascadeOnDelete();
            $table->unique(['tag_id', 'page_id']);
        });

        Schema::create('page_category_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tag_id')->constrained('tags')->cascadeOnDelete();
            $table->foreignId('page_category_id')->constrained('page_categories')->cascadeOnDelete();
            $table->unique(['tag_id', 'page_category_id']);
        });

        Schema::create('tag_text_block', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tag_id')->constrained('tags')->cascadeOnDelete();
            $table->foreignId('text_block_id')->constrained('text_blocks')->cascadeOnDelete();
            $table->unique(['tag_id', 'text_block_id']);
        });

        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->text('question');
            $table->unsignedTinyInteger('difficulty')->default(1);
            $table->string('level', 8)->nullable();
            $table->string('theory_text_block_uuid', 36)->nullable();
            $table->string('seeder')->nullable();
            $table->timestamps();
        });

        Schema::create('seed_runs', function (Blueprint $table) {
            $table->id();
            $table->string('class_name')->unique();
            $table->timestamp('ran_at')->nullable();
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function singlePackagePlan(
        string $relativePath,
        string $packageType,
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
                'package_type' => $packageType,
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
                    ? 'php artisan page-v3:seed-package ' . $relativePath . '/definition.json'
                    : ($action === 'refresh'
                        ? 'php artisan page-v3:refresh-package ' . $relativePath . '/definition.json --force'
                        : null),
                'warnings' => [],
            ]],
            'error' => null,
        ];
    }
}
