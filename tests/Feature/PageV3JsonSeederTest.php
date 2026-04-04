<?php

namespace Tests\Feature;

use App\Http\Controllers\SeedRunController;
use App\Http\Controllers\TheoryController;
use App\Models\Page;
use App\Models\PageCategory;
use App\Models\TextBlock;
use App\Services\QuestionDeletionService;
use App\Services\SeederTestTargetResolver;
use App\Support\Database\JsonPageDirectorySeeder;
use App\Support\Database\JsonPageLocalizationManager;
use App\Support\Database\JsonPageRuntimeSeeder;
use App\Support\Database\JsonTestLocalizationManager;
use Database\Seeders\Page_V3\Adjectives\AdjectivesCategorySeeder;
use Database\Seeders\Page_V3\Adjectives\AdjectivesComparativeVsSuperlativeTheorySeeder;
use Database\Seeders\Page_V3\BasicGrammar\BasicGrammarCategorySeeder;
use Database\Seeders\Page_V3\BasicGrammar\BasicGrammarSentenceTypesTheorySeeder;
use Database\Seeders\Page_V3\BasicGrammar\WordOrder\BasicWordOrderTheorySeeder;
use Database\Seeders\Page_V3\BasicGrammar\WordOrder\WordOrderCategorySeeder;
use Database\Seeders\Page_V3\BasicGrammar\WordOrder\WordOrderVerbsObjectsTheorySeeder;
use Database\Seeders\Page_V3\PassiveVoice\PassiveVoiceCategorySeeder;
use Database\Seeders\Page_V3\PassiveVoice\Basics\PassiveVoiceModalVerbsTheorySeeder;
use Database\Seeders\Page_V3\PassiveVoice\Tenses\PassiveVoicePresentSimpleTheorySeeder;
use Database\Seeders\Page_V3\PassiveVoice\Tenses\PassiveVoiceTensesCategorySeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PageV3JsonSeederTest extends TestCase
{
    private string $tempSeederPackageDirectory;

    private string $tempLocalizationRoot;

    protected function setUp(): void
    {
        parent::setUp();

        $this->rebuildMinimalSchema();

        $this->tempSeederPackageDirectory = database_path('seeders/Page_V3/_tmp_tests/JsonOnlyPageSeeder');
        $this->tempLocalizationRoot = $this->tempSeederPackageDirectory . DIRECTORY_SEPARATOR . 'localizations';
    }

    protected function tearDown(): void
    {
        $tempRoot = dirname($this->tempSeederPackageDirectory);

        if (File::isDirectory($tempRoot)) {
            File::deleteDirectory($tempRoot);
        }

        if (File::isDirectory($this->tempLocalizationRoot)) {
            File::deleteDirectory($this->tempLocalizationRoot);
        }

        parent::tearDown();
    }

    private function rebuildMinimalSchema(): void
    {
        Schema::disableForeignKeyConstraints();

        foreach ([
            'seed_runs',
            'site_tree_items',
            'site_tree_variants',
            'tag_text_block',
            'page_category_tag',
            'page_tag',
            'text_blocks',
            'pages',
            'page_categories',
            'questions',
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

        Schema::create('site_tree_variants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->boolean('is_base')->default(false);
            $table->timestamps();
        });

        Schema::create('site_tree_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('variant_id')->constrained('site_tree_variants')->cascadeOnDelete();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('title');
            $table->string('linked_page_title')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->string('seeder')->nullable();
        });

        Schema::create('seed_runs', function (Blueprint $table) {
            $table->id();
            $table->string('class_name')->unique();
            $table->timestamp('ran_at')->nullable();
        });
    }

    private function makeTheoryControllerForLocalizedTitleTests(): TheoryController
    {
        $autoGeneratedTestService = \Mockery::mock(\App\Services\AutoGeneratedTestService::class);
        $autoGeneratedTestService
            ->shouldReceive('generateTests')
            ->andReturn(collect());

        $promptLinkedTestsService = \Mockery::mock(\App\Services\TheoryPagePromptLinkedTestsService::class);
        $promptLinkedTestsService
            ->shouldReceive('buildForPage')
            ->andReturn(collect());

        $textBlockMatcher = \Mockery::mock(\App\Services\Theory\TextBlockToQuestionsMatcherService::class);
        $textBlockMatcher
            ->shouldReceive('findQuestionsForTextBlocks')
            ->andReturn([]);

        return new class($autoGeneratedTestService, $promptLinkedTestsService, $textBlockMatcher) extends TheoryController
        {
            protected function categoryList(): \Illuminate\Support\Collection
            {
                return PageCategory::query()
                    ->whereNull('parent_id')
                    ->where('type', 'theory')
                    ->with([
                        'pages' => fn ($query) => $this->applyPageTypeFilter($query)->orderBy('title'),
                        'children' => function ($query) {
                            $this->applyCategoryChildrenRelations($query);
                        },
                    ])
                    ->withCount([
                        'pages' => fn ($query) => $this->applyPageTypeFilter($query),
                    ])
                    ->orderBy('title')
                    ->get();
            }

            protected function getRelatedTestsWithMetadata(array $tagIds, \Illuminate\Support\Collection $tags): \Illuminate\Support\Collection
            {
                return collect();
            }
        };
    }

    public function test_passive_voice_category_page_v3_seeder_creates_localized_blocks(): void
    {
        (new PassiveVoiceCategorySeeder())->__invoke();

        $category = PageCategory::query()->where('slug', 'passive-voice')->first();

        $this->assertNotNull($category);
        $this->assertSame(PassiveVoiceCategorySeeder::class, $category->seeder);
        $this->assertSame('uk', $category->language);

        $this->assertSame(5, TextBlock::query()->where('page_category_id', $category->id)->where('locale', 'uk')->count());
        $this->assertSame(5, TextBlock::query()->where('page_category_id', $category->id)->where('locale', 'en')->count());
        $this->assertSame(5, TextBlock::query()->where('page_category_id', $category->id)->where('locale', 'pl')->count());

        $this->assertDatabaseHas('seed_runs', [
            'class_name' => PassiveVoiceCategorySeeder::class,
        ]);

        $this->assertSame(
            5,
            TextBlock::query()
                ->where('page_category_id', $category->id)
                ->where('locale', 'en')
                ->where('seeder', 'Database\\Seeders\\Page_V3\\Localizations\\En\\PassiveVoiceCategoryLocalizationSeeder')
                ->count()
        );

        $this->assertSame(
            5,
            TextBlock::query()
                ->where('page_category_id', $category->id)
                ->where('locale', 'pl')
                ->where('seeder', 'Database\\Seeders\\Page_V3\\Localizations\\Pl\\PassiveVoiceCategoryLocalizationSeeder')
                ->count()
        );
    }

    public function test_json_page_runtime_seeder_supports_page_definition_and_virtual_localization_seeders(): void
    {
        File::ensureDirectoryExists($this->tempSeederPackageDirectory);
        File::ensureDirectoryExists($this->tempLocalizationRoot);

        $definitionPath = $this->tempSeederPackageDirectory . DIRECTORY_SEPARATOR . 'definition.json';
        $localizationPath = $this->tempLocalizationRoot . DIRECTORY_SEPARATOR . 'en.json';
        $baseSeederClass = 'Database\\Seeders\\Page_V3\\Generated\\Tmp\\JsonOnlyPageSeeder';
        $localizationSeederClass = 'Database\\Seeders\\Page_V3\\Localizations\\En\\JsonOnlyPageLocalizationSeeder';

        File::put($definitionPath, json_encode([
            'schema_version' => 1,
            'content_type' => 'page',
            'slug' => 'json-only-page-v3',
            'type' => 'theory',
            'seeder' => [
                'class' => $baseSeederClass,
            ],
            'page' => [
                'title' => 'JSON Only Page',
                'subtitle_html' => '<p><strong>JSON Only Page</strong> subtitle.</p>',
                'subtitle_text' => 'JSON only subtitle text.',
                'locale' => 'uk',
                'category' => [
                    'slug' => 'json-only-category',
                    'title' => 'JSON Only Category',
                    'language' => 'uk',
                    'type' => 'theory',
                ],
                'tags' => [
                    'JSON Page',
                    'Theory'
                ],
                'base_tags' => [
                    'JSON Page'
                ],
                'blocks' => [
                    [
                        'id' => 'overview',
                        'uuid_key' => 'overview',
                        'type' => 'forms-grid',
                        'column' => 'left',
                        'level' => 'A2',
                        'body' => json_encode([
                            'title' => 'Огляд',
                            'items' => [
                                ['label' => '1', 'title' => 'Перший блок', 'subtitle' => 'Опис першого блоку.'],
                            ],
                        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                        'tags' => ['Overview Block'],
                    ],
                ],
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL);

        File::put($localizationPath, json_encode([
            'locale' => 'en',
            'seeder' => [
                'class' => $localizationSeederClass,
            ],
            'target' => [
                'definition_path' => $definitionPath,
                'seeder_class' => $baseSeederClass,
            ],
            'blocks' => [
                [
                    'reference' => 'subtitle',
                    'body' => '<p><strong>JSON Only Page</strong> subtitle in English.</p>',
                ],
                [
                    'reference' => 'overview',
                    'body' => json_encode([
                        'title' => 'Overview',
                        'items' => [
                            ['label' => '1', 'title' => 'First block', 'subtitle' => 'Description of the first block.'],
                        ],
                    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                ],
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL);

        $controller = new class(
            app(QuestionDeletionService::class),
            app(\App\Services\SeederPromptTheoryPageResolver::class),
            app(SeederTestTargetResolver::class),
            app(JsonTestLocalizationManager::class),
            app(JsonPageLocalizationManager::class),
        ) extends SeedRunController {
            public function overviewData(?string $activeTab = null): array
            {
                return $this->assembleSeedRunOverview($activeTab);
            }

            public function previewData(string $className): array
            {
                return $this->buildSeederPreview($className);
            }
        };

        $overviewBeforeBaseRun = $controller->overviewData('localizations');
        $pendingLocalizationSeeder = $overviewBeforeBaseRun['pendingSeeders']
            ->firstWhere('class_name', $localizationSeederClass);

        $this->assertNotNull($pendingLocalizationSeeder);
        $this->assertFalse((bool) data_get($pendingLocalizationSeeder, 'can_execute', true));
        $this->assertNotNull(data_get($pendingLocalizationSeeder, 'execution_block_reason'));

        $previewBeforeBaseRun = $controller->previewData($localizationSeederClass);
        $this->assertSame('page_localizations', $previewBeforeBaseRun['type']);
        $this->assertSame('en', $previewBeforeBaseRun['locale']);
        $this->assertSame(2, $previewBeforeBaseRun['localizedBlockCount']);
        $this->assertSame(0, $previewBeforeBaseRun['existingTargetCount']);

        $blockedRequest = Request::create('/admin/seed-runs/run?tab=localizations', 'POST', [
            'class_name' => $localizationSeederClass,
        ]);
        $blockedRequest->headers->set('Accept', 'application/json');

        $blockedResponse = $controller->run($blockedRequest);

        $this->assertInstanceOf(JsonResponse::class, $blockedResponse);
        $this->assertSame(422, $blockedResponse->getStatusCode());

        $directorySeeder = new class($definitionPath) extends JsonPageDirectorySeeder
        {
            public function __construct(private readonly string $singleDefinitionPath)
            {
            }

            protected function definitionPaths(): array
            {
                return [$this->singleDefinitionPath];
            }
        };

        $directorySeeder->run();

        $page = Page::query()->where('slug', 'json-only-page-v3')->first();

        $this->assertNotNull($page);
        $this->assertSame($baseSeederClass, $page->seeder);
        $this->assertSame(2, TextBlock::query()->where('page_id', $page->id)->where('locale', 'uk')->where('seeder', $baseSeederClass)->count());
        $this->assertSame(2, TextBlock::query()->where('page_id', $page->id)->where('locale', 'en')->where('seeder', $localizationSeederClass)->count());

        $overviewAfterBaseRun = $controller->overviewData('localizations');
        $pendingLocalizationSeeder = $overviewAfterBaseRun['pendingSeeders']
            ->firstWhere('class_name', $localizationSeederClass);

        $this->assertNotNull($pendingLocalizationSeeder);
        $this->assertTrue((bool) data_get($pendingLocalizationSeeder, 'can_execute', false));
        $this->assertNull(data_get($pendingLocalizationSeeder, 'execution_block_reason'));

        $request = Request::create('/admin/seed-runs/run?tab=localizations', 'POST', [
            'class_name' => $localizationSeederClass,
        ]);
        $request->headers->set('Accept', 'application/json');

        $response = $controller->run($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $payload = $response->getData(true);

        $this->assertSame($localizationSeederClass, $payload['class_name']);
        $this->assertTrue((bool) ($payload['seeder_moved'] ?? false));
        $this->assertDatabaseHas('seed_runs', [
            'class_name' => $localizationSeederClass,
        ]);

        $previewAfterRun = $controller->previewData($localizationSeederClass);
        $this->assertSame(1, $previewAfterRun['existingTargetCount']);
    }

    public function test_adjectives_page_v3_seeder_attaches_page_to_theory_category(): void
    {
        (new AdjectivesCategorySeeder())->__invoke();
        (new AdjectivesComparativeVsSuperlativeTheorySeeder())->__invoke();

        $category = PageCategory::query()->where('slug', 'prykmetniky-ta-pryslinknyky')->first();
        $page = Page::query()
            ->where('slug', 'comparative-vs-superlative')
            ->where('type', 'theory')
            ->first();

        $this->assertNotNull($category);
        $this->assertNotNull($page);
        $this->assertSame('theory', $category->type);
        $this->assertSame($category->id, $page->page_category_id);
        $this->assertSame(
            $category->id,
            TextBlock::query()
                ->where('page_id', $page->id)
                ->where('locale', 'uk')
                ->value('page_category_id')
        );
    }

    public function test_theory_show_uses_only_preferred_locale_blocks(): void
    {
        (new PassiveVoiceCategorySeeder())->__invoke();
        (new PassiveVoiceModalVerbsTheorySeeder())->__invoke();

        $originalLocale = app()->getLocale();
        app()->setLocale('pl');

        try {
            $category = PageCategory::query()->where('slug', 'passive-voice')->first();

            $this->assertNotNull($category);

            $controller = $this->makeTheoryControllerForLocalizedTitleTests();

            $view = $controller->show($category, 'theory-passive-voice-modal-verbs');
            $data = $view->getData();
            $page = $data['page'];
            $selectedCategory = $data['selectedCategory'];

            $this->assertSame(['pl'], $page->textBlocks->pluck('locale')->unique()->values()->all());
            $this->assertSame('Strona bierna z czasownikami modalnymi', $page->title);
            $this->assertSame('Strona bierna (Passive Voice)', $selectedCategory->title);
            $this->assertSame('Teoria', $data['sectionTitle']);

            $html = $view->render();

            $this->assertStringContainsString('Znaczenie czasowników modalnych w stronie biernej', $html);
            $this->assertStringContainsString('Problem można łatwo rozwiązać.', $html);
            $this->assertStringContainsString('Strona bierna z czasownikami modalnymi', $html);
            $this->assertStringContainsString('Strona bierna (Passive Voice)', $html);
            $this->assertStringNotContainsString('Значення модальних дієслів у пасиві', $html);
            $this->assertStringNotContainsString('Проблему можна легко вирішити.', $html);
            $this->assertStringNotContainsString('Пасив з модальними дієсловами', $html);
        } finally {
            app()->setLocale($originalLocale);
        }
    }

    public function test_theory_category_uses_localized_titles_for_sidebar_and_heading(): void
    {
        (new BasicGrammarCategorySeeder())->__invoke();
        (new PassiveVoiceCategorySeeder())->__invoke();
        (new PassiveVoiceTensesCategorySeeder())->__invoke();
        (new PassiveVoicePresentSimpleTheorySeeder())->__invoke();

        $originalLocale = app()->getLocale();
        app()->setLocale('pl');

        try {
            $category = PageCategory::query()->where('slug', 'passive-voice-tenses')->first();

            $this->assertNotNull($category);

            $controller = $this->makeTheoryControllerForLocalizedTitleTests();
            $view = $controller->category($category);
            $data = $view->getData();

            $selectedCategory = $data['selectedCategory'];
            $categories = $data['categories'];

            $this->assertSame('Strona bierna w różnych czasach', $selectedCategory->title);
            $this->assertSame('Podstawowa gramatyka', $categories->firstWhere('slug', 'basic-grammar')?->title);
            $this->assertSame('Strona bierna (Passive Voice)', $categories->firstWhere('slug', 'passive-voice')?->title);
            $this->assertSame('Teoria', $data['sectionTitle']);
            $this->assertArrayNotHasKey('relatedTests', $data);

            $html = $view->render();

            $this->assertStringContainsString('Strona bierna w różnych czasach', $html);
            $this->assertStringContainsString('Podstawowa gramatyka', $html);
            $this->assertStringContainsString('Strona bierna (Passive Voice)', $html);
            $this->assertStringNotContainsString('Пасив у різних часах', $html);
            $this->assertStringNotContainsString('Базова граматика', $html);
            $this->assertStringNotContainsString(__('public.common.related_tests'), $html);
        } finally {
            app()->setLocale($originalLocale);
        }
    }

    public function test_word_order_page_polish_localization_does_not_leak_html_or_ukrainian_fragments(): void
    {
        (new BasicGrammarCategorySeeder())->__invoke();
        (new WordOrderCategorySeeder())->__invoke();
        (new WordOrderVerbsObjectsTheorySeeder())->__invoke();

        $originalLocale = app()->getLocale();
        app()->setLocale('pl');

        try {
            $category = PageCategory::query()->where('slug', 'word-order')->first();

            $this->assertNotNull($category);

            $controller = $this->makeTheoryControllerForLocalizedTitleTests();
            $view = $controller->show($category, 'theory-word-order-verbs-objects');
            $data = $view->getData();
            $page = $data['page'];

            $this->assertSame(['pl'], $page->textBlocks->pluck('locale')->unique()->values()->all());
            $this->assertSame('Kolejność słów', $page->title);

            $html = $view->render();

            $this->assertStringContainsString('Kolejność słów', $html);
            $this->assertStringContainsString('Podmiot + czasownik modalny + czasownik w formie podstawowej + dopełnienie', $html);
            $this->assertStringContainsString('Wyłącz to.', $html);
            $this->assertStringNotContainsString('wymagane', $html);
            $this->assertStringNotContainsString('Займенник', $html);
            $this->assertStringNotContainsString('Модальні', $html);
            $this->assertStringNotContainsString('[[[CODX', $html);
        } finally {
            app()->setLocale($originalLocale);
        }
    }

    public function test_page_v3_child_category_seeder_links_to_parent_category(): void
    {
        (new BasicGrammarCategorySeeder())->__invoke();
        (new WordOrderCategorySeeder())->__invoke();

        $rootCategory = PageCategory::query()->where('slug', 'basic-grammar')->first();
        $childCategory = PageCategory::query()->where('slug', 'word-order')->first();

        $this->assertNotNull($rootCategory);
        $this->assertNotNull($childCategory);
        $this->assertSame($rootCategory->id, $childCategory->parent_id);
        $this->assertSame('theory', $childCategory->type);
    }

    public function test_page_v3_page_seeder_preserves_existing_root_category_type(): void
    {
        (new BasicGrammarCategorySeeder())->__invoke();
        (new BasicGrammarSentenceTypesTheorySeeder())->__invoke();

        $category = PageCategory::query()->where('slug', 'basic-grammar')->first();
        $page = Page::query()->where('slug', 'sentence-types')->where('type', 'theory')->first();

        $this->assertNotNull($category);
        $this->assertNotNull($page);
        $this->assertSame('theory', $category->type);
        $this->assertSame($category->id, $page->page_category_id);
    }

    public function test_page_v3_page_seeder_preserves_existing_child_category_parent(): void
    {
        (new BasicGrammarCategorySeeder())->__invoke();
        (new WordOrderCategorySeeder())->__invoke();
        (new BasicWordOrderTheorySeeder())->__invoke();

        $rootCategory = PageCategory::query()->where('slug', 'basic-grammar')->first();
        $childCategory = PageCategory::query()->where('slug', 'word-order')->first();
        $page = Page::query()->where('slug', 'theory-basic-word-order')->where('type', 'theory')->first();

        $this->assertNotNull($rootCategory);
        $this->assertNotNull($childCategory);
        $this->assertNotNull($page);
        $this->assertSame('theory', $childCategory->type);
        $this->assertSame($rootCategory->id, $childCategory->parent_id);
        $this->assertSame($childCategory->id, $page->page_category_id);
    }
}
