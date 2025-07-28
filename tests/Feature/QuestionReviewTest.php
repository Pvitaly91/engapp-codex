<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;
use Illuminate\Support\Str;
use App\Models\{Category, Question, QuestionOption, QuestionAnswer, Tag};

class QuestionReviewTest extends TestCase
{
    // Ensures the question review flow shows a question and stores the user's selection
    /**
     * @test
     * Verify that a user can review a question and the review is saved.
     */
    public function question_review_flow(): void
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
            '2025_07_28_112705_create_question_review_results_table.php',
            '2025_07_28_113005_add_comment_to_question_review_results_table.php',
            '2025_07_28_113010_add_original_tags_to_question_review_results_table.php',
            '2025_07_31_000002_add_uuid_to_questions_table.php',
        ];
        foreach ($migrations as $file) {
            Artisan::call('migrate', ['--path' => 'database/migrations/' . $file]);
        }

        \Illuminate\Support\Facades\Schema::table('question_option_question', function ($table) {
            $table->tinyInteger('flag')->nullable()->after('option_id');
        });

        $category = Category::create(['name' => 'test']);
        $question = Question::create([
            'uuid' => 'test-question-1',
            'question' => 'Choose {a1}',
            'difficulty' => 1,
            'category_id' => $category->id,
        ]);
        $opt1 = new QuestionOption(['option' => 'yes']);
        $opt1->question_id = $question->id;
        $opt1->save();
        $opt2 = new QuestionOption(['option' => 'no']);
        $opt2->question_id = $question->id;
        $opt2->save();
        $question->options()->attach($opt1->id);
        $question->options()->attach($opt2->id);
        $qa = new QuestionAnswer(['marker' => 'a1']);
        $qa->question_id = $question->id;
        $qa->answer = 'yes';
        $qa->save();
        $tag1 = Tag::create(['name' => 'tag1']);
        $tag2 = Tag::create(['name' => 'tag2']);
        $question->tags()->attach($tag1->id);

        $response = $this->get('/question-review');
        $response->assertStatus(200);

        $edit = $this->get('/question-review/'.$question->id);
        $edit->assertStatus(200);

        $response = $this->post('/question-review', [
            'question_id' => $question->id,
            'answers' => ['a1' => 'no'],
            'tags' => [$tag2->id],
            'comment' => 'ok',
        ]);
        $response->assertRedirect('/question-review');

        $this->assertDatabaseHas('question_review_results', [
            'question_id' => $question->id,
            'comment' => 'ok',
            'original_tags' => json_encode([$tag1->id]),
            'tags' => json_encode([$tag2->id]),
        ]);

        $page = $this->get('/question-review-results');
        $page->assertStatus(200);
        $page->assertSee('Choose <strong>yes</strong>', false);
        $page->assertSee('Choose <strong>no</strong>', false);
        $page->assertSeeInOrder(['Original tags:', 'tag1'], false);
        $page->assertSeeInOrder(['Updated tags:', 'tag2'], false);
    }
}
