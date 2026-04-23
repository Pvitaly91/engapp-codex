<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\PageCategory;
use App\Models\TextBlock;
use App\Services\PageV3PromptGenerator\PageV3BlueprintService;
use App\Services\PageV3PromptGenerator\PageV3ChangedPackagesApplyService;
use App\Services\PageV3PromptGenerator\PageV3SkeletonWriterService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class PageV3ApplyChangedCommandTest extends TestCase
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

    public function test_it_preflights_untracked_page_v3_packages_with_category_before_page(): void
    {
        $generated = $this->writeNewCategoryPage(
            'Apply Changed Page',
            'Tests\\CodexFeature\\PageApplyChanged',
            'Apply Changed Category'
        );
        $this->makeCategoryDefinitionSeedable($generated);
        $this->makeCategoryLocalizationSeedable($generated);
        $this->makePageDefinitionSeedable($generated);
        $this->makePageLocalizationSeedable($generated);
        $pageCount = Page::query()->count();
        $categoryCount = PageCategory::query()->count();
        $blockCount = TextBlock::query()->count();
        $seedRunCount = DB::table('seed_runs')->count();

        [$exitCode, $payload] = $this->callApplyChanged(dirname((string) $generated['preview']['category_seeder_relative_path']), [
            '--include-untracked' => true,
            '--dry-run' => true,
            '--skip-release-check' => true,
            '--json' => true,
        ]);

        $this->assertSame(0, $exitCode, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $this->assertSame('working_tree', $payload['diff']['mode']);
        $this->assertSame(2, $payload['plan']['summary']['changed_packages']);
        $this->assertSame([
            $generated['preview']['category_package_relative_path'],
            $generated['preview']['page_package_relative_path'],
        ], array_column($payload['plan']['phases']['upsert_present'], 'current_relative_path'));
        $this->assertSame(2, $payload['preflight']['summary']['candidates']);
        $this->assertSame(0, $payload['execution']['cleanup_deleted']['started']);
        $this->assertSame(0, Page::query()->count() - $pageCount);
        $this->assertSame($categoryCount, PageCategory::query()->count());
        $this->assertSame($blockCount, TextBlock::query()->count());
        $this->assertSame($seedRunCount, DB::table('seed_runs')->count());
    }

    public function test_it_resolves_supported_page_v3_single_package_targets(): void
    {
        $generated = $this->writeNewCategoryPage(
            'Apply Changed Target Page',
            'Tests\\CodexFeature\\PageApplyChangedTarget',
            'Apply Changed Target Category'
        );
        $this->makePageDefinitionSeedable($generated);
        $targets = [
            $generated['preview']['page_package_relative_path'],
            base_path($generated['preview']['page_definition_relative_path']),
            $generated['preview']['page_seeder_relative_path'],
            base_path($generated['preview']['page_real_seeder_relative_path']),
        ];

        foreach ($targets as $target) {
            [$exitCode, $payload] = $this->callApplyChanged($target, [
                '--include-untracked' => true,
                '--dry-run' => true,
                '--skip-release-check' => true,
                '--json' => true,
            ]);

            $this->assertSame(0, $exitCode, $target . PHP_EOL . json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $this->assertTrue((bool) $payload['scope']['single_package'], $target);
            $this->assertSame(1, $payload['plan']['summary']['changed_packages'], $target);
            $this->assertSame($generated['preview']['page_package_relative_path'], $payload['plan']['packages'][0]['current_relative_path'], $target);
        }
    }

    public function test_it_rejects_targets_outside_the_page_v3_root(): void
    {
        [$exitCode, $payload] = $this->callApplyChanged(base_path('composer.json'), [
            '--dry-run' => true,
            '--json' => true,
        ]);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('database/seeders/Page_V3', $payload['error']['message']);
    }

    public function test_live_apply_requires_force(): void
    {
        $generated = $this->writeExistingPage(
            'Apply Changed Force Page',
            $this->existingCategoryContext('Tests\\CodexFeature\\PageApplyChangedForce', 'page-apply-changed-force-category')
        );
        $this->makePageDefinitionSeedable($generated);

        [$exitCode, $payload] = $this->callApplyChanged($generated['preview']['page_package_relative_path'], [
            '--include-untracked' => true,
            '--skip-release-check' => true,
            '--json' => true,
        ]);

        $this->assertSame(1, $exitCode);
        $this->assertSame('force_required', $payload['error']['reason']);
        $this->assertSame(0, Page::query()->count());
        $this->assertSame(0, DB::table('seed_runs')->count());
    }

    public function test_live_apply_seeds_new_category_before_page(): void
    {
        $generated = $this->writeNewCategoryPage(
            'Apply Changed Live Page',
            'Tests\\CodexFeature\\PageApplyChangedLive',
            'Apply Changed Live Category'
        );
        $this->makeCategoryDefinitionSeedable($generated);
        $this->makeCategoryLocalizationSeedable($generated);
        $this->makePageDefinitionSeedable($generated);
        $this->makePageLocalizationSeedable($generated);

        [$exitCode, $payload] = $this->callApplyChanged(dirname((string) $generated['preview']['category_seeder_relative_path']), [
            '--include-untracked' => true,
            '--force' => true,
            '--skip-release-check' => true,
            '--json' => true,
        ]);

        $this->assertSame(0, $exitCode, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $this->assertSame(2, $payload['execution']['upsert_present']['started']);
        $this->assertSame($generated['preview']['category_package_relative_path'], $payload['execution']['upsert_present']['packages'][0]['relative_path']);
        $this->assertSame($generated['preview']['page_package_relative_path'], $payload['execution']['upsert_present']['packages'][1]['relative_path']);
        $this->assertSame(1, PageCategory::query()->where('seeder', $this->fullyQualifiedSeederClass((string) $generated['preview']['category_seeder_relative_path']))->count());
        $this->assertSame(1, Page::query()->where('seeder', $this->fullyQualifiedSeederClass((string) $generated['preview']['page_seeder_relative_path']))->count());
    }

    public function test_with_release_check_and_skip_release_check_are_reported(): void
    {
        $generated = $this->writeExistingPage(
            'Apply Changed Release Page',
            $this->existingCategoryContext('Tests\\CodexFeature\\PageApplyChangedRelease', 'page-apply-changed-release-category')
        );
        $this->makePageDefinitionSeedable($generated);

        [$exitCode, $payload] = $this->callApplyChanged($generated['preview']['page_package_relative_path'], [
            '--include-untracked' => true,
            '--dry-run' => true,
            '--with-release-check' => true,
            '--skip-release-check' => true,
            '--check-profile' => 'scaffold',
            '--json' => true,
        ]);

        $this->assertSame(0, $exitCode);
        $this->assertTrue((bool) $payload['scope']['with_release_check']);
        $this->assertTrue((bool) $payload['scope']['skip_release_check']);
        $this->assertTrue((bool) $payload['plan']['packages'][0]['release_check']['executed']);
        $this->assertTrue((bool) $payload['preflight']['packages'][0]['service_result']['mode']['release_check_skipped']);
    }

    public function test_it_writes_a_report_and_supports_empty_plan_behavior(): void
    {
        Storage::fake('local');
        $emptyDirectory = base_path('database/seeders/Page_V3/Tests/CodexFeature/PageApplyChangedEmpty');
        File::ensureDirectoryExists($emptyDirectory);
        $this->cleanupPaths[] = $emptyDirectory;

        [$exitCode, $payload] = $this->callApplyChanged('database/seeders/Page_V3/Tests/CodexFeature/PageApplyChangedEmpty', [
            '--dry-run' => true,
            '--write-report' => true,
            '--json' => true,
        ]);

        $this->assertSame(0, $exitCode);
        $this->assertNull($payload['error']);
        $this->assertSame(0, $payload['plan']['summary']['changed_packages']);
        $this->assertCount(1, Storage::disk('local')->allFiles('changed-package-apply-reports/page-v3'));
        $this->assertNotNull($payload['artifacts']['report_path']);
    }

    public function test_command_passes_staged_and_ref_modes_to_service(): void
    {
        $mock = Mockery::mock(PageV3ChangedPackagesApplyService::class);
        $mock->shouldReceive('run')
            ->once()
            ->with(null, Mockery::on(fn (array $options): bool => ($options['staged'] ?? false) === true))
            ->andReturn($this->mockedResult());
        $mock->shouldReceive('run')
            ->once()
            ->with(null, Mockery::on(fn (array $options): bool => ($options['base'] ?? null) === 'HEAD~1' && ($options['head'] ?? null) === 'HEAD'))
            ->andReturn($this->mockedResult());

        $this->app->instance(PageV3ChangedPackagesApplyService::class, $mock);

        [$firstExitCode] = $this->callApplyChanged(null, [
            '--staged' => true,
            '--json' => true,
        ]);
        [$secondExitCode] = $this->callApplyChanged(null, [
            '--base' => 'HEAD~1',
            '--head' => 'HEAD',
            '--json' => true,
        ]);

        $this->assertSame(0, $firstExitCode);
        $this->assertSame(0, $secondExitCode);
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array{0:int,1:array<string,mixed>}
     */
    private function callApplyChanged(?string $target, array $arguments): array
    {
        $payloadArguments = $arguments;

        if ($target !== null) {
            $payloadArguments['target'] = $target;
        }

        $exitCode = Artisan::call('page-v3:apply-changed', $payloadArguments);

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
                'body' => '<p>Page apply-changed block.</p>',
                'tags' => ['Page Apply Changed'],
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
                'body' => '<p>Localized page apply-changed block.</p>',
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
                'body' => '<p>Category apply-changed block.</p>',
                'tags' => ['Category Apply Changed'],
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
                'body' => '<p>Localized category apply-changed block.</p>',
            ],
        ];

        File::put(
            $localizationPath,
            json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function mockedResult(): array
    {
        return [
            'diff' => [
                'mode' => 'working_tree',
                'base' => null,
                'head' => 'HEAD',
                'include_untracked' => false,
            ],
            'scope' => [
                'input' => 'database/seeders/Page_V3',
                'resolved_root_absolute_path' => base_path('database/seeders/Page_V3'),
                'resolved_root_relative_path' => 'database/seeders/Page_V3',
                'single_package' => false,
                'with_release_check' => false,
                'skip_release_check' => false,
                'check_profile' => 'release',
                'strict' => false,
            ],
            'plan' => [
                'summary' => [
                    'changed_packages' => 0,
                    'seed_candidates' => 0,
                    'refresh_candidates' => 0,
                    'deleted_cleanup_candidates' => 0,
                    'skipped' => 0,
                    'blocked' => 0,
                    'warnings' => 0,
                ],
                'phases' => [
                    'cleanup_deleted' => [],
                    'upsert_present' => [],
                ],
                'packages' => [],
                'error' => null,
            ],
            'preflight' => [
                'executed' => true,
                'summary' => [
                    'candidates' => 0,
                    'ok' => 0,
                    'warn' => 0,
                    'fail' => 0,
                    'skipped' => 0,
                ],
                'packages' => [],
            ],
            'execution' => [
                'dry_run' => true,
                'force' => false,
                'fail_fast' => true,
                'folder_transactional' => false,
                'cleanup_deleted' => [
                    'started' => 0,
                    'completed' => 0,
                    'succeeded' => 0,
                    'failed' => 0,
                    'stopped_on' => null,
                    'packages' => [],
                ],
                'upsert_present' => [
                    'started' => 0,
                    'completed' => 0,
                    'succeeded' => 0,
                    'failed' => 0,
                    'stopped_on' => null,
                    'packages' => [],
                ],
            ],
            'artifacts' => [
                'report_path' => null,
            ],
            'error' => null,
        ];
    }

    private function fullyQualifiedSeederClass(string $seederRelativePath): string
    {
        $relative = str_replace('\\', '/', $seederRelativePath);
        $relative = preg_replace('#^database/seeders/Page_V3/#', '', $relative) ?? $relative;
        $relative = preg_replace('#\.php$#', '', $relative) ?? $relative;

        return 'Database\\Seeders\\Page_V3\\' . str_replace('/', '\\', trim($relative, '/'));
    }
}
