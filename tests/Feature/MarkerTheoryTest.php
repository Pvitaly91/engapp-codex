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

class MarkerTheoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureSchema();
        $this->resetData();
    }

    /** @test */
    public function it_returns_null_when_no_marker_tags_exist(): void
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
            ->postJson(route('question.marker-theory'), [
                'question_id' => $question->id,
                'marker' => 'a1',
            ]);

        $response->assertOk();
        $response->assertJson([
            'theory_block' => null,
        ]);
    }

    /** @test */
    public function it_returns_matching_theory_block_by_tags(): void
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
        $tagDoDoesAuxiliary = Tag::create(['name' => 'do/does/did', 'category' => 'Auxiliary']);

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

        // Add marker tags for question
        DB::table('question_marker_tag')->insert([
            ['question_id' => $question->id, 'marker' => 'a1', 'tag_id' => $tagPresentSimple->id, 'created_at' => now(), 'updated_at' => now()],
            ['question_id' => $question->id, 'marker' => 'a1', 'tag_id' => $tagDoDoesAuxiliary->id, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $response = $this->withSession(['admin_authenticated' => true])
            ->postJson(route('question.marker-theory'), [
                'question_id' => $question->id,
                'marker' => 'a1',
            ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'theory_block' => [
                'uuid',
                'type',
                'body',
                'level',
                'matched_tags',
                'matched_tag_ids',
                'score',
                'marker',
            ],
        ]);
        $response->assertJsonPath('theory_block.marker', 'a1');
        $this->assertEquals(5.5, $response->json('theory_block.score'));

        // Verify matched_tag_ids contains the correct IDs (intersection of marker tags and block tags)
        $matchedTagIds = $response->json('theory_block.matched_tag_ids');
        $this->assertIsArray($matchedTagIds);
        $this->assertCount(2, $matchedTagIds);
        // The matched_tag_ids should contain the IDs of the marker tags that matched with the text block
        $this->assertContains($tagPresentSimple->id, $matchedTagIds);
        $this->assertContains($tagDoDoesAuxiliary->id, $matchedTagIds);
    }

    /** @test */
    public function it_does_not_match_when_only_types_of_questions_tag_matches(): void
    {
        $category = Category::create(['name' => 'Test Category']);
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

        // Add marker tags for question (only types-of-questions)
        DB::table('question_marker_tag')->insert([
            ['question_id' => $question->id, 'marker' => 'a1', 'tag_id' => $tagTypesOfQuestions->id, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $response = $this->withSession(['admin_authenticated' => true])
            ->postJson(route('question.marker-theory'), [
                'question_id' => $question->id,
                'marker' => 'a1',
            ]);

        $response->assertOk();
        $response->assertJson([
            'theory_block' => null,
        ]);
    }

    /** @test */
    public function it_validates_marker_format(): void
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
            ->postJson(route('question.marker-theory'), [
                'question_id' => $question->id,
                'marker' => 'invalid',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['marker']);
    }

    /** @test */
    public function tag_based_matching_works_independently_of_uuid_theory_link(): void
    {
        // This test verifies that tag-based matching returns matched_tag_ids
        // even when the question has NO theory_text_block_uuid set.
        // This proves the two mechanisms are independent.

        $category = Category::create(['name' => 'Test Category']);
        $pageCategory = PageCategory::create([
            'title' => 'Grammar',
            'slug' => 'grammar',
            'language' => 'en',
        ]);

        $page = Page::create([
            'title' => 'Tag Questions',
            'slug' => 'tag-questions',
            'text' => 'Theory about tag questions',
            'page_category_id' => $pageCategory->id,
        ]);

        $textBlock = TextBlock::create([
            'uuid' => (string) Str::uuid(),
            'heading' => 'Tag Questions Formation',
            'body' => json_encode(['title' => 'How to form tag questions']),
            'page_id' => $page->id,
            'page_category_id' => $pageCategory->id,
            'sort_order' => 1,
        ]);

        // Create tags
        $tagTagQuestions = Tag::create(['name' => 'tag-questions', 'category' => 'Question Types']);
        $tagPresentSimple = Tag::create(['name' => 'present-simple', 'category' => 'Tenses']);

        // Attach tags to text block
        $textBlock->tags()->attach([$tagTagQuestions->id, $tagPresentSimple->id]);

        // Create question WITHOUT theory_text_block_uuid (null)
        $question = Question::create([
            'uuid' => (string) Str::uuid(),
            'question' => 'She likes coffee, {a1} she?',
            'difficulty' => 1,
            'level' => 'B1',
            'category_id' => $category->id,
            'theory_text_block_uuid' => null, // Explicitly null - no UUID-based theory link
        ]);

        $optionDoesnt = QuestionOption::create(['option' => "doesn't"]);
        $question->options()->attach([$optionDoesnt->id]);

        QuestionAnswer::create([
            'question_id' => $question->id,
            'marker' => 'a1',
            'option_id' => $optionDoesnt->id,
        ]);

        // Add marker tags for question (tag-based link)
        DB::table('question_marker_tag')->insert([
            ['question_id' => $question->id, 'marker' => 'a1', 'tag_id' => $tagTagQuestions->id, 'created_at' => now(), 'updated_at' => now()],
            ['question_id' => $question->id, 'marker' => 'a1', 'tag_id' => $tagPresentSimple->id, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $response = $this->withSession(['admin_authenticated' => true])
            ->postJson(route('question.marker-theory'), [
                'question_id' => $question->id,
                'marker' => 'a1',
            ]);

        $response->assertOk();
        // Tag-based matching should work even without UUID theory link
        $response->assertJsonStructure([
            'theory_block' => [
                'uuid',
                'matched_tags',
                'matched_tag_ids',
                'score',
                'marker',
            ],
        ]);

        // Verify matched_tag_ids contains the intersection of marker tags and block tags
        $matchedTagIds = $response->json('theory_block.matched_tag_ids');
        $this->assertIsArray($matchedTagIds);
        $this->assertCount(2, $matchedTagIds);
        $this->assertContains($tagTagQuestions->id, $matchedTagIds);
        $this->assertContains($tagPresentSimple->id, $matchedTagIds);
    }

    /** @test */
    public function matched_tag_ids_is_intersection_of_marker_and_block_tags(): void
    {
        // Test that matched_tag_ids only contains tags that exist in BOTH
        // the marker tags AND the text block tags (true intersection)

        $category = Category::create(['name' => 'Test Category']);
        $pageCategory = PageCategory::create([
            'title' => 'Grammar',
            'slug' => 'grammar-test',
            'language' => 'en',
        ]);

        $page = Page::create([
            'title' => 'Modal Verbs',
            'slug' => 'modal-verbs',
            'text' => 'Theory about modal verbs',
            'page_category_id' => $pageCategory->id,
        ]);

        $textBlock = TextBlock::create([
            'uuid' => (string) Str::uuid(),
            'heading' => 'Modal Verbs in Questions',
            'body' => json_encode(['title' => 'How to use modal verbs in questions']),
            'page_id' => $page->id,
            'page_category_id' => $pageCategory->id,
            'sort_order' => 1,
        ]);

        // Create tags - some shared, some unique to marker, some unique to block
        $tagModalVerbs = Tag::create(['name' => 'modal-verbs', 'category' => 'Grammar']); // shared
        $tagCanCould = Tag::create(['name' => 'can-could', 'category' => 'Modals']); // shared
        $tagMarkerOnly = Tag::create(['name' => 'marker-specific-tag', 'category' => 'Other']); // marker only
        $tagBlockOnly = Tag::create(['name' => 'block-specific-tag', 'category' => 'Other']); // block only

        // Attach only some tags to text block (modal-verbs, can-could, block-specific)
        $textBlock->tags()->attach([$tagModalVerbs->id, $tagCanCould->id, $tagBlockOnly->id]);

        $question = Question::create([
            'uuid' => (string) Str::uuid(),
            'question' => '{a1} you speak English?',
            'difficulty' => 1,
            'level' => 'A2',
            'category_id' => $category->id,
        ]);

        $optionCan = QuestionOption::create(['option' => 'Can']);
        $question->options()->attach([$optionCan->id]);

        QuestionAnswer::create([
            'question_id' => $question->id,
            'marker' => 'a1',
            'option_id' => $optionCan->id,
        ]);

        // Add marker tags (modal-verbs, can-could, marker-specific) - overlaps but not identical
        DB::table('question_marker_tag')->insert([
            ['question_id' => $question->id, 'marker' => 'a1', 'tag_id' => $tagModalVerbs->id, 'created_at' => now(), 'updated_at' => now()],
            ['question_id' => $question->id, 'marker' => 'a1', 'tag_id' => $tagCanCould->id, 'created_at' => now(), 'updated_at' => now()],
            ['question_id' => $question->id, 'marker' => 'a1', 'tag_id' => $tagMarkerOnly->id, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $response = $this->withSession(['admin_authenticated' => true])
            ->postJson(route('question.marker-theory'), [
                'question_id' => $question->id,
                'marker' => 'a1',
            ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'theory_block' => ['matched_tag_ids'],
        ]);

        // matched_tag_ids should only contain the intersection (modal-verbs, can-could)
        // NOT marker-specific-tag (only in marker) or block-specific-tag (only in block)
        $matchedTagIds = $response->json('theory_block.matched_tag_ids');
        $this->assertIsArray($matchedTagIds);
        $this->assertCount(2, $matchedTagIds); // Only shared tags
        $this->assertContains($tagModalVerbs->id, $matchedTagIds);
        $this->assertContains($tagCanCould->id, $matchedTagIds);
        $this->assertNotContains($tagMarkerOnly->id, $matchedTagIds); // marker-only should NOT be in matched
        $this->assertNotContains($tagBlockOnly->id, $matchedTagIds); // block-only should NOT be in matched
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
