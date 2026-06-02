<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Page;
use App\Models\PageCategory;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionOption;
use App\Models\Tag;
use App\Models\Test;
use App\Models\TextBlock;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class TechPageTheoryBlocksTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureSchema();
        $this->resetData();
    }

    /** @test */
    public function it_shows_question_tags_on_tech_page(): void
    {
        $category = Category::create(['name' => 'Present Simple']);

        $tag1 = Tag::create(['name' => 'present-simple', 'category' => 'Tenses']);
        $tag2 = Tag::create(['name' => 'positive-sentences', 'category' => 'Grammar']);

        $question = Question::create([
            'uuid' => (string) Str::uuid(),
            'question' => 'I {a1} to school every day.',
            'difficulty' => 1,
            'level' => 'A1',
            'category_id' => $category->id,
        ]);

        $question->tags()->attach([$tag1->id, $tag2->id]);

        $optionGo = QuestionOption::create(['option' => 'go']);
        $question->options()->attach([$optionGo->id]);

        QuestionAnswer::create([
            'question_id' => $question->id,
            'marker' => 'a1',
            'option_id' => $optionGo->id,
        ]);

        $test = Test::create([
            'name' => 'Simple test',
            'slug' => 'simple-test',
            'filters' => [],
            'questions' => [$question->id],
        ]);

        $response = $this->withSession(['admin_authenticated' => true])
            ->get(route('saved-test.tech', $test->slug));

        $response->assertOk();
        $response->assertSee('Question tags:');
        $response->assertSee('present-simple');
        $response->assertSee('positive-sentences');
    }

    /** @test */
    public function it_shows_no_tags_message_when_question_has_no_tags(): void
    {
        $category = Category::create(['name' => 'Present Simple']);

        $question = Question::create([
            'uuid' => (string) Str::uuid(),
            'question' => 'I {a1} to school every day.',
            'difficulty' => 1,
            'level' => 'A1',
            'category_id' => $category->id,
        ]);

        $optionGo = QuestionOption::create(['option' => 'go']);
        $question->options()->attach([$optionGo->id]);

        QuestionAnswer::create([
            'question_id' => $question->id,
            'marker' => 'a1',
            'option_id' => $optionGo->id,
        ]);

        $test = Test::create([
            'name' => 'Simple test',
            'slug' => 'simple-test',
            'filters' => [],
            'questions' => [$question->id],
        ]);

        $response = $this->withSession(['admin_authenticated' => true])
            ->get(route('saved-test.tech', $test->slug));

        $response->assertOk();
        $response->assertSee('Question tags:');
        $response->assertSee('No tags');
    }

    /** @test */
    public function it_shows_matched_theory_blocks_section(): void
    {
        $category = Category::create(['name' => 'Present Simple']);

        $tag1 = Tag::create(['name' => 'present-simple', 'category' => 'Tenses']);
        $tag2 = Tag::create(['name' => 'types-of-questions', 'category' => 'Grammar']);

        $question = Question::create([
            'uuid' => (string) Str::uuid(),
            'question' => 'I {a1} to school every day.',
            'difficulty' => 1,
            'level' => 'A1',
            'category_id' => $category->id,
        ]);

        $question->tags()->attach([$tag1->id, $tag2->id]);

        $optionGo = QuestionOption::create(['option' => 'go']);
        $question->options()->attach([$optionGo->id]);

        QuestionAnswer::create([
            'question_id' => $question->id,
            'marker' => 'a1',
            'option_id' => $optionGo->id,
        ]);

        $test = Test::create([
            'name' => 'Simple test',
            'slug' => 'simple-test',
            'filters' => [],
            'questions' => [$question->id],
        ]);

        $response = $this->withSession(['admin_authenticated' => true])
            ->get(route('saved-test.tech', $test->slug));

        $response->assertOk();
        $response->assertSee('Matched theory (text_blocks)');
    }

    /** @test */
    public function it_shows_warning_when_no_matching_theory_blocks(): void
    {
        $category = Category::create(['name' => 'Present Simple']);

        // Only add types-of-questions tag - this should NOT match
        // because matching requires at least one tag OTHER than types-of-questions
        $tag = Tag::create(['name' => 'types-of-questions', 'category' => 'Grammar']);

        $question = Question::create([
            'uuid' => (string) Str::uuid(),
            'question' => 'I {a1} to school every day.',
            'difficulty' => 1,
            'level' => 'A1',
            'category_id' => $category->id,
        ]);

        $question->tags()->attach([$tag->id]);

        $optionGo = QuestionOption::create(['option' => 'go']);
        $question->options()->attach([$optionGo->id]);

        QuestionAnswer::create([
            'question_id' => $question->id,
            'marker' => 'a1',
            'option_id' => $optionGo->id,
        ]);

        $test = Test::create([
            'name' => 'Simple test',
            'slug' => 'simple-test',
            'filters' => [],
            'questions' => [$question->id],
        ]);

        $response = $this->withSession(['admin_authenticated' => true])
            ->get(route('saved-test.tech', $test->slug));

        $response->assertOk();
        $response->assertSee('No matching theory blocks found');
    }

    /** @test */
    public function it_matches_theory_blocks_by_tags(): void
    {
        $category = Category::create(['name' => 'Present Simple']);
        $pageCategory = PageCategory::create([
            'title' => 'Types of Questions',
            'slug' => 'types-of-questions',
            'language' => 'en',
        ]);

        $page = Page::create([
            'title' => 'Present Simple Questions',
            'slug' => 'present-simple-questions',
            'text' => 'Theory about present simple questions',
            'page_category_id' => $pageCategory->id,
        ]);

        $textBlock = TextBlock::create([
            'uuid' => (string) Str::uuid(),
            'heading' => 'How to form present simple questions',
            'body' => 'To form a question in present simple, use do/does + subject + base verb. For example: Do you like coffee?',
            'page_id' => $page->id,
            'page_category_id' => $pageCategory->id,
            'sort_order' => 1,
        ]);

        // Create tags and attach to both question and text block
        $tag1 = Tag::create(['name' => 'present-simple', 'category' => 'Tenses']);
        $tag2 = Tag::create(['name' => 'types-of-questions', 'category' => 'Grammar']);

        $textBlock->tags()->attach([$tag1->id, $tag2->id]);

        $question = Question::create([
            'uuid' => (string) Str::uuid(),
            'question' => '{a1} you like coffee?',
            'difficulty' => 1,
            'level' => 'A1',
            'category_id' => $category->id,
        ]);

        $question->tags()->attach([$tag1->id, $tag2->id]);

        $optionDo = QuestionOption::create(['option' => 'Do']);
        $question->options()->attach([$optionDo->id]);

        QuestionAnswer::create([
            'question_id' => $question->id,
            'marker' => 'a1',
            'option_id' => $optionDo->id,
        ]);

        $test = Test::create([
            'name' => 'Simple test',
            'slug' => 'simple-test',
            'filters' => [],
            'questions' => [$question->id],
        ]);

        $response = $this->withSession(['admin_authenticated' => true])
            ->get(route('saved-test.tech', $test->slug));

        $response->assertOk();
        $response->assertSee('Matched theory (text_blocks)');
        $response->assertSee('Score: 2'); // Matches both tags
        $response->assertSee('How to form present simple questions');
        $response->assertSee('Matched tags:');
        $response->assertSee('present-simple');
    }

    /** @test */
    public function it_does_not_match_when_only_types_of_questions_tag_matches(): void
    {
        $category = Category::create(['name' => 'Present Simple']);
        $pageCategory = PageCategory::create([
            'title' => 'Types of Questions',
            'slug' => 'types-of-questions',
            'language' => 'en',
        ]);

        $page = Page::create([
            'title' => 'General Questions',
            'slug' => 'general-questions',
            'text' => 'Theory about general questions',
            'page_category_id' => $pageCategory->id,
        ]);

        $textBlock = TextBlock::create([
            'uuid' => (string) Str::uuid(),
            'heading' => 'General question formation',
            'body' => 'Questions are formed differently in different tenses.',
            'page_id' => $page->id,
            'page_category_id' => $pageCategory->id,
            'sort_order' => 1,
        ]);

        // Text block only has types-of-questions tag
        $tagTypesOfQuestions = Tag::create(['name' => 'types-of-questions', 'category' => 'Grammar']);
        $textBlock->tags()->attach([$tagTypesOfQuestions->id]);

        // Question has both types-of-questions and present-simple
        $tagPresentSimple = Tag::create(['name' => 'present-simple', 'category' => 'Tenses']);

        $question = Question::create([
            'uuid' => (string) Str::uuid(),
            'question' => '{a1} you like coffee?',
            'difficulty' => 1,
            'level' => 'A1',
            'category_id' => $category->id,
        ]);

        $question->tags()->attach([$tagTypesOfQuestions->id, $tagPresentSimple->id]);

        $optionDo = QuestionOption::create(['option' => 'Do']);
        $question->options()->attach([$optionDo->id]);

        QuestionAnswer::create([
            'question_id' => $question->id,
            'marker' => 'a1',
            'option_id' => $optionDo->id,
        ]);

        $test = Test::create([
            'name' => 'Simple test',
            'slug' => 'simple-test',
            'filters' => [],
            'questions' => [$question->id],
        ]);

        $response = $this->withSession(['admin_authenticated' => true])
            ->get(route('saved-test.tech', $test->slug));

        $response->assertOk();
        $response->assertSee('No matching theory blocks found');
    }

    private function ensureSchema(): void
    {
        if (! Schema::hasTable('categories')) {
            Schema::create('categories', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('page_categories')) {
            Schema::create('page_categories', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('slug');
                $table->string('language')->nullable();
                $table->string('type')->nullable();
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->string('seeder')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('pages')) {
            Schema::create('pages', function (Blueprint $table) {
                $table->id();
                $table->string('slug');
                $table->string('title');
                $table->text('text')->nullable();
                $table->string('type')->nullable();
                $table->string('seeder')->nullable();
                $table->unsignedBigInteger('page_category_id')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('text_blocks')) {
            Schema::create('text_blocks', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique()->nullable();
                $table->unsignedBigInteger('page_id')->nullable();
                $table->unsignedBigInteger('page_category_id')->nullable();
                $table->string('locale')->nullable();
                $table->string('type')->nullable();
                $table->string('column')->nullable();
                $table->string('heading')->nullable();
                $table->string('css_class')->nullable();
                $table->integer('sort_order')->default(0);
                $table->text('body')->nullable();
                $table->string('level')->nullable();
                $table->string('seeder')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('tag_text_block')) {
            Schema::create('tag_text_block', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tag_id');
                $table->unsignedBigInteger('text_block_id');
                $table->unique(['tag_id', 'text_block_id']);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('questions')) {
            Schema::create('questions', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->text('question');
                $table->unsignedTinyInteger('difficulty')->default(1);
                $table->string('level', 2)->nullable();
                $table->unsignedBigInteger('category_id')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('tags')) {
            Schema::create('tags', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('category')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('question_tag')) {
            Schema::create('question_tag', function (Blueprint $table) {
                $table->unsignedBigInteger('question_id');
                $table->unsignedBigInteger('tag_id');
            });
        }

        if (! Schema::hasTable('page_tag')) {
            Schema::create('page_tag', function (Blueprint $table) {
                $table->unsignedBigInteger('page_id');
                $table->unsignedBigInteger('tag_id');
            });
        }

        if (! Schema::hasTable('question_options')) {
            Schema::create('question_options', function (Blueprint $table) {
                $table->id();
                $table->string('option')->unique();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('question_option_question')) {
            Schema::create('question_option_question', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('question_id');
                $table->unsignedBigInteger('option_id');
                $table->tinyInteger('flag')->nullable();
                $table->unique(['question_id', 'option_id', 'flag'], 'qoq_question_option_flag_unique');
            });
        }

        if (! Schema::hasTable('question_answers')) {
            Schema::create('question_answers', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('question_id');
                $table->unsignedBigInteger('option_id');
                $table->string('marker');
                $table->timestamps();
                $table->unique(['question_id', 'marker', 'option_id'], 'question_marker_option_unique');
            });
        }

        if (! Schema::hasTable('tests')) {
            Schema::create('tests', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->json('filters')->nullable();
                $table->json('questions');
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('verb_hints')) {
            Schema::create('verb_hints', function (Blueprint $table) {
                $table->id();
                $table->string('marker');
                $table->unsignedBigInteger('question_id');
                $table->unsignedBigInteger('option_id');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('question_hints')) {
            Schema::create('question_hints', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('question_id');
                $table->string('provider');
                $table->string('locale', 5);
                $table->text('hint');
                $table->timestamps();
                $table->unique(['question_id', 'provider', 'locale']);
            });
        }

        if (! Schema::hasTable('chatgpt_explanations')) {
            Schema::create('chatgpt_explanations', function (Blueprint $table) {
                $table->id();
                $table->text('question');
                $table->text('wrong_answer')->nullable();
                $table->text('correct_answer');
                $table->string('language');
                $table->text('explanation');
                $table->timestamps();
                $table->unique(['question', 'wrong_answer', 'correct_answer', 'language'], 'chatgpt_explanations_unique');
            });
        }
    }

    private function resetData(): void
    {
        foreach ([
            'tag_text_block',
            'text_blocks',
            'page_tag',
            'pages',
            'page_categories',
            'chatgpt_explanations',
            'question_hints',
            'verb_hints',
            'question_option_question',
            'question_answers',
            'question_options',
            'question_tag',
            'questions',
            'categories',
            'tests',
            'tags',
        ] as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->delete();
            }
        }
    }
}
