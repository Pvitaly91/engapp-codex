<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Question;
use App\Models\QuestionHint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Feature\Concerns\EnsuresQuestionSchema;
use Tests\TestCase;

class QuestionHintUpdateTest extends TestCase
{
    use EnsuresQuestionSchema;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureQuestionSchema();
        $this->resetQuestionData();
    }

    /** @test */
    public function hint_text_can_be_updated(): void
    {
        $category = Category::create(['name' => 'Grammar']);

        $question = Question::create([
            'uuid' => 'question-1',
            'question' => 'I {a1} to school.',
            'difficulty' => 1,
            'category_id' => $category->id,
        ]);

        $hint = QuestionHint::create([
            'question_id' => $question->id,
            'provider' => 'chatgpt',
            'locale' => 'uk',
            'hint' => 'Почніть із дієслова go.',
        ]);

        $response = $this->putJson(route('question-hints.update', $hint->id), [
            'hint' => 'Використовуйте форму go.',
        ]);

        $response->assertNoContent();

        $this->assertDatabaseHas('question_hints', [
            'id' => $hint->id,
            'hint' => 'Використовуйте форму go.',
        ]);
    }

    private function resetQuestionData(): void
    {
        foreach ([
            'question_hints',
            'questions',
            'categories',
        ] as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->delete();
            }
        }
    }
}
