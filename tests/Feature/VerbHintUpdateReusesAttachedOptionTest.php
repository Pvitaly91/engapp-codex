<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\{Artisan, Schema, DB};
use Tests\TestCase;
use App\Models\{Category, Question, QuestionOption, QuestionAnswer, VerbHint};

class VerbHintUpdateReusesAttachedOptionTest extends TestCase
{
    /** @test */
    public function renaming_hint_to_option_already_attached_updates_flag(): void
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

        $optExisting = QuestionOption::create(['option' => 'go']);
        $question->options()->attach($optExisting->id);
        $ans = new QuestionAnswer();
        $ans->marker = 'a1';
        $ans->answer = 'go';
        $ans->question_id = $question->id;
        $ans->save();

        $hintOption = QuestionOption::create(['option' => 'do']);
        $question->options()->attach($hintOption->id, ['flag' => 1]);

        $verbHint = VerbHint::create([
            'question_id' => $question->id,
            'marker' => 'a1',
            'option_id' => $hintOption->id,
        ]);

        $response = $this->putJson(route('verb-hints.update', $verbHint->id), [
            'hint' => 'go',
        ]);

        $response->assertNoContent();

        $this->assertEquals($optExisting->id, $verbHint->fresh()->option_id);
        $this->assertEquals(1, QuestionOption::where('option', 'go')->count());
        $this->assertDatabaseHas('question_option_question', [
            'question_id' => $question->id,
            'option_id' => $optExisting->id,
            'flag' => 1,
        ]);
    }
}
