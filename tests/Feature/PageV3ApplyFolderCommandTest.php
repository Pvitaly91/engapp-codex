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

class PageV3ApplyFolderCommandTest extends TestCase
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

    public function test_it_resolves_supported_page_v3_single_package_targets(): void
    {
        $generated = $this->writeExistingPage(
            'Apply Target Page',
            $this->existingCategoryContext('Tests\\CodexFeature\\PageApplyTargets', 'page-apply-targets-category')
        );
        $this->makePageDefinitionSeedable($generated);

        $targets = [
            $generated['preview']['page_package_relative_path'],
            base_path($generated['preview']['page_definition_relative_path']),
            $generated['preview']['page_seeder_relative_path'],
            base_path($generated['preview']['page_real_seeder_relative_path']),
        ];

        foreach ($targets as $target) {
            [$exitCode, $payload] = $this->callApply($target, [
                '--dry-run' => true,
                '--skip-release-check' => true,
                '--json' => true,
            ]);

            $this->assertSame(0, $exitCode);
            $this->assertTrue((bool) $payload['scope']['single_package']);
            $this->assertSame(1, $payload['plan']['summary']['total_packages']);
            $this->assertSame($generated['preview']['page_package_relative_path'], $payload['plan']['packages'][0]['relative_path']);
            $this->assertSame('ok', $payload['execution']['packages'][0]['status']);
        }
    }

    public function test_it_resolves_a_page_v3_folder_root_and_executes_category_before_page(): void
    {
        $generated = $this->writeNewCategoryPage(
            'Category Before Page Apply',
            'Tests\\CodexFeature\\PageApplyFolderRoot',
            'Category Before Page Apply'
        );
        $this->makeCategoryDefinitionSeedable($generated);
        $this->makeCategoryLocalizationSeedable($generated);
        $this->makePageDefinitionSeedable($generated);
        $this->makePageLocalizationSeedable($generated);

        [$exitCode, $payload] = $this->callApply(dirname((string) $generated['preview']['category_seeder_relative_path']), [
            '--dry-run' => true,
            '--skip-release-check' => true,
            '--json' => true,
        ]);

        $this->assertSame(0, $exitCode);
        $this->assertFalse((bool) $payload['scope']['single_package']);
        $this->assertSame(2, $payload['plan']['summary']['total_packages']);
        $this->assertSame('category', $payload['plan']['packages'][0]['package_type']);
        $this->assertSame($generated['preview']['category_package_relative_path'], $payload['execution']['packages'][0]['relative_path']);
        $this->assertSame($generated['preview']['page_package_relative_path'], $payload['execution']['packages'][1]['relative_path']);
    }

    public function test_it_rejects_targets_outside_the_page_v3_root(): void
    {
        [$exitCode, $payload] = $this->callApply(base_path('composer.json'), [
            '--dry-run' => true,
            '--json' => true,
        ]);

        $this->assertSame(1, $exitCode);
        $this->assertSame('target_resolution', $payload['error']['stage']);
        $this->assertStringContainsString('database/seeders/Page_V3', $payload['error']['message']);
    }

    public function test_live_apply_requires_force(): void
    {
        $generated = $this->writeExistingPage(
            'Live Apply Force Page',
            $this->existingCategoryContext('Tests\\CodexFeature\\PageApplyForce', 'page-apply-force-category')
        );
        $this->makePageDefinitionSeedable($generated);

        [$exitCode, $payload] = $this->callApply($generated['preview']['page_package_relative_path'], [
            '--skip-release-check' => true,
            '--json' => true,
        ]);

        $this->assertSame(1, $exitCode);
        $this->assertSame('force_required', $payload['error']['reason']);
        $this->assertSame(0, Page::query()->count());
        $this->assertSame(0, DB::table('seed_runs')->count());
    }

    public function test_missing_mode_executes_only_seed_candidates(): void
    {
        $context = $this->existingCategoryContext('Tests\\CodexFeature\\PageApplyMissing', 'page-apply-missing-category');
        $alpha = $this->writeExistingPage('Alpha Missing Page Apply', $context);
        $beta = $this->writeExistingPage('Beta Missing Page Apply', $context);
        $this->makePageDefinitionSeedable($alpha);
        $this->makePageDefinitionSeedable($beta);
        $this->seedPagePackageLive($alpha);

        [$exitCode, $payload] = $this->callApply(dirname((string) $alpha['preview']['page_seeder_relative_path']), [
            '--mode' => 'missing',
            '--dry-run' => true,
            '--skip-release-check' => true,
            '--json' => true,
        ]);

        $this->assertSame(0, $exitCode);
        $this->assertSame(1, $payload['execution']['started']);
        $this->assertSame('skip', $payload['execution']['packages'][0]['action']);
        $this->assertSame('skipped', $payload['execution']['packages'][0]['status']);
        $this->assertSame('seed', $payload['execution']['packages'][1]['action']);
        $this->assertSame('ok', $payload['execution']['packages'][1]['status']);
    }

    public function test_refresh_mode_executes_only_refresh_candidates(): void
    {
        $context = $this->existingCategoryContext('Tests\\CodexFeature\\PageApplyRefresh', 'page-apply-refresh-category');
        $alpha = $this->writeExistingPage('Alpha Refresh Page Apply', $context);
        $beta = $this->writeExistingPage('Beta Refresh Page Apply', $context);
        $this->makePageDefinitionSeedable($alpha);
        $this->makePageDefinitionSeedable($beta);
        $this->seedPagePackageLive($alpha);

        [$exitCode, $payload] = $this->callApply(dirname((string) $alpha['preview']['page_seeder_relative_path']), [
            '--mode' => 'refresh',
            '--dry-run' => true,
            '--skip-release-check' => true,
            '--json' => true,
        ]);

        $this->assertSame(0, $exitCode);
        $this->assertSame(1, $payload['execution']['started']);
        $this->assertSame('refresh', $payload['execution']['packages'][0]['action']);
        $this->assertSame('ok', $payload['execution']['packages'][0]['status']);
        $this->assertSame('skip', $payload['execution']['packages'][1]['action']);
    }

    public function test_sync_mode_executes_seed_and_refresh_and_dry_run_leaves_db_unchanged(): void
    {
        $context = $this->existingCategoryContext('Tests\\CodexFeature\\PageApplySync', 'page-apply-sync-category');
        $alpha = $this->writeExistingPage('Alpha Sync Page Apply', $context);
        $beta = $this->writeExistingPage('Beta Sync Page Apply', $context);
        $this->makePageDefinitionSeedable($alpha);
        $this->makePageDefinitionSeedable($beta);
        $this->seedPagePackageLive($alpha);

        $pageCount = Page::query()->count();
        $categoryCount = PageCategory::query()->count();
        $blockCount = TextBlock::query()->count();
        $seedRunCount = DB::table('seed_runs')->count();

        [$exitCode, $payload] = $this->callApply(dirname((string) $alpha['preview']['page_seeder_relative_path']), [
            '--mode' => 'sync',
            '--dry-run' => true,
            '--skip-release-check' => true,
            '--json' => true,
        ]);

        $this->assertSame(0, $exitCode);
        $this->assertSame(2, $payload['execution']['started']);
        $this->assertSame('refresh', $payload['execution']['packages'][0]['action']);
        $this->assertSame('seed', $payload['execution']['packages'][1]['action']);
        $this->assertSame($pageCount, Page::query()->count());
        $this->assertSame($categoryCount, PageCategory::query()->count());
        $this->assertSame($blockCount, TextBlock::query()->count());
        $this->assertSame($seedRunCount, DB::table('seed_runs')->count());
    }

    public function test_blocked_planner_state_aborts_before_any_writes(): void
    {
        $generated = $this->writeExistingPage(
            'Blocked Page Apply',
            $this->existingCategoryContext('Tests\\CodexFeature\\PageApplyBlocked', 'page-apply-blocked-category')
        );
        $this->makePageDefinitionSeedable($generated);
        $this->seedPagePackageLive($generated);
        DB::table('seed_runs')
            ->where('class_name', $generated['preview']['page_fully_qualified_class_name'])
            ->delete();

        [$exitCode, $payload] = $this->callApply($generated['preview']['page_package_relative_path'], [
            '--dry-run' => true,
            '--skip-release-check' => true,
            '--json' => true,
        ]);

        $this->assertSame(1, $exitCode);
        $this->assertSame('blocked_packages', $payload['error']['reason']);
        $this->assertSame(0, $payload['execution']['started']);
        $this->assertSame(1, Page::query()->where('seeder', $generated['preview']['page_fully_qualified_class_name'])->count());
    }

    public function test_page_referencing_category_outside_scope_keeps_warning_and_report(): void
    {
        Storage::fake('local');
        $generated = $this->writeExistingPage(
            'Outside Scope Page Apply',
            $this->existingCategoryContext('Tests\\CodexFeature\\PageApplyOutsideScope', 'page-apply-outside-scope-category')
        );
        $this->makePageDefinitionSeedable($generated);

        [$exitCode, $payload] = $this->callApply($generated['preview']['page_package_relative_path'], [
            '--dry-run' => true,
            '--skip-release-check' => true,
            '--write-report' => true,
            '--json' => true,
        ]);

        $reportFiles = Storage::disk('local')->allFiles('folder-apply-reports/page-v3');

        $this->assertSame(0, $exitCode);
        $this->assertNotEmpty($payload['plan']['packages'][0]['warnings']);
        $this->assertStringContainsString('outside the current scope', $payload['plan']['packages'][0]['warnings'][0]);
        $this->assertCount(1, $reportFiles);
    }

    public function test_fail_fast_stops_after_first_page_failure_and_reports_pending_packages(): void
    {
        $context = $this->existingCategoryContext('Tests\\CodexFeature\\PageApplyFailFast', 'page-apply-fail-fast-category');
        $alpha = $this->writeExistingPage('Alpha Fail Fast Page Apply', $context);
        $beta = $this->writeExistingPage('Beta Fail Fast Page Apply', $context);
        $gamma = $this->writeExistingPage('Gamma Fail Fast Page Apply', $context);
        $this->makePageDefinitionSeedable($alpha);
        $this->makePageDefinitionSeedable($beta);
        $this->makePageDefinitionSeedable($gamma);
        $this->seedPagePackageLive($beta);
        $this->breakPageDefinition($beta);

        [$exitCode, $payload] = $this->callApply(dirname((string) $alpha['preview']['page_seeder_relative_path']), [
            '--mode' => 'sync',
            '--force' => true,
            '--skip-release-check' => true,
            '--json' => true,
        ]);

        $this->assertSame(1, $exitCode);
        $this->assertSame('package_failed', $payload['error']['reason']);
        $this->assertSame(2, $payload['execution']['completed']);
        $this->assertSame('ok', $payload['execution']['packages'][0]['status']);
        $this->assertSame('failed', $payload['execution']['packages'][1]['status']);
        $this->assertSame('pending', $payload['execution']['packages'][2]['status']);
        $this->assertSame(1, Page::query()->where('seeder', $alpha['preview']['page_fully_qualified_class_name'])->count());
        $this->assertSame(1, Page::query()->where('seeder', $beta['preview']['page_fully_qualified_class_name'])->count());
        $this->assertSame(0, Page::query()->where('seeder', $gamma['preview']['page_fully_qualified_class_name'])->count());
    }

    public function test_with_release_check_and_skip_release_check_are_reported_in_json_output(): void
    {
        $generated = $this->writeExistingPage(
            'Release Check Page Apply',
            $this->existingCategoryContext('Tests\\CodexFeature\\PageApplyReleaseCheck', 'page-apply-release-check-category')
        );
        $this->makePageDefinitionSeedable($generated);

        [$exitCode, $payload] = $this->callApply($generated['preview']['page_package_relative_path'], [
            '--dry-run' => true,
            '--with-release-check' => true,
            '--skip-release-check' => true,
            '--check-profile' => 'scaffold',
            '--json' => true,
        ]);

        $this->assertSame(0, $exitCode);
        $this->assertTrue((bool) $payload['scope']['with_release_check']);
        $this->assertTrue((bool) $payload['plan']['packages'][0]['release_check']['executed']);
        $this->assertTrue((bool) $payload['execution']['packages'][0]['service_result']['mode']['release_check_skipped']);
    }

    public function test_strict_mode_fails_before_execution_when_planner_returns_release_check_warnings(): void
    {
        $generated = $this->writeNewCategoryPage(
            'Strict Page Apply',
            'Tests\\CodexFeature\\PageApplyStrict',
            'Strict Page Apply Category'
        );
        $this->makeCategoryDefinitionSeedable($generated);
        $this->makePageDefinitionSeedable($generated);

        [$exitCode, $payload] = $this->callApply(dirname((string) $generated['preview']['category_seeder_relative_path']), [
            '--dry-run' => true,
            '--with-release-check' => true,
            '--skip-release-check' => true,
            '--check-profile' => 'scaffold',
            '--strict' => true,
            '--json' => true,
        ]);

        $this->assertSame(1, $exitCode);
        $this->assertSame('release_check', $payload['error']['stage']);
        $this->assertSame('warnings_are_fatal', $payload['error']['reason']);
        $this->assertSame(0, $payload['execution']['started']);
    }

    public function test_it_supports_empty_scope(): void
    {
        $emptyDirectory = base_path('database/seeders/Page_V3/Tests/CodexFeature/PageApplyEmpty');
        File::ensureDirectoryExists($emptyDirectory);
        $this->cleanupPaths[] = $emptyDirectory;

        [$exitCode, $payload] = $this->callApply('database/seeders/Page_V3/Tests/CodexFeature/PageApplyEmpty', [
            '--dry-run' => true,
            '--json' => true,
        ]);

        $this->assertSame(0, $exitCode);
        $this->assertNull($payload['error']);
        $this->assertSame(0, $payload['plan']['summary']['total_packages']);
        $this->assertSame([], $payload['execution']['packages']);
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array{0:int,1:array<string,mixed>}
     */
    private function callApply(string $target, array $arguments): array
    {
        $exitCode = Artisan::call('page-v3:apply-folder', array_merge([
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
                'id' => 'overview',
                'uuid_key' => 'overview',
                'type' => 'box',
                'column' => 'left',
                'body' => '<p>Page_V3 folder apply block.</p>',
                'tags' => ['Page Apply'],
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
                'reference' => 'overview',
                'body' => '<p>Localized Page_V3 folder apply block.</p>',
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
                'body' => '<p>Category folder apply block.</p>',
                'tags' => ['Category Apply'],
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
                'body' => '<p>Localized category folder apply block.</p>',
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
    private function breakPageDefinition(array $generated): void
    {
        $definitionPath = base_path((string) $generated['preview']['page_definition_relative_path']);
        $definition = json_decode(File::get($definitionPath), true, 512, JSON_THROW_ON_ERROR);
        $definition['page']['title'] = '';

        File::put(
            $definitionPath,
            json_encode($definition, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL
        );
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
