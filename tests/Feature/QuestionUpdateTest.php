<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;
use App\Models\{Category, Question};

class QuestionUpdateTest extends TestCase
{
    /** @test */
    public function question_text_can_be_updated_via_route(): void
    {
        $migrations = [
            '2025_07_20_143201_create_categories_table.php',
            '2025_07_20_143210_create_quastion_table.php',
            '2025_07_20_180521_add_flag_to_question_table.php',
            '2025_07_20_193626_add_source_to_qustion_table.php',
            '2025_07_31_000002_add_uuid_to_questions_table.php',
            '2025_07_31_000003_add_level_to_questions_table.php',
        ];
        foreach ($migrations as $file) {
            Artisan::call('migrate', ['--path' => 'database/migrations/' . $file]);
        }

        $category = Category::create(['name' => 'test']);
        $question = Question::create([
            'uuid' => 'q1',
            'question' => 'Original {a1}',
            'difficulty' => 1,
            'category_id' => $category->id,
        ]);

        $response = $this->putJson(route('questions.update', $question->id), [
            'question' => 'Updated {a1}',
        ]);

        $response->assertNoContent();
        $this->assertDatabaseHas('questions', [
            'id' => $question->id,
            'question' => 'Updated {a1}',
        ]);
    }
}
