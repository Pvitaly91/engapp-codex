<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\{Artisan, Schema, DB};
use Tests\TestCase;
use App\Models\{Category, Question, QuestionOption, QuestionAnswer, Test};

class SavedTestStepOrderTest extends TestCase
{
    /** @test */
    public function step_page_can_switch_between_sequential_and_random_order(): void
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

        $category = Category::create(['name' => 'test']);
        $q1 = Question::create([
            'uuid' => 'q1',
            'question' => 'Q1',
            'difficulty' => 1,
            'category_id' => $category->id,
        ]);
        $opt1 = QuestionOption::create(['option' => 'yes']);
        $q1->options()->attach($opt1->id);
        $qa1 = new QuestionAnswer();
        $qa1->marker = 'a1';
        $qa1->answer = 'yes';
        $qa1->question_id = $q1->id;
        $qa1->save();

        $q2 = Question::create([
            'uuid' => 'q2',
            'question' => 'Q2',
            'difficulty' => 1,
            'category_id' => $category->id,
        ]);
        $opt2 = QuestionOption::create(['option' => 'no']);
        $q2->options()->attach($opt2->id);
        $qa2 = new QuestionAnswer();
        $qa2->marker = 'a1';
        $qa2->answer = 'no';
        $qa2->question_id = $q2->id;
        $qa2->save();

        $testModel = Test::create([
            'name' => 'sample',
            'slug' => 'sample',
            'filters' => [],
            'questions' => [$q2->id, $q1->id],
        ]);

        $response = $this->get('/test/' . $testModel->slug);
        $response->assertStatus(200);
        $response->assertSeeInOrder(['Q1', 'Q2']);

        $response = $this->get('/test/' . $testModel->slug . '/step');
        $response->assertStatus(200);
        $response->assertSee('Q1');

        $response = $this->get('/test/' . $testModel->slug . '/step?order=random');
        $response->assertStatus(200);
        $this->assertEquals('random', session('step_' . $testModel->slug . '_order'));
    }
}
