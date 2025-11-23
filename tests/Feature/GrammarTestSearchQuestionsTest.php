<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Question;
use App\Models\Tag;
use App\Models\Source;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GrammarTestSearchQuestionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create(['is_admin' => true]));
    }

    public function test_search_questions_returns_results_by_question_text(): void
    {
        $category = Category::factory()->create();
        $question = Question::factory()->create([
            'question' => 'This is a test question about {a1}',
            'category_id' => $category->id,
        ]);

        $response = $this->get(route('grammar-test.search-questions', ['q' => 'test question']));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'items',
                'total',
                'page',
                'perPage',
                'lastPage',
            ])
            ->assertJsonPath('items.0.id', $question->id)
            ->assertJsonPath('items.0.question', $question->question);
    }

    public function test_search_questions_returns_results_by_id(): void
    {
        $category = Category::factory()->create();
        $question = Question::factory()->create([
            'question' => 'Some question {a1}',
            'category_id' => $category->id,
        ]);

        $response = $this->get(route('grammar-test.search-questions', ['q' => $question->id]));

        $response->assertStatus(200)
            ->assertJsonPath('items.0.id', $question->id);
    }

    public function test_search_questions_returns_results_by_uuid(): void
    {
        $category = Category::factory()->create();
        $uuid = '123e4567-e89b-12d3-a456-426614174000';
        $question = Question::factory()->create([
            'question' => 'Some question {a1}',
            'category_id' => $category->id,
            'uuid' => $uuid,
        ]);

        $response = $this->get(route('grammar-test.search-questions', ['q' => substr($uuid, 0, 8)]));

        $response->assertStatus(200)
            ->assertJsonPath('items.0.uuid', $uuid);
    }

    public function test_search_questions_returns_results_by_tag(): void
    {
        $category = Category::factory()->create();
        $question = Question::factory()->create([
            'question' => 'Question with tag {a1}',
            'category_id' => $category->id,
        ]);
        $tag = Tag::factory()->create(['name' => 'Present Simple']);
        $question->tags()->attach($tag);

        $response = $this->get(route('grammar-test.search-questions', ['q' => 'Present Simple']));

        $response->assertStatus(200)
            ->assertJsonPath('items.0.id', $question->id)
            ->assertJsonPath('items.0.tags.0', 'Present Simple');
    }

    public function test_search_questions_returns_empty_when_no_matches(): void
    {
        $response = $this->get(route('grammar-test.search-questions', ['q' => 'nonexistent query']));

        $response->assertStatus(200)
            ->assertJsonPath('items', [])
            ->assertJsonPath('total', 0);
    }

    public function test_search_questions_returns_all_when_no_query(): void
    {
        $category = Category::factory()->create();
        Question::factory()->count(3)->create(['category_id' => $category->id]);

        $response = $this->get(route('grammar-test.search-questions'));

        $response->assertStatus(200)
            ->assertJsonPath('total', 3);
    }

    public function test_search_questions_paginates_results(): void
    {
        $category = Category::factory()->create();
        Question::factory()->count(25)->create(['category_id' => $category->id]);

        $response = $this->get(route('grammar-test.search-questions', ['page' => 1]));

        $response->assertStatus(200)
            ->assertJsonPath('page', 1)
            ->assertJsonPath('perPage', 20)
            ->assertJsonPath('total', 25)
            ->assertJsonPath('lastPage', 2);
    }
}
