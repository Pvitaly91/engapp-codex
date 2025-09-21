<?php

namespace Tests\Feature;

use App\Models\{Category, Question, QuestionOption, QuestionAnswer, VerbHint};
use Illuminate\Support\Facades\{Artisan, Schema, DB};
use Tests\TestCase;

class VerbHintUpdateRenameBackTest extends TestCase
{
    /** @test */
    public function can_rename_verb_hint_back_to_original_value(): void
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
            'question' => 'Q2 {a1}',
            'difficulty' => 1,
            'category_id' => $category->id,
        ]);

        $optionRun = QuestionOption::create(['option' => 'run']);
        $q1->options()->attach($optionRun->id);
        $ans1 = new QuestionAnswer();
        $ans1->marker = 'a1';
        $ans1->answer = 'run';
        $ans1->question_id = $q1->id;
        $ans1->save();

        $q2->options()->attach($optionRun->id);
        $ans2 = new QuestionAnswer();
        $ans2->marker = 'a1';
        $ans2->answer = 'run';
        $ans2->question_id = $q2->id;
        $ans2->save();

        $verbHint1 = VerbHint::create([
            'question_id' => $q1->id,
            'marker' => 'a1',
            'option_id' => $optionRun->id,
        ]);
        VerbHint::create([
            'question_id' => $q2->id,
            'marker' => 'a1',
            'option_id' => $optionRun->id,
        ]);

        $this->putJson(route('verb-hints.update', $verbHint1->id), ['hint' => 'jump'])
            ->assertOk()
            ->assertJsonPath('data.id', $q1->id)
            ->assertJsonPath('data.answers.0.verb_hint.value', 'jump');

        $verbHint1 = $verbHint1->fresh();

        $this->putJson(route('verb-hints.update', $verbHint1->id), ['hint' => 'run'])
            ->assertOk()
            ->assertJsonPath('data.id', $q1->id)
            ->assertJsonPath('data.answers.0.verb_hint.value', 'run');

        $this->assertDatabaseHas('verb_hints', [
            'id' => $verbHint1->id,
            'option_id' => $optionRun->id,
        ]);

        $this->assertDatabaseHas('question_option_question', [
            'question_id' => $q1->id,
            'option_id' => $optionRun->id,
            'flag' => 1,
        ]);

        $this->assertDatabaseMissing('question_options', [
            'option' => 'jump',
        ]);

        $this->assertEquals(1, DB::table('question_option_question')
            ->where('question_id', $q1->id)
            ->where('option_id', $optionRun->id)
            ->count());
    }
}
