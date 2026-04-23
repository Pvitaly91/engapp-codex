<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\PageCategory;
use App\Models\Question;
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

class PageV3UnseedFolderCommandTest extends TestCase
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

    public function test_it_resolves_supported_single_page_package_targets_for_folder_unseed(): void
    {
        $generated = $this->writeNewCategoryPage(
            'Page Folder Unseed Targets',
            'Tests\\CodexFeature\\PageUnseedFolderTargets',
            'Page Folder Unseed Targets Category'
        );
        $this->makeCategoryDefinitionSeedable($generated);
        $this->makeCategoryLocalizationSeedable($generated);
        $this->makePageDefinitionSeedable($generated);
        $this->makePageLocalizationSeedable($generated);
        $this->seedCategoryPackageLive($generated);
        $this->seedPagePackageLive($generated);

        $targets = [
            $generated['preview']['page_package_relative_path'],
            base_path((string) $generated['preview']['page_definition_relative_path']),
            $generated['preview']['page_seeder_relative_path'],
            base_path((string) $generated['preview']['page_real_seeder_relative_path']),
        ];

        foreach ($targets as $target) {
            [$exitCode, $payload] = $this->callUnseedFolder($target, [
                '--dry-run' => true,
                '--json' => true,
            ]);

            $this->assertSame(0, $exitCode, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
            $this->assertTrue((bool) $payload['scope']['single_package']);
            $this->assertSame(1, $payload['plan']['summary']['total_packages']);
            $this->assertSame(1, $payload['plan']['summary']['unseed_candidates']);
            $this->assertSame($generated['preview']['page_package_relative_path'], $payload['plan']['packages'][0]['relative_path']);
            $this->assertSame(1, $payload['preflight']['summary']['ok']);
            $this->assertSame(0, $payload['execution']['started']);
        }
    }

    public function test_dry_run_preflights_full_page_v3_scope_and_keeps_db_unchanged(): void
    {
        Storage::fake('local');

        $generated = $this->writeNewCategoryPage(
            'Page Folder Unseed Dry Run',
            'Tests\\CodexFeature\\PageUnseedFolderDryRun',
            'Page Folder Unseed Dry Run Category'
        );
        $this->makeCategoryDefinitionSeedable($generated);
        $this->makeCategoryLocalizationSeedable($generated);
        $this->makePageDefinitionSeedable($generated);
        $this->makePageLocalizationSeedable($generated);
        $this->seedCategoryPackageLive($generated);
        $this->seedPagePackageLive($generated);

        $pageCount = Page::query()->count();
        $categoryCount = PageCategory::query()->count();
        $blockCount = TextBlock::query()->count();
        $seedRunCount = DB::table('seed_runs')->count();

        [$exitCode, $payload] = $this->callUnseedFolder(dirname((string) $generated['preview']['category_seeder_relative_path']), [
            '--dry-run' => true,
            '--write-report' => true,
            '--json' => true,
        ]);

        $reportFiles = Storage::disk('local')->allFiles('folder-unseed-reports/page-v3');

        $this->assertSame(0, $exitCode);
        $this->assertSame(2, $payload['plan']['summary']['unseed_candidates']);
        $this->assertSame(2, $payload['preflight']['summary']['candidates']);
        $this->assertSame($generated['preview']['page_package_relative_path'], $payload['plan']['packages'][0]['relative_path']);
        $this->assertSame($generated['preview']['category_package_relative_path'], $payload['plan']['packages'][1]['relative_path']);
        $this->assertSame('ok', $payload['preflight']['packages'][0]['status']);
        $this->assertSame('ok', $payload['preflight']['packages'][1]['status']);
        $this->assertSame(0, $payload['execution']['started']);
        $this->assertCount(1, $reportFiles);
        $this->assertSame($pageCount, Page::query()->count());
        $this->assertSame($categoryCount, PageCategory::query()->count());
        $this->assertSame($blockCount, TextBlock::query()->count());
        $this->assertSame($seedRunCount, DB::table('seed_runs')->count());
    }

    public function test_preflight_failure_aborts_live_page_v3_folder_unseed_before_any_writes(): void
    {
        $generated = $this->writeNewCategoryPage(
            'Page Folder Unseed Guard',
            'Tests\\CodexFeature\\PageUnseedFolderGuard',
            'Page Folder Unseed Guard Category'
        );
        $this->makeCategoryDefinitionSeedable($generated);
        $this->makeCategoryLocalizationSeedable($generated);
        $this->makePageDefinitionSeedable($generated);
        $this->makePageLocalizationSeedable($generated);
        $this->seedCategoryPackageLive($generated);
        $this->seedPagePackageLive($generated);

        $textBlock = TextBlock::query()
            ->whereNotNull('page_id')
            ->orderBy('id')
            ->firstOrFail();

        Question::query()->create([
            'uuid' => 'page-folder-unseed-guard-question',
            'question' => 'Question linked to package page block.',
            'difficulty' => 1,
            'level' => 'A1',
            'theory_text_block_uuid' => (string) $textBlock->uuid,
            'seeder' => 'Tests\\Seeders\\ExternalQuestionSeeder',
        ]);

        $pageCount = Page::query()->count();
        $categoryCount = PageCategory::query()->count();
        $blockCount = TextBlock::query()->count();
        $seedRunCount = DB::table('seed_runs')->count();

        [$exitCode, $payload] = $this->callUnseedFolder(dirname((string) $generated['preview']['category_seeder_relative_path']), [
            '--force' => true,
            '--json' => true,
        ]);

        $this->assertSame(1, $exitCode);
        $this->assertSame('preflight', $payload['error']['stage']);
        $this->assertSame('preflight_failed', $payload['error']['reason']);
        $this->assertSame($generated['preview']['page_package_relative_path'], $payload['error']['package']);
        $this->assertSame('questions_reference_package_blocks', $payload['error']['service_error']['reason']);
        $this->assertSame(0, $payload['execution']['started']);
        $this->assertSame($pageCount, Page::query()->count());
        $this->assertSame($categoryCount, PageCategory::query()->count());
        $this->assertSame($blockCount, TextBlock::query()->count());
        $this->assertSame($seedRunCount, DB::table('seed_runs')->count());
    }

    /**
     * @return array{0:int,1:array<string,mixed>}
     */
    private function callUnseedFolder(string $target, array $arguments): array
    {
        $exitCode = Artisan::call('page-v3:unseed-folder', array_merge([
            'target' => $target,
        ], $arguments));

        /** @var array<string, mixed> $payload */
        $payload = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);

        return [$exitCode, $payload];
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
    private function writeExistingPage(string $topic, array $categoryContext): array
    {
        return $this->writeGenerated($topic, 'existing', $categoryContext, null);
    }

    /**
     * @return array<string, mixed>
     */
    private function writeNewCategoryPage(string $topic, string $namespace, string $newCategoryTitle): array
    {
        return $this->writeGenerated($topic, 'new', [
            'namespace' => $namespace,
        ], $newCategoryTitle);
    }

    /**
     * @param  array<string, mixed>|null  $categoryContext
     * @return array<string, mixed>
     */
    private function writeGenerated(
        string $topic,
        string $categoryMode,
        ?array $categoryContext,
        ?string $newCategoryTitle,
    ): array {
        $preview = app(PageV3BlueprintService::class)->buildPreview(
            $topic,
            $categoryMode,
            $categoryContext,
            $newCategoryTitle
        );

        $this->cleanupPaths[] = base_path((string) $preview['page_seeder_relative_path']);
        $this->cleanupPaths[] = base_path((string) $preview['page_package_relative_path']);

        if ($categoryMode === 'new') {
            $this->cleanupPaths[] = base_path((string) $preview['category_seeder_relative_path']);
            $this->cleanupPaths[] = base_path((string) $preview['category_package_relative_path']);
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
                'selected_category' => $categoryMode === 'existing' ? $categoryContext : null,
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
     * @return array<string, mixed>
     */
    private function existingCategoryContext(string $namespace, string $slug): array
    {
        $classStem = str_replace(' ', '', ucwords(str_replace('-', ' ', $slug)));

        return [
            'title' => str_replace('-', ' ', ucfirst($slug)),
            'slug' => $slug,
            'namespace' => $namespace,
            'seeder_relative_path' => 'database/seeders/Page_V3/'
                . str_replace('\\', '/', $namespace)
                . '/'
                . $classStem
                . 'CategorySeeder.php',
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
                'body' => '<p>Page_V3 folder unseed block.</p>',
                'tags' => ['Page Unseed'],
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
                'body' => '<p>Localized Page_V3 folder unseed block.</p>',
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
                'body' => '<p>Category folder unseed block.</p>',
                'tags' => ['Category Unseed'],
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
                'body' => '<p>Localized category folder unseed block.</p>',
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
    }
}
