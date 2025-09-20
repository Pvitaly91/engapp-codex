<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionOption;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Feature\Concerns\EnsuresQuestionSchema;
use Tests\TestCase;

class QuestionAnswerUpdateTest extends TestCase
{
    use EnsuresQuestionSchema;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureQuestionSchema();
        $this->resetQuestionData();
    }

    /** @test */
    public function answer_value_can_be_updated_via_route(): void
    {
        $category = Category::create(['name' => 'Grammar']);

        $question = Question::create([
            'uuid' => 'question-1',
            'question' => 'I {a1} to school.',
            'difficulty' => 1,
            'category_id' => $category->id,
        ]);

        $optionGo = QuestionOption::create(['option' => 'go']);
        $question->options()->attach($optionGo->id);

        $answer = QuestionAnswer::create([
            'question_id' => $question->id,
            'marker' => 'a1',
            'option_id' => $optionGo->id,
        ]);

        $response = $this->putJson(route('question-answers.update', $answer->id), [
            'value' => 'goes',
        ]);

        $response->assertNoContent();

        $newOption = QuestionOption::where('option', 'goes')->first();
        $this->assertNotNull($newOption);

        $this->assertDatabaseHas('question_answers', [
            'id' => $answer->id,
            'option_id' => $newOption->id,
        ]);

        $this->assertDatabaseHas('question_option_question', [
            'question_id' => $question->id,
            'option_id' => $newOption->id,
        ]);

        $this->assertDatabaseMissing('question_option_question', [
            'question_id' => $question->id,
            'option_id' => $optionGo->id,
        ]);
    }

    private function resetQuestionData(): void
    {
        foreach ([
            'verb_hints',
            'question_answers',
            'question_option_question',
            'question_options',
            'questions',
            'categories',
        ] as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->delete();
            }
        }
    }
}
