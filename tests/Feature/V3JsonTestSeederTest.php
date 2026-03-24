<?php

namespace Tests\Feature;

use App\Models\ChatGPTExplanation;
use App\Models\Question;
use App\Models\QuestionHint;
use App\Support\Database\JsonTestDirectorySeeder;
use Database\Seeders\V2\PassiveVoiceAllTensesV2Seeder;
use Database\Seeders\V3\PassiveVoiceAllTensesV3Seeder;
use Illuminate\Database\Schema\Blueprint;
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
}
