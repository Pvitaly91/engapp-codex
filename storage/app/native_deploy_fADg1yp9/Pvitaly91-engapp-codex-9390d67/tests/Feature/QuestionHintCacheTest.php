<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\{Category, Question};
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use App\Services\{ChatGPTService, GeminiService};

class QuestionHintCacheTest extends TestCase
{
    /** @test */
    public function hints_are_cached_and_can_be_refreshed()
    {
        $migrations = [
            '2025_07_20_143201_create_categories_table.php',
            '2025_07_20_143210_create_quastion_table.php',
            '2025_07_20_143243_create_quastion_answers_table.php',
            '2025_07_31_000002_add_uuid_to_questions_table.php',
            '2025_08_05_000001_create_question_hints_table.php',
        ];
        foreach ($migrations as $file) {
            Artisan::call('migrate', ['--path' => 'database/migrations/' . $file]);
        }

        $category = Category::create(['name' => 'Dummy']);
        $question = Question::create([
            'uuid' => (string) Str::uuid(),
            'question' => 'Test sentence?',
            'category_id' => $category->id,
        ]);

        $this->mock(ChatGPTService::class, function ($mock) {
            $mock->shouldReceive('hintSentenceStructure')
                ->twice()
                ->andReturn('first-gpt', 'second-gpt');
        });
        $this->mock(GeminiService::class, function ($mock) {
            $mock->shouldReceive('hintSentenceStructure')
                ->twice()
                ->andReturn('first-gemini', 'second-gemini');
        });

        $this->postJson(route('question.hint'), ['question_id' => $question->id])
            ->assertJson([
                'chatgpt' => 'first-gpt',
                'gemini' => 'first-gemini',
            ]);

        $this->assertDatabaseHas('question_hints', [
            'question_id' => $question->id,
            'provider' => 'chatgpt',
            'hint' => 'first-gpt',
        ]);

        // second request without refresh should use cached values
        $this->postJson(route('question.hint'), ['question_id' => $question->id])
            ->assertJson([
                'chatgpt' => 'first-gpt',
                'gemini' => 'first-gemini',
            ]);

        // request with refresh gets new values
        $this->postJson(route('question.hint'), ['question_id' => $question->id, 'refresh' => true])
            ->assertJson([
                'chatgpt' => 'second-gpt',
                'gemini' => 'second-gemini',
            ]);

        $this->assertDatabaseHas('question_hints', [
            'question_id' => $question->id,
            'provider' => 'chatgpt',
            'hint' => 'second-gpt',
        ]);
    }

    /** @test */
    public function hints_can_be_generated_from_question_text()
    {
        $this->mock(ChatGPTService::class, function ($mock) {
            $mock->shouldReceive('hintSentenceStructure')
                ->once()
                ->andReturn('text-gpt');
        });
        $this->mock(GeminiService::class, function ($mock) {
            $mock->shouldReceive('hintSentenceStructure')
                ->once()
                ->andReturn('text-gemini');
        });

        $this->postJson(route('question.hint'), ['question' => 'Sample sentence?'])
            ->assertJson([
                'chatgpt' => 'text-gpt',
                'gemini' => 'text-gemini',
            ]);
    }
}
