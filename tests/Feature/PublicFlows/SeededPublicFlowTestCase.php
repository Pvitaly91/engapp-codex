<?php

namespace Tests\Feature\PublicFlows;

use App\Models\Category;
use App\Models\Page;
use App\Models\PageCategory;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionOption;
use App\Models\SavedGrammarTest;
use App\Models\Tag;
use App\Models\Test;
use App\Models\Translate;
use App\Models\VerbHint;
use App\Models\Word;
use App\Services\MarkerTheoryMatcherService;
use App\Services\TagAggregationService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\Support\PublicRouteMatrix;
use Tests\TestCase;

abstract class SeededPublicFlowTestCase extends TestCase
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
            'coming-soon.enabled' => false,
            'tests.tech_info_enabled' => false,
        ]);

        if (! self::$fixtureBootstrapped) {
            $this->rebuildMinimalSchema();
            $this->seedPublicFixture();
            self::$fixtureBootstrapped = true;
        }
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    protected function assertNoRawTranslationKeys(string $html): void
    {
        $this->assertDoesNotMatchRegularExpression(
            '/(?:public|tests)\.[A-Za-z0-9_.-]+/',
            $html
        );
    }

    protected function assertHtmlLocale(string $html, string $locale): void
    {
        $this->assertStringContainsString(sprintf('<html lang="%s"', $locale), $html);
    }

    protected function assertJsonItemExists(array $items, string $key, string $expectedValue): array
    {
        $item = collect($items)->first(function ($candidate) use ($key, $expectedValue) {
            return is_array($candidate) && ($candidate[$key] ?? null) === $expectedValue;
        });

        $this->assertIsArray($item, sprintf('Unable to find JSON item where %s = %s.', $key, $expectedValue));

        return $item;
    }

    protected function currentQuestion(array $payload): array
    {
        $question = $payload['question'] ?? null;

        $this->assertIsArray($question, 'Expected the trainer payload to include a question.');

        return $question;
    }

    private function usePersistentSqliteDatabase(): void
    {
        $directory = storage_path('framework/testing');

        if (! is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        self::$databasePath ??= $directory . DIRECTORY_SEPARATOR . sprintf(
            'public-flow-suite-%s.sqlite',
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
        $viewsPath = storage_path('framework/views-public-flow-tests');

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
        $aggregationService = Mockery::mock(TagAggregationService::class);
        $aggregationService
            ->shouldReceive('getAggregations')
            ->andReturn([
                [
                    'main_tag' => PublicRouteMatrix::CATALOG_TAG,
                    'similar_tags' => [],
                    'category' => 'Tenses',
                ],
            ]);
        $this->app->instance(TagAggregationService::class, $aggregationService);

        $markerTheoryMatcher = Mockery::mock(MarkerTheoryMatcherService::class);
        $markerTheoryMatcher
            ->shouldReceive('getAllMarkerTags')
            ->andReturn([]);
        $this->app->instance(MarkerTheoryMatcherService::class, $markerTheoryMatcher);
    }

    private function rebuildMinimalSchema(): void
    {
        Schema::disableForeignKeyConstraints();

        foreach ([
            'saved_grammar_test_questions',
            'saved_grammar_tests',
            'tests',
            'question_answers',
            'question_option_question',
            'question_options',
            'question_tag',
            'questions',
            'categories',
            'tag_word',
            'translates',
            'words',
            'pages',
            'page_categories',
            'verb_hints',
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
            $table->unsignedBigInteger('page_category_id')->nullable();
            $table->string('slug');
            $table->string('title');
            $table->text('text')->nullable();
            $table->string('type')->nullable();
            $table->string('seeder')->nullable();
            $table->timestamps();
        });

        Schema::create('categories', function (Blueprint $table) {
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

        Schema::create('question_options', function (Blueprint $table) {
            $table->id();
            $table->string('option')->unique();
            $table->timestamps();
        });

        Schema::create('question_option_question', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('question_id');
            $table->unsignedBigInteger('option_id');
            $table->tinyInteger('flag')->nullable();
            $table->unique(['question_id', 'option_id', 'flag'], 'qoq_question_option_flag_unique');
        });

        Schema::create('question_answers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('question_id');
            $table->unsignedBigInteger('option_id');
            $table->string('marker');
            $table->timestamps();
            $table->unique(['question_id', 'marker', 'option_id'], 'question_marker_option_unique');
        });

        Schema::create('verb_hints', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('question_id');
            $table->unsignedBigInteger('option_id')->nullable();
            $table->string('marker')->nullable();
            $table->timestamps();
        });

        Schema::create('tests', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->json('filters')->nullable();
            $table->json('questions');
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
            $table->unique(['saved_grammar_test_id', 'question_uuid'], 'saved_grammar_test_questions_unique');
        });

        Schema::create('words', function (Blueprint $table) {
            $table->id();
            $table->string('word');
            $table->string('type')->nullable();
            $table->timestamps();
        });

        Schema::create('translates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('word_id');
            $table->string('lang', 8);
            $table->string('translation');
            $table->timestamps();
        });

        Schema::create('tag_word', function (Blueprint $table) {
            $table->unsignedBigInteger('tag_id');
            $table->unsignedBigInteger('word_id');
            $table->unique(['tag_id', 'word_id']);
        });
    }

    private function seedPublicFixture(): void
    {
        $pageCategory = PageCategory::create([
            'title' => 'Basic Grammar',
            'slug' => PublicRouteMatrix::PAGE_CATEGORY_SLUG,
        ]);

        Page::create([
            'page_category_id' => $pageCategory->id,
            'slug' => PublicRouteMatrix::PAGE_SLUG,
            'title' => PublicRouteMatrix::PAGE_TITLE,
            'text' => 'Present simple overview.',
        ]);

        $questionCategory = Category::create([
            'name' => 'Present Simple',
        ]);

        $catalogTag = Tag::create([
            'name' => PublicRouteMatrix::CATALOG_TAG,
            'category' => 'Tenses',
        ]);

        $movementTag = Tag::create([
            'name' => 'Movement',
            'category' => 'Vocabulary',
        ]);

        $question = Question::create([
            'uuid' => PublicRouteMatrix::QUESTION_UUID,
            'question' => 'I {a1} breakfast every day.',
            'difficulty' => 1,
            'level' => 'A1',
            'category_id' => $questionCategory->id,
        ]);

        $question->tags()->attach([$catalogTag->id]);

        $options = collect(['eat', 'eats', 'ate', 'eating'])
            ->map(fn (string $option) => QuestionOption::create(['option' => $option]));

        foreach ($options as $option) {
            DB::table('question_option_question')->insert([
                'question_id' => $question->id,
                'option_id' => $option->id,
                'flag' => null,
            ]);
        }

        QuestionAnswer::create([
            'question_id' => $question->id,
            'option_id' => $options->first()->id,
            'marker' => 'a1',
        ]);

        VerbHint::create([
            'question_id' => $question->id,
            'option_id' => $options->first()->id,
            'marker' => 'a1',
        ]);

        Test::create([
            'name' => PublicRouteMatrix::LEGACY_TEST_NAME,
            'slug' => PublicRouteMatrix::LEGACY_TEST_SLUG,
            'filters' => [],
            'questions' => [$question->id],
        ]);

        $savedTest = SavedGrammarTest::create([
            'uuid' => PublicRouteMatrix::SAVED_TEST_UUID,
            'name' => 'Saved Present Simple Test',
            'slug' => 'saved-present-simple-test',
            'filters' => [],
        ]);

        $savedTest->questionLinks()->create([
            'question_uuid' => $question->uuid,
            'position' => 0,
        ]);

        $this->seedVerbSet(
            base: 'go',
            past: 'went',
            participle: 'gone',
            ukrainian: 'йти',
            polish: 'iść',
            tagId: $movementTag->id
        );

        $this->seedVerbSet(
            base: 'come',
            past: 'came',
            participle: 'come',
            ukrainian: 'приходити',
            polish: 'przychodzić'
        );

        $this->seedVerbSet(
            base: 'see',
            past: 'saw',
            participle: 'seen',
            ukrainian: 'бачити',
            polish: 'widzieć'
        );
    }

    private function seedVerbSet(
        string $base,
        string $past,
        string $participle,
        string $ukrainian,
        string $polish,
        ?int $tagId = null
    ): void {
        $baseWord = Word::create(['word' => $base, 'type' => 'base']);
        $pastWord = Word::create(['word' => $past, 'type' => 'past']);
        $participleWord = Word::create(['word' => $participle, 'type' => 'participle']);

        foreach ([
            [$baseWord->id, 'uk', $ukrainian],
            [$baseWord->id, 'pl', $polish],
            [$pastWord->id, 'uk', $ukrainian],
            [$pastWord->id, 'pl', $polish],
            [$participleWord->id, 'uk', $ukrainian],
            [$participleWord->id, 'pl', $polish],
        ] as [$wordId, $lang, $translation]) {
            Translate::create([
                'word_id' => $wordId,
                'lang' => $lang,
                'translation' => $translation,
            ]);
        }

        if ($tagId !== null) {
            DB::table('tag_word')->insert([
                'tag_id' => $tagId,
                'word_id' => $baseWord->id,
            ]);
        }
    }
}
