<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\PageCategory;
use App\Models\TextBlock;
use App\Services\PageV3PromptGenerator\PageV3BlueprintService;
use App\Services\PageV3PromptGenerator\PageV3SkeletonWriterService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PageV3RefreshPackageCommandTest extends TestCase
{
    /**
     * @var array<int, string>
     */
    private array $cleanupPaths = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->rebuildMinimalSchema();
    }

    protected function tearDown(): void
    {
        foreach (array_reverse($this->cleanupPaths) as $path) {
            if (File::isDirectory($path)) {
                File::deleteDirectory($path);

                continue;
            }

            File::delete($path);
        }

        parent::tearDown();
    }

    public function test_dry_run_refresh_rolls_back_seeded_page_package_and_writes_report(): void
    {
        Storage::fake('local');

        $generated = $this->writeGenerated(
            topic: 'Feature Page Refresh Dry Run',
            categoryMode: 'existing',
            categoryContext: [
                'title' => 'Feature Page Refresh Category',
                'slug' => 'feature-page-refresh-category',
                'namespace' => 'Tests\\CodexFeature\\PageRefreshDryRun',
                'seeder_relative_path' => 'database/seeders/Page_V3/Tests/CodexFeature/PageRefreshDryRun/FeaturePageRefreshCategorySeeder.php',
            ],
        );
        $this->makeDefinitionSeedable($generated);
        $this->makeLocalizationSeedable($generated);
        $this->seedPackageLive($generated);

        $expectedPageCount = Page::query()->count();
        $expectedCategoryCount = PageCategory::query()->count();
        $expectedBlockCount = TextBlock::query()->count();

        $exitCode = Artisan::call('page-v3:refresh-package', [
            'target' => $generated['preview']['page_package_absolute_path'],
            '--dry-run' => true,
            '--check-profile' => 'scaffold',
            '--write-report' => true,
            '--json' => true,
        ]);

        $payload = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);
        $reportFiles = Storage::disk('local')->allFiles('package-refresh-reports/page-v3');

        $this->assertSame(0, $exitCode);
        $this->assertTrue((bool) $payload['mode']['dry_run']);
        $this->assertTrue((bool) $payload['preflight']['executed']);
        $this->assertSame('admin_refresh', $payload['phases']['refresh_strategy']);
        $this->assertTrue((bool) $payload['result']['refreshed']);
        $this->assertTrue((bool) $payload['result']['seeded_after']);
        $this->assertTrue((bool) $payload['phases']['rolled_back']);
        $this->assertFalse((bool) $payload['result']['seed_run_logged']);
        $this->assertCount(1, $reportFiles);
        $this->assertSame($expectedPageCount, Page::query()->count());
        $this->assertSame($expectedCategoryCount, PageCategory::query()->count());
        $this->assertSame($expectedBlockCount, TextBlock::query()->count());
        $this->assertSame(1, DB::table('seed_runs')->count());
    }

    public function test_live_refresh_requires_force_and_then_falls_back_to_seed_only_when_package_is_absent(): void
    {
        $generated = $this->writeGenerated(
            topic: 'Feature Page Refresh Seed Fallback',
            categoryMode: 'existing',
            categoryContext: [
                'title' => 'Feature Page Refresh Fallback Category',
                'slug' => 'feature-page-refresh-fallback-category',
                'namespace' => 'Tests\\CodexFeature\\PageRefreshSeedFallback',
                'seeder_relative_path' => 'database/seeders/Page_V3/Tests/CodexFeature/PageRefreshSeedFallback/FeaturePageRefreshSeedFallbackCategorySeeder.php',
            ],
        );
        $this->makeDefinitionSeedable($generated);
        $this->makeLocalizationSeedable($generated);

        $blockedExitCode = Artisan::call('page-v3:refresh-package', [
            'target' => $generated['preview']['page_package_relative_path'],
            '--skip-release-check' => true,
            '--json' => true,
        ]);
        $blockedPayload = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(1, $blockedExitCode);
        $this->assertSame('force_required', $blockedPayload['error']['reason']);
        $this->assertSame(0, Page::query()->count());
        $this->assertSame(0, DB::table('seed_runs')->count());

        $liveExitCode = Artisan::call('page-v3:refresh-package', [
            'target' => $generated['preview']['page_seeder_relative_path'],
            '--skip-release-check' => true,
            '--force' => true,
            '--json' => true,
        ]);
        $livePayload = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(0, $liveExitCode);
        $this->assertSame('seed_only_fallback', $livePayload['phases']['refresh_strategy']);
        $this->assertFalse((bool) $livePayload['ownership']['seed_run_present']);
        $this->assertFalse((bool) $livePayload['ownership']['package_present_in_db']);
        $this->assertNotEmpty($livePayload['warnings']);
        $this->assertTrue((bool) $livePayload['result']['refreshed']);
        $this->assertTrue((bool) $livePayload['result']['seeded_after']);
        $this->assertFalse((bool) $livePayload['phases']['rolled_back']);
        $this->assertTrue((bool) $livePayload['result']['seed_run_logged']);
        $this->assertSame(1, Page::query()->where('seeder', $generated['preview']['page_fully_qualified_class_name'])->count());
        $this->assertGreaterThan(0, TextBlock::query()->count());
        $this->assertSame(1, DB::table('seed_runs')
            ->where('class_name', $generated['preview']['page_fully_qualified_class_name'])
            ->count());
    }

    public function test_strict_mode_makes_seed_only_fallback_warning_fatal_for_page_package(): void
    {
        $generated = $this->writeGenerated(
            topic: 'Feature Page Refresh Strict',
            categoryMode: 'existing',
            categoryContext: [
                'title' => 'Feature Page Refresh Strict Category',
                'slug' => 'feature-page-refresh-strict-category',
                'namespace' => 'Tests\\CodexFeature\\PageRefreshStrict',
                'seeder_relative_path' => 'database/seeders/Page_V3/Tests/CodexFeature/PageRefreshStrict/FeaturePageRefreshStrictCategorySeeder.php',
            ],
        );
        $this->makeDefinitionSeedable($generated);

        $exitCode = Artisan::call('page-v3:refresh-package', [
            'target' => $generated['preview']['page_real_seeder_relative_path'],
            '--dry-run' => true,
            '--skip-release-check' => true,
            '--strict' => true,
            '--json' => true,
        ]);

        $payload = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(1, $exitCode);
        $this->assertSame('impact', $payload['error']['stage']);
        $this->assertSame('warnings_are_fatal', $payload['error']['reason']);
        $this->assertFalse((bool) $payload['result']['refreshed']);
        $this->assertSame(0, Page::query()->count());
        $this->assertSame(0, DB::table('seed_runs')->count());
    }

    public function test_live_refresh_rolls_back_when_page_reseed_fails_after_delete_phase(): void
    {
        $generated = $this->writeGenerated(
            topic: 'Feature Page Refresh Rollback',
            categoryMode: 'existing',
            categoryContext: [
                'title' => 'Feature Page Refresh Rollback Category',
                'slug' => 'feature-page-refresh-rollback-category',
                'namespace' => 'Tests\\CodexFeature\\PageRefreshRollback',
                'seeder_relative_path' => 'database/seeders/Page_V3/Tests/CodexFeature/PageRefreshRollback/FeaturePageRefreshRollbackCategorySeeder.php',
            ],
        );
        $this->makeDefinitionSeedable($generated);
        $this->makeLocalizationSeedable($generated);
        $this->seedPackageLive($generated);

        $definitionPath = base_path((string) $generated['preview']['page_definition_relative_path']);
        $definition = json_decode(File::get($definitionPath), true, 512, JSON_THROW_ON_ERROR);
        $definition['page']['title'] = '';
        File::put(
            $definitionPath,
            json_encode($definition, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL
        );

        $exitCode = Artisan::call('page-v3:refresh-package', [
            'target' => $generated['preview']['page_real_seeder_relative_path'],
            '--skip-release-check' => true,
            '--force' => true,
            '--json' => true,
        ]);

        $payload = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(1, $exitCode);
        $this->assertSame('refresh', $payload['error']['stage']);
        $this->assertSame('refresh_failed', $payload['error']['reason']);
        $this->assertTrue((bool) $payload['phases']['rolled_back']);
        $this->assertFalse((bool) $payload['result']['refreshed']);
        $this->assertSame(1, Page::query()->where('seeder', $generated['preview']['page_fully_qualified_class_name'])->count());
        $this->assertGreaterThan(0, TextBlock::query()->count());
        $this->assertSame(1, DB::table('seed_runs')
            ->where('class_name', $generated['preview']['page_fully_qualified_class_name'])
            ->count());
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
     * @param  array<string, mixed>|null  $categoryContext
     * @return array<string, mixed>
     */
    private function writeGenerated(
        string $topic,
        string $categoryMode,
        ?array $categoryContext = null,
    ): array {
        $preview = app(PageV3BlueprintService::class)->buildPreview($topic, $categoryMode, $categoryContext, null);
        $preview['page_package_absolute_path'] = base_path($preview['page_package_relative_path']);

        $this->cleanupPaths[] = base_path($preview['page_seeder_relative_path']);
        $this->cleanupPaths[] = base_path($preview['page_package_relative_path']);

        $generated = [
            'source' => [
                'source_type' => 'manual_topic',
                'source_label' => 'Manual topic',
                'topic' => $topic,
            ],
            'category' => [
                'mode' => $categoryMode,
                'mode_label' => $categoryMode,
                'selected_category' => $categoryContext,
            ],
            'preview' => $preview,
        ];

        app(PageV3SkeletonWriterService::class)->write($generated, true);

        return [
            'preview' => $preview,
        ];
    }

    /**
     * @param  array<string, mixed>  $generated
     */
    private function makeDefinitionSeedable(array $generated): void
    {
        $definitionPath = base_path((string) $generated['preview']['page_definition_relative_path']);
        $definition = json_decode(File::get($definitionPath), true, 512, JSON_THROW_ON_ERROR);

        $definition['page']['blocks'] = [
            [
                'id' => 'overview',
                'uuid_key' => 'overview',
                'type' => 'box',
                'column' => 'left',
                'body' => '<p>Feature Page refresh package block.</p>',
                'tags' => ['Feature Page Refresh'],
            ],
        ];

        File::put(
            $definitionPath,
            json_encode($definition, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL
        );
    }

    /**
     * @param  array<string, mixed>  $generated
     */
    private function makeLocalizationSeedable(array $generated): void
    {
        $localizationPath = base_path(
            trim((string) $generated['preview']['page_package_relative_path'], '/\\')
            . DIRECTORY_SEPARATOR
            . 'localizations'
            . DIRECTORY_SEPARATOR
            . 'en.json'
        );
        $payload = json_decode(File::get($localizationPath), true, 512, JSON_THROW_ON_ERROR);
        $payload['blocks'] = [
            [
                'reference' => 'overview',
                'body' => '<p>Localized Feature Page refresh block.</p>',
            ],
        ];

        File::put(
            $localizationPath,
            json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL
        );
    }

    /**
     * @param  array<string, mixed>  $generated
     */
    private function seedPackageLive(array $generated): void
    {
        $exitCode = Artisan::call('page-v3:seed-package', [
            'target' => $generated['preview']['page_package_relative_path'],
            '--skip-release-check' => true,
        ]);

        $this->assertSame(0, $exitCode, Artisan::output());
    }
}
