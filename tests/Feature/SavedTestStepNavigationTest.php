<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\{Artisan, Schema, DB};
use Tests\TestCase;
use App\Models\{Category, Question, QuestionOption, QuestionAnswer, Test};

class SavedTestStepNavigationTest extends TestCase
{
    /** @test */
    public function user_can_navigate_questions_without_answering(): void
    {
        $migrations = [
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
            '2025_08_04_000002_add_description_to_tests_table.php',
        ];
        foreach ($migrations as $file) {
            Artisan::call('migrate', ['--path' => 'database/migrations/' . $file]);
        }

        DB::statement('DROP TABLE question_options');
        DB::statement('CREATE TABLE question_options (id INTEGER PRIMARY KEY AUTOINCREMENT, option VARCHAR UNIQUE, created_at DATETIME, updated_at DATETIME)');

        Schema::table('question_option_question', function ($table) {
            $table->tinyInteger('flag')->nullable()->after('option_id');
        });

        $category = Category::create(['name' => 'cat']);

        $questions = [];
        for ($i = 1; $i <= 3; $i++) {
            $q = Question::create([
                'uuid' => 'q' . $i,
                'question' => 'Q' . $i . ' {a' . $i . '}',
                'difficulty' => 1,
                'category_id' => $category->id,
            ]);
            $option = QuestionOption::create(['option' => 'opt' . $i]);
            $q->options()->attach($option->id);
            $ans = new QuestionAnswer();
            $ans->marker = 'a' . $i;
            $ans->answer = 'opt' . $i;
            $ans->question_id = $q->id;
            $ans->save();
            $questions[] = $q->id;
        }

        $testModel = Test::create([
            'name' => 'sample',
            'slug' => 'sample',
            'filters' => [],
            'questions' => $questions,
        ]);

        $response = $this->get('/test/' . $testModel->slug . '/step');
        $response->assertStatus(200);
        $response->assertSee('Q1');
        $response->assertSee('?nav=next');

        $response = $this->get('/test/' . $testModel->slug . '/step?nav=next');
        $response->assertStatus(200);
        $response->assertSee('Q2');
        $response->assertSee('?nav=prev');

        $response = $this->get('/test/' . $testModel->slug . '/step?nav=prev');
        $response->assertStatus(200);
        $response->assertSee('Q1');
    }
}
