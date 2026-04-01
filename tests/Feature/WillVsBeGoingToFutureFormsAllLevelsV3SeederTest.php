<?php

namespace Tests\Feature;

use App\Models\Question;
use App\Models\QuestionHint;
use Database\Seeders\V3\AI\ChatGpt\WillVsBeGoingToFutureFormsAllLevelsV3Seeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class WillVsBeGoingToFutureFormsAllLevelsV3SeederTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->rebuildMinimalSchema();
    }

    /** @test */
    public function all_levels_v3_seeder_creates_twelve_questions_per_level_with_localizations(): void
    {
        (new WillVsBeGoingToFutureFormsAllLevelsV3Seeder())->__invoke();

        $seededQuestions = Question::query()
            ->with(['tags'])
            ->where('seeder', WillVsBeGoingToFutureFormsAllLevelsV3Seeder::class)
            ->get();

        $this->assertCount(72, $seededQuestions);
        $this->assertSame(
            [
                'A1' => 12,
                'A2' => 12,
                'B1' => 12,
                'B2' => 12,
                'C1' => 12,
                'C2' => 12,
            ],
            $seededQuestions
                ->groupBy('level')
                ->map(fn ($questions) => $questions->count())
                ->sortKeys()
                ->all()
        );

        $a1Question = $seededQuestions->firstWhere('question', "It's cold in here. I {a1} the window.");
        $this->assertNotNull($a1Question);
        $this->assertSame(1, $a1Question->difficulty);
        $this->assertSame(
            [
                'Future Form Choice',
                'Future Forms Practice',
                'Future Simple',
                'Spontaneous Decisions and Offers',
                'Will vs Be Going To All Levels',
            ],
            $a1Question->tags->pluck('name')->sort()->values()->all()
        );

        $c2Question = $seededQuestions->firstWhere(
            'question',
            "With liquidity evaporating and counterparties pulling back, the institution {a1} emergency support within days."
        );
        $this->assertNotNull($c2Question);
        $this->assertSame(5, $c2Question->difficulty);

        $this->assertSame(216, QuestionHint::query()->count());

        $ukHint = QuestionHint::query()
            ->where('question_id', $a1Question->id)
            ->where('provider', 'chatgpt')
            ->where('locale', 'uk')
            ->value('hint');
        $enHint = QuestionHint::query()
            ->where('question_id', $a1Question->id)
            ->where('provider', 'chatgpt')
            ->where('locale', 'en')
            ->value('hint');
        $plHint = QuestionHint::query()
            ->where('question_id', $a1Question->id)
            ->where('provider', 'chatgpt')
            ->where('locale', 'pl')
            ->value('hint');

        $this->assertStringContainsString('Форма: **will + V1**.', $ukHint);
        $this->assertStringContainsString('Form: **will + base verb**.', $enHint);
        $this->assertStringContainsString('Forma: **will + czasownik w formie podstawowej**.', $plHint);

        $this->assertDatabaseHas('chatgpt_explanations', [
            'question' => $c2Question->question,
            'wrong_answer' => 'will',
            'correct_answer' => 'is going to',
            'language' => 'uk',
        ]);

        $this->assertDatabaseHas('chatgpt_explanations', [
            'question' => $c2Question->question,
            'wrong_answer' => 'will',
            'correct_answer' => 'is going to',
            'language' => 'en',
        ]);

        $this->assertDatabaseHas('chatgpt_explanations', [
            'question' => $c2Question->question,
            'wrong_answer' => 'will',
            'correct_answer' => 'is going to',
            'language' => 'pl',
        ]);

        $this->assertDatabaseHas('seed_runs', [
            'class_name' => WillVsBeGoingToFutureFormsAllLevelsV3Seeder::class,
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
