<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Question;
use App\Models\QuestionVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Feature\Concerns\EnsuresQuestionSchema;
use Tests\TestCase;

class QuestionVariantUpdateTest extends TestCase
{
    use EnsuresQuestionSchema;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureQuestionSchema();
        $this->resetQuestionData();
    }

    /** @test */
    public function variant_text_can_be_updated(): void
    {
        $category = Category::create(['name' => 'Grammar']);

        $question = Question::create([
            'uuid' => 'question-1',
            'question' => 'They {a1} together.',
            'difficulty' => 1,
            'category_id' => $category->id,
        ]);

        $variant = QuestionVariant::create([
            'question_id' => $question->id,
            'text' => 'We {a1} as well.',
        ]);

        $response = $this->putJson(route('question-variants.update', $variant->id), [
            'text' => 'We also {a1}.',
        ]);

        $response->assertNoContent();

        $this->assertDatabaseHas('question_variants', [
            'id' => $variant->id,
            'text' => 'We also {a1}.',
        ]);
    }

    private function resetQuestionData(): void
    {
        foreach ([
            'question_variants',
            'questions',
            'categories',
        ] as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->delete();
            }
        }
    }
}
