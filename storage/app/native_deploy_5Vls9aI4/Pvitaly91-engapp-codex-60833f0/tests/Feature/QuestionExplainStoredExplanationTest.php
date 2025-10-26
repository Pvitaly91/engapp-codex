<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ChatGPTExplanation;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionOption;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class QuestionExplainStoredExplanationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureSchema();
        $this->resetData();
    }

    public function test_it_returns_stored_explanation_for_wrong_answer(): void
    {
        $question = $this->createQuestion('Where {a1} my shoes?', 'are', ['is']);

        ChatGPTExplanation::create([
            'question' => $question->question,
            'wrong_answer' => 'is',
            'correct_answer' => 'are',
            'language' => 'ua',
            'explanation' => 'Використовуй "are" з множиною.',
        ]);

        $response = $this->postJson(route('question.explain'), [
            'question_id' => $question->id,
            'answer' => 'is',
        ]);

        $response->assertOk();
        $response->assertJson([
            'correct' => false,
            'explanation' => 'Використовуй "are" з множиною.',
        ]);
    }

    public function test_it_returns_stored_explanation_for_correct_answer(): void
    {
        $question = $this->createQuestion('Do {a1} like apples?', 'you', ['they']);

        ChatGPTExplanation::create([
            'question' => $question->question,
            'wrong_answer' => '',
            'correct_answer' => 'you',
            'language' => 'ua',
            'explanation' => 'Правильно, "you" узгоджується з дієсловом у питанні.',
        ]);

        $response = $this->postJson(route('question.explain'), [
            'question_id' => $question->id,
            'answer' => 'you',
            'correct_answer' => 'you',
        ]);

        $response->assertOk();
        $response->assertJson([
            'correct' => true,
            'explanation' => 'Правильно, "you" узгоджується з дієсловом у питанні.',
        ]);
    }

    private function createQuestion(string $text, string $correctOption, array $otherOptions): Question
    {
        $category = Category::create(['name' => 'Grammar']);

        $question = Question::create([
            'uuid' => (string) Str::uuid(),
            'question' => $text,
            'difficulty' => 1,
            'flag' => 0,
            'category_id' => $category->id,
            'level' => 'A1',
        ]);

        $optionRecords = [];
        $options = array_values(array_unique(array_merge([$correctOption], $otherOptions)));

        foreach ($options as $value) {
            $record = QuestionOption::firstOrCreate(['option' => $value]);
            $optionRecords[$value] = $record;

            DB::table('question_option_question')->insert([
                'question_id' => $question->id,
                'option_id' => $record->id,
                'flag' => null,
            ]);
        }

        QuestionAnswer::create([
            'question_id' => $question->id,
            'option_id' => $optionRecords[$correctOption]->id,
            'marker' => 'a1',
        ]);

        return $question;
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
                $table->boolean('flag')->default(0);
                $table->unsignedBigInteger('category_id');
                $table->string('level', 2)->nullable();
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
            'question_option_question',
            'question_answers',
            'questions',
            'question_options',
            'categories',
        ] as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->delete();
            }
        }
    }
}
