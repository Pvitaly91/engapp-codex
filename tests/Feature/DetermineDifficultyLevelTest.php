<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\{Artisan, Schema, DB};
use Tests\TestCase;
use App\Models\{Category, Question, QuestionOption, QuestionAnswer, Test};
use App\Services\{ChatGPTService, GeminiService};

class DetermineDifficultyLevelTest extends TestCase
{
    private array $migrations = [
        '2025_07_20_143201_create_categories_table.php',
        '2025_07_20_143210_create_quastion_table.php',
        '2025_07_20_143230_create_quastion_options_table.php',
        '2025_07_20_143243_create_quastion_answers_table.php',
        '2025_07_20_164021_add_verb_hint_to_question_answers.php',
        '2025_07_20_180521_add_flag_to_question_table.php',
        '2025_07_20_193626_add_source_to_qustion_table.php',
        '2025_07_27_000001_add_unique_index_to_question_answers.php',
        '2025_07_28_000002_create_verb_hints_table.php',
        '2025_07_29_000001_create_question_option_question_table.php',
        '2025_07_30_000001_create_tags_table.php',
        '2025_07_30_000003_create_question_tag_table.php',
        '2025_07_31_000002_add_uuid_to_questions_table.php',
        '2025_07_20_184450_create_tests_table.php',
        '2025_08_01_000001_add_category_to_tags_table.php',
        '2025_08_04_000002_add_description_to_tests_table.php',
    ];

    private function setupDatabase(): array
    {
        Artisan::call('migrate:reset');
        foreach ($this->migrations as $file) {
            Artisan::call('migrate', ['--path' => 'database/migrations/' . $file]);
        }

        DB::statement('DROP TABLE question_options');
        DB::statement('CREATE TABLE question_options (id INTEGER PRIMARY KEY AUTOINCREMENT, option VARCHAR UNIQUE, created_at DATETIME, updated_at DATETIME)');

        Schema::table('question_option_question', function ($table) {
            $table->tinyInteger('flag')->nullable()->after('option_id');
        });

        $category = Category::create(['name' => 'test']);

        $question = Question::create([
            'uuid' => 'q1',
            'question' => 'Q1 {a1}',
            'difficulty' => 1,
            'category_id' => $category->id,
        ]);

        $option = QuestionOption::create(['option' => 'yes']);
        $question->options()->attach($option->id);
        $answer = new QuestionAnswer();
        $answer->marker = 'a1';
        $answer->answer = 'yes';
        $answer->question_id = $question->id;
        $answer->save();

        $test = Test::create([
            'name' => 'sample',
            'slug' => 'sample',
            'filters' => [],
            'questions' => [$question->id],
        ]);

        return [$question, $test];
    }

    /** @test */
    public function determine_level_returns_level_from_chatgpt(): void
    {
        [$question, $test] = $this->setupDatabase();

        $this->mock(ChatGPTService::class, function ($mock) {
            $mock->shouldReceive('determineDifficulty')
                ->with('Q1 {a1}')
                ->once()
                ->andReturn('A1');
        });

        $response = $this->postJson('/admin/test/' . $test->slug . '/step/determine-level', [
            'question_id' => $question->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['level' => 'A1']);
    }

    /** @test */
    public function determine_level_returns_level_from_gemini(): void
    {
        [$question, $test] = $this->setupDatabase();

        $this->mock(GeminiService::class, function ($mock) {
            $mock->shouldReceive('determineDifficulty')
                ->with('Q1 {a1}')
                ->once()
                ->andReturn('B1');
        });

        $response = $this->postJson('/admin/test/' . $test->slug . '/step/determine-level-gemini', [
            'question_id' => $question->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['level' => 'B1']);
    }
}

