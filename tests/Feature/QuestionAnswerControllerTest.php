<?php

namespace Tests\Feature;

use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionOption;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class QuestionAnswerControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureSchema();
    }

    /** @test */
    public function it_updates_answer_value_and_reassigns_option(): void
    {
        $question = Question::create([
            'uuid' => 'q-test',
            'question' => 'I {a1} to school.',
            'difficulty' => 1,
        ]);

        $oldOption = QuestionOption::create(['option' => 'goes']);
        $question->options()->attach($oldOption->id, ['flag' => 0]);

        $answer = QuestionAnswer::create([
            'question_id' => $question->id,
            'marker' => 'a1',
            'option_id' => $oldOption->id,
        ]);

        $response = $this->from('/back')->put(route('question-answers.update', $answer), [
            'marker' => 'A1',
            'value' => 'go',
            'from' => '/back',
        ]);

        $response->assertRedirect('/back');

        $newOption = QuestionOption::where('option', 'go')->first();
        $this->assertNotNull($newOption);

        $this->assertDatabaseHas('question_answers', [
            'id' => $answer->id,
            'marker' => 'a1',
            'option_id' => $newOption->id,
        ]);

        $this->assertDatabaseHas('question_option_question', [
            'question_id' => $question->id,
            'option_id' => $newOption->id,
            'flag' => 0,
        ]);

        $this->assertDatabaseMissing('question_options', [
            'id' => $oldOption->id,
        ]);
    }

    private function ensureSchema(): void
    {
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
    }
}
