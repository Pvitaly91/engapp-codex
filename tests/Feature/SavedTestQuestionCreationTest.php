<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Question;
use App\Models\Test;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class SavedTestQuestionCreationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('tests');
        Schema::dropIfExists('questions');
        Schema::dropIfExists('categories');

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->text('question');
            $table->unsignedTinyInteger('difficulty')->default(1);
            $table->boolean('flag')->default(0);
            $table->string('level', 2)->nullable();
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('source_id')->nullable();
            $table->timestamps();
        });

        Schema::create('tests', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->json('filters');
            $table->json('questions');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /** @test */
    public function it_reuses_existing_question_category_when_creating_new_question(): void
    {
        $category = Category::create(['name' => 'Grammar']);

        $existingQuestion = Question::create([
            'uuid' => (string) Str::uuid(),
            'question' => 'Existing {a1} sentence?',
            'difficulty' => 1,
            'level' => 'A1',
            'category_id' => $category->id,
        ]);

        $test = Test::create([
            'name' => 'Sample test',
            'slug' => 'sample-test',
            'filters' => [],
            'questions' => [$existingQuestion->id],
        ]);

        $response = $this->post(route('saved-test.questions.store', $test->slug), [
            'question' => 'New {a1} content?',
            'level' => 'B1',
        ]);

        $response->assertRedirect(route('saved-test.tech', $test->slug));

        $test->refresh();
        $questions = $test->questions;
        $this->assertCount(2, $questions);

        $newQuestionId = end($questions);
        $newQuestion = Question::findOrFail($newQuestionId);

        $this->assertSame($category->id, $newQuestion->category_id);
        $this->assertSame('B1', $newQuestion->level);
    }

    /** @test */
    public function it_assigns_first_available_category_when_test_has_no_questions(): void
    {
        $category = Category::create(['name' => 'Default']);

        $test = Test::create([
            'name' => 'Empty test',
            'slug' => 'empty-test',
            'filters' => [],
            'questions' => [],
        ]);

        $response = $this->post(route('saved-test.questions.store', $test->slug), [
            'question' => 'Standalone {a1} example?',
            'level' => '',
        ]);

        $response->assertRedirect(route('saved-test.tech', $test->slug));

        $test->refresh();
        $questions = $test->questions;
        $this->assertCount(1, $questions);

        $newQuestion = Question::findOrFail($questions[0]);

        $this->assertSame($category->id, $newQuestion->category_id);
        $this->assertNull($newQuestion->level);
    }
}
