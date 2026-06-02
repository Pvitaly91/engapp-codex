<?php

namespace Tests\Feature\AdminFlows;

use App\Models\Category;
use App\Models\Page;
use App\Models\PageCategory;
use App\Models\Question;
use App\Models\SavedGrammarTest;
use App\Models\SiteTreeItem;
use App\Models\SiteTreeVariant;
use App\Models\Source;
use App\Models\Tag;
use App\Models\Test;
use App\Modules\GitDeployment\Models\BackupBranch;
use App\Modules\GitDeployment\Models\BranchUsageHistory;
use App\Services\GrammarTestFilterService;
use App\Services\QuestionDeletionService;
use App\Services\QuestionTechnicalInfoService;
use App\Services\QuestionVariantService;
use App\Services\SavedTestResolver;
use App\Services\SeederPromptTheoryPageResolver;
use App\Services\SeederTestTargetResolver;
use App\Services\TagAggregationService;
use App\Services\TheoryBlockMatcherService;
use App\Support\Database\JsonPageLocalizationManager;
use App\Support\Database\JsonTestLocalizationManager;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Testing\TestResponse;
use Mockery;
use Tests\Support\AdminRouteMatrix;
use Tests\TestCase;

abstract class SeededAdminFlowTestCase extends TestCase
{
    private static bool $fixtureBootstrapped = false;

    private static bool $compiledViewsRefreshed = false;

    private static ?string $databasePath = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->usePersistentSqliteDatabase();
        $this->prepareCompiledViews();
        $this->bindServiceMocks();

        config([
            'app.url' => 'http://localhost',
            'coming-soon.enabled' => false,
            'tests.tech_info_enabled' => false,
            'admin.username' => AdminRouteMatrix::ADMIN_USERNAME,
            'admin.password_hash' => password_hash(AdminRouteMatrix::ADMIN_PASSWORD, PASSWORD_BCRYPT),
        ]);

        if (! self::$fixtureBootstrapped) {
            $this->rebuildMinimalSchema();
            $this->seedAdminFixture();
            self::$fixtureBootstrapped = true;
        }
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    protected function adminSession(): array
    {
        return ['admin_authenticated' => true];
    }

    protected function assertRedirectsToLogin(TestResponse $response, string $path): void
    {
        $this->assertTrue(
            $response->isRedirect(route('login.show')),
            sprintf('Expected [%s] to redirect to the admin login form.', $path)
        );
    }

    protected function assertOkPageWithMarkers(TestResponse $response, array $markers): string
    {
        $response->assertOk();

        $html = $response->getContent();

        $this->assertNoRawTranslationKeys($html);

        foreach ($markers as $marker) {
            $response->assertSeeText($marker);
        }

        return $html;
    }

    protected function assertNoRawTranslationKeys(string $html): void
    {
        $this->assertDoesNotMatchRegularExpression(
            '/(?:public|tests)\.[A-Za-z0-9_.-]+/',
            $html
        );
    }

    private function usePersistentSqliteDatabase(): void
    {
        $directory = storage_path('framework/testing');

        if (! is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        self::$databasePath ??= $directory . DIRECTORY_SEPARATOR . sprintf(
            'admin-flow-suite-%s.sqlite',
            getmypid() ?: uniqid('process-', true)
        );

        if (! file_exists(self::$databasePath)) {
            touch(self::$databasePath);
        }

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => self::$databasePath,
            'database.connections.sqlite.foreign_key_constraints' => true,
        ]);

        DB::purge('sqlite');
        DB::reconnect('sqlite');
    }

    private function prepareCompiledViews(): void
    {
        $defaultViewsPath = storage_path('framework/views');
        $viewsPath = storage_path('framework/views-admin-flow-tests');

        foreach ([$defaultViewsPath, $viewsPath] as $path) {
            if (! is_dir($path)) {
                mkdir($path, 0777, true);
            }
        }

        config(['view.compiled' => $viewsPath]);

        if (! self::$compiledViewsRefreshed) {
            $this->flushCompiledViews($defaultViewsPath);
            $this->flushCompiledViews($viewsPath);

            self::$compiledViewsRefreshed = true;
        }
    }

    private function flushCompiledViews(string $path): void
    {
        foreach (glob($path . DIRECTORY_SEPARATOR . '*.php') ?: [] as $compiledView) {
            @unlink($compiledView);
        }
    }

    private function bindServiceMocks(): void
    {
        $questionVariantService = Mockery::mock(QuestionVariantService::class);
        $questionVariantService->shouldIgnoreMissing();
        $questionVariantService->shouldReceive('supportsVariants')->andReturn(false);
        $this->app->instance(QuestionVariantService::class, $questionVariantService);

        $savedTestResolver = Mockery::mock(SavedTestResolver::class);
        $savedTestResolver->shouldIgnoreMissing();
        $this->app->instance(SavedTestResolver::class, $savedTestResolver);

        $filterService = Mockery::mock(GrammarTestFilterService::class);
        $filterService->shouldIgnoreMissing();
        $filterService->shouldReceive('seederSourceGroups')->andReturn(collect([
            [
                'seeder' => AdminRouteMatrix::SEEDER_CLASS,
                'sources' => [
                    ['id' => 1],
                ],
            ],
        ]));
        $filterService->shouldReceive('normalize')->andReturnUsing(fn (array $filters) => $filters);
        $filterService->shouldReceive('questionsFromFilters')->andReturn(collect());
        $this->app->instance(GrammarTestFilterService::class, $filterService);

        $questionDeletionService = Mockery::mock(QuestionDeletionService::class);
        $questionDeletionService->shouldIgnoreMissing();
        $this->app->instance(QuestionDeletionService::class, $questionDeletionService);

        $questionTechnicalInfoService = Mockery::mock(QuestionTechnicalInfoService::class);
        $questionTechnicalInfoService->shouldIgnoreMissing();
        $this->app->instance(QuestionTechnicalInfoService::class, $questionTechnicalInfoService);

        $tagAggregationService = Mockery::mock(TagAggregationService::class);
        $tagAggregationService->shouldIgnoreMissing();
        $tagAggregationService->shouldReceive('getAggregations')->andReturn([
            [
                'main_tag' => AdminRouteMatrix::TAG_NAME,
                'similar_tags' => [],
                'category' => AdminRouteMatrix::TAG_CATEGORY,
            ],
        ]);
        $this->app->instance(TagAggregationService::class, $tagAggregationService);

        $theoryBlockMatcherService = Mockery::mock(TheoryBlockMatcherService::class);
        $theoryBlockMatcherService->shouldIgnoreMissing();
        $this->app->instance(TheoryBlockMatcherService::class, $theoryBlockMatcherService);

        $promptTheoryResolver = Mockery::mock(SeederPromptTheoryPageResolver::class);
        $promptTheoryResolver->shouldIgnoreMissing();
        $promptTheoryResolver->shouldReceive('resolveForSeeders')->andReturn(collect());
        $this->app->instance(SeederPromptTheoryPageResolver::class, $promptTheoryResolver);

        $testTargetResolver = Mockery::mock(SeederTestTargetResolver::class);
        $testTargetResolver->shouldIgnoreMissing();
        $testTargetResolver->shouldReceive('resolveForSeeders')->andReturn(collect());
        $this->app->instance(SeederTestTargetResolver::class, $testTargetResolver);

        $jsonTestLocalizationManager = Mockery::mock(JsonTestLocalizationManager::class);
        $jsonTestLocalizationManager->shouldIgnoreMissing();
        $jsonTestLocalizationManager->shouldReceive('isVirtualLocalizationSeeder')->andReturn(false);
        $jsonTestLocalizationManager->shouldReceive('virtualSeederClasses')->andReturn([]);
        $this->app->instance(JsonTestLocalizationManager::class, $jsonTestLocalizationManager);

        $jsonPageLocalizationManager = Mockery::mock(JsonPageLocalizationManager::class);
        $jsonPageLocalizationManager->shouldIgnoreMissing();
        $jsonPageLocalizationManager->shouldReceive('isVirtualLocalizationSeeder')->andReturn(false);
        $jsonPageLocalizationManager->shouldReceive('virtualSeederClasses')->andReturn([]);
        $this->app->instance(JsonPageLocalizationManager::class, $jsonPageLocalizationManager);
    }

    private function rebuildMinimalSchema(): void
    {
        Schema::disableForeignKeyConstraints();

        foreach ([
            'seed_runs',
            'saved_grammar_test_questions',
            'saved_grammar_tests',
            'tests',
            'question_tag',
            'tag_word',
            'questions',
            'sources',
            'categories',
            'page_tag',
            'page_category_tag',
            'words',
            'tags',
            'pages',
            'page_categories',
            'site_tree_items',
            'site_tree_variants',
            'branch_usage_history',
            'backup_branches',
        ] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::enableForeignKeyConstraints();

        Schema::create('backup_branches', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('commit_hash')->nullable();
            $table->timestamp('pushed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('branch_usage_history', function (Blueprint $table) {
            $table->id();
            $table->string('branch_name');
            $table->string('action');
            $table->text('description')->nullable();
            $table->json('paths')->nullable();
            $table->timestamp('used_at')->nullable();
            $table->timestamps();
        });

        Schema::create('site_tree_variants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->boolean('is_base')->default(false);
            $table->timestamps();
        });

        Schema::create('site_tree_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedBigInteger('variant_id');
            $table->string('title');
            $table->string('linked_page_title')->nullable();
            $table->string('linked_page_url')->nullable();
            $table->string('link_method')->nullable();
            $table->string('level')->nullable();
            $table->boolean('is_checked')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('page_categories', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('language', 8)->nullable();
            $table->string('type')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('seeder')->nullable();
            $table->timestamps();
        });

        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('page_category_id')->nullable();
            $table->string('slug');
            $table->string('title');
            $table->text('text')->nullable();
            $table->string('type')->nullable();
            $table->string('seeder')->nullable();
            $table->timestamps();
        });

        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category')->nullable();
            $table->timestamps();
        });

        Schema::create('words', function (Blueprint $table) {
            $table->id();
            $table->string('word');
            $table->timestamps();
        });

        Schema::create('page_tag', function (Blueprint $table) {
            $table->unsignedBigInteger('page_id');
            $table->unsignedBigInteger('tag_id');
            $table->unique(['page_id', 'tag_id']);
        });

        Schema::create('page_category_tag', function (Blueprint $table) {
            $table->unsignedBigInteger('page_category_id');
            $table->unsignedBigInteger('tag_id');
            $table->unique(['page_category_id', 'tag_id']);
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('sources', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->text('question');
            $table->unsignedTinyInteger('difficulty')->default(1);
            $table->string('level', 2)->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->unsignedTinyInteger('flag')->default(0);
            $table->string('type')->nullable();
            $table->json('options_by_marker')->nullable();
            $table->string('theory_text_block_uuid', 36)->nullable();
            $table->string('seeder')->nullable();
            $table->timestamps();
        });

        Schema::create('question_tag', function (Blueprint $table) {
            $table->unsignedBigInteger('question_id');
            $table->unsignedBigInteger('tag_id');
            $table->unique(['question_id', 'tag_id']);
        });

        Schema::create('tag_word', function (Blueprint $table) {
            $table->unsignedBigInteger('tag_id');
            $table->unsignedBigInteger('word_id');
            $table->unique(['tag_id', 'word_id']);
        });

        Schema::create('tests', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->json('filters')->nullable();
            $table->json('questions')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('saved_grammar_tests', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->json('filters')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('saved_grammar_test_questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('saved_grammar_test_id');
            $table->uuid('question_uuid');
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
            $table->unique(
                ['saved_grammar_test_id', 'question_uuid'],
                'saved_grammar_test_questions_unique'
            );
        });

        Schema::create('seed_runs', function (Blueprint $table) {
            $table->id();
            $table->string('class_name');
            $table->timestamp('ran_at')->nullable();
            $table->timestamps();
        });
    }

    private function seedAdminFixture(): void
    {
        $tag = Tag::create([
            'name' => AdminRouteMatrix::TAG_NAME,
            'category' => AdminRouteMatrix::TAG_CATEGORY,
        ]);

        $pageCategory = PageCategory::create([
            'title' => AdminRouteMatrix::PAGE_CATEGORY_TITLE,
            'slug' => AdminRouteMatrix::PAGE_CATEGORY_SLUG,
            'language' => 'uk',
            'type' => 'theory',
            'seeder' => AdminRouteMatrix::SEEDER_CLASS,
        ]);

        $page = Page::create([
            'page_category_id' => $pageCategory->id,
            'slug' => AdminRouteMatrix::PAGE_SLUG,
            'title' => AdminRouteMatrix::PAGE_TITLE,
            'text' => 'Admin smoke theory content.',
            'type' => 'theory',
            'seeder' => AdminRouteMatrix::SEEDER_CLASS,
        ]);

        $page->tags()->attach($tag->id);
        $pageCategory->tags()->attach($tag->id);

        $category = Category::create([
            'name' => AdminRouteMatrix::QUESTION_CATEGORY,
        ]);

        $source = Source::create([
            'name' => AdminRouteMatrix::SOURCE_NAME,
        ]);

        $question = Question::create([
            'uuid' => AdminRouteMatrix::QUESTION_UUID,
            'question' => 'I {a1} grammar every morning.',
            'difficulty' => 2,
            'level' => 'A1',
            'category_id' => $category->id,
            'source_id' => $source->id,
            'seeder' => AdminRouteMatrix::SEEDER_CLASS,
        ]);

        $question->tags()->attach($tag->id);

        Test::create([
            'name' => AdminRouteMatrix::LEGACY_TEST_NAME,
            'slug' => AdminRouteMatrix::LEGACY_TEST_SLUG,
            'filters' => [],
            'questions' => [$question->id],
            'description' => 'Legacy admin smoke test.',
        ]);

        $savedTest = SavedGrammarTest::create([
            'uuid' => AdminRouteMatrix::SAVED_TEST_UUID,
            'name' => AdminRouteMatrix::SAVED_TEST_NAME,
            'slug' => AdminRouteMatrix::SAVED_TEST_SLUG,
            'filters' => [],
            'description' => 'Saved admin smoke test.',
        ]);

        $savedTest->questionLinks()->create([
            'question_uuid' => $question->uuid,
            'position' => 0,
        ]);

        $baseVariant = SiteTreeVariant::create([
            'name' => 'Base',
            'slug' => 'base',
            'is_base' => true,
        ]);

        $rootItem = SiteTreeItem::create([
            'variant_id' => $baseVariant->id,
            'title' => AdminRouteMatrix::SITE_TREE_ROOT,
            'linked_page_title' => $pageCategory->title,
            'linked_page_url' => '/theory/' . AdminRouteMatrix::PAGE_CATEGORY_SLUG,
            'link_method' => 'manual',
            'level' => 'A1',
            'is_checked' => true,
            'sort_order' => 0,
        ]);

        SiteTreeItem::create([
            'parent_id' => $rootItem->id,
            'variant_id' => $baseVariant->id,
            'title' => AdminRouteMatrix::SITE_TREE_CHILD,
            'linked_page_title' => $page->title,
            'linked_page_url' => '/theory/' . AdminRouteMatrix::PAGE_CATEGORY_SLUG . '/' . AdminRouteMatrix::PAGE_SLUG,
            'link_method' => 'manual',
            'level' => 'A1',
            'is_checked' => true,
            'sort_order' => 0,
        ]);

        BackupBranch::create([
            'name' => 'backup/admin-smoke',
            'commit_hash' => '1234567890abcdef1234567890abcdef12345678',
            'pushed_at' => Carbon::now()->subHours(2),
        ]);

        BranchUsageHistory::create([
            'branch_name' => 'main',
            'action' => 'deploy',
            'description' => 'Admin smoke deployment history entry.',
            'paths' => ['routes/admin.php'],
            'used_at' => Carbon::now()->subHour(),
        ]);

        DB::table('seed_runs')->insert([
            'class_name' => AdminRouteMatrix::SEEDER_CLASS,
            'ran_at' => Carbon::now()->subMinutes(30),
            'created_at' => Carbon::now()->subMinutes(30),
            'updated_at' => Carbon::now()->subMinutes(30),
        ]);
    }
}
