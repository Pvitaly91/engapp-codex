<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;
use App\Models\{Category, Question, QuestionOption, QuestionAnswer, Test};

class SavedTestVersionsTest extends TestCase
{
    /** @test */
    public function random_and_step_pages_load(): void
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
        ];
        foreach ($migrations as $file) {
            Artisan::call('migrate', ['--path' => 'database/migrations/' . $file]);
        }

        Schema::table('question_option_question', function ($table) {
            $table->tinyInteger('flag')->nullable()->after('option_id');
        });

        $category = Category::create(['name' => 'test']);
        $question = Question::create([
            'uuid' => 'q1',
            'question' => 'Choose {a1}',
            'difficulty' => 1,
            'category_id' => $category->id,
        ]);
        $opt = new QuestionOption(['option' => 'yes']);
        $opt->question_id = $question->id;
        $opt->save();
        $question->options()->attach($opt->id);
        $qa = new QuestionAnswer();
        $qa->marker = 'a1';
        $qa->answer = 'yes';
        $qa->question_id = $question->id;
        $qa->save();

        $testModel = Test::create([
            'name' => 'sample',
            'slug' => 'sample',
            'filters' => [],
            'questions' => [$question->id],
        ]);

        $this->get('/test/' . $testModel->slug . '/random')->assertStatus(200);
        $this->get('/test/' . $testModel->slug . '/step')->assertStatus(200);
    }

    /** @test */
    public function step_mode_finishes_after_high_percentage(): void
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
        ];
        foreach ($migrations as $file) {
            Artisan::call('migrate', ['--path' => 'database/migrations/' . $file]);
        }

        Schema::table('question_option_question', function ($table) {
            $table->tinyInteger('flag')->nullable()->after('option_id');
        });

        $category = Category::create(['name' => 'test']);
        $q1 = Question::create([
            'uuid' => 'q1',
            'question' => 'Choose {a1}',
            'difficulty' => 1,
            'category_id' => $category->id,
        ]);
        $o1 = new QuestionOption(['option' => 'yes']);
        $o1->question_id = $q1->id;
        $o1->save();
        $q1->options()->attach($o1->id);
        $a1 = new QuestionAnswer();
        $a1->marker = 'a1';
        $a1->answer = 'yes';
        $a1->question_id = $q1->id;
        $a1->save();

        $q2 = Question::create([
            'uuid' => 'q2',
            'question' => 'Pick {a1} again',
            'difficulty' => 1,
            'category_id' => $category->id,
        ]);
        $o2 = new QuestionOption(['option' => 'yes']);
        $o2->question_id = $q2->id;
        $o2->save();
        $q2->options()->attach($o2->id);
        $a2 = new QuestionAnswer();
        $a2->marker = 'a1';
        $a2->answer = 'yes';
        $a2->question_id = $q2->id;
        $a2->save();

        $testModel = Test::create([
            'name' => 'sample',
            'slug' => 'sample',
            'filters' => [],
            'questions' => [$q1->id, $q2->id],
        ]);

        $this->get('/test/' . $testModel->slug . '/step')->assertStatus(200);

        $this->post('/test/' . $testModel->slug . '/step/check', [
            'question_id' => $q1->id,
            'answers' => ['a1' => 'yes'],
        ])->assertRedirect('/test/' . $testModel->slug . '/step');

        $this->get('/test/' . $testModel->slug . '/step')
            ->assertStatus(200)
            ->assertSee('Finished');
    }
}
