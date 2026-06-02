<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Services\PageV3PromptGenerator\PageV3BlueprintService;
use App\Services\PageV3PromptGenerator\PageV3SkeletonWriterService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PageV3FolderPlanCommandTest extends TestCase
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

    public function test_sync_mode_orders_category_before_page_in_one_page_v3_folder(): void
    {
        $generated = $this->writeGenerated(
            topic: 'Feature Page Plan Topic',
            categoryMode: 'new',
            categoryContext: null,
            newCategoryTitle: 'Feature Page Plan Category',
        );
        $this->makeCategoryDefinitionSeedable($generated);
        $this->makeCategoryLocalizationSeedable($generated);
        $this->makePageDefinitionSeedable($generated);
        $this->makePageLocalizationSeedable($generated);
        $this->seedCategoryPackageLive($generated);

        $exitCode = Artisan::call('page-v3:plan-folder', [
            'target' => dirname((string) $generated['preview']['category_seeder_relative_path']),
            '--json' => true,
        ]);

        $payload = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(0, $exitCode);
        $this->assertSame(2, $payload['summary']['total_packages']);
        $this->assertSame(1, $payload['summary']['seed_candidates']);
        $this->assertSame(1, $payload['summary']['refresh_candidates']);
        $this->assertSame($generated['preview']['category_package_relative_path'], $payload['packages'][0]['relative_path']);
        $this->assertSame('category', $payload['packages'][0]['package_type']);
        $this->assertSame('refresh', $payload['packages'][0]['recommended_action']);
        $this->assertSame($generated['preview']['page_package_relative_path'], $payload['packages'][1]['relative_path']);
        $this->assertSame('page', $payload['packages'][1]['package_type']);
        $this->assertSame('seed', $payload['packages'][1]['recommended_action']);
    }

    public function test_unseed_mode_orders_page_before_category_for_destructive_plan(): void
    {
        $generated = $this->writeGenerated(
            topic: 'Feature Page Plan Unseed Topic',
            categoryMode: 'new',
            categoryContext: null,
            newCategoryTitle: 'Feature Page Plan Unseed Category',
        );
        $this->makeCategoryDefinitionSeedable($generated);
        $this->makeCategoryLocalizationSeedable($generated);
        $this->makePageDefinitionSeedable($generated);
        $this->makePageLocalizationSeedable($generated);
        $this->seedCategoryPackageLive($generated);
        $this->seedPagePackageLive($generated);

        $exitCode = Artisan::call('page-v3:plan-folder', [
            'target' => dirname((string) $generated['preview']['category_seeder_relative_path']),
            '--mode' => 'unseed',
            '--json' => true,
        ]);

        $payload = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(0, $exitCode);
        $this->assertSame('unseed', $payload['scope']['mode']);
        $this->assertSame(2, $payload['summary']['total_packages']);
        $this->assertSame(2, $payload['summary']['unseed_candidates']);
        $this->assertSame($generated['preview']['page_package_relative_path'], $payload['packages'][0]['relative_path']);
        $this->assertSame('page', $payload['packages'][0]['package_type']);
        $this->assertSame('unseed', $payload['packages'][0]['recommended_action']);
        $this->assertStringContainsString('page-v3:unseed-package', $payload['packages'][0]['next_step_command']);
        $this->assertStringContainsString('--force', $payload['packages'][0]['next_step_command']);
        $this->assertSame($generated['preview']['category_package_relative_path'], $payload['packages'][1]['relative_path']);
        $this->assertSame('category', $payload['packages'][1]['package_type']);
        $this->assertSame('unseed', $payload['packages'][1]['recommended_action']);
    }

    public function test_destroy_files_mode_orders_page_before_category_for_file_destroy_plan(): void
    {
        $generated = $this->writeGenerated(
            topic: 'Feature Page Plan Destroy Files Topic',
            categoryMode: 'new',
            categoryContext: null,
            newCategoryTitle: 'Feature Page Plan Destroy Files Category',
        );
        $this->makeCategoryDefinitionSeedable($generated);
        $this->makeCategoryLocalizationSeedable($generated);
        $this->makePageDefinitionSeedable($generated);
        $this->makePageLocalizationSeedable($generated);

        $exitCode = Artisan::call('page-v3:plan-folder', [
            'target' => dirname((string) $generated['preview']['category_seeder_relative_path']),
            '--mode' => 'destroy-files',
            '--json' => true,
        ]);

        $payload = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(0, $exitCode);
        $this->assertSame('destroy-files', $payload['scope']['mode']);
        $this->assertSame(2, $payload['summary']['destroy_files_candidates']);
        $this->assertSame($generated['preview']['page_package_relative_path'], $payload['packages'][0]['relative_path']);
        $this->assertSame('page', $payload['packages'][0]['package_type']);
        $this->assertSame('destroy_files', $payload['packages'][0]['recommended_action']);
        $this->assertSame($generated['preview']['category_package_relative_path'], $payload['packages'][1]['relative_path']);
        $this->assertSame('category', $payload['packages'][1]['package_type']);
        $this->assertSame('destroy_files', $payload['packages'][1]['recommended_action']);
    }

    public function test_destroy_files_mode_blocks_category_when_sibling_page_is_outside_scope(): void
    {
        $generated = $this->writeGenerated(
            topic: 'Feature Page Plan Category Scope',
            categoryMode: 'new',
            categoryContext: null,
            newCategoryTitle: 'Feature Page Plan Category Scope',
        );
        $this->makeCategoryDefinitionSeedable($generated);
        $this->makeCategoryLocalizationSeedable($generated);
        $this->makePageDefinitionSeedable($generated);
        $this->makePageLocalizationSeedable($generated);

        $exitCode = Artisan::call('page-v3:plan-folder', [
            'target' => $generated['preview']['category_package_relative_path'],
            '--mode' => 'destroy-files',
            '--json' => true,
        ]);

        $payload = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(0, $exitCode);
        $this->assertSame(1, $payload['summary']['total_packages']);
        $this->assertSame(0, $payload['summary']['destroy_files_candidates']);
        $this->assertSame(1, $payload['summary']['blocked']);
        $this->assertSame('blocked', $payload['packages'][0]['recommended_action']);
        $this->assertNotEmpty($payload['packages'][0]['warnings']);
        $this->assertStringContainsString('outside the current scope', $payload['packages'][0]['warnings'][0]);
    }

    public function test_destroy_mode_orders_page_before_category_for_combined_destroy_plan(): void
    {
        $generated = $this->writeGenerated(
            topic: 'Feature Page Plan Destroy Topic',
            categoryMode: 'new',
            categoryContext: null,
            newCategoryTitle: 'Feature Page Plan Destroy Category',
        );
        $this->makeCategoryDefinitionSeedable($generated);
        $this->makeCategoryLocalizationSeedable($generated);
        $this->makePageDefinitionSeedable($generated);
        $this->makePageLocalizationSeedable($generated);
        $this->seedCategoryPackageLive($generated);

        $exitCode = Artisan::call('page-v3:plan-folder', [
            'target' => dirname((string) $generated['preview']['category_seeder_relative_path']),
            '--mode' => 'destroy',
            '--json' => true,
        ]);

        $payload = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(0, $exitCode);
        $this->assertSame('destroy', $payload['scope']['mode']);
        $this->assertSame(2, $payload['summary']['destroy_candidates']);
        $this->assertSame($generated['preview']['page_package_relative_path'], $payload['packages'][0]['relative_path']);
        $this->assertSame('page', $payload['packages'][0]['package_type']);
        $this->assertSame('destroy', $payload['packages'][0]['recommended_action']);
        $this->assertFalse((bool) $payload['packages'][0]['needs_unseed']);
        $this->assertTrue((bool) $payload['packages'][0]['needs_file_destroy']);
        $this->assertSame($generated['preview']['category_package_relative_path'], $payload['packages'][1]['relative_path']);
        $this->assertSame('category', $payload['packages'][1]['package_type']);
        $this->assertSame('destroy', $payload['packages'][1]['recommended_action']);
        $this->assertTrue((bool) $payload['packages'][1]['needs_unseed']);
        $this->assertTrue((bool) $payload['packages'][1]['needs_file_destroy']);
    }

    public function test_destroy_mode_blocks_category_when_sibling_page_is_outside_scope(): void
    {
        $generated = $this->writeGenerated(
            topic: 'Feature Page Plan Combined Category Scope',
            categoryMode: 'new',
            categoryContext: null,
            newCategoryTitle: 'Feature Page Plan Combined Category Scope',
        );
        $this->makeCategoryDefinitionSeedable($generated);
        $this->makeCategoryLocalizationSeedable($generated);
        $this->makePageDefinitionSeedable($generated);
        $this->makePageLocalizationSeedable($generated);

        $exitCode = Artisan::call('page-v3:plan-folder', [
            'target' => $generated['preview']['category_package_relative_path'],
            '--mode' => 'destroy',
            '--json' => true,
        ]);

        $payload = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(0, $exitCode);
        $this->assertSame(1, $payload['summary']['total_packages']);
        $this->assertSame(0, $payload['summary']['destroy_candidates']);
        $this->assertSame(1, $payload['summary']['blocked']);
        $this->assertSame('blocked', $payload['packages'][0]['recommended_action']);
        $this->assertNotEmpty($payload['packages'][0]['warnings']);
        $this->assertStringContainsString('outside the current scope', $payload['packages'][0]['warnings'][0]);
    }

    public function test_strict_mode_fails_when_page_v3_package_has_db_content_without_seed_run(): void
    {
        $generated = $this->writeGenerated(
            topic: 'Feature Page Strict Planner',
            categoryMode: 'existing',
            categoryContext: [
                'title' => 'Feature Page Strict Planner Category',
                'slug' => 'feature-page-strict-planner-category',
                'namespace' => 'Tests\\CodexFeature\\PagePlanStrict',
                'seeder_relative_path' => 'database/seeders/Page_V3/Tests/CodexFeature/PagePlanStrict/FeaturePageStrictPlannerCategorySeeder.php',
            ],
        );
        $this->makePageDefinitionSeedable($generated);
        $this->makePageLocalizationSeedable($generated);
        $this->seedPagePackageLive($generated);

        DB::table('seed_runs')
            ->where('class_name', $generated['preview']['page_fully_qualified_class_name'])
            ->delete();

        $exitCode = Artisan::call('page-v3:plan-folder', [
            'target' => $generated['preview']['page_package_relative_path'],
            '--strict' => true,
            '--json' => true,
        ]);

        $payload = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(1, $exitCode);
        $this->assertSame('planning', $payload['error']['stage']);
        $this->assertSame('blocked_packages', $payload['error']['reason']);
        $this->assertSame('db_only_without_seed_run', $payload['packages'][0]['package_state']);
        $this->assertSame('blocked', $payload['packages'][0]['recommended_action']);
    }

    public function test_strict_mode_fails_on_page_v3_release_check_warnings_when_requested(): void
    {
        $generated = $this->writeGenerated(
            topic: 'Feature Page Release Warning Planner',
            categoryMode: 'new',
            categoryContext: null,
            newCategoryTitle: 'Feature Page Release Warning Category',
        );

        $exitCode = Artisan::call('page-v3:plan-folder', [
            'target' => dirname((string) $generated['preview']['category_seeder_relative_path']),
            '--with-release-check' => true,
            '--check-profile' => 'scaffold',
            '--strict' => true,
            '--json' => true,
        ]);

        $payload = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(1, $exitCode);
        $this->assertSame('release_check', $payload['error']['stage']);
        $this->assertSame('warnings_are_fatal', $payload['error']['reason']);
        $this->assertSame('not_seeded', $payload['packages'][0]['package_state']);
        $this->assertSame('seed', $payload['packages'][0]['recommended_action']);
        $this->assertTrue((bool) $payload['packages'][0]['release_check']['executed']);
        $this->assertSame('warn', $payload['packages'][0]['release_check']['status']);
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
        ?string $newCategoryTitle = null,
    ): array {
        $preview = app(PageV3BlueprintService::class)->buildPreview(
            $topic,
            $categoryMode,
            $categoryContext,
            $newCategoryTitle
        );

        $this->cleanupPaths[] = base_path($preview['page_seeder_relative_path']);
        $this->cleanupPaths[] = base_path($preview['page_package_relative_path']);

        if ($categoryMode === 'new') {
            $this->cleanupPaths[] = base_path($preview['category_seeder_relative_path']);
            $this->cleanupPaths[] = base_path($preview['category_package_relative_path']);
        }

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
                'new_category_title' => $newCategoryTitle,
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
    private function makePageDefinitionSeedable(array $generated): void
    {
        $definitionPath = base_path((string) $generated['preview']['page_definition_relative_path']);
        $definition = json_decode(File::get($definitionPath), true, 512, JSON_THROW_ON_ERROR);

        $definition['page']['blocks'] = [
            [
                'id' => 'page-overview',
                'uuid_key' => 'page-overview',
                'type' => 'box',
                'column' => 'left',
                'body' => '<p>Feature Page plan block.</p>',
                'tags' => ['Feature Page Plan'],
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
    private function makePageLocalizationSeedable(array $generated): void
    {
        $localizationPath = base_path((string) $generated['preview']['page_localization_en_relative_path']);
        $payload = json_decode(File::get($localizationPath), true, 512, JSON_THROW_ON_ERROR);
        $payload['blocks'] = [
            [
                'reference' => 'page-overview',
                'body' => '<p>Localized Feature Page plan block.</p>',
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
    private function makeCategoryDefinitionSeedable(array $generated): void
    {
        $definitionPath = base_path((string) $generated['preview']['category_definition_relative_path']);
        $definition = json_decode(File::get($definitionPath), true, 512, JSON_THROW_ON_ERROR);
        $definition['description']['blocks'] = [
            [
                'id' => 'category-overview',
                'uuid_key' => 'category-overview',
                'type' => 'box',
                'column' => 'left',
                'body' => '<p>Feature Category plan block.</p>',
                'tags' => ['Feature Category Plan'],
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
    private function makeCategoryLocalizationSeedable(array $generated): void
    {
        $localizationPath = base_path((string) $generated['preview']['category_localization_en_relative_path']);
        $payload = json_decode(File::get($localizationPath), true, 512, JSON_THROW_ON_ERROR);
        $payload['blocks'] = [
            [
                'reference' => 'category-overview',
                'body' => '<p>Localized Feature Category plan block.</p>',
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
    private function seedCategoryPackageLive(array $generated): void
    {
        $exitCode = Artisan::call('page-v3:seed-package', [
            'target' => $generated['preview']['category_package_relative_path'],
            '--skip-release-check' => true,
        ]);

        $this->assertSame(0, $exitCode, Artisan::output());
    }

    /**
     * @param  array<string, mixed>  $generated
     */
    private function seedPagePackageLive(array $generated): void
    {
        $exitCode = Artisan::call('page-v3:seed-package', [
            'target' => $generated['preview']['page_package_relative_path'],
            '--skip-release-check' => true,
        ]);

        $this->assertSame(0, $exitCode, Artisan::output());
        $this->assertSame(1, Page::query()->where('seeder', $generated['preview']['page_fully_qualified_class_name'])->count());
    }
}
