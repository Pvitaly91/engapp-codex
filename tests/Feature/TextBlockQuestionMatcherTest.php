<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Page;
use App\Models\PageCategory;
use App\Models\Question;
use App\Models\Tag;
use App\Models\TextBlock;
use App\Services\Theory\TextBlockQuestionMatcherService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class TextBlockQuestionMatcherTest extends TestCase
{
    protected TextBlockQuestionMatcherService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureSchema();
        $this->resetData();
        $this->service = app(TextBlockQuestionMatcherService::class);
    }

    /** @test */
    public function it_returns_empty_collection_when_block_has_no_tags(): void
    {
        $pageCategory = PageCategory::create([
            'title' => 'Grammar',
            'slug' => 'grammar',
            'language' => 'en',
        ]);

        $page = Page::create([
            'title' => 'Test Page',
            'slug' => 'test-page',
            'text' => 'Test content',
            'page_category_id' => $pageCategory->id,
        ]);

        $textBlock = TextBlock::create([
            'uuid' => (string) Str::uuid(),
            'heading' => 'Test Block',
            'body' => json_encode(['title' => 'Test']),
            'page_id' => $page->id,
            'page_category_id' => $pageCategory->id,
            'sort_order' => 1,
        ]);

        $result = $this->service->findQuestionsForTextBlock($textBlock);

        $this->assertTrue($result->isEmpty());
    }

    /** @test */
    public function it_finds_questions_matching_block_tags(): void
    {
        $pageCategory = PageCategory::create([
            'title' => 'Grammar',
            'slug' => 'grammar',
            'language' => 'en',
        ]);

        $page = Page::create([
            'title' => 'Present Simple',
            'slug' => 'present-simple',
            'text' => 'Theory about present simple',
            'page_category_id' => $pageCategory->id,
        ]);

        $textBlock = TextBlock::create([
            'uuid' => (string) Str::uuid(),
            'heading' => 'Present Simple Questions',
            'body' => json_encode(['title' => 'How to form questions']),
            'page_id' => $page->id,
            'page_category_id' => $pageCategory->id,
            'sort_order' => 1,
        ]);

        // Create tags
        $tagPresentSimple = Tag::create(['name' => 'present-simple', 'category' => 'Tenses']);
        $tagDoDoesAuxiliary = Tag::create(['name' => 'do-does-did', 'category' => 'Auxiliary']);

        // Attach tags to text block
        $textBlock->tags()->attach([$tagPresentSimple->id, $tagDoDoesAuxiliary->id]);

        // Create a question with matching tags
        $category = Category::create(['name' => 'Test Category']);
        $question = Question::create([
            'uuid' => (string) Str::uuid(),
            'question' => '{a1} you like coffee?',
            'difficulty' => 1,
            'level' => 'A1',
            'category_id' => $category->id,
        ]);

        // Attach same tags to question
        $question->tags()->attach([$tagPresentSimple->id, $tagDoDoesAuxiliary->id]);

        $result = $this->service->findQuestionsForTextBlock($textBlock);

        $this->assertFalse($result->isEmpty());
        $this->assertTrue($result->contains('id', $question->id));
    }

    /** @test */
    public function it_does_not_match_on_only_general_tags(): void
    {
        $pageCategory = PageCategory::create([
            'title' => 'Grammar',
            'slug' => 'grammar',
            'language' => 'en',
        ]);

        $page = Page::create([
            'title' => 'General Questions',
            'slug' => 'general-questions',
            'text' => 'Theory about questions',
            'page_category_id' => $pageCategory->id,
        ]);

        $textBlock = TextBlock::create([
            'uuid' => (string) Str::uuid(),
            'heading' => 'Questions Overview',
            'body' => json_encode(['title' => 'Types of Questions']),
            'page_id' => $page->id,
            'page_category_id' => $pageCategory->id,
            'sort_order' => 1,
        ]);

        // Create only general tag
        $tagGeneral = Tag::create(['name' => 'types-of-questions', 'category' => 'Grammar']);
        $textBlock->tags()->attach([$tagGeneral->id]);

        // Create a question with only the general tag
        $category = Category::create(['name' => 'Test Category']);
        $question = Question::create([
            'uuid' => (string) Str::uuid(),
            'question' => '{a1} you like coffee?',
            'difficulty' => 1,
            'level' => 'A1',
            'category_id' => $category->id,
        ]);

        $question->tags()->attach([$tagGeneral->id]);

        $result = $this->service->findQuestionsForTextBlock($textBlock);

        // Should not match because only general tag
        $this->assertTrue($result->isEmpty());
    }

    /** @test */
    public function it_prioritizes_questions_with_more_matching_tags(): void
    {
        $pageCategory = PageCategory::create([
            'title' => 'Grammar',
            'slug' => 'grammar',
            'language' => 'en',
        ]);

        $page = Page::create([
            'title' => 'Present Simple',
            'slug' => 'present-simple',
            'text' => 'Theory',
            'page_category_id' => $pageCategory->id,
        ]);

        $textBlock = TextBlock::create([
            'uuid' => (string) Str::uuid(),
            'heading' => 'Present Simple',
            'body' => json_encode(['title' => 'Test']),
            'page_id' => $page->id,
            'page_category_id' => $pageCategory->id,
            'sort_order' => 1,
        ]);

        // Create multiple tags
        $tag1 = Tag::create(['name' => 'present-simple', 'category' => 'Tenses']);
        $tag2 = Tag::create(['name' => 'do-does-did', 'category' => 'Auxiliary']);
        $tag3 = Tag::create(['name' => 'yes-no-questions', 'category' => 'Question Types']);

        // Text block has all tags
        $textBlock->tags()->attach([$tag1->id, $tag2->id, $tag3->id]);

        $category = Category::create(['name' => 'Test Category']);

        // Question 1 matches only 1 tag (should have lower score)
        $question1 = Question::create([
            'uuid' => (string) Str::uuid(),
            'question' => 'Question 1',
            'difficulty' => 1,
            'level' => 'A1',
            'category_id' => $category->id,
        ]);
        $question1->tags()->attach([$tag1->id]);

        // Question 2 matches all 3 tags (should have higher score)
        $question2 = Question::create([
            'uuid' => (string) Str::uuid(),
            'question' => 'Question 2',
            'difficulty' => 1,
            'level' => 'A1',
            'category_id' => $category->id,
        ]);
        $question2->tags()->attach([$tag1->id, $tag2->id, $tag3->id]);

        $result = $this->service->findQuestionsForTextBlock($textBlock, 5);

        // Both should be in results (question2 has higher score)
        $this->assertTrue($result->contains('id', $question2->id));
    }

    /** @test */
    public function it_excludes_specified_question_ids(): void
    {
        $pageCategory = PageCategory::create([
            'title' => 'Grammar',
            'slug' => 'grammar',
            'language' => 'en',
        ]);

        $page = Page::create([
            'title' => 'Present Simple',
            'slug' => 'present-simple',
            'text' => 'Theory',
            'page_category_id' => $pageCategory->id,
        ]);

        $textBlock = TextBlock::create([
            'uuid' => (string) Str::uuid(),
            'heading' => 'Test Block',
            'body' => json_encode(['title' => 'Test']),
            'page_id' => $page->id,
            'page_category_id' => $pageCategory->id,
            'sort_order' => 1,
        ]);

        $tag1 = Tag::create(['name' => 'present-simple', 'category' => 'Tenses']);
        $tag2 = Tag::create(['name' => 'do-does-did', 'category' => 'Auxiliary']);
        $textBlock->tags()->attach([$tag1->id, $tag2->id]);

        $category = Category::create(['name' => 'Test Category']);

        // Create two matching questions
        $question1 = Question::create([
            'uuid' => (string) Str::uuid(),
            'question' => 'Question 1',
            'difficulty' => 1,
            'level' => 'A1',
            'category_id' => $category->id,
        ]);
        $question1->tags()->attach([$tag1->id, $tag2->id]);

        $question2 = Question::create([
            'uuid' => (string) Str::uuid(),
            'question' => 'Question 2',
            'difficulty' => 1,
            'level' => 'A1',
            'category_id' => $category->id,
        ]);
        $question2->tags()->attach([$tag1->id, $tag2->id]);

        // Exclude question1
        $result = $this->service->findQuestionsForTextBlock($textBlock, 5, [$question1->id]);

        $this->assertFalse($result->contains('id', $question1->id));
        $this->assertTrue($result->contains('id', $question2->id));
    }

    /** @test */
    public function it_batches_questions_for_multiple_blocks_without_duplicates(): void
    {
        $pageCategory = PageCategory::create([
            'title' => 'Grammar',
            'slug' => 'grammar',
            'language' => 'en',
        ]);

        $page = Page::create([
            'title' => 'Present Simple',
            'slug' => 'present-simple',
            'text' => 'Theory',
            'page_category_id' => $pageCategory->id,
        ]);

        // Create two text blocks with same tags
        $textBlock1 = TextBlock::create([
            'uuid' => (string) Str::uuid(),
            'heading' => 'Block 1',
            'body' => json_encode(['title' => 'Block 1']),
            'page_id' => $page->id,
            'page_category_id' => $pageCategory->id,
            'sort_order' => 1,
        ]);

        $textBlock2 = TextBlock::create([
            'uuid' => (string) Str::uuid(),
            'heading' => 'Block 2',
            'body' => json_encode(['title' => 'Block 2']),
            'page_id' => $page->id,
            'page_category_id' => $pageCategory->id,
            'sort_order' => 2,
        ]);

        $tag1 = Tag::create(['name' => 'present-simple', 'category' => 'Tenses']);
        $tag2 = Tag::create(['name' => 'do-does-did', 'category' => 'Auxiliary']);

        $textBlock1->tags()->attach([$tag1->id, $tag2->id]);
        $textBlock2->tags()->attach([$tag1->id, $tag2->id]);

        $category = Category::create(['name' => 'Test Category']);

        // Create multiple questions
        $questions = [];
        for ($i = 0; $i < 10; $i++) {
            $q = Question::create([
                'uuid' => (string) Str::uuid(),
                'question' => "Question $i",
                'difficulty' => 1,
                'level' => 'A1',
                'category_id' => $category->id,
            ]);
            $q->tags()->attach([$tag1->id, $tag2->id]);
            $questions[] = $q;
        }

        $blocks = TextBlock::whereIn('id', [$textBlock1->id, $textBlock2->id])->get();
        $result = $this->service->findQuestionsForTextBlocks($blocks, 3);

        // Should have results for both blocks
        $this->assertArrayHasKey($textBlock1->uuid, $result);
        $this->assertArrayHasKey($textBlock2->uuid, $result);

        // Get all question IDs from both blocks
        $block1QuestionIds = $result[$textBlock1->uuid]->pluck('id')->toArray();
        $block2QuestionIds = $result[$textBlock2->uuid]->pluck('id')->toArray();

        // No duplicates across blocks
        $intersection = array_intersect($block1QuestionIds, $block2QuestionIds);
        $this->assertEmpty($intersection, 'Questions should not be duplicated across blocks');
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

        if (! Schema::hasTable('tags')) {
            Schema::create('tags', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('category')->nullable();
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
                $table->string('theory_text_block_uuid', 36)->nullable();
                $table->unsignedBigInteger('category_id')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('question_tag')) {
            Schema::create('question_tag', function (Blueprint $table) {
                $table->unsignedBigInteger('question_id');
                $table->unsignedBigInteger('tag_id');
                $table->primary(['question_id', 'tag_id']);
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
    }

    private function resetData(): void
    {
        foreach ([
            'question_answers',
            'question_option_question',
            'question_options',
            'tag_text_block',
            'text_blocks',
            'pages',
            'page_categories',
            'question_tag',
            'questions',
            'categories',
            'tags',
        ] as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->delete();
            }
        }
    }
}
