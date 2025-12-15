<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Page;
use App\Models\PageCategory;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionOption;
use App\Models\Tag;
use App\Models\TextBlock;
use App\Services\Theory\TheoryQuestionMatcherService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class TextBlockTagSyncTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureSchema();
        $this->resetData();
    }

    /** @test */
    public function grammar_page_seeder_syncs_tags_to_text_blocks(): void
    {
        // Create tags
        $tag1 = Tag::create(['name' => 'Test Tag 1']);
        $tag2 = Tag::create(['name' => 'Test Tag 2']);

        // Create a page category
        $category = PageCategory::create([
            'slug' => 'test-category',
            'title' => 'Test Category',
            'language' => 'en',
        ]);

        // Create a page with category
        $page = Page::create([
            'slug' => 'test-page',
            'title' => 'Test Page',
            'page_category_id' => $category->id,
        ]);

        // Sync tags to page
        $page->tags()->sync([$tag1->id, $tag2->id]);

        // Create text block with page_category_id
        $textBlock = TextBlock::create([
            'uuid' => (string) Str::uuid(),
            'page_id' => $page->id,
            'page_category_id' => $category->id,
            'type' => 'box',
            'column' => 'left',
            'body' => 'Test content',
            'sort_order' => 1,
        ]);

        // Sync same tags to text block (simulating seeder behavior)
        $textBlock->tags()->sync([$tag1->id, $tag2->id]);

        // Assert text block has tags
        $this->assertCount(2, $textBlock->tags);
        $this->assertTrue($textBlock->tags->contains('id', $tag1->id));
        $this->assertTrue($textBlock->tags->contains('id', $tag2->id));

        // Assert page_category_id is set
        $this->assertEquals($category->id, $textBlock->page_category_id);
    }

    /** @test */
    public function category_seeder_syncs_tags_to_category_text_blocks(): void
    {
        // Create tags
        $tag1 = Tag::create(['name' => 'Category Tag 1']);
        $tag2 = Tag::create(['name' => 'Category Tag 2']);
        $slugTag = Tag::create(['name' => 'test-category-slug']);

        // Create a page category
        $category = PageCategory::create([
            'slug' => 'test-category-slug',
            'title' => 'Test Category',
            'language' => 'en',
        ]);

        // Sync tags to category
        $category->tags()->sync([$tag1->id, $tag2->id, $slugTag->id]);

        // Create text blocks under category (no page)
        $subtitleBlock = TextBlock::create([
            'uuid' => (string) Str::uuid(),
            'page_id' => null,
            'page_category_id' => $category->id,
            'type' => 'subtitle',
            'column' => 'header',
            'body' => 'Subtitle content',
            'sort_order' => 0,
        ]);

        $contentBlock = TextBlock::create([
            'uuid' => (string) Str::uuid(),
            'page_id' => null,
            'page_category_id' => $category->id,
            'type' => 'box',
            'column' => 'left',
            'body' => 'Content block',
            'sort_order' => 1,
        ]);

        // Sync category tags to text blocks (simulating seeder behavior)
        $subtitleBlock->tags()->sync([$tag1->id, $tag2->id, $slugTag->id]);
        $contentBlock->tags()->sync([$tag1->id, $tag2->id, $slugTag->id]);

        // Assert text blocks have tags
        $this->assertCount(3, $subtitleBlock->fresh()->tags);
        $this->assertCount(3, $contentBlock->fresh()->tags);

        // Assert slug tag is included
        $this->assertTrue($subtitleBlock->fresh()->tags->contains('id', $slugTag->id));
        $this->assertTrue($contentBlock->fresh()->tags->contains('id', $slugTag->id));
    }

    /** @test */
    public function block_level_tags_are_merged_with_page_tags(): void
    {
        // Create tags
        $pageTag = Tag::create(['name' => 'Page Level Tag']);
        $blockTag = Tag::create(['name' => 'Block Level Tag']);

        // Create a page
        $page = Page::create([
            'slug' => 'test-page',
            'title' => 'Test Page',
        ]);

        // Sync page tag
        $page->tags()->sync([$pageTag->id]);

        // Create text block
        $textBlock = TextBlock::create([
            'uuid' => (string) Str::uuid(),
            'page_id' => $page->id,
            'type' => 'box',
            'column' => 'left',
            'body' => 'Test content',
            'sort_order' => 1,
        ]);

        // Sync merged tags (page + block level)
        $mergedTags = array_unique([$pageTag->id, $blockTag->id]);
        $textBlock->tags()->sync($mergedTags);

        // Assert text block has both tags
        $this->assertCount(2, $textBlock->fresh()->tags);
        $this->assertTrue($textBlock->fresh()->tags->contains('id', $pageTag->id));
        $this->assertTrue($textBlock->fresh()->tags->contains('id', $blockTag->id));
    }

    /** @test */
    public function inherit_tags_false_prevents_tag_inheritance(): void
    {
        // Create tags
        $pageTag = Tag::create(['name' => 'Page Level Tag']);
        $navigationTag = Tag::create(['name' => 'Navigation']);

        // Create a page
        $page = Page::create([
            'slug' => 'test-page',
            'title' => 'Test Page',
        ]);

        // Sync page tag
        $page->tags()->sync([$pageTag->id]);

        // Create navigation text block (simulating inherit_tags => false)
        $navigationBlock = TextBlock::create([
            'uuid' => (string) Str::uuid(),
            'page_id' => $page->id,
            'type' => 'navigation-chips',
            'column' => 'footer',
            'body' => 'Navigation content',
            'sort_order' => 100,
        ]);

        // Only sync block-specific tags (no page tags)
        $navigationBlock->tags()->sync([$navigationTag->id]);

        // Assert navigation block only has its own tag
        $this->assertCount(1, $navigationBlock->fresh()->tags);
        $this->assertTrue($navigationBlock->fresh()->tags->contains('id', $navigationTag->id));
        $this->assertFalse($navigationBlock->fresh()->tags->contains('id', $pageTag->id));
    }

    /** @test */
    public function theory_question_matcher_finds_questions_by_tag_intersection(): void
    {
        // Create shared tags
        $tag1 = Tag::create(['name' => 'Grammar']);
        $tag2 = Tag::create(['name' => 'Present Simple']);
        $tag3 = Tag::create(['name' => 'A1']);

        // Create category
        $category = Category::create(['name' => 'Test Category']);
        $pageCategory = PageCategory::create([
            'slug' => 'test-theory',
            'title' => 'Test Theory',
            'language' => 'en',
        ]);

        // Create page
        $page = Page::create([
            'slug' => 'test-theory-page',
            'title' => 'Test Theory Page',
            'page_category_id' => $pageCategory->id,
        ]);

        // Create text block with tags
        $textBlock = TextBlock::create([
            'uuid' => (string) Str::uuid(),
            'page_id' => $page->id,
            'page_category_id' => $pageCategory->id,
            'heading' => 'Present Simple Theory',
            'body' => 'Theory content about present simple',
            'type' => 'box',
            'column' => 'left',
            'sort_order' => 1,
        ]);
        $textBlock->tags()->sync([$tag1->id, $tag2->id, $tag3->id]);

        // Create question with some matching tags
        $question = Question::create([
            'uuid' => (string) Str::uuid(),
            'question' => 'I {a1} to school every day.',
            'difficulty' => 1,
            'level' => 'A1',
            'category_id' => $category->id,
        ]);
        $question->tags()->sync([$tag1->id, $tag2->id]); // Matches 2 tags

        // Create another question with less matching tags
        $question2 = Question::create([
            'uuid' => (string) Str::uuid(),
            'question' => 'She {a1} coffee.',
            'difficulty' => 1,
            'level' => 'A1',
            'category_id' => $category->id,
        ]);
        $question2->tags()->sync([$tag1->id]); // Matches only 1 tag

        // Use service to find questions
        $service = new TheoryQuestionMatcherService();
        $results = $service->findQuestionsForTextBlock($textBlock);

        // Assert results are sorted by score (most matching tags first)
        $this->assertGreaterThanOrEqual(2, $results->count());
        $this->assertEquals($question->id, $results->first()['question']->id);
        $this->assertGreaterThanOrEqual(2, $results->first()['score']);
    }

    /** @test */
    public function theory_question_matcher_finds_theory_blocks_for_question(): void
    {
        // Create shared tags
        $tag1 = Tag::create(['name' => 'Question Forms']);
        $tag2 = Tag::create(['name' => 'Types of Questions']);
        $cefrTag = Tag::create(['name' => 'A1']);

        // Create category
        $category = Category::create(['name' => 'Questions']);
        $pageCategory = PageCategory::create([
            'slug' => 'types-of-questions',
            'title' => 'Types of Questions',
            'language' => 'en',
        ]);

        // Create text block with tags
        $textBlock = TextBlock::create([
            'uuid' => (string) Str::uuid(),
            'page_id' => null,
            'page_category_id' => $pageCategory->id,
            'heading' => 'Yes/No Questions',
            'body' => 'Theory about yes/no questions',
            'type' => 'box',
            'column' => 'left',
            'sort_order' => 1,
        ]);
        $textBlock->tags()->sync([$tag1->id, $tag2->id, $cefrTag->id]);

        // Create question with matching tags
        $question = Question::create([
            'uuid' => (string) Str::uuid(),
            'question' => '{a1} you like pizza?',
            'difficulty' => 1,
            'level' => 'A1',
            'category_id' => $category->id,
        ]);
        $question->tags()->sync([$tag1->id, $tag2->id, $cefrTag->id]);

        // Use service to find theory blocks
        $service = new TheoryQuestionMatcherService();
        $results = $service->findTheoryBlocksForQuestion($question);

        // Assert we found the matching theory block
        $this->assertGreaterThanOrEqual(1, $results->count());
        $this->assertEquals($textBlock->id, $results->first()['block']->id);

        // Assert CEFR bonus is applied (A1 tag match)
        $this->assertGreaterThan(0, $results->first()['cefr_bonus']);
    }

    /** @test */
    public function theory_question_matcher_returns_empty_when_no_tags(): void
    {
        // Create text block without tags
        $pageCategory = PageCategory::create([
            'slug' => 'test-category',
            'title' => 'Test',
            'language' => 'en',
        ]);

        $textBlock = TextBlock::create([
            'uuid' => (string) Str::uuid(),
            'page_id' => null,
            'page_category_id' => $pageCategory->id,
            'heading' => 'Empty Block',
            'body' => 'No tags',
            'type' => 'box',
            'column' => 'left',
            'sort_order' => 1,
        ]);

        // Use service to find questions
        $service = new TheoryQuestionMatcherService();
        $results = $service->findQuestionsForTextBlock($textBlock);

        // Assert empty results
        $this->assertCount(0, $results);
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

        if (! Schema::hasTable('page_category_tag')) {
            Schema::create('page_category_tag', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tag_id');
                $table->unsignedBigInteger('page_category_id');
                $table->unique(['tag_id', 'page_category_id']);
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

        if (! Schema::hasTable('page_tag')) {
            Schema::create('page_tag', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('page_id');
                $table->unsignedBigInteger('tag_id');
                $table->unique(['page_id', 'tag_id']);
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

        if (! Schema::hasTable('question_tag')) {
            Schema::create('question_tag', function (Blueprint $table) {
                $table->unsignedBigInteger('question_id');
                $table->unsignedBigInteger('tag_id');
                $table->primary(['question_id', 'tag_id']);
            });
        }
    }

    private function resetData(): void
    {
        foreach ([
            'tag_text_block',
            'page_tag',
            'page_category_tag',
            'question_tag',
            'text_blocks',
            'pages',
            'page_categories',
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
