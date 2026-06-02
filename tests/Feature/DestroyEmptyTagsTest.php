<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\{Artisan, Schema, DB};
use Tests\TestCase;
use App\Models\{Category, Question, QuestionOption, QuestionAnswer, Tag};

class DestroyEmptyTagsTest extends TestCase
{
    /** @test */
    public function destroy_empty_tags_deletes_tags_without_questions(): void
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
            '2025_07_18_182347_create_words_table.php',
            '2025_07_30_000001_create_tags_table.php',
            '2025_07_30_000002_create_tag_word_table.php',
            '2025_07_30_000003_create_question_tag_table.php',
            '2025_07_31_000002_add_uuid_to_questions_table.php',
            '2025_08_01_000001_add_category_to_tags_table.php',
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

        // Create tags
        $tagWithQuestion = Tag::create(['name' => 'Past Simple', 'category' => 'Tenses']);
        $emptyTag1 = Tag::create(['name' => 'Empty Tag 1', 'category' => 'Empty']);
        $emptyTag2 = Tag::create(['name' => 'Empty Tag 2', 'category' => null]);

        // Attach one tag to the question
        $question->tags()->attach($tagWithQuestion->id);

        // Verify initial state
        $this->assertEquals(3, Tag::count());

        // Call the destroy empty tags endpoint with session authentication
        $response = $this->withSession(['admin_authenticated' => true])
            ->delete('/admin/test-tags/empty');

        // Assert redirect and success message
        $response->assertRedirect(route('test-tags.index'));
        $response->assertSessionHas('status', 'Видалено тегів без питань: 2.');

        // Verify that only tags without questions were deleted
        $this->assertEquals(1, Tag::count());
        $this->assertDatabaseHas('tags', ['id' => $tagWithQuestion->id]);
        $this->assertDatabaseMissing('tags', ['id' => $emptyTag1->id]);
        $this->assertDatabaseMissing('tags', ['id' => $emptyTag2->id]);
    }

    /** @test */
    public function destroy_empty_tags_returns_message_when_no_empty_tags_exist(): void
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
            '2025_07_18_182347_create_words_table.php',
            '2025_07_30_000001_create_tags_table.php',
            '2025_07_30_000002_create_tag_word_table.php',
            '2025_07_30_000003_create_question_tag_table.php',
            '2025_07_31_000002_add_uuid_to_questions_table.php',
            '2025_08_01_000001_add_category_to_tags_table.php',
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

        // Create only tag with question
        $tagWithQuestion = Tag::create(['name' => 'Past Simple', 'category' => 'Tenses']);
        $question->tags()->attach($tagWithQuestion->id);

        // Verify initial state
        $this->assertEquals(1, Tag::count());

        // Call the destroy empty tags endpoint with session authentication
        $response = $this->withSession(['admin_authenticated' => true])
            ->delete('/admin/test-tags/empty');

        // Assert redirect and message
        $response->assertRedirect(route('test-tags.index'));
        $response->assertSessionHas('status', 'Не знайдено тегів без питань.');

        // Verify that tag with question was not deleted
        $this->assertEquals(1, Tag::count());
        $this->assertDatabaseHas('tags', ['id' => $tagWithQuestion->id]);
    }
}
