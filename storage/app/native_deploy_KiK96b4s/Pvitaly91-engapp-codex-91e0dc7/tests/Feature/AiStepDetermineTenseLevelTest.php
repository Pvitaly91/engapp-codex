<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Artisan;
use App\Models\Tag;
use App\Services\{ChatGPTService, GeminiService};

class AiStepDetermineTenseLevelTest extends TestCase
{
    private array $migrations = [
        '2025_07_30_000001_create_tags_table.php',
        '2025_08_01_000001_add_category_to_tags_table.php',
    ];

    private function setupDatabase(): void
    {
        Artisan::call('migrate:reset');
        foreach ($this->migrations as $file) {
            Artisan::call('migrate', ['--path' => 'database/migrations/' . $file]);
        }
        Tag::create(['name' => 'Past Simple', 'category' => 'Tenses']);
        Tag::create(['name' => 'Present Simple', 'category' => 'Tenses']);
    }

    /** @test */
    public function determine_tense_returns_tags_from_chatgpt_on_ai_step(): void
    {
        $this->setupDatabase();

        $this->withSession(['ai_step.current_question' => ['question' => 'Q1 {a1}']]);

        $this->mock(ChatGPTService::class, function ($mock) {
            $mock->shouldReceive('determineTenseTags')
                ->withArgs(function ($questionText, $tenses) {
                    return $questionText === 'Q1 {a1}' && $tenses === ['Past Simple', 'Present Simple'];
                })
                ->once()
                ->andReturn(['Past Simple', 'Present Simple']);
        });

        $response = $this->postJson('/admin/ai-test/step/determine-tense');

        $response->assertStatus(200);
        $response->assertJson(['tags' => ['Past Simple', 'Present Simple']]);
    }

    /** @test */
    public function determine_tense_returns_tags_from_gemini_on_ai_step(): void
    {
        $this->setupDatabase();

        $this->withSession(['ai_step.current_question' => ['question' => 'Q1 {a1}']]);

        $this->mock(GeminiService::class, function ($mock) {
            $mock->shouldReceive('determineTenseTags')
                ->withArgs(function ($questionText, $tenses) {
                    return $questionText === 'Q1 {a1}' && $tenses === ['Past Simple', 'Present Simple'];
                })
                ->once()
                ->andReturn(['Past Simple', 'Present Simple']);
        });

        $response = $this->postJson('/admin/ai-test/step/determine-tense-gemini');

        $response->assertStatus(200);
        $response->assertJson(['tags' => ['Past Simple', 'Present Simple']]);
    }

    /** @test */
    public function determine_level_returns_level_from_chatgpt_on_ai_step(): void
    {
        $this->setupDatabase();

        $this->withSession(['ai_step.current_question' => ['question' => 'Q1 {a1}']]);

        $this->mock(ChatGPTService::class, function ($mock) {
            $mock->shouldReceive('determineDifficulty')
                ->with('Q1 {a1}')
                ->once()
                ->andReturn('A1');
        });

        $response = $this->postJson('/admin/ai-test/step/determine-level');

        $response->assertStatus(200);
        $response->assertJson(['level' => 'A1']);
    }

    /** @test */
    public function determine_level_returns_level_from_gemini_on_ai_step(): void
    {
        $this->setupDatabase();

        $this->withSession(['ai_step.current_question' => ['question' => 'Q1 {a1}']]);

        $this->mock(GeminiService::class, function ($mock) {
            $mock->shouldReceive('determineDifficulty')
                ->with('Q1 {a1}')
                ->once()
                ->andReturn('B1');
        });

        $response = $this->postJson('/admin/ai-test/step/determine-level-gemini');

        $response->assertStatus(200);
        $response->assertJson(['level' => 'B1']);
    }
}
