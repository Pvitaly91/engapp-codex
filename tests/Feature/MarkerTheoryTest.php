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
                'score',
                'marker',
            ],
        ]);
        $response->assertJsonPath('theory_block.marker', 'a1');
        $this->assertEquals(5.5, $response->json('theory_block.score'));
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
    public function available_theory_tags_are_limited_to_theory_page(): void
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

        $otherPage = Page::create([
            'title' => 'Past Simple',
            'slug' => 'past-simple',
            'text' => 'Other theory',
            'page_category_id' => $pageCategory->id,
        ]);

        $textBlock = TextBlock::create([
            'uuid' => (string) Str::uuid(),
            'heading' => 'Present Simple Questions',
            'body' => '...',
            'page_id' => $page->id,
            'page_category_id' => $pageCategory->id,
            'sort_order' => 1,
        ]);

        $otherTextBlock = TextBlock::create([
            'uuid' => (string) Str::uuid(),
            'heading' => 'Past Simple Questions',
            'body' => '...',
            'page_id' => $otherPage->id,
            'page_category_id' => $pageCategory->id,
            'sort_order' => 1,
        ]);

        $tagPresentSimple = Tag::create(['name' => 'present-simple', 'category' => 'Tenses']);
        $tagDo = Tag::create(['name' => 'do-auxiliary', 'category' => 'Auxiliary']);
        $tagPast = Tag::create(['name' => 'past-simple', 'category' => 'Tenses']);

        $textBlock->tags()->attach([$tagPresentSimple->id, $tagDo->id]);
        $otherTextBlock->tags()->attach([$tagPast->id]);

        $question = Question::create([
            'uuid' => (string) Str::uuid(),
            'question' => '{a1} you like coffee?',
            'difficulty' => 1,
            'level' => 'A1',
            'category_id' => $category->id,
            'theory_text_block_uuid' => $textBlock->uuid,
        ]);

        $response = $this->withSession(['admin_authenticated' => true])
            ->getJson(route('questions.markers.available-theory-tags', [
                'question' => $question->uuid,
                'marker' => 'a1',
            ]));

        $response->assertOk();
        $response->assertJsonPath('page_id', $page->id);

        $returnedIds = collect($response->json('tags'))->pluck('id');
        $this->assertCount(2, $returnedIds);
        $this->assertTrue($returnedIds->contains($tagPresentSimple->id));
        $this->assertTrue($returnedIds->contains($tagDo->id));
        $this->assertFalse($returnedIds->contains($tagPast->id));
    }

    /** @test */
    public function add_tags_from_theory_page_adds_only_allowed_unique_tags(): void
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
            'body' => '...',
            'page_id' => $page->id,
            'page_category_id' => $pageCategory->id,
            'sort_order' => 1,
        ]);

        $tagPresentSimple = Tag::create(['name' => 'present-simple', 'category' => 'Tenses']);
        $tagDo = Tag::create(['name' => 'do-auxiliary', 'category' => 'Auxiliary']);
        $tagPast = Tag::create(['name' => 'past-simple', 'category' => 'Tenses']);

        $textBlock->tags()->attach([$tagPresentSimple->id, $tagDo->id]);

        $question = Question::create([
            'uuid' => (string) Str::uuid(),
            'question' => '{a1} you like coffee?',
            'difficulty' => 1,
            'level' => 'A1',
            'category_id' => $category->id,
            'theory_text_block_uuid' => $textBlock->uuid,
        ]);

        DB::table('question_marker_tag')->insert([
            'question_id' => $question->id,
            'marker' => 'a1',
            'tag_id' => $tagPresentSimple->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->withSession(['admin_authenticated' => true])
            ->postJson(route('questions.markers.add-tags-from-theory-page', [
                'question' => $question->uuid,
                'marker' => 'a1',
            ]), [
                'tag_ids' => [
                    $tagPresentSimple->id, // duplicate
                    $tagDo->id, // allowed new tag
                    $tagPast->id, // not allowed
                ],
            ]);

        $response->assertOk();

        $savedTags = DB::table('question_marker_tag')
            ->where('question_id', $question->id)
            ->where('marker', 'a1')
            ->pluck('tag_id');

        $this->assertCount(2, $savedTags);
        $this->assertTrue($savedTags->contains($tagPresentSimple->id));
        $this->assertTrue($savedTags->contains($tagDo->id));
        $this->assertFalse($savedTags->contains($tagPast->id));

        $response->assertJsonPath('marker_tags', function ($tags) use ($tagDo, $tagPresentSimple) {
            return in_array($tagPresentSimple->name, $tags, true)
                && in_array($tagDo->name, $tags, true);
        });
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
