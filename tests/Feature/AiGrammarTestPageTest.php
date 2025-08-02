<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;
use App\Models\{Category, Tag};
use App\Services\ChatGPTService;

class AiGrammarTestPageTest extends TestCase
{
    /** @test */
    public function page_generates_question_and_stores_it(): void
    {
        $migrations = [
            '2025_07_20_143201_create_categories_table.php',
            '2025_07_20_143210_create_quastion_table.php',
            '2025_07_20_143230_create_quastion_options_table.php',
            '2025_07_20_143243_create_quastion_answers_table.php',
            '2025_07_20_164021_add_verb_hint_to_question_answers.php',
            '2025_07_20_180521_add_flag_to_question_table.php',
            '2025_07_20_193626_add_source_to_qustion_table.php',
            '2025_07_26_000001_create_sources_table.php',
            '2025_07_27_000001_add_unique_index_to_question_answers.php',
            '2025_07_28_000002_create_verb_hints_table.php',
            '2025_07_29_000001_create_question_option_question_table.php',
            '2025_07_30_000001_create_tags_table.php',
            '2025_07_30_000003_create_question_tag_table.php',
            '2025_07_31_000002_add_uuid_to_questions_table.php',
            '2025_07_18_182347_create_words_table.php',
            '2025_07_20_184450_create_tests_table.php',
            '2025_08_01_000001_add_category_to_tags_table.php',
            '2025_08_04_000002_add_description_to_tests_table.php',
        ];
        foreach ($migrations as $file) {
            Artisan::call('migrate', ['--path' => 'database/migrations/' . $file]);
        }

        \Illuminate\Support\Facades\DB::statement('CREATE TABLE temp_options (id INTEGER PRIMARY KEY AUTOINCREMENT, option VARCHAR UNIQUE, created_at DATETIME, updated_at DATETIME)');
        \Illuminate\Support\Facades\DB::statement('INSERT INTO temp_options (id, option, created_at, updated_at) SELECT id, option, created_at, updated_at FROM question_options');
        \Illuminate\Support\Facades\DB::statement('DROP TABLE question_options');
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE temp_options RENAME TO question_options');

        \Illuminate\Support\Facades\DB::statement('DROP TABLE question_answers');
        \Illuminate\Support\Facades\DB::statement('CREATE TABLE question_answers (id INTEGER PRIMARY KEY AUTOINCREMENT, question_id INTEGER, marker VARCHAR, option_id INTEGER, created_at DATETIME, updated_at DATETIME)');

        \Illuminate\Support\Facades\Schema::table('question_option_question', function ($table) {
            $table->tinyInteger('flag')->nullable()->after('option_id');
        });

        $category = Category::create(['name' => 'Present']);
        $tag = Tag::create(['name' => 'tag1', 'category' => 'Tenses']);

        $this->mock(ChatGPTService::class, function ($mock) {
            $mock->shouldReceive('generateGrammarQuestion')
                ->andReturn([
                    'question' => 'He {a1} here.',
                    'answers' => ['a1' => 'is'],
                    'verb_hints' => ['a1' => 'be'],
                ]);
            $mock->shouldReceive('explainWrongAnswer')->andReturn('x');
        });

        $this->post('/ai-test/start', [
            'tags' => [$tag->id],
            'answers_min' => 1,
            'answers_max' => 2,
        ])->assertRedirect('/ai-test/step');

        $this->get('/ai-test/step')->assertStatus(200);

        $this->assertDatabaseHas('words', ['word' => 'he']);
        $this->assertDatabaseHas('words', ['word' => 'here']);
        $this->assertDatabaseHas('words', ['word' => 'is']);

        $this->assertDatabaseMissing('questions', ['question' => 'He {a1} here.']);

        $this->post('/ai-test/check', [
            'answers' => ['a1' => 'is'],
        ])->assertRedirect('/ai-test/step');

        $this->assertDatabaseHas('questions', ['question' => 'He {a1} here.', 'flag' => 1]);
        $this->assertDatabaseHas('verb_hints', ['marker' => 'a1']);
    }
}
