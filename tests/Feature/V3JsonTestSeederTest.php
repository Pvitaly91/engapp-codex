<?php

namespace Tests\Feature;

use App\Http\Controllers\SeedRunController;
use App\Support\Database\JsonPageLocalizationManager;
use App\Models\SavedGrammarTest;
use App\Models\ChatGPTExplanation;
use App\Models\Question;
use App\Models\QuestionHint;
use App\Support\Database\JsonTestDirectorySeeder;
use App\Support\Database\JsonTestLocalizationManager;
use App\Support\Database\JsonRuntimeSeeder;
use App\Services\QuestionDeletionService;
use App\Services\SeederPromptTheoryPageResolver;
use App\Services\SeederTestTargetResolver;
use Database\Seeders\V2\PassiveVoiceAllTensesV2Seeder;
use Database\Seeders\V3\PassiveVoiceAllTensesV3Seeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use ReflectionMethod;
use Tests\TestCase;

class V3JsonTestSeederTest extends TestCase
{
    private string $tempDefinitionsDirectory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->rebuildMinimalSchema();

        $this->tempDefinitionsDirectory = database_path('seeders/V3/definitions/_tmp_tests');
    }

    protected function tearDown(): void
    {
        if (File::isDirectory($this->tempDefinitionsDirectory)) {
            File::deleteDirectory($this->tempDefinitionsDirectory);
        }

        parent::tearDown();
    }

    private function rebuildMinimalSchema(): void
    {
        Schema::disableForeignKeyConstraints();

        foreach ([
            'seed_runs',
            'chatgpt_explanations',
            'question_hints',
            'question_variants',
            'saved_grammar_test_questions',
            'saved_grammar_tests',
            'tests',
            'tag_text_block',
            'page_tag',
            'page_category_tag',
            'text_blocks',
            'pages',
            'page_categories',
            'question_tag',
            'question_option_question',
            'verb_hints',
            'question_answers',
            'question_options',
            'questions',
            'tags',
            'sources',
            'categories',
        ] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::enableForeignKeyConstraints();

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('sources', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

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
            $table->foreignId('parent_id')->nullable()->constrained('page_categories')->nullOnDelete();
            $table->string('seeder')->nullable();
            $table->timestamps();
        });

        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->text('text')->nullable();
            $table->string('type')->nullable();
            $table->string('seeder')->nullable();
            $table->foreignId('page_category_id')->nullable()->constrained('page_categories')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('text_blocks', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->nullable();
            $table->foreignId('page_id')->nullable()->constrained('pages')->cascadeOnDelete();
            $table->foreignId('page_category_id')->nullable()->constrained('page_categories')->cascadeOnDelete();
            $table->string('locale', 5)->nullable();
            $table->string('type')->nullable();
            $table->string('column')->nullable();
            $table->string('heading')->nullable();
            $table->string('css_class')->nullable();
            $table->integer('sort_order')->default(0);
            $table->text('body')->nullable();
            $table->string('level')->nullable();
            $table->string('seeder')->nullable();
            $table->timestamps();
        });

        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->text('question');
            $table->unsignedTinyInteger('difficulty')->default(1);
            $table->string('level', 2)->nullable();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->boolean('flag')->default(0);
            $table->foreignId('source_id')->nullable()->constrained('sources')->nullOnDelete();
            $table->string('seeder')->nullable();
            $table->string('type')->nullable();
            $table->json('options_by_marker')->nullable();
            $table->uuid('theory_text_block_uuid')->nullable();
            $table->timestamps();
            $table->index('seeder');
        });

        Schema::create('question_options', function (Blueprint $table) {
            $table->id();
            $table->string('option')->unique();
            $table->timestamps();
        });

        Schema::create('question_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('questions')->cascadeOnDelete();
            $table->string('marker');
            $table->foreignId('option_id')->constrained('question_options')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['question_id', 'marker', 'option_id'], 'question_marker_option_unique');
        });

        Schema::create('verb_hints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('questions')->cascadeOnDelete();
            $table->string('marker');
            $table->foreignId('option_id')->constrained('question_options')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('question_option_question', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('questions')->cascadeOnDelete();
            $table->foreignId('option_id')->constrained('question_options')->cascadeOnDelete();
            $table->tinyInteger('flag')->nullable();
            $table->unique(
                ['question_id', 'option_id', 'flag'],
                'question_option_question_question_id_option_id_flag_unique'
            );
        });

        Schema::create('question_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tag_id')->constrained('tags')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('questions')->cascadeOnDelete();
            $table->unique(['tag_id', 'question_id']);
        });

        Schema::create('page_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained('pages')->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained('tags')->cascadeOnDelete();
            $table->unique(['page_id', 'tag_id']);
        });

        Schema::create('page_category_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_category_id')->constrained('page_categories')->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained('tags')->cascadeOnDelete();
            $table->unique(['page_category_id', 'tag_id']);
        });

        Schema::create('tag_text_block', function (Blueprint $table) {
            $table->id();
            $table->foreignId('text_block_id')->constrained('text_blocks')->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained('tags')->cascadeOnDelete();
            $table->unique(['text_block_id', 'tag_id']);
        });

        Schema::create('question_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('questions')->cascadeOnDelete();
            $table->text('text');
            $table->timestamps();
        });

        Schema::create('question_hints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('questions')->cascadeOnDelete();
            $table->string('provider');
            $table->string('locale', 5);
            $table->text('hint');
            $table->timestamps();
            $table->unique(['question_id', 'provider', 'locale']);
        });

        Schema::create('chatgpt_explanations', function (Blueprint $table) {
            $table->id();
            $table->text('question');
            $table->text('wrong_answer');
            $table->text('correct_answer');
            $table->string('language')->default('uk');
            $table->text('explanation');
            $table->timestamps();
            $table->unique(
                ['question', 'wrong_answer', 'correct_answer', 'language'],
                'chatgpt_explanations_unique'
            );
        });

        Schema::create('saved_grammar_tests', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->json('filters');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('saved_grammar_test_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('saved_grammar_test_id')->constrained('saved_grammar_tests')->cascadeOnDelete();
            $table->uuid('question_uuid');
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->index('question_uuid');
        });

        Schema::create('tests', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->json('filters');
            $table->json('questions');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('seed_runs', function (Blueprint $table) {
            $table->id();
            $table->string('class_name')->unique();
            $table->timestamp('ran_at')->nullable();
        });
    }

    public function test_passive_voice_v3_seeder_preserves_v2_uuid_and_localized_content(): void
    {
        $legacySeeder = new PassiveVoiceAllTensesV2Seeder();
        $entriesMethod = new ReflectionMethod($legacySeeder, 'questionEntries');
        $entriesMethod->setAccessible(true);
        $legacyEntries = $entriesMethod->invoke($legacySeeder);

        $uuidMethod = new ReflectionMethod($legacySeeder, 'generateQuestionUuid');
        $uuidMethod->setAccessible(true);
        $legacyUuid = $uuidMethod->invoke($legacySeeder, 1, 'The new chemical {a1} when it exploded.');

        (new PassiveVoiceAllTensesV3Seeder())->__invoke();

        $this->assertSame(
            count($legacyEntries),
            Question::query()->where('seeder', PassiveVoiceAllTensesV3Seeder::class)->count()
        );

        $question = Question::query()
            ->with(['tags', 'verbHints.option'])
            ->where('uuid', $legacyUuid)
            ->first();

        $this->assertNotNull($question);
        $this->assertSame('The new chemical {a1} when it exploded.', $question->question);
        $this->assertSame(
            [
                'Mixed Tenses',
                'Passive Construction',
                'Passive Voice All Tenses',
                'Passive Voice Practice',
            ],
            $question->tags->pluck('name')->sort()->values()->all()
        );
        $this->assertSame('test', $question->verbHints->first()?->option?->option);

        $hint = QuestionHint::query()
            ->where('question_id', $question->id)
            ->where('provider', 'chatgpt')
            ->where('locale', 'uk')
            ->value('hint');

        $this->assertNotNull($hint);
        $this->assertStringContainsString('Past Continuous Passive', $hint);

        $uaExplanation = ChatGPTExplanation::query()
            ->where('question', $question->question)
            ->where('wrong_answer', 'had being tested')
            ->where('correct_answer', 'was being tested')
            ->where('language', 'ua')
            ->value('explanation');

        $ukExplanation = ChatGPTExplanation::query()
            ->where('question', $question->question)
            ->where('wrong_answer', 'had being tested')
            ->where('correct_answer', 'was being tested')
            ->where('language', 'uk')
            ->value('explanation');

        $this->assertNotNull($uaExplanation);
        $this->assertSame($uaExplanation, $ukExplanation);
        $this->assertStringContainsString('Past Perfect Passive', $uaExplanation);

        $enHint = QuestionHint::query()
            ->where('question_id', $question->id)
            ->where('provider', 'chatgpt')
            ->where('locale', 'en')
            ->value('hint');

        $plHint = QuestionHint::query()
            ->where('question_id', $question->id)
            ->where('provider', 'chatgpt')
            ->where('locale', 'pl')
            ->value('hint');

        $this->assertNotNull($enHint);
        $this->assertStringContainsString('Past Continuous Passive', $enHint);
        $this->assertNotNull($plHint);
        $this->assertStringContainsString('Past Continuous Passive', $plHint);

        $enExplanation = ChatGPTExplanation::query()
            ->where('question', $question->question)
            ->where('wrong_answer', 'had being tested')
            ->where('correct_answer', 'was being tested')
            ->where('language', 'en')
            ->value('explanation');

        $plExplanation = ChatGPTExplanation::query()
            ->where('question', $question->question)
            ->where('wrong_answer', 'had being tested')
            ->where('correct_answer', 'was being tested')
            ->where('language', 'pl')
            ->value('explanation');

        $this->assertNotNull($enExplanation);
        $this->assertStringContainsString('Past Perfect Passive form is', $enExplanation);
        $this->assertNotNull($plExplanation);
        $this->assertStringContainsString('forma "had being" nie występuje', $plExplanation);

        $this->assertSame(
            count($legacyEntries),
            QuestionHint::query()->where('locale', 'en')->count()
        );
        $this->assertSame(
            count($legacyEntries),
            QuestionHint::query()->where('locale', 'pl')->count()
        );
    }

    public function test_directory_seeder_supports_json_only_definition_and_skips_second_run(): void
    {
        File::ensureDirectoryExists($this->tempDefinitionsDirectory);

        $definitionPath = $this->tempDefinitionsDirectory . DIRECTORY_SEPARATOR . 'json_only_sample.json';

        File::put($definitionPath, json_encode([
            'schema_version' => 1,
            'defaults' => [
                'default_locale' => 'uk',
            ],
            'category' => [
                'name' => 'JSON Only Category',
            ],
            'questions' => [
                [
                    'id' => 1,
                    'question' => 'JSON only sample {a1}.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'works',
                            'options' => ['works', 'broken'],
                            'verb_hint' => 'work',
                        ],
                    ],
                    'localizations' => [
                        'uk' => [
                            'hints' => [
                                'Орієнтуйся на правильну форму дієслова.',
                            ],
                            'explanations' => [
                                'a1' => [
                                    'works' => '✅ Правильна відповідь.',
                                    'broken' => '❌ Це не та форма.',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL);

        $seeder = new class($definitionPath) extends JsonTestDirectorySeeder
        {
            public function __construct(private readonly string $singleDefinitionPath)
            {
            }

            protected function definitionPaths(): array
            {
                return [$this->singleDefinitionPath];
            }
        };

        $seeder->run();

        $question = Question::query()
            ->where('question', 'JSON only sample {a1}.')
            ->first();

        $this->assertNotNull($question);
        $this->assertTrue(Str::endsWith($question->seeder ?? '', 'JsonOnlySampleSeeder'));

        $this->assertDatabaseHas('seed_runs', [
            'class_name' => $question->seeder,
        ]);

        $hint = QuestionHint::query()
            ->where('question_id', $question->id)
            ->where('locale', 'uk')
            ->value('hint');

        $this->assertSame('Орієнтуйся на правильну форму дієслова.', $hint);

        $this->assertDatabaseHas('chatgpt_explanations', [
            'question' => 'JSON only sample {a1}.',
            'wrong_answer' => 'broken',
            'correct_answer' => 'works',
            'language' => 'ua',
        ]);

        $this->assertDatabaseHas('chatgpt_explanations', [
            'question' => 'JSON only sample {a1}.',
            'wrong_answer' => 'broken',
            'correct_answer' => 'works',
            'language' => 'uk',
        ]);

        $seeder->run();

        $this->assertSame(
            1,
            Question::query()->where('question', 'JSON only sample {a1}.')->count()
        );

        $this->assertSame(
            1,
            DB::table('seed_runs')->where('class_name', $question->seeder)->count()
        );
    }

    public function test_runtime_seeder_can_persist_saved_test_with_ordered_question_links(): void
    {
        File::ensureDirectoryExists($this->tempDefinitionsDirectory);

        $definitionPath = $this->tempDefinitionsDirectory . DIRECTORY_SEPARATOR . 'json_saved_test_sample.json';

        File::put($definitionPath, json_encode([
            'schema_version' => 1,
            'seeder' => [
                'class' => 'Database\\Seeders\\V3\\IA\\ChatGptPro\\JsonSavedTestSampleSeeder',
                'uuid_namespace' => 'JsonSavedTestSampleSeeder',
            ],
            'defaults' => [
                'default_locale' => 'uk',
            ],
            'category' => [
                'name' => 'JSON Saved Test Category',
            ],
            'saved_test' => [
                'uuid' => 'json-saved-test-sample',
                'slug' => 'json-saved-test-sample',
                'name' => 'JSON Saved Test Sample',
                'description' => 'Sample saved-test payload for the legacy JSON seeder contract.',
                'filters' => [
                    'levels' => ['A1'],
                    'prompt_generator' => [
                        'source_type' => 'theory_page',
                        'theory_page_id' => 712,
                        'theory_page_ids' => [712],
                        'theory_page' => [
                            'id' => 712,
                            'slug' => 'plural-nouns-s-es-ies',
                            'title' => 'Plural Nouns — Множина іменників: правила, винятки, приклади',
                            'category_slug_path' => 'imennyky-artykli-ta-kilkist',
                            'page_seeder_class' => 'Database\\Seeders\\Page_V3\\NounsArticlesQuantity\\NounsArticlesQuantityPluralNounsTheorySeeder',
                            'url' => 'https://gramlyze.com/theory/imennyky-artykli-ta-kilkist/plural-nouns-s-es-ies',
                        ],
                    ],
                ],
                'question_uuids' => [
                    'json-saved-test-question-2',
                    'json-saved-test-question-1',
                ],
            ],
            'questions' => [
                [
                    'uuid' => 'json-saved-test-question-1',
                    'question' => 'Sample saved test {a1}.',
                    'level' => 'A1',
                    'markers' => [
                        'a1' => [
                            'answer' => 'works',
                            'options' => ['works', 'fails'],
                        ],
                    ],
                ],
                [
                    'uuid' => 'json-saved-test-question-2',
                    'question' => 'Ordered links {a1}.',
                    'level' => 'A1',
                    'markers' => [
                        'a1' => [
                            'answer' => 'matter',
                            'options' => ['matter', 'break'],
                        ],
                    ],
                ],
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL);

        (new JsonRuntimeSeeder($definitionPath))->seedFile();

        $savedTest = SavedGrammarTest::query()
            ->with('questionLinks')
            ->where('slug', 'json-saved-test-sample')
            ->first();

        $this->assertNotNull($savedTest);
        $this->assertSame('json-saved-test-sample', $savedTest->uuid);
        $this->assertSame('JSON Saved Test Sample', $savedTest->name);
        $this->assertSame(
            [
                'json-saved-test-question-2',
                'json-saved-test-question-1',
            ],
            $savedTest->questionLinks
                ->sortBy('position')
                ->pluck('question_uuid')
                ->values()
                ->all()
        );
        $this->assertSame(
            712,
            data_get($savedTest->filters, 'prompt_generator.theory_page_id')
        );
        $this->assertSame(
            'Database\\Seeders\\Page_V3\\NounsArticlesQuantity\\NounsArticlesQuantityPluralNounsTheorySeeder',
            data_get($savedTest->filters, 'prompt_generator.theory_page.page_seeder_class')
        );
        $this->assertSame(
            [
                'Database\\Seeders\\V3\\IA\\ChatGptPro\\JsonSavedTestSampleSeeder',
            ],
            data_get($savedTest->filters, 'seeder_classes')
        );
        $this->assertSame(2, (int) data_get($savedTest->filters, 'num_questions'));
    }

    public function test_runtime_seeder_normalizes_legacy_string_prompt_generator_into_structured_filters(): void
    {
        File::ensureDirectoryExists($this->tempDefinitionsDirectory);

        $definitionPath = $this->tempDefinitionsDirectory . DIRECTORY_SEPARATOR . 'json_saved_test_string_prompt_generator.json';

        File::put($definitionPath, json_encode([
            'schema_version' => 1,
            'seeder' => [
                'class' => 'Database\\Seeders\\V3\\IA\\ChatGptPro\\JsonSavedTestStringPromptGeneratorSeeder',
                'uuid_namespace' => 'JsonSavedTestStringPromptGeneratorSeeder',
            ],
            'defaults' => [
                'default_locale' => 'uk',
            ],
            'category' => [
                'name' => 'JSON Saved Test Category',
            ],
            'saved_test' => [
                'uuid' => 'json-saved-test-string-prompt',
                'slug' => 'json-saved-test-string-prompt',
                'name' => 'JSON Saved Test String Prompt',
                'filters' => [
                    'levels' => ['A1'],
                    'prompt_generator' => 'source_type=theory_page; theory_page_id=712; theory_page_ids=[712]; theory_page.id=712; theory_page.slug=plural-nouns-s-es-ies; theory_page.title=Plural Nouns — Множина іменників: правила, винятки, приклади; theory_page.category_slug_path=imennyky-artykli-ta-kilkist; theory_page.page_seeder_class=Database\\Seeders\\Page_V3\\NounsArticlesQuantity\\NounsArticlesQuantityPluralNounsTheorySeeder; theory_page.url=https://gramlyze.com/theory/imennyky-artykli-ta-kilkist/plural-nouns-s-es-ies',
                ],
            ],
            'questions' => [
                [
                    'uuid' => 'json-string-prompt-question-1',
                    'question' => 'String prompt generator {a1}.',
                    'level' => 'A1',
                    'markers' => [
                        'a1' => [
                            'answer' => 'works',
                            'options' => ['works', 'fails'],
                        ],
                    ],
                ],
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL);

        (new JsonRuntimeSeeder($definitionPath))->seedFile();

        $savedTest = SavedGrammarTest::query()
            ->where('slug', 'json-saved-test-string-prompt')
            ->first();

        $this->assertNotNull($savedTest);
        $this->assertSame(
            712,
            data_get($savedTest->filters, 'prompt_generator.theory_page_id')
        );
        $this->assertSame(
            [712],
            data_get($savedTest->filters, 'prompt_generator.theory_page_ids')
        );
        $this->assertSame(
            'plural-nouns-s-es-ies',
            data_get($savedTest->filters, 'prompt_generator.theory_page.slug')
        );
        $this->assertSame(
            'Database\\Seeders\\Page_V3\\NounsArticlesQuantity\\NounsArticlesQuantityPluralNounsTheorySeeder',
            data_get($savedTest->filters, 'prompt_generator.theory_page.page_seeder_class')
        );
    }

    public function test_runtime_seeder_rejects_duplicate_saved_test_slug_for_different_uuid(): void
    {
        File::ensureDirectoryExists($this->tempDefinitionsDirectory);

        $firstDefinitionPath = $this->tempDefinitionsDirectory . DIRECTORY_SEPARATOR . 'json_saved_test_slug_a.json';
        $secondDefinitionPath = $this->tempDefinitionsDirectory . DIRECTORY_SEPARATOR . 'json_saved_test_slug_b.json';

        $baseDefinition = [
            'schema_version' => 1,
            'defaults' => [
                'default_locale' => 'uk',
            ],
            'category' => [
                'name' => 'JSON Saved Test Category',
            ],
            'questions' => [
                [
                    'uuid' => 'json-shared-slug-question-1',
                    'question' => 'Shared slug question {a1}.',
                    'level' => 'A1',
                    'markers' => [
                        'a1' => [
                            'answer' => 'works',
                            'options' => ['works', 'fails'],
                        ],
                    ],
                ],
            ],
        ];

        File::put($firstDefinitionPath, json_encode(array_merge($baseDefinition, [
            'seeder' => [
                'class' => 'Database\\Seeders\\V3\\IA\\ChatGptPro\\JsonSavedTestSlugASeeder',
                'uuid_namespace' => 'JsonSavedTestSlugASeeder',
            ],
            'saved_test' => [
                'uuid' => 'json-shared-slug-a',
                'slug' => 'json-shared-saved-test-slug',
                'name' => 'JSON Shared Slug A',
            ],
            'questions' => [
                [
                    'uuid' => 'json-shared-slug-question-a',
                    'question' => 'Shared slug question A {a1}.',
                    'level' => 'A1',
                    'markers' => [
                        'a1' => [
                            'answer' => 'works',
                            'options' => ['works', 'fails'],
                        ],
                    ],
                ],
            ],
        ]), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL);

        File::put($secondDefinitionPath, json_encode(array_merge($baseDefinition, [
            'seeder' => [
                'class' => 'Database\\Seeders\\V3\\IA\\ChatGptPro\\JsonSavedTestSlugBSeeder',
                'uuid_namespace' => 'JsonSavedTestSlugBSeeder',
            ],
            'saved_test' => [
                'uuid' => 'json-shared-slug-b',
                'slug' => 'json-shared-saved-test-slug',
                'name' => 'JSON Shared Slug B',
            ],
            'questions' => [
                [
                    'uuid' => 'json-shared-slug-question-b',
                    'question' => 'Shared slug question B {a1}.',
                    'level' => 'A1',
                    'markers' => [
                        'a1' => [
                            'answer' => 'works',
                            'options' => ['works', 'fails'],
                        ],
                    ],
                ],
            ],
        ]), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL);

        (new JsonRuntimeSeeder($firstDefinitionPath))->seedFile();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('each seeder must use a unique saved_test.slug');

        (new JsonRuntimeSeeder($secondDefinitionPath))->seedFile();
    }

    public function test_seed_runs_supports_virtual_localization_seeders_for_preview_and_execution(): void
    {
        $controller = new class(
            app(QuestionDeletionService::class),
            app(SeederPromptTheoryPageResolver::class),
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

        $localizationSeederClass = 'Database\\Seeders\\V3\\Localizations\\En\\PassiveVoiceAllTensesV3LocalizationSeeder';

        $overviewBeforeBaseRun = $controller->overviewData('localizations');
        $pendingLocalizationSeeder = $overviewBeforeBaseRun['pendingSeeders']
            ->firstWhere('class_name', $localizationSeederClass);

        $this->assertNotNull($pendingLocalizationSeeder);
        $this->assertFalse((bool) data_get($pendingLocalizationSeeder, 'can_execute', true));
        $this->assertNotNull(data_get($pendingLocalizationSeeder, 'execution_block_reason'));

        $previewBeforeBaseRun = $controller->previewData($localizationSeederClass);

        $this->assertSame('question_localizations', $previewBeforeBaseRun['type']);
        $this->assertSame('en', $previewBeforeBaseRun['locale']);
        $this->assertSame(30, $previewBeforeBaseRun['localizedQuestionCount']);
        $this->assertSame(0, $previewBeforeBaseRun['existingQuestionCount']);

        $blockedRequest = Request::create('/admin/seed-runs/run?tab=localizations', 'POST', [
            'class_name' => $localizationSeederClass,
        ]);
        $blockedRequest->headers->set('Accept', 'application/json');

        $blockedResponse = $controller->run($blockedRequest);

        $this->assertInstanceOf(JsonResponse::class, $blockedResponse);
        $this->assertSame(422, $blockedResponse->getStatusCode());

        (new PassiveVoiceAllTensesV3Seeder())->__invoke();

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

        $overviewAfterRun = $controller->overviewData('localizations');
        $this->assertTrue(
            $overviewAfterRun['executedSeeders']->pluck('class_name')->contains($localizationSeederClass)
        );

        $previewAfterRun = $controller->previewData($localizationSeederClass);
        $this->assertSame(30, $previewAfterRun['existingQuestionCount']);
    }
}
