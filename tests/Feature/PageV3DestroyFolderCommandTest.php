<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\PageCategory;
use App\Models\TextBlock;
use App\Services\PageV3PromptGenerator\PageV3BlueprintService;
use App\Services\PageV3PromptGenerator\PageV3FolderDestroyService;
use App\Services\PageV3PromptGenerator\PageV3SkeletonWriterService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class PageV3DestroyFolderCommandTest extends TestCase
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

    public function test_it_resolves_supported_page_v3_single_package_targets(): void
    {
        $generated = $this->writeExistingPage(
            'Destroy Target Page',
            $this->existingCategoryContext('Tests\\CodexFeature\\PageDestroyTargets', 'page-destroy-targets-category')
        );

        $targets = [
            $generated['preview']['page_package_relative_path'],
            base_path($generated['preview']['page_definition_relative_path']),
            $generated['preview']['page_seeder_relative_path'],
            base_path($generated['preview']['page_real_seeder_relative_path']),
        ];

        foreach ($targets as $target) {
            [$exitCode, $payload] = $this->callDestroy($target, [
                '--dry-run' => true,
                '--json' => true,
            ]);

            $this->assertSame(0, $exitCode, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $this->assertTrue((bool) $payload['scope']['single_package']);
            $this->assertSame(1, $payload['plan']['summary']['total_packages']);
            $this->assertSame(1, $payload['plan']['summary']['destroy_candidates']);
            $this->assertSame($generated['preview']['page_package_relative_path'], $payload['plan']['packages'][0]['relative_path']);
        }
    }

    public function test_it_rejects_targets_outside_the_page_v3_root(): void
    {
        [$exitCode, $payload] = $this->callDestroy(base_path('composer.json'), [
            '--dry-run' => true,
            '--json' => true,
        ]);

        $this->assertSame(1, $exitCode);
        $this->assertSame('target_resolution', $payload['error']['stage']);
        $this->assertStringContainsString('database/seeders/Page_V3', $payload['error']['message']);
    }

    public function test_dry_run_preflights_page_v3_candidates_and_changes_nothing(): void
    {
        $generated = $this->writeNewCategoryPage(
            'Destroy Dry Run Page',
            'Tests\\CodexFeature\\PageDestroyDryRun',
            'Destroy Dry Run Category'
        );
        $this->makeCategoryDefinitionSeedable($generated);
        $this->makeCategoryLocalizationSeedable($generated);
        $this->makePageDefinitionSeedable($generated);
        $this->makePageLocalizationSeedable($generated);
        $this->seedCategoryPackageLive($generated);
        $this->seedPagePackageLive($generated);

        $initialPageCount = Page::query()->count();
        $initialCategoryCount = PageCategory::query()->count();
        $initialBlockCount = TextBlock::query()->count();
        $initialSeedRunCount = DB::table('seed_runs')->count();

        [$exitCode, $payload] = $this->callDestroy(dirname((string) $generated['preview']['category_seeder_relative_path']), [
            '--dry-run' => true,
            '--json' => true,
        ]);

        $this->assertSame(0, $exitCode, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $this->assertSame(2, $payload['plan']['summary']['destroy_candidates']);
        $this->assertSame(2, $payload['preflight']['summary']['candidates']);
        $this->assertSame(2, $payload['preflight']['summary']['db_candidates']);
        $this->assertSame(2, $payload['preflight']['summary']['file_candidates']);
        $this->assertTrue(File::exists(base_path($generated['preview']['page_seeder_relative_path'])));
        $this->assertTrue(File::exists(base_path($generated['preview']['category_seeder_relative_path'])));
        $this->assertSame($initialPageCount, Page::query()->count());
        $this->assertSame($initialCategoryCount, PageCategory::query()->count());
        $this->assertSame($initialBlockCount, TextBlock::query()->count());
        $this->assertSame($initialSeedRunCount, DB::table('seed_runs')->count());
    }

    public function test_live_page_v3_destroy_requires_force(): void
    {
        $generated = $this->writeExistingPage(
            'Destroy Force Page',
            $this->existingCategoryContext('Tests\\CodexFeature\\PageDestroyForce', 'page-destroy-force-category')
        );

        [$exitCode, $payload] = $this->callDestroy($generated['preview']['page_package_relative_path'], [
            '--json' => true,
        ]);

        $this->assertSame(1, $exitCode);
        $this->assertSame('force_required', $payload['error']['reason']);
        $this->assertTrue(File::exists(base_path($generated['preview']['page_seeder_relative_path'])));
        $this->assertTrue(File::isDirectory(base_path($generated['preview']['page_package_relative_path'])));
    }

    public function test_category_blocked_by_out_of_scope_page_dependents_fails_before_live_writes(): void
    {
        $generated = $this->writeNewCategoryPage(
            'Destroy Scoped Category Page',
            'Tests\\CodexFeature\\PageDestroyBlocked',
            'Destroy Scoped Category'
        );
        $this->makeCategoryDefinitionSeedable($generated);
        $this->makeCategoryLocalizationSeedable($generated);
        $this->makePageDefinitionSeedable($generated);
        $this->makePageLocalizationSeedable($generated);

        [$exitCode, $payload] = $this->callDestroy($generated['preview']['category_package_relative_path'], [
            '--force' => true,
            '--json' => true,
        ]);

        $this->assertSame(1, $exitCode);
        $this->assertSame('blocked_packages', $payload['error']['reason']);
        $this->assertSame(0, $payload['execution']['db_phase']['started']);
        $this->assertSame(0, $payload['execution']['file_phase']['started']);
        $this->assertTrue(File::exists(base_path($generated['preview']['category_seeder_relative_path'])));
        $this->assertTrue(File::exists(base_path($generated['preview']['page_seeder_relative_path'])));
    }

    public function test_successful_live_run_removes_in_scope_page_v3_db_seed_runs_and_files_only_inside_scope(): void
    {
        $generated = $this->writeNewCategoryPage(
            'Destroy Live Page',
            'Tests\\CodexFeature\\PageDestroyLive',
            'Destroy Live Category'
        );
        $scopeRoot = dirname((string) $generated['preview']['category_seeder_relative_path']);
        $this->makeCategoryDefinitionSeedable($generated);
        $this->makeCategoryLocalizationSeedable($generated);
        $this->makePageDefinitionSeedable($generated);
        $this->makePageLocalizationSeedable($generated);
        $this->seedCategoryPackageLive($generated);
        $this->seedPagePackageLive($generated);
        $outside = $this->writeExistingPage(
            'Destroy Outside Page',
            $this->existingCategoryContext('Tests\\CodexFeature\\PageDestroyOutside', 'page-destroy-outside-category')
        );
        $categorySeederClass = 'Database\\Seeders\\Page_V3\\'
            . trim((string) $generated['preview']['category_namespace'], '\\')
            . '\\'
            . (string) $generated['preview']['category_class_name'];

        [$exitCode, $payload] = $this->callDestroy($scopeRoot, [
            '--force' => true,
            '--remove-empty-dirs' => true,
            '--json' => true,
        ]);

        $this->assertSame(0, $exitCode, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $this->assertSame(2, $payload['execution']['db_phase']['succeeded']);
        $this->assertSame(2, $payload['execution']['file_phase']['succeeded']);
        $this->assertSame(0, Page::query()->where('seeder', $generated['preview']['page_fully_qualified_class_name'])->count());
        $this->assertSame(0, PageCategory::query()->where('seeder', $categorySeederClass)->count());
        $this->assertSame(0, DB::table('seed_runs')->where('class_name', $generated['preview']['page_fully_qualified_class_name'])->count());
        $this->assertSame(0, DB::table('seed_runs')->where('class_name', $categorySeederClass)->count());
        $this->assertFalse(File::exists(base_path($generated['preview']['page_seeder_relative_path'])));
        $this->assertFalse(File::isDirectory(base_path($generated['preview']['page_package_relative_path'])));
        $this->assertFalse(File::exists(base_path($generated['preview']['category_seeder_relative_path'])));
        $this->assertFalse(File::isDirectory(base_path($generated['preview']['category_package_relative_path'])));
        $this->assertFalse(File::isDirectory(base_path($scopeRoot)));
        $this->assertTrue(File::exists(base_path($outside['preview']['page_seeder_relative_path'])));
        $this->assertTrue(File::isDirectory(base_path($outside['preview']['page_package_relative_path'])));
    }

    public function test_strict_mode_fails_on_page_v3_destroy_warnings(): void
    {
        $generated = $this->writeExistingPage(
            'Destroy Strict Page',
            $this->existingCategoryContext('Tests\\CodexFeature\\PageDestroyStrict', 'page-destroy-strict-category')
        );

        [$exitCode, $payload] = $this->callDestroy($generated['preview']['page_package_relative_path'], [
            '--dry-run' => true,
            '--strict' => true,
            '--json' => true,
        ]);

        $this->assertSame(1, $exitCode);
        $this->assertSame('warnings_are_fatal', $payload['error']['reason']);
        $this->assertNotEmpty($payload['plan']['packages'][0]['warnings']);
        $this->assertTrue(File::isDirectory(base_path($generated['preview']['page_package_relative_path'])));
    }

    public function test_it_writes_a_report_and_supports_empty_scope(): void
    {
        Storage::fake('local');
        $emptyDirectory = base_path('database/seeders/Page_V3/Tests/CodexFeature/PageDestroyEmpty');
        File::ensureDirectoryExists($emptyDirectory);
        $this->cleanupPaths[] = $emptyDirectory;

        [$exitCode, $payload] = $this->callDestroy('database/seeders/Page_V3/Tests/CodexFeature/PageDestroyEmpty', [
            '--dry-run' => true,
            '--write-report' => true,
            '--json' => true,
        ]);

        $reportFiles = Storage::disk('local')->allFiles('folder-destroy-reports/page-v3');

        $this->assertSame(0, $exitCode);
        $this->assertNull($payload['error']);
        $this->assertSame(0, $payload['plan']['summary']['total_packages']);
        $this->assertCount(1, $reportFiles);
        $this->assertNotNull($payload['artifacts']['report_path']);
    }

    public function test_command_can_render_page_v3_db_phase_fail_fast_payloads(): void
    {
        $mock = Mockery::mock(PageV3FolderDestroyService::class);
        $mock->shouldReceive('run')
            ->once()
            ->andReturn($this->mockedResult('db_phase_failed', 'scope-target'));

        $this->app->instance(PageV3FolderDestroyService::class, $mock);

        [$exitCode, $payload] = $this->callDestroy('scope-target', [
            '--force' => true,
            '--json' => true,
        ]);

        $this->assertSame(1, $exitCode);
        $this->assertSame('db_phase_failed', $payload['error']['reason']);
        $this->assertSame(0, $payload['execution']['file_phase']['started']);
    }

    public function test_command_can_render_page_v3_file_phase_fail_fast_payloads(): void
    {
        $mock = Mockery::mock(PageV3FolderDestroyService::class);
        $mock->shouldReceive('run')
            ->once()
            ->andReturn($this->mockedResult('file_phase_failed', 'scope-target'));

        $this->app->instance(PageV3FolderDestroyService::class, $mock);

        [$exitCode, $payload] = $this->callDestroy('scope-target', [
            '--force' => true,
            '--json' => true,
        ]);

        $this->assertSame(1, $exitCode);
        $this->assertSame('file_phase_failed', $payload['error']['reason']);
        $this->assertSame('pending', $payload['execution']['file_phase']['packages'][2]['status']);
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array{0:int,1:array<string,mixed>}
     */
    private function callDestroy(string $target, array $arguments): array
    {
        $exitCode = Artisan::call('page-v3:destroy-folder', array_merge([
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
                'body' => '<p>Page destroy block.</p>',
                'tags' => ['Page Destroy'],
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
                'body' => '<p>Localized page destroy block.</p>',
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
                'body' => '<p>Category destroy block.</p>',
                'tags' => ['Category Destroy'],
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
                'body' => '<p>Localized category destroy block.</p>',
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

    /**
     * @return array<string, mixed>
     */
    private function mockedResult(string $reason, string $target): array
    {
        return [
            'scope' => [
                'input' => $target,
                'resolved_root_absolute_path' => base_path('database/seeders/Page_V3/Tests/CodexFeature/Mocked'),
                'resolved_root_relative_path' => 'database/seeders/Page_V3/Tests/CodexFeature/Mocked',
                'single_package' => false,
                'mode' => 'destroy',
                'strict' => false,
            ],
            'plan' => [
                'summary' => [
                    'total_packages' => 3,
                    'seed_candidates' => 0,
                    'refresh_candidates' => 0,
                    'unseed_candidates' => 0,
                    'destroy_files_candidates' => 0,
                    'destroy_candidates' => 3,
                    'skipped' => 0,
                    'blocked' => 0,
                    'warnings' => 0,
                ],
                'packages' => [
                    ['relative_path' => 'database/seeders/Page_V3/Tests/CodexFeature/Mocked/PageSeeder', 'recommended_action' => 'destroy', 'needs_unseed' => false, 'needs_file_destroy' => true],
                    ['relative_path' => 'database/seeders/Page_V3/Tests/CodexFeature/Mocked/CategorySeeder', 'recommended_action' => 'destroy', 'needs_unseed' => true, 'needs_file_destroy' => true],
                    ['relative_path' => 'database/seeders/Page_V3/Tests/CodexFeature/Mocked/LateSeeder', 'recommended_action' => 'destroy', 'needs_unseed' => false, 'needs_file_destroy' => true],
                ],
                'error' => null,
            ],
            'preflight' => [
                'executed' => true,
                'db_packages' => [],
                'file_packages' => [],
                'summary' => [
                    'candidates' => 3,
                    'db_candidates' => 1,
                    'file_candidates' => 3,
                    'ok' => 3,
                    'warn' => 0,
                    'fail' => 0,
                    'skipped' => 0,
                ],
            ],
            'execution' => [
                'dry_run' => false,
                'force' => true,
                'remove_empty_dirs' => false,
                'fail_fast' => true,
                'folder_transactional' => false,
                'db_phase' => [
                    'started' => 1,
                    'completed' => 1,
                    'succeeded' => $reason === 'file_phase_failed' ? 1 : 0,
                    'failed' => $reason === 'db_phase_failed' ? 1 : 0,
                    'stopped_on' => $reason === 'db_phase_failed' ? 'database/seeders/Page_V3/Tests/CodexFeature/Mocked/CategorySeeder' : null,
                    'packages' => [
                        ['relative_path' => 'database/seeders/Page_V3/Tests/CodexFeature/Mocked/PageSeeder', 'status' => 'skipped', 'seed_run_removed' => false],
                        ['relative_path' => 'database/seeders/Page_V3/Tests/CodexFeature/Mocked/CategorySeeder', 'status' => $reason === 'db_phase_failed' ? 'failed' : 'ok', 'seed_run_removed' => $reason !== 'db_phase_failed'],
                        ['relative_path' => 'database/seeders/Page_V3/Tests/CodexFeature/Mocked/LateSeeder', 'status' => 'skipped', 'seed_run_removed' => false],
                    ],
                ],
                'file_phase' => [
                    'started' => $reason === 'db_phase_failed' ? 0 : 2,
                    'completed' => $reason === 'file_phase_failed' ? 2 : ($reason === 'db_phase_failed' ? 0 : 2),
                    'succeeded' => $reason === 'file_phase_failed' ? 1 : ($reason === 'db_phase_failed' ? 0 : 2),
                    'failed' => $reason === 'file_phase_failed' ? 1 : 0,
                    'stopped_on' => $reason === 'file_phase_failed' ? 'database/seeders/Page_V3/Tests/CodexFeature/Mocked/CategorySeeder' : null,
                    'packages' => [
                        ['relative_path' => 'database/seeders/Page_V3/Tests/CodexFeature/Mocked/PageSeeder', 'status' => $reason === 'db_phase_failed' ? 'pending' : 'ok'],
                        ['relative_path' => 'database/seeders/Page_V3/Tests/CodexFeature/Mocked/CategorySeeder', 'status' => $reason === 'file_phase_failed' ? 'failed' : ($reason === 'db_phase_failed' ? 'pending' : 'ok')],
                        ['relative_path' => 'database/seeders/Page_V3/Tests/CodexFeature/Mocked/LateSeeder', 'status' => 'pending'],
                    ],
                ],
            ],
            'artifacts' => [
                'report_path' => null,
            ],
            'error' => [
                'stage' => 'execution',
                'reason' => $reason,
                'message' => $reason === 'db_phase_failed'
                    ? 'Folder destroy stopped during DB phase; file phase did not start.'
                    : 'Folder destroy stopped during file phase after DB phase completed.',
            ],
        ];
    }
}
