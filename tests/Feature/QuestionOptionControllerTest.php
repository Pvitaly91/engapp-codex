<?php

namespace Tests\Feature;

use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionOption;
use App\Models\VerbHint;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class QuestionOptionControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureSchema();
    }

    /** @test */
    public function it_creates_a_new_option_when_shared_between_questions(): void
    {
        $questionA = Question::create([
            'uuid' => 'q-a',
            'question' => 'They {a1} together.',
            'difficulty' => 1,
        ]);
        $questionB = Question::create([
            'uuid' => 'q-b',
            'question' => 'He {a1} alone.',
            'difficulty' => 1,
        ]);

        $sharedOption = QuestionOption::create(['option' => 'go']);

        $questionA->options()->attach($sharedOption->id, ['flag' => 0]);
        $questionB->options()->attach($sharedOption->id, ['flag' => 0]);

        $answer = QuestionAnswer::create([
            'question_id' => $questionA->id,
            'marker' => 'a1',
            'option_id' => $sharedOption->id,
        ]);

        $verbHint = VerbHint::create([
            'question_id' => $questionA->id,
            'marker' => 'a1',
            'option_id' => $sharedOption->id,
        ]);

        $response = $this->from('/back')->put(route('question-options.update', $sharedOption), [
            'question_id' => $questionA->id,
            'option' => 'walk',
            'from' => '/back',
        ]);

        $response->assertRedirect('/back');

        $newOption = QuestionOption::where('option', 'walk')->first();
        $this->assertNotNull($newOption);

        $this->assertDatabaseHas('question_answers', [
            'id' => $answer->id,
            'option_id' => $newOption->id,
        ]);

        $this->assertDatabaseHas('verb_hints', [
            'id' => $verbHint->id,
            'option_id' => $newOption->id,
        ]);

        $this->assertDatabaseHas('question_option_question', [
            'question_id' => $questionA->id,
            'option_id' => $newOption->id,
            'flag' => 0,
        ]);

        $this->assertDatabaseMissing('question_option_question', [
            'question_id' => $questionA->id,
            'option_id' => $sharedOption->id,
            'flag' => 0,
        ]);

        $this->assertDatabaseHas('question_option_question', [
            'question_id' => $questionB->id,
            'option_id' => $sharedOption->id,
            'flag' => 0,
        ]);

        $this->assertDatabaseHas('question_options', [
            'id' => $sharedOption->id,
            'option' => 'go',
        ]);
    }

    /** @test */
    public function it_stores_a_new_option_for_a_question(): void
    {
        $question = Question::create([
            'uuid' => 'q-option-store',
            'question' => 'Do they {a1}?',
            'difficulty' => 1,
        ]);

        $response = $this->from('/back')->post(route('question-options.store'), [
            'question_id' => $question->id,
            'option' => 'agree',
            'from' => '/back',
        ]);

        $response->assertRedirect('/back');

        $option = QuestionOption::where('option', 'agree')->first();
        $this->assertNotNull($option);

        $this->assertDatabaseHas('question_option_question', [
            'question_id' => $question->id,
            'option_id' => $option->id,
            'flag' => 0,
        ]);
    }

    /** @test */
    public function it_deletes_an_unused_option_from_a_question(): void
    {
        $question = Question::create([
            'uuid' => 'q-option-delete',
            'question' => 'He will {a1}.',
            'difficulty' => 1,
        ]);

        $option = QuestionOption::create(['option' => 'respond']);
        $question->options()->attach($option->id, ['flag' => 0]);

        $response = $this->from('/back')->delete(route('question-options.destroy', $option), [
            'question_id' => $question->id,
            'from' => '/back',
        ]);

        $response->assertRedirect('/back');

        $this->assertDatabaseMissing('question_option_question', [
            'question_id' => $question->id,
            'option_id' => $option->id,
            'flag' => 0,
        ]);

        $this->assertDatabaseMissing('question_options', [
            'id' => $option->id,
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
