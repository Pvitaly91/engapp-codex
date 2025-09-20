<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ChatGPTExplanation;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionHint;
use App\Models\QuestionOption;
use App\Models\QuestionVariant;
use App\Models\Test;
use App\Models\VerbHint;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class SavedTestTechnicalPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureSchema();
        $this->resetData();
    }

    /** @test */
    public function it_displays_detailed_information_for_each_question(): void
    {
        $category = Category::create(['name' => 'Present Simple']);

        $question = Question::create([
            'uuid' => (string) Str::uuid(),
            'question' => 'I {a1} to school every day.',
            'difficulty' => 1,
            'level' => 'A1',
            'category_id' => $category->id,
        ]);

        $optionGo = QuestionOption::create(['option' => 'go']);
        $optionGoes = QuestionOption::create(['option' => 'goes']);
        $optionWent = QuestionOption::create(['option' => 'went']);

        $question->options()->attach([$optionGo->id, $optionGoes->id, $optionWent->id]);

        QuestionAnswer::create([
            'question_id' => $question->id,
            'marker' => 'a1',
            'option_id' => $optionGo->id,
        ]);

        VerbHint::create([
            'question_id' => $question->id,
            'marker' => 'a1',
            'option_id' => $optionGo->id,
        ]);

        QuestionHint::create([
            'question_id' => $question->id,
            'provider' => 'chatgpt',
            'locale' => 'uk',
            'hint' => 'Використовуйте базову форму дієслова.',
        ]);

        QuestionHint::create([
            'question_id' => $question->id,
            'provider' => 'gemini',
            'locale' => 'uk',
            'hint' => 'Порада від Gemini.',
        ]);

        if (Schema::hasTable('question_variants')) {
            QuestionVariant::create([
                'question_id' => $question->id,
                'text' => 'They {a1} to school together.',
            ]);
        }

        ChatGPTExplanation::create([
            'question' => 'I {a1} to school every day.',
            'wrong_answer' => 'goes',
            'correct_answer' => 'go',
            'language' => 'uk',
            'explanation' => 'Форма go вживається з I.',
        ]);

        $test = Test::create([
            'name' => 'Simple test',
            'slug' => 'simple-test',
            'filters' => [],
            'questions' => [$question->id],
        ]);

        $response = $this->get(route('saved-test.tech', $test->slug));

        $response->assertOk();
        $response->assertDontSee('<details open', false);
        $response->assertSee('Правильні відповіді');
        $response->assertSee('Показати ▼');
        $response->assertSee('I <mark class="rounded bg-emerald-100 px-1 py-0.5 font-semibold text-emerald-800">go</mark> to school every day.', false);
        $response->assertSee('Варіанти відповіді');
        $response->assertSee('goes');
        $response->assertSee('Verb hint');
        $response->assertSee('Question hints');
        $response->assertSee('ChatGPT explanations');
        $response->assertSee('Форма go вживається з I.');
        $response->assertSeeInOrder([
            'Варіанти запитання',
            'Правильні відповіді',
        ]);

        if (Schema::hasTable('question_variants')) {
            $response->assertSee('They <mark class="rounded bg-emerald-100 px-1 py-0.5 font-semibold text-emerald-800">go</mark> to school together.', false);
        }
    }

    /** @test */
    public function it_shows_edit_actions_for_question_elements(): void
    {
        $category = Category::create(['name' => 'Present Simple']);

        $question = Question::create([
            'uuid' => (string) Str::uuid(),
            'question' => 'I {a1} to school every day.',
            'difficulty' => 1,
            'level' => 'A1',
            'category_id' => $category->id,
        ]);

        $optionGo = QuestionOption::create(['option' => 'go']);
        $optionGoes = QuestionOption::create(['option' => 'goes']);
        $optionWent = QuestionOption::create(['option' => 'went']);

        $question->options()->attach([$optionGo->id, $optionGoes->id, $optionWent->id]);

        $answer = QuestionAnswer::create([
            'question_id' => $question->id,
            'marker' => 'a1',
            'option_id' => $optionGo->id,
        ]);

        $verbHint = VerbHint::create([
            'question_id' => $question->id,
            'marker' => 'a1',
            'option_id' => $optionGo->id,
        ]);

        $hint = QuestionHint::create([
            'question_id' => $question->id,
            'provider' => 'chatgpt',
            'locale' => 'uk',
            'hint' => 'Використовуйте базову форму дієслова.',
        ]);

        $variant = null;
        if (Schema::hasTable('question_variants')) {
            $variant = QuestionVariant::create([
                'question_id' => $question->id,
                'text' => 'They {a1} to school together.',
            ]);
        }

        $test = Test::create([
            'name' => 'Simple test',
            'slug' => 'simple-test',
            'filters' => [],
            'questions' => [$question->id],
        ]);

        $response = $this->get(route('saved-test.tech', $test->slug));

        $response->assertOk();
        $response->assertSee('Редагувати питання');
        $response->assertSee('Змінити');
        $response->assertSee('Редагувати відповідь');
        $response->assertSee("editAnswer({$answer->id}", false);
        $response->assertSee("editOption({$question->id}, {$optionGo->id}", false);
        $response->assertSee("editQuestionHint({$hint->id}", false);
        $response->assertSee("editVerbHintValue({$verbHint->id}", false);

        if ($variant) {
            $response->assertSee("editVariant({$variant->id}", false);
        }
    }

    private function ensureSchema(): void
    {
        if (! Schema::hasTable('categories')) {
            Schema::create('categories', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('questions')) {
            Schema::create('questions', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->text('question');
                $table->unsignedTinyInteger('difficulty')->default(1);
                $table->string('level', 2)->nullable();
                $table->unsignedBigInteger('category_id')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('question_options')) {
            Schema::create('question_options', function (Blueprint $table) {
                $table->id();
                $table->string('option')->unique();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('question_option_question')) {
            Schema::create('question_option_question', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('question_id');
                $table->unsignedBigInteger('option_id');
                $table->tinyInteger('flag')->nullable();
                $table->unique(['question_id', 'option_id', 'flag'], 'qoq_question_option_flag_unique');
            });
        }

        if (! Schema::hasTable('question_answers')) {
            Schema::create('question_answers', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('question_id');
                $table->unsignedBigInteger('option_id');
                $table->string('marker');
                $table->timestamps();
                $table->unique(['question_id', 'marker', 'option_id'], 'question_marker_option_unique');
            });
        }

        if (! Schema::hasTable('verb_hints')) {
            Schema::create('verb_hints', function (Blueprint $table) {
                $table->id();
                $table->string('marker');
                $table->unsignedBigInteger('question_id');
                $table->unsignedBigInteger('option_id');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('question_hints')) {
            Schema::create('question_hints', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('question_id');
                $table->string('provider');
                $table->string('locale', 5);
                $table->text('hint');
                $table->timestamps();
                $table->unique(['question_id', 'provider', 'locale']);
            });
        }

        if (! Schema::hasTable('question_variants')) {
            Schema::create('question_variants', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('question_id');
                $table->text('text');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('tests')) {
            Schema::create('tests', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->json('filters')->nullable();
                $table->json('questions');
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('chatgpt_explanations')) {
            Schema::create('chatgpt_explanations', function (Blueprint $table) {
                $table->id();
                $table->text('question');
                $table->text('wrong_answer')->nullable();
                $table->text('correct_answer');
                $table->string('language');
                $table->text('explanation');
                $table->timestamps();
                $table->unique(['question', 'wrong_answer', 'correct_answer', 'language'], 'chatgpt_explanations_unique');
            });
        }
    }

    private function resetData(): void
    {
        foreach ([
            'chatgpt_explanations',
            'question_hints',
            'verb_hints',
            'question_variants',
            'question_option_question',
            'question_answers',
            'question_options',
            'questions',
            'categories',
            'tests',
        ] as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->delete();
            }
        }
    }
}
