<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\{Artisan, Schema, DB};
use Tests\TestCase;
use App\Models\{Category, Question, QuestionOption, QuestionAnswer, VerbHint};

class VerbHintCreateTest extends TestCase
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
        '2025_08_04_000002_add_description_to_tests_table.php',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        foreach ($this->migrations as $file) {
            Artisan::call('migrate', ['--path' => 'database/migrations/' . $file]);
        }

        if (Schema::hasTable('question_options')) {
            DB::statement('DROP TABLE question_options');
        }

        DB::statement('CREATE TABLE question_options (id INTEGER PRIMARY KEY AUTOINCREMENT, option VARCHAR UNIQUE, created_at DATETIME, updated_at DATETIME)');

        if (! Schema::hasColumn('question_option_question', 'flag')) {
            Schema::table('question_option_question', function ($table) {
                $table->tinyInteger('flag')->nullable()->after('option_id');
            });
        }

        foreach ([
            'question_option_question',
            'question_answers',
            'verb_hints',
            'questions',
            'categories',
        ] as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->delete();
            }
        }
    }

    /** @test */
    public function adding_verb_hint_creates_option_and_record(): void
    {
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

        $response = $this->postJson(route('verb-hints.store'), [
            'question_id' => $question->id,
            'marker' => 'a1',
            'hint' => 'do',
        ]);
        $response->assertOk()->assertJsonPath('data.id', $question->id);
        $response->assertJsonFragment(['verb_hint' => ['value' => 'do']]);
        $this->assertDatabaseHas('verb_hints', [
            'question_id' => $question->id,
            'marker' => 'a1',
        ]);
        $hintOption = QuestionOption::where('option', 'do')->first();
        $this->assertNotNull($hintOption);
        $this->assertDatabaseHas('question_option_question', [
            'question_id' => $question->id,
            'option_id' => $hintOption->id,
            'flag' => 1,
        ]);
    }

    /** @test */
    public function adding_hint_for_existing_option_updates_flag(): void
    {
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

        $response = $this->postJson(route('verb-hints.store'), [
            'question_id' => $question->id,
            'marker' => 'a1',
            'hint' => 'run',
        ]);
        $response->assertOk()->assertJsonPath('data.id', $question->id);

        $this->assertEquals(1, QuestionOption::where('option', 'run')->count());
        $this->assertDatabaseHas('question_option_question', [
            'question_id' => $question->id,
            'option_id' => $option->id,
            'flag' => 1,
        ]);
    }

    /** @test */
    public function cannot_add_hint_when_marker_is_missing(): void
    {
        $category = Category::create(['name' => 'test']);

        $question = Question::create([
            'uuid' => 'q-missing',
            'question' => 'Q1 {a1}',
            'difficulty' => 1,
            'category_id' => $category->id,
        ]);

        $response = $this->postJson(route('verb-hints.store'), [
            'question_id' => $question->id,
            'marker' => 'a2',
            'hint' => 'verb',
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseCount('verb_hints', 0);
    }

    /** @test */
    public function cannot_add_duplicate_marker_for_question(): void
    {
        $category = Category::create(['name' => 'test']);

        $question = Question::create([
            'uuid' => 'q-dup',
            'question' => 'Q1 {a1}',
            'difficulty' => 1,
            'category_id' => $category->id,
        ]);

        $option = QuestionOption::create(['option' => 'run']);
        $question->options()->attach($option->id);

        $answer = new QuestionAnswer();
        $answer->marker = 'a1';
        $answer->answer = 'run';
        $answer->question_id = $question->id;
        $answer->save();

        VerbHint::create([
            'question_id' => $question->id,
            'marker' => 'a1',
            'option_id' => $option->id,
        ]);

        $response = $this->postJson(route('verb-hints.store'), [
            'question_id' => $question->id,
            'marker' => 'a1',
            'hint' => 'redo',
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseHas('verb_hints', [
            'question_id' => $question->id,
            'marker' => 'a1',
        ]);
    }
}
