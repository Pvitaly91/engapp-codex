<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionOption;
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
    private TextBlockToQuestionsMatcherService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureSchema();
        $this->resetData();
        $this->service = new TextBlockToQuestionsMatcherService();
    }

    /** @test */
    public function it_returns_empty_collection_when_block_has_no_tags(): void
    {
        $block = TextBlock::create([
            'uuid' => (string) Str::uuid(),
            'heading' => 'Test block',
            'body' => 'Test body',
        ]);

        $result = $this->service->findQuestionsForTextBlock($block);

        $this->assertTrue($result->isEmpty());
    }

    /** @test */
    public function it_returns_empty_collection_when_block_has_no_anchor_tags(): void
    {
        $block = TextBlock::create([
            'uuid' => (string) Str::uuid(),
            'heading' => 'Test block',
            'body' => 'Test body',
        ]);

        // Only add detail tag (no anchor)
        $detailTag = Tag::create(['name' => 'Can/Could', 'category' => 'English Grammar Auxiliary']);
        $block->tags()->attach([$detailTag->id]);

        $result = $this->service->findQuestionsForTextBlock($block);

        $this->assertTrue($result->isEmpty());
    }

    /** @test */
    public function it_returns_questions_matching_anchor_and_detail_tags(): void
    {
        $category = Category::create(['name' => 'Questions']);

        // Create tags
        $anchorTag1 = Tag::create(['name' => 'Types of Questions', 'category' => 'English Grammar Theme']);
        $anchorTag2 = Tag::create(['name' => 'Yes/No Questions', 'category' => 'English Grammar Detail']);
        $detailTag = Tag::create(['name' => 'Can/Could', 'category' => 'English Grammar Auxiliary']);

        // Create text block with anchor and detail tags
        $block = TextBlock::create([
            'uuid' => (string) Str::uuid(),
            'heading' => 'Yes/No Questions with Can/Could',
            'body' => 'Theory about modal verb can/could in questions',
        ]);
        $block->tags()->attach([$anchorTag1->id, $anchorTag2->id, $detailTag->id]);

        // Create matching question (has all anchor tags + detail tag)
        $matchingQuestion = Question::create([
            'uuid' => (string) Str::uuid(),
            'question' => '{a1} you swim?',
            'difficulty' => 1,
            'level' => 'A1',
            'category_id' => $category->id,
        ]);
        $matchingQuestion->tags()->attach([$anchorTag1->id, $anchorTag2->id, $detailTag->id]);
        $this->addQuestionOption($matchingQuestion, 'Can');

        // Create non-matching question (has anchor tags but wrong detail)
        $otherDetailTag = Tag::create(['name' => 'Will/Would', 'category' => 'English Grammar Auxiliary']);
        $nonMatchingQuestion = Question::create([
            'uuid' => (string) Str::uuid(),
            'question' => '{a1} you come tomorrow?',
            'difficulty' => 1,
            'level' => 'A1',
            'category_id' => $category->id,
        ]);
        $nonMatchingQuestion->tags()->attach([$anchorTag1->id, $anchorTag2->id, $otherDetailTag->id]);
        $this->addQuestionOption($nonMatchingQuestion, 'Will');

        $result = $this->service->findQuestionsForTextBlock($block);

        // Should only return the matching question
        $this->assertCount(1, $result);
        $this->assertEquals($matchingQuestion->id, $result->first()->id);
    }

    /** @test */
    public function it_returns_empty_when_no_questions_match_detail_tags(): void
    {
        $category = Category::create(['name' => 'Questions']);

        // Create tags
        $anchorTag1 = Tag::create(['name' => 'Types of Questions', 'category' => 'English Grammar Theme']);
        $anchorTag2 = Tag::create(['name' => 'Yes/No Questions', 'category' => 'English Grammar Detail']);
        $detailTag = Tag::create(['name' => 'Must', 'category' => 'English Grammar Auxiliary']);
        $otherDetailTag = Tag::create(['name' => 'Should', 'category' => 'English Grammar Auxiliary']);

        // Create text block with anchor + detail tags
        $block = TextBlock::create([
            'uuid' => (string) Str::uuid(),
            'heading' => 'Yes/No Questions with Must',
            'body' => 'Theory about modal verb must in questions',
        ]);
        $block->tags()->attach([$anchorTag1->id, $anchorTag2->id, $detailTag->id]);

        // Create question with anchor tags but DIFFERENT detail tag
        $question = Question::create([
            'uuid' => (string) Str::uuid(),
            'question' => '{a1} I go?',
            'difficulty' => 1,
            'level' => 'A1',
            'category_id' => $category->id,
        ]);
        $question->tags()->attach([$anchorTag1->id, $anchorTag2->id, $otherDetailTag->id]);
        $this->addQuestionOption($question, 'Should');

        $result = $this->service->findQuestionsForTextBlock($block);

        // Should return empty - no question has the required "Must" detail tag
        $this->assertTrue($result->isEmpty());
    }

    /** @test */
    public function it_returns_questions_when_block_has_only_anchor_tags(): void
    {
        $category = Category::create(['name' => 'Questions']);

        // Create anchor tags only
        $anchorTag1 = Tag::create(['name' => 'Types of Questions', 'category' => 'English Grammar Theme']);
        $anchorTag2 = Tag::create(['name' => 'Yes/No Questions', 'category' => 'English Grammar Detail']);

        // Create text block with only anchor tags (no detail tags)
        $block = TextBlock::create([
            'uuid' => (string) Str::uuid(),
            'heading' => 'Yes/No Questions Overview',
            'body' => 'General overview of Yes/No questions',
        ]);
        $block->tags()->attach([$anchorTag1->id, $anchorTag2->id]);

        // Create question with anchor tags
        $question = Question::create([
            'uuid' => (string) Str::uuid(),
            'question' => 'Do you like coffee?',
            'difficulty' => 1,
            'level' => 'A1',
            'category_id' => $category->id,
        ]);
        $question->tags()->attach([$anchorTag1->id, $anchorTag2->id]);
        $this->addQuestionOption($question, 'Do');

        $result = $this->service->findQuestionsForTextBlock($block);

        // Should return question since block has no detail tags (no detail requirement)
        $this->assertCount(1, $result);
        $this->assertEquals($question->id, $result->first()->id);
    }

    /** @test */
    public function it_requires_all_anchor_tags_to_match(): void
    {
        $category = Category::create(['name' => 'Questions']);

        // Create two anchor tags
        $anchorTag1 = Tag::create(['name' => 'Types of Questions', 'category' => 'English Grammar Theme']);
        $anchorTag2 = Tag::create(['name' => 'Yes/No Questions', 'category' => 'English Grammar Detail']);

        // Create text block with both anchor tags
        $block = TextBlock::create([
            'uuid' => (string) Str::uuid(),
            'heading' => 'Yes/No Questions',
            'body' => 'Theory about Yes/No questions',
        ]);
        $block->tags()->attach([$anchorTag1->id, $anchorTag2->id]);

        // Create question with only ONE anchor tag
        $question = Question::create([
            'uuid' => (string) Str::uuid(),
            'question' => 'Do you like coffee?',
            'difficulty' => 1,
            'level' => 'A1',
            'category_id' => $category->id,
        ]);
        $question->tags()->attach([$anchorTag1->id]); // Missing anchorTag2
        $this->addQuestionOption($question, 'Do');

        $result = $this->service->findQuestionsForTextBlock($block);

        // Should return empty - question doesn't have ALL anchor tags
        $this->assertTrue($result->isEmpty());
    }

    /** @test */
    public function it_matches_questions_with_any_one_detail_tag(): void
    {
        $category = Category::create(['name' => 'Questions']);

        // Create tags
        $anchorTag = Tag::create(['name' => 'Types of Questions', 'category' => 'English Grammar Theme']);
        $detailTag1 = Tag::create(['name' => 'Can/Could', 'category' => 'English Grammar Auxiliary']);
        $detailTag2 = Tag::create(['name' => 'Will/Would', 'category' => 'English Grammar Auxiliary']);
        $detailTag3 = Tag::create(['name' => 'Should', 'category' => 'English Grammar Auxiliary']);

        // Create text block with multiple detail tags
        $block = TextBlock::create([
            'uuid' => (string) Str::uuid(),
            'heading' => 'Modal Questions',
            'body' => 'Theory about modals in questions',
        ]);
        $block->tags()->attach([$anchorTag->id, $detailTag1->id, $detailTag2->id, $detailTag3->id]);

        // Create question with only one of the detail tags
        $question = Question::create([
            'uuid' => (string) Str::uuid(),
            'question' => '{a1} you help me?',
            'difficulty' => 1,
            'level' => 'A1',
            'category_id' => $category->id,
        ]);
        $question->tags()->attach([$anchorTag->id, $detailTag2->id]); // Has 'Will/Would'
        $this->addQuestionOption($question, 'Will');

        $result = $this->service->findQuestionsForTextBlock($block);

        // Should return question - it has anchor + at least ONE detail tag
        $this->assertCount(1, $result);
        $this->assertEquals($question->id, $result->first()->id);
    }

    /** @test */
    public function it_excludes_specified_question_ids(): void
    {
        $category = Category::create(['name' => 'Questions']);

        // Create tags
        $anchorTag = Tag::create(['name' => 'Types of Questions', 'category' => 'English Grammar Theme']);

        // Create text block
        $block = TextBlock::create([
            'uuid' => (string) Str::uuid(),
            'heading' => 'Questions Overview',
            'body' => 'Theory about questions',
        ]);
        $block->tags()->attach([$anchorTag->id]);

        // Create two matching questions
        $question1 = Question::create([
            'uuid' => (string) Str::uuid(),
            'question' => 'Question 1?',
            'difficulty' => 1,
            'level' => 'A1',
            'category_id' => $category->id,
        ]);
        $question1->tags()->attach([$anchorTag->id]);
        $this->addQuestionOption($question1, 'Answer1');

        $question2 = Question::create([
            'uuid' => (string) Str::uuid(),
            'question' => 'Question 2?',
            'difficulty' => 1,
            'level' => 'A1',
            'category_id' => $category->id,
        ]);
        $question2->tags()->attach([$anchorTag->id]);
        $this->addQuestionOption($question2, 'Answer2');

        // Exclude first question
        $result = $this->service->findQuestionsForTextBlock($block, 5, [$question1->id]);

        // Should only return second question
        $this->assertCount(1, $result);
        $this->assertEquals($question2->id, $result->first()->id);
    }

    /** @test */
    public function it_respects_limit_parameter(): void
    {
        $category = Category::create(['name' => 'Questions']);

        // Create tags
        $anchorTag = Tag::create(['name' => 'Types of Questions', 'category' => 'English Grammar Theme']);

        // Create text block
        $block = TextBlock::create([
            'uuid' => (string) Str::uuid(),
            'heading' => 'Questions Overview',
            'body' => 'Theory about questions',
        ]);
        $block->tags()->attach([$anchorTag->id]);

        // Create 10 matching questions
        for ($i = 1; $i <= 10; $i++) {
            $question = Question::create([
                'uuid' => (string) Str::uuid(),
                'question' => "Question {$i}?",
                'difficulty' => 1,
                'level' => 'A1',
                'category_id' => $category->id,
            ]);
            $question->tags()->attach([$anchorTag->id]);
            $this->addQuestionOption($question, "Answer{$i}");
        }

        // Request only 3
        $result = $this->service->findQuestionsForTextBlock($block, 3);

        $this->assertCount(3, $result);
    }

    /** @test */
    public function it_provides_correct_diagnostics(): void
    {
        $category = Category::create(['name' => 'Questions']);

        // Create tags
        $anchorTag1 = Tag::create(['name' => 'Types of Questions', 'category' => 'English Grammar Theme']);
        $anchorTag2 = Tag::create(['name' => 'Yes/No Questions', 'category' => 'English Grammar Detail']);
        $detailTag = Tag::create(['name' => 'Can/Could', 'category' => 'English Grammar Auxiliary']);
        $ignoredTag = Tag::create(['name' => 'Grammar', 'category' => 'General']);

        // Create text block
        $block = TextBlock::create([
            'uuid' => (string) Str::uuid(),
            'heading' => 'Can/Could Questions',
            'body' => 'Theory',
        ]);
        $block->tags()->attach([$anchorTag1->id, $anchorTag2->id, $detailTag->id, $ignoredTag->id]);

        // Create matching question
        $question = Question::create([
            'uuid' => (string) Str::uuid(),
            'question' => '{a1} you swim?',
            'difficulty' => 1,
            'level' => 'A1',
            'category_id' => $category->id,
        ]);
        $question->tags()->attach([$anchorTag1->id, $anchorTag2->id, $detailTag->id]);
        $this->addQuestionOption($question, 'Can');

        $diagnostics = $this->service->getMatchingDiagnostics($block);

        $this->assertEquals($block->uuid, $diagnostics['block_uuid']);
        $this->assertContains('types of questions', $diagnostics['anchor_tags']);
        $this->assertContains('yes/no questions', $diagnostics['anchor_tags']);
        $this->assertContains('can/could', $diagnostics['detail_tags']);
        $this->assertNotContains('grammar', $diagnostics['anchor_tags']);
        $this->assertNotContains('grammar', $diagnostics['detail_tags']);
        $this->assertGreaterThan(0, $diagnostics['candidates_after_anchors']);
        $this->assertGreaterThan(0, $diagnostics['candidates_after_details']);
        $this->assertNotEmpty($diagnostics['top_questions']);
    }

    private function addQuestionOption(Question $question, string $optionText): void
    {
        $option = QuestionOption::firstOrCreate(['option' => $optionText]);
        $question->options()->attach([$option->id]);

        QuestionAnswer::create([
            'question_id' => $question->id,
            'marker' => 'a1',
            'option_id' => $option->id,
        ]);
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
                $table->unsignedBigInteger('category_id')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('question_tag')) {
            Schema::create('question_tag', function (Blueprint $table) {
                $table->unsignedBigInteger('question_id');
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
    }

    private function resetData(): void
    {
        foreach ([
            'tag_text_block',
            'text_blocks',
            'question_option_question',
            'question_answers',
            'question_options',
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
