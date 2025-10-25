<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Question;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Feature\Concerns\EnsuresQuestionSchema;
use Tests\TestCase;

class TechnicalQuestionCreateTest extends TestCase
{
    use EnsuresQuestionSchema;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureQuestionSchema();
        $this->resetQuestionData();
    }

    /** @test */
    public function answer_can_be_created_with_optional_verb_hint(): void
    {
        $category = Category::create(['name' => 'Grammar']);

        $question = Question::create([
            'uuid' => 'question-1',
            'question' => 'I {a1} to school.',
            'difficulty' => 1,
            'category_id' => $category->id,
        ]);

        $response = $this->postJson(route('question-answers.store'), [
            'question_id' => $question->id,
            'marker' => 'a1',
            'value' => 'go',
            'verb_hint' => 'do',
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.id', $question->id);
        $response->assertJsonPath('data.answers_by_marker.a1', 'go');
        $response->assertJsonFragment(['verb_hint' => ['value' => 'do']]);

        $this->assertDatabaseHas('question_answers', [
            'question_id' => $question->id,
            'marker' => 'a1',
        ]);

        $this->assertDatabaseHas('question_options', ['option' => 'go']);
        $this->assertDatabaseHas('question_options', ['option' => 'do']);
        $this->assertDatabaseHas('verb_hints', [
            'question_id' => $question->id,
            'marker' => 'a1',
        ]);
    }

    /** @test */
    public function option_can_be_added_to_question(): void
    {
        $category = Category::create(['name' => 'Grammar']);

        $question = Question::create([
            'uuid' => 'question-2',
            'question' => 'She {a1} tennis.',
            'difficulty' => 1,
            'category_id' => $category->id,
        ]);

        $response = $this->postJson(route('questions.options.store', $question->id), [
            'value' => 'plays',
        ]);

        $response->assertOk()->assertJsonFragment(['label' => 'plays']);

        $this->assertDatabaseHas('question_options', ['option' => 'plays']);
        $this->assertDatabaseHas('question_option_question', [
            'question_id' => $question->id,
        ]);
    }

    /** @test */
    public function question_hint_can_be_created(): void
    {
        $category = Category::create(['name' => 'Grammar']);

        $question = Question::create([
            'uuid' => 'question-3',
            'question' => 'They {a1} here often.',
            'difficulty' => 1,
            'category_id' => $category->id,
        ]);

        $response = $this->postJson(route('question-hints.store'), [
            'question_id' => $question->id,
            'provider' => 'system',
            'locale' => 'uk',
            'hint' => 'Використайте Present Simple.',
        ]);

        $response->assertOk()->assertJsonFragment([
            'provider' => 'system',
            'locale' => 'uk',
        ]);

        $this->assertDatabaseHas('question_hints', [
            'question_id' => $question->id,
            'provider' => 'system',
            'locale' => 'uk',
        ]);
    }

    /** @test */
    public function chatgpt_explanation_can_be_created(): void
    {
        $category = Category::create(['name' => 'Grammar']);

        $question = Question::create([
            'uuid' => 'question-4',
            'question' => 'He {a1} coffee.',
            'difficulty' => 1,
            'category_id' => $category->id,
        ]);

        $response = $this->postJson(route('chatgpt-explanations.store'), [
            'question_id' => $question->id,
            'language' => 'uk',
            'correct_answer' => 'drinks',
            'wrong_answer' => 'drink',
            'explanation' => 'Потрібно додати -s у третій особі.',
        ]);

        $response->assertOk()->assertJsonFragment([
            'correct_answer' => 'drinks',
            'language' => 'uk',
        ]);

        $this->assertDatabaseHas('chatgpt_explanations', [
            'question' => $question->question,
            'correct_answer' => 'drinks',
            'language' => 'uk',
        ]);
    }

    private function resetQuestionData(): void
    {
        foreach ([
            'chatgpt_explanations',
            'question_hints',
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
