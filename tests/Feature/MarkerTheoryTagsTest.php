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
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class MarkerTheoryTagsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureSchema();
        $this->resetData();
    }

    /** @test */
    public function it_returns_available_theory_tags_for_marker(): void
    {
        [$question, $page, $textBlock, $tags] = $this->createTestData();

        $response = $this->withSession(['admin_authenticated' => true])
            ->getJson(route('api.v2.markers.available-theory-tags', [
                'questionUuid' => $question->uuid,
                'marker' => 'a1',
            ]));

        $response->assertOk();
        $response->assertJsonStructure([
            'tags' => [
                '*' => ['id', 'name', 'category'],
            ],
            'page_id',
            'marker',
            'question_id',
        ]);

        // Should contain all tags from the text block on that page
        $this->assertEquals($page->id, $response->json('page_id'));
        $this->assertEquals('a1', $response->json('marker'));

        $returnedTagNames = collect($response->json('tags'))->pluck('name')->toArray();
        $this->assertContains('present-simple', $returnedTagNames);
        $this->assertContains('do-does-did', $returnedTagNames);
    }

    /** @test */
    public function available_theory_tags_returns_empty_when_no_theory_page(): void
    {
        $category = Category::create(['name' => 'Test Category']);

        $question = Question::create([
            'uuid' => (string) Str::uuid(),
            'question' => '{a1} you like coffee?',
            'difficulty' => 1,
            'level' => 'A1',
            'category_id' => $category->id,
        ]);

        $optionDo = QuestionOption::create(['option' => 'Do']);
        $question->options()->attach([$optionDo->id]);

        QuestionAnswer::create([
            'question_id' => $question->id,
            'marker' => 'a1',
            'option_id' => $optionDo->id,
        ]);

        $response = $this->withSession(['admin_authenticated' => true])
            ->getJson(route('api.v2.markers.available-theory-tags', [
                'questionUuid' => $question->uuid,
                'marker' => 'a1',
            ]));

        $response->assertOk();
        $response->assertJson([
            'tags' => [],
            'page_id' => null,
        ]);
    }

    /** @test */
    public function available_theory_tags_validates_marker_format(): void
    {
        $category = Category::create(['name' => 'Test Category']);

        $question = Question::create([
            'uuid' => (string) Str::uuid(),
            'question' => '{a1} you like coffee?',
            'difficulty' => 1,
            'level' => 'A1',
            'category_id' => $category->id,
        ]);

        $response = $this->withSession(['admin_authenticated' => true])
            ->getJson(route('api.v2.markers.available-theory-tags', [
                'questionUuid' => $question->uuid,
                'marker' => 'invalid',
            ]));

        $response->assertStatus(422);
    }

    /** @test */
    public function it_adds_valid_tags_to_marker(): void
    {
        [$question, $page, $textBlock, $tags] = $this->createTestData();

        // Get initial marker tags count
        $initialCount = DB::table('question_marker_tag')
            ->where('question_id', $question->id)
            ->where('marker', 'a1')
            ->count();

        // There's a third tag not yet associated with the marker
        $newTag = Tag::create(['name' => 'new-tag', 'category' => 'Test']);
        $textBlock->tags()->attach([$newTag->id]);

        $response = $this->withSession(['admin_authenticated' => true])
            ->postJson(route('api.v2.markers.add-tags-from-theory-page', [
                'questionUuid' => $question->uuid,
                'marker' => 'a1',
            ]), [
                'tag_ids' => [$newTag->id],
            ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'added',
            'skipped',
            'marker_tags' => [
                '*' => ['id', 'name'],
            ],
            'theory_block',
            'marker',
            'question_id',
        ]);

        $this->assertEquals(1, $response->json('added'));
        $this->assertEquals(0, $response->json('skipped'));

        // Verify tag was added in database
        $newCount = DB::table('question_marker_tag')
            ->where('question_id', $question->id)
            ->where('marker', 'a1')
            ->count();

        $this->assertEquals($initialCount + 1, $newCount);
    }

    /** @test */
    public function it_does_not_add_duplicate_tags(): void
    {
        [$question, $page, $textBlock, $tags] = $this->createTestData();

        // Try to add tags that already exist on the marker
        $existingTagId = $tags[0]->id;

        $response = $this->withSession(['admin_authenticated' => true])
            ->postJson(route('api.v2.markers.add-tags-from-theory-page', [
                'questionUuid' => $question->uuid,
                'marker' => 'a1',
            ]), [
                'tag_ids' => [$existingTagId],
            ]);

        $response->assertOk();
        $this->assertEquals(0, $response->json('added'));
        $this->assertEquals(1, $response->json('skipped'));
    }

    /** @test */
    public function it_rejects_tags_not_on_theory_page(): void
    {
        [$question, $page, $textBlock, $tags] = $this->createTestData();

        // Create a tag that is NOT on the theory page
        $invalidTag = Tag::create(['name' => 'unrelated-tag', 'category' => 'Other']);

        $response = $this->withSession(['admin_authenticated' => true])
            ->postJson(route('api.v2.markers.add-tags-from-theory-page', [
                'questionUuid' => $question->uuid,
                'marker' => 'a1',
            ]), [
                'tag_ids' => [$invalidTag->id],
            ]);

        $response->assertStatus(422);
        $response->assertJsonPath('invalid_tag_ids', [$invalidTag->id]);
    }

    /** @test */
    public function add_tags_validates_marker_format(): void
    {
        [$question, $page, $textBlock, $tags] = $this->createTestData();

        $response = $this->withSession(['admin_authenticated' => true])
            ->postJson(route('api.v2.markers.add-tags-from-theory-page', [
                'questionUuid' => $question->uuid,
                'marker' => 'invalid',
            ]), [
                'tag_ids' => [$tags[0]->id],
            ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function add_tags_requires_tag_ids(): void
    {
        [$question, $page, $textBlock, $tags] = $this->createTestData();

        $response = $this->withSession(['admin_authenticated' => true])
            ->postJson(route('api.v2.markers.add-tags-from-theory-page', [
                'questionUuid' => $question->uuid,
                'marker' => 'a1',
            ]), [
                'tag_ids' => [],
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['tag_ids']);
    }

    /** @test */
    public function endpoints_require_admin_authentication(): void
    {
        $category = Category::create(['name' => 'Test Category']);

        $question = Question::create([
            'uuid' => (string) Str::uuid(),
            'question' => '{a1} you like coffee?',
            'difficulty' => 1,
            'level' => 'A1',
            'category_id' => $category->id,
        ]);

        // GET without auth
        $response = $this->getJson(route('api.v2.markers.available-theory-tags', [
            'questionUuid' => $question->uuid,
            'marker' => 'a1',
        ]));
        $response->assertRedirect();

        // POST without auth
        $response = $this->postJson(route('api.v2.markers.add-tags-from-theory-page', [
            'questionUuid' => $question->uuid,
            'marker' => 'a1',
        ]), [
            'tag_ids' => [1],
        ]);
        $response->assertRedirect();
    }

    private function createTestData(): array
    {
        $category = Category::create(['name' => 'Test Category']);
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
            'body' => json_encode(['title' => 'How to form questions', 'intro' => 'Use do/does + subject + base verb']),
            'page_id' => $page->id,
            'page_category_id' => $pageCategory->id,
            'sort_order' => 1,
        ]);

        // Create tags
        $tagPresentSimple = Tag::create(['name' => 'present-simple', 'category' => 'Tenses']);
        $tagDoDoesAuxiliary = Tag::create(['name' => 'do-does-did', 'category' => 'Auxiliary']);

        // Attach tags to text block
        $textBlock->tags()->attach([$tagPresentSimple->id, $tagDoDoesAuxiliary->id]);

        $question = Question::create([
            'uuid' => (string) Str::uuid(),
            'question' => '{a1} you like coffee?',
            'difficulty' => 1,
            'level' => 'A1',
            'category_id' => $category->id,
        ]);

        $optionDo = QuestionOption::create(['option' => 'Do']);
        $question->options()->attach([$optionDo->id]);

        QuestionAnswer::create([
            'question_id' => $question->id,
            'marker' => 'a1',
            'option_id' => $optionDo->id,
        ]);

        // Add marker tags for question (so it can find the theory page)
        DB::table('question_marker_tag')->insert([
            ['question_id' => $question->id, 'marker' => 'a1', 'tag_id' => $tagPresentSimple->id, 'created_at' => now(), 'updated_at' => now()],
            ['question_id' => $question->id, 'marker' => 'a1', 'tag_id' => $tagDoDoesAuxiliary->id, 'created_at' => now(), 'updated_at' => now()],
        ]);

        return [$question, $page, $textBlock, [$tagPresentSimple, $tagDoDoesAuxiliary]];
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
                $table->string('theory_text_block_uuid', 36)->nullable();
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

        if (! Schema::hasTable('question_marker_tag')) {
            Schema::create('question_marker_tag', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('question_id');
                $table->string('marker', 10);
                $table->unsignedBigInteger('tag_id');
                $table->unique(['question_id', 'marker', 'tag_id']);
                $table->index(['question_id', 'marker']);
                $table->timestamps();
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
            'tag_text_block',
            'text_blocks',
            'pages',
            'page_categories',
            'question_option_question',
            'question_answers',
            'question_options',
            'question_tag',
            'question_marker_tag',
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
