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
use Tests\TestCase;

class PageV3SeedPackageCommandTest extends TestCase
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

    public function test_dry_run_rolls_back_page_seed_changes_for_absolute_package_target(): void
    {
        $generated = $this->writeGenerated(
            topic: 'Feature Page Seed Dry Run',
            categoryMode: 'existing',
            categoryContext: [
                'title' => 'Feature Page Seed Category',
                'slug' => 'feature-page-seed-category',
                'namespace' => 'Tests\\CodexFeature\\PageSeedDryRun',
                'seeder_relative_path' => 'database/seeders/Page_V3/Tests/CodexFeature/PageSeedDryRun/FeaturePageSeedCategorySeeder.php',
            ],
        );
        $this->makeDefinitionSeedable($generated);

        $exitCode = Artisan::call('page-v3:seed-package', [
            'target' => $generated['preview']['page_package_absolute_path'],
            '--dry-run' => true,
            '--check-profile' => 'scaffold',
            '--json' => true,
        ]);

        $payload = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(0, $exitCode);
        $this->assertSame(
            $generated['preview']['page_definition_relative_path'],
            $payload['target']['definition_relative_path']
        );
        $this->assertSame(
            $generated['preview']['page_fully_qualified_class_name'],
            $payload['target']['resolved_seeder_class']
        );
        $this->assertTrue((bool) $payload['mode']['dry_run']);
        $this->assertTrue((bool) $payload['preflight']['executed']);
        $this->assertTrue((bool) $payload['result']['seeded']);
        $this->assertTrue((bool) $payload['result']['rolled_back']);
        $this->assertFalse((bool) $payload['result']['seed_run_logged']);
        $this->assertSame('page', $payload['definition_summary']['content_type']);
        $this->assertSame(0, Page::query()->count());
        $this->assertSame(0, TextBlock::query()->count());
        $this->assertSame(0, DB::table('seed_runs')->count());
    }

    public function test_release_profile_blocks_live_page_seed_for_non_release_ready_definition_target(): void
    {
        $generated = $this->writeGenerated(
            topic: 'Feature Page Seed Release Gate',
            categoryMode: 'existing',
            categoryContext: [
                'title' => 'Feature Page Release Category',
                'slug' => 'feature-page-release-category',
                'namespace' => 'Tests\\CodexFeature\\PageSeedReleaseGate',
                'seeder_relative_path' => 'database/seeders/Page_V3/Tests/CodexFeature/PageSeedReleaseGate/FeaturePageReleaseCategorySeeder.php',
            ],
        );
        $this->makeDefinitionSeedable($generated);

        $exitCode = Artisan::call('page-v3:seed-package', [
            'target' => $generated['preview']['page_definition_relative_path'],
            '--json' => true,
        ]);

        $payload = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(1, $exitCode);
        $this->assertSame('preflight', $payload['error']['stage']);
        $this->assertSame('checks_failed', $payload['error']['reason']);
        $this->assertTrue((bool) $payload['preflight']['executed']);
        $this->assertFalse((bool) $payload['result']['seeded']);
        $this->assertSame(0, Page::query()->count());
        $this->assertSame(0, DB::table('seed_runs')->count());
    }

    public function test_skip_release_check_allows_live_page_seed_and_safe_rerun_for_real_seeder_target(): void
    {
        $generated = $this->writeGenerated(
            topic: 'Feature Page Seed Live',
            categoryMode: 'existing',
            categoryContext: [
                'title' => 'Feature Page Live Category',
                'slug' => 'feature-page-live-category',
                'namespace' => 'Tests\\CodexFeature\\PageSeedLive',
                'seeder_relative_path' => 'database/seeders/Page_V3/Tests/CodexFeature/PageSeedLive/FeaturePageLiveCategorySeeder.php',
            ],
        );
        $this->makeDefinitionSeedable($generated);

        $firstExitCode = Artisan::call('page-v3:seed-package', [
            'target' => $generated['preview']['page_real_seeder_relative_path'],
            '--skip-release-check' => true,
            '--json' => true,
        ]);
        $firstPayload = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);

        $secondExitCode = Artisan::call('page-v3:seed-package', [
            'target' => $generated['preview']['page_real_seeder_relative_path'],
            '--skip-release-check' => true,
            '--json' => true,
        ]);
        $secondPayload = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(0, $firstExitCode);
        $this->assertSame(0, $secondExitCode);
        $this->assertFalse((bool) $firstPayload['preflight']['executed']);
        $this->assertTrue((bool) $firstPayload['result']['seeded']);
        $this->assertFalse((bool) $firstPayload['result']['rolled_back']);
        $this->assertTrue((bool) $firstPayload['result']['seed_run_logged']);
        $this->assertTrue((bool) $secondPayload['result']['seeded']);
        $this->assertSame(1, Page::query()->where('seeder', $generated['preview']['page_fully_qualified_class_name'])->count());
        $this->assertSame(1, PageCategory::query()->where('slug', 'feature-page-live-category')->count());
        $this->assertSame(
            2,
            TextBlock::query()->where('seeder', $generated['preview']['page_fully_qualified_class_name'])->count()
        );
        $this->assertSame(1, DB::table('seed_runs')
            ->where('class_name', $generated['preview']['page_fully_qualified_class_name'])
            ->count());
    }

    public function test_strict_mode_makes_scaffold_warnings_fatal_for_page_package_directory_target(): void
    {
        $generated = $this->writeGenerated(
            topic: 'Feature Page Seed Strict',
            categoryMode: 'existing',
            categoryContext: [
                'title' => 'Feature Page Strict Category',
                'slug' => 'feature-page-strict-category',
                'namespace' => 'Tests\\CodexFeature\\PageSeedStrict',
                'seeder_relative_path' => 'database/seeders/Page_V3/Tests/CodexFeature/PageSeedStrict/FeaturePageStrictCategorySeeder.php',
            ],
        );
        $this->makeDefinitionSeedable($generated);

        $exitCode = Artisan::call('page-v3:seed-package', [
            'target' => $generated['preview']['page_package_relative_path'],
            '--dry-run' => true,
            '--check-profile' => 'scaffold',
            '--strict' => true,
            '--json' => true,
        ]);

        $payload = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(1, $exitCode);
        $this->assertSame('preflight', $payload['error']['stage']);
        $this->assertSame('warnings_are_fatal', $payload['error']['reason']);
        $this->assertFalse((bool) $payload['result']['seeded']);
        $this->assertSame(0, Page::query()->count());
        $this->assertSame(0, DB::table('seed_runs')->count());
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
                'body' => '<p>Feature Page seed package block.</p>',
                'tags' => ['Feature Page Seed'],
            ],
        ];

        File::put(
            $definitionPath,
            json_encode($definition, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL
        );
    }
}
