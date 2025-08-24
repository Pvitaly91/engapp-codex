<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\{Artisan, Schema, DB};
use Tests\TestCase;
use App\Models\{Category, Question, QuestionOption, QuestionAnswer, VerbHint};

class VerbHintDeleteTest extends TestCase
{
    /** @test */
    public function deleting_unshared_verb_hint_removes_option(): void
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

        $question = Question::create([
            'uuid' => 'q1',
            'question' => 'Q1 {a1}',
            'difficulty' => 1,
            'category_id' => $category->id,
        ]);

        $option = QuestionOption::create(['option' => 'run']);
        $question->options()->attach($option->id);
        $ans = new QuestionAnswer();
        $ans->marker = 'a1';
        $ans->answer = 'run';
        $ans->question_id = $question->id;
        $ans->save();

        $hintOption = QuestionOption::create(['option' => 'do']);
        $question->options()->attach($hintOption->id, ['flag' => 1]);
        $verbHint = VerbHint::create([
            'question_id' => $question->id,
            'marker' => 'a1',
            'option_id' => $hintOption->id,
        ]);

        $response = $this->deleteJson(route('verb-hints.destroy', $verbHint->id));

        $response->assertNoContent();
        $this->assertDatabaseMissing('verb_hints', ['id' => $verbHint->id]);
        $this->assertDatabaseMissing('question_option_question', [
            'question_id' => $question->id,
            'option_id' => $hintOption->id,
            'flag' => 1,
        ]);
        $this->assertDatabaseMissing('question_options', ['id' => $hintOption->id]);
    }

    /** @test */
    public function deleting_shared_verb_hint_detaches_only(): void
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
            'question' => 'Q1 {a1}',
            'difficulty' => 1,
            'category_id' => $category->id,
        ]);
        $q2 = Question::create([
            'uuid' => 'q2',
            'question' => 'Q2 {b1}',
            'difficulty' => 1,
            'category_id' => $category->id,
        ]);

        $sharedOption = QuestionOption::create(['option' => 'do']);
        $q1->options()->attach($sharedOption->id, ['flag' => 1]);
        $q2->options()->attach($sharedOption->id, ['flag' => 1]);

        $verbHint1 = VerbHint::create([
            'question_id' => $q1->id,
            'marker' => 'a1',
            'option_id' => $sharedOption->id,
        ]);
        VerbHint::create([
            'question_id' => $q2->id,
            'marker' => 'b1',
            'option_id' => $sharedOption->id,
        ]);

        $response = $this->deleteJson(route('verb-hints.destroy', $verbHint1->id));

        $response->assertNoContent();
        $this->assertDatabaseMissing('verb_hints', ['id' => $verbHint1->id]);
        $this->assertDatabaseHas('verb_hints', [
            'question_id' => $q2->id,
            'marker' => 'b1',
            'option_id' => $sharedOption->id,
        ]);
        $this->assertDatabaseHas('question_options', ['id' => $sharedOption->id]);
        $this->assertDatabaseHas('question_option_question', [
            'question_id' => $q2->id,
            'option_id' => $sharedOption->id,
            'flag' => 1,
        ]);
    }
}
