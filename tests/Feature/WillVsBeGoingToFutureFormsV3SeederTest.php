<?php

namespace Tests\Feature;

use App\Models\ChatGPTExplanation;
use App\Models\Question;
use App\Models\QuestionHint;
use Database\Seeders\V2\WillVsBeGoingToFutureFormsV2Seeder;
use Database\Seeders\V3\WillVsBeGoingToFutureFormsV3Seeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use ReflectionMethod;
use Tests\TestCase;

class WillVsBeGoingToFutureFormsV3SeederTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->rebuildMinimalSchema();
    }

    /** @test */
    public function v3_seeder_preserves_v2_questions_and_merges_localizations(): void
    {
        $legacySeeder = new WillVsBeGoingToFutureFormsV2Seeder();

        $entriesMethod = new ReflectionMethod($legacySeeder, 'questionEntries');
        $entriesMethod->setAccessible(true);
        $legacyEntries = $entriesMethod->invoke($legacySeeder);

        $uuidMethod = new ReflectionMethod(\Database\Seeders\QuestionSeeder::class, 'generateQuestionUuid');
        $uuidMethod->setAccessible(true);
        $legacyUuid = $uuidMethod->invoke(
            $legacySeeder,
            'page1',
            1,
            "A: We're out of milk.\nB: Don't worry, I {a1} buy some on the way home."
        );

        (new WillVsBeGoingToFutureFormsV3Seeder())->__invoke();

        $this->assertSame(
            count($legacyEntries),
            Question::query()->where('seeder', WillVsBeGoingToFutureFormsV3Seeder::class)->count()
        );

        $question = Question::query()
            ->with(['tags'])
            ->where('uuid', $legacyUuid)
            ->first();

        $this->assertNotNull($question);
        $this->assertSame(
            [
                'Future Form Choice',
                'Future Forms Practice',
                'Future Simple',
                'Spontaneous Decisions and Offers',
                'Will vs Be Going To',
            ],
            $question->tags->pluck('name')->sort()->values()->all()
        );

        $ukHint = QuestionHint::query()
            ->where('question_id', $question->id)
            ->where('provider', 'chatgpt')
            ->where('locale', 'uk')
            ->first();
        $enHint = QuestionHint::query()
            ->where('question_id', $question->id)
            ->where('provider', 'chatgpt')
            ->where('locale', 'en')
            ->first();
        $plHint = QuestionHint::query()
            ->where('question_id', $question->id)
            ->where('provider', 'chatgpt')
            ->where('locale', 'pl')
            ->first();

        $this->assertNotNull($ukHint);
        $this->assertNotNull($enHint);
        $this->assertNotNull($plHint);
        $this->assertStringContainsString('Форма: **will + V1**.', $ukHint->hint);
        $this->assertStringContainsString('Form: **will + base verb**.', $enHint->hint);
        $this->assertStringContainsString('Forma: **will + czasownik w formie podstawowej**.', $plHint->hint);

        $this->assertDatabaseHas('chatgpt_explanations', [
            'question' => $question->question,
            'wrong_answer' => "'m going to",
            'correct_answer' => "'ll",
            'language' => 'en',
        ]);

        $this->assertDatabaseHas('chatgpt_explanations', [
            'question' => $question->question,
            'wrong_answer' => "'m going to",
            'correct_answer' => "'ll",
            'language' => 'pl',
        ]);

        $this->assertDatabaseHas('seed_runs', [
            'class_name' => WillVsBeGoingToFutureFormsV3Seeder::class,
        ]);
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
}
