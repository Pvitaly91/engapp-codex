<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Page;
use App\Models\PageCategory;
use App\Models\Question;
use App\Models\Tag;
use App\Models\TextBlock;
use App\Services\Theory\TextBlockToQuestionsMatcherService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class TextBlockToQuestionsMatcherTest extends TestCase
{
    protected TextBlockToQuestionsMatcherService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureSchema();
        $this->resetData();
        $this->service = app(TextBlockToQuestionsMatcherService::class);
    }

    /** @test */
    public function it_requires_anchor_and_detail_matches(): void
    {
        [$block, $anchors, $details] = $this->createBlockWithTags(['types-of-questions', 'yes-no-questions'], ['can-could']);

        $category = Category::create(['name' => 'Test Category']);

        $validQuestion = $this->createQuestionWithTags($category->id, [$anchors['types-of-questions'], $anchors['yes-no-questions'], $details['can-could']]);
        $missingDetail = $this->createQuestionWithTags($category->id, [$anchors['types-of-questions'], $anchors['yes-no-questions']]);

        $results = $this->service->findQuestionsForTextBlock($block, 5);

        $this->assertTrue($results->contains('id', $validQuestion->id));
        $this->assertFalse($results->contains('id', $missingDetail->id));
    }

    /** @test */
    public function it_returns_empty_when_no_detail_match_exists(): void
    {
        [$block, $anchors] = $this->createBlockWithTags(['types-of-questions', 'yes-no-questions'], ['will-would']);

        $category = Category::create(['name' => 'Test Category']);

        // Question has anchors but lacks required detail
        $this->createQuestionWithTags($category->id, [$anchors['types-of-questions'], $anchors['yes-no-questions']]);

        $results = $this->service->findQuestionsForTextBlock($block, 5);

        $this->assertTrue($results->isEmpty());
    }

    /** @test */
    public function it_matches_yes_no_modal_blocks_by_any_modal_detail(): void
    {
        [$block, $anchors, $details] = $this->createBlockWithTags(
            ['types-of-questions', 'yes-no-questions'],
            ['can-could', 'will-would', 'should', 'must', 'may-might']
        );

        $category = Category::create(['name' => 'Test Category']);

        $questionWithCan = $this->createQuestionWithTags($category->id, [$anchors['types-of-questions'], $anchors['yes-no-questions'], $details['can-could']]);
        $questionWithWill = $this->createQuestionWithTags($category->id, [$anchors['types-of-questions'], $anchors['yes-no-questions'], $details['will-would']]);
        $questionMissingModal = $this->createQuestionWithTags($category->id, [$anchors['types-of-questions'], $anchors['yes-no-questions']]);

        $results = $this->service->findQuestionsForTextBlock($block, 5);

        $this->assertTrue($results->contains('id', $questionWithCan->id));
        $this->assertTrue($results->contains('id', $questionWithWill->id));
        $this->assertFalse($results->contains('id', $questionMissingModal->id));
    }

    private function createBlockWithTags(array $anchorTags, array $detailTags): array
    {
        $pageCategory = PageCategory::create([
            'title' => 'Grammar',
            'slug' => 'grammar',
            'language' => 'en',
        ]);

        $page = Page::create([
            'title' => 'Test Page',
            'slug' => 'test-page-'.Str::random(6),
            'text' => 'Theory',
            'page_category_id' => $pageCategory->id,
        ]);

        $block = TextBlock::create([
            'uuid' => (string) Str::uuid(),
            'heading' => 'Test Block',
            'body' => json_encode(['title' => 'Test']),
            'page_id' => $page->id,
            'page_category_id' => $pageCategory->id,
            'sort_order' => 1,
            'level' => 'A1',
        ]);

        $createdTags = [];

        foreach (array_merge($anchorTags, $detailTags) as $tagName) {
            $createdTags[$tagName] = Tag::create([
                'name' => $tagName,
                'category' => in_array($tagName, $anchorTags, true) ? 'Question Types' : 'Auxiliary',
            ])->id;
        }

        $block->tags()->attach(array_values($createdTags));

        return [$block, $createdTags, $createdTags];
    }

    private function createQuestionWithTags(int $categoryId, array $tagIds): Question
    {
        $question = Question::create([
            'uuid' => (string) Str::uuid(),
            'question' => 'Placeholder?',
            'difficulty' => 1,
            'level' => 'A1',
            'category_id' => $categoryId,
        ]);

        $question->tags()->attach($tagIds);

        return $question;
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
                $table->string('language')->default('en');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('pages')) {
            Schema::create('pages', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('slug');
                $table->text('text');
                $table->unsignedBigInteger('page_category_id');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('text_blocks')) {
            Schema::create('text_blocks', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->string('heading')->nullable();
                $table->json('body')->nullable();
                $table->unsignedBigInteger('page_id');
                $table->unsignedBigInteger('page_category_id');
                $table->integer('sort_order')->default(0);
                $table->string('level', 2)->nullable();
                $table->string('type')->nullable();
                $table->string('column')->nullable();
                $table->string('locale')->nullable();
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
                $table->string('seeder')->nullable();
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

        if (! Schema::hasTable('question_marker_tag')) {
            Schema::create('question_marker_tag', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('question_id');
                $table->string('marker');
                $table->unsignedBigInteger('tag_id');
                $table->timestamps();
                $table->unique(['question_id', 'marker', 'tag_id'], 'question_marker_tag_unique');
            });
        }

        if (! Schema::hasTable('verb_hints')) {
            Schema::create('verb_hints', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('question_id');
                $table->string('marker');
                $table->unsignedBigInteger('option_id')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('question_hints')) {
            Schema::create('question_hints', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('question_id');
                $table->string('provider')->nullable();
                $table->string('locale')->nullable();
                $table->text('hint')->nullable();
                $table->timestamps();
            });
        }
    }

    private function resetData(): void
    {
        foreach ([
            'question_answers',
            'question_option_question',
            'question_options',
            'question_marker_tag',
            'tag_text_block',
            'text_blocks',
            'pages',
            'page_categories',
            'question_tag',
            'questions',
            'categories',
            'tags',
            'verb_hints',
            'question_hints',
        ] as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->delete();
            }
        }
    }
}

