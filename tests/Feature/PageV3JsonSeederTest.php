<?php

namespace Tests\Feature;

use App\Http\Controllers\SeedRunController;
use App\Models\Page;
use App\Models\PageCategory;
use App\Models\TextBlock;
use App\Services\QuestionDeletionService;
use App\Support\Database\JsonPageDirectorySeeder;
use App\Support\Database\JsonPageLocalizationManager;
use App\Support\Database\JsonPageRuntimeSeeder;
use App\Support\Database\JsonTestLocalizationManager;
use Database\Seeders\Page_V3\PassiveVoiceV2\PassiveVoiceV2CategorySeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PageV3JsonSeederTest extends TestCase
{
    private string $tempDefinitionsDirectory;

    private string $tempLocalizationRoot;

    protected function setUp(): void
    {
        parent::setUp();

        $this->rebuildMinimalSchema();

        $this->tempDefinitionsDirectory = database_path('seeders/Page_V3/definitions/_tmp_tests');
        $this->tempLocalizationRoot = database_path('seeders/Page_V3/localizations/_tmp_tests');
    }

    protected function tearDown(): void
    {
        if (File::isDirectory($this->tempDefinitionsDirectory)) {
            File::deleteDirectory($this->tempDefinitionsDirectory);
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

    public function test_passive_voice_v2_category_page_v3_seeder_creates_localized_blocks(): void
    {
        (new PassiveVoiceV2CategorySeeder())->__invoke();

        $category = PageCategory::query()->where('slug', 'passive-voice')->first();

        $this->assertNotNull($category);
        $this->assertSame(PassiveVoiceV2CategorySeeder::class, $category->seeder);
        $this->assertSame('uk', $category->language);

        $this->assertSame(5, TextBlock::query()->where('page_category_id', $category->id)->where('locale', 'uk')->count());
        $this->assertSame(5, TextBlock::query()->where('page_category_id', $category->id)->where('locale', 'en')->count());
        $this->assertSame(5, TextBlock::query()->where('page_category_id', $category->id)->where('locale', 'pl')->count());

        $this->assertDatabaseHas('seed_runs', [
            'class_name' => PassiveVoiceV2CategorySeeder::class,
        ]);

        $this->assertSame(
            5,
            TextBlock::query()
                ->where('page_category_id', $category->id)
                ->where('locale', 'en')
                ->where('seeder', 'Database\\Seeders\\Page_V3\\Localizations\\En\\PassiveVoiceV2CategoryLocalizationSeeder')
                ->count()
        );

        $this->assertSame(
            5,
            TextBlock::query()
                ->where('page_category_id', $category->id)
                ->where('locale', 'pl')
                ->where('seeder', 'Database\\Seeders\\Page_V3\\Localizations\\Pl\\PassiveVoiceV2CategoryLocalizationSeeder')
                ->count()
        );
    }

    public function test_json_page_runtime_seeder_supports_page_definition_and_virtual_localization_seeders(): void
    {
        File::ensureDirectoryExists($this->tempDefinitionsDirectory);
        $tempLocalizationDirectory = $this->tempLocalizationRoot . DIRECTORY_SEPARATOR . 'en';

        File::ensureDirectoryExists($tempLocalizationDirectory);

        $definitionPath = $this->tempDefinitionsDirectory . DIRECTORY_SEPARATOR . 'json_only_page.json';
        $localizationPath = $tempLocalizationDirectory . DIRECTORY_SEPARATOR . 'json_only_page.en.json';
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
            app(JsonTestLocalizationManager::class),
            app(JsonPageLocalizationManager::class),
        ) extends SeedRunController {
            public function overviewData(): array
            {
                return $this->assembleSeedRunOverview();
            }

            public function previewData(string $className): array
            {
                return $this->buildSeederPreview($className);
            }
        };

        $overviewBeforeBaseRun = $controller->overviewData();
        $this->assertTrue(
            $overviewBeforeBaseRun['pendingSeeders']->pluck('class_name')->contains($localizationSeederClass)
        );

        $previewBeforeBaseRun = $controller->previewData($localizationSeederClass);
        $this->assertSame('page_localizations', $previewBeforeBaseRun['type']);
        $this->assertSame('en', $previewBeforeBaseRun['locale']);
        $this->assertSame(2, $previewBeforeBaseRun['localizedBlockCount']);
        $this->assertSame(0, $previewBeforeBaseRun['existingTargetCount']);

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

        $request = Request::create('/admin/seed-runs/run', 'POST', [
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
}
