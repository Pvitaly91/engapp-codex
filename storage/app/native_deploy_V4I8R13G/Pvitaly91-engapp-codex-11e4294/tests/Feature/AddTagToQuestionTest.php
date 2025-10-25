<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\{Artisan, Schema, DB};
use Tests\TestCase;
use App\Models\{Category, Question, QuestionOption, QuestionAnswer, Test, Tag};

class AddTagToQuestionTest extends TestCase
{
    /** @test */
    public function add_tag_endpoint_attaches_tag_to_question(): void
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
            '2025_08_01_000001_add_category_to_tags_table.php',
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

        $tag = Tag::create(['name' => 'Past Simple', 'category' => 'Tenses']);

        $testModel = Test::create([
            'name' => 'sample',
            'slug' => 'sample',
            'filters' => [],
            'questions' => [$question->id],
        ]);

        $response = $this->postJson('/admin/test/' . $testModel->slug . '/step/add-tag', [
            'question_id' => $question->id,
            'tag' => $tag->name,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['tags' => [$tag->name]]);
        $this->assertDatabaseHas('question_tag', [
            'question_id' => $question->id,
            'tag_id' => $tag->id,
        ]);
    }
}

