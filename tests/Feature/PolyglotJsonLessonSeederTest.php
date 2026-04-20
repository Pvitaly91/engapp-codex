<?php

namespace Tests\Feature;

use App\Models\Question;
use App\Models\SavedGrammarTest;
use Database\Seeders\V2\Polyglot\PolyglotThereIsThereAreLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotToBeLessonSeeder;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class PolyglotJsonLessonSeederTest extends TestCase
{
    use RebuildsComposeTestSchema;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'coming-soon.enabled' => false,
            'tests.tech_info_enabled' => false,
        ]);

        $this->rebuildComposeTestSchema();
    }

    public function test_json_backed_to_be_seeder_still_works(): void
    {
        $this->seed(PolyglotToBeLessonSeeder::class);

        $test = SavedGrammarTest::query()
            ->where('slug', 'polyglot-to-be-a1')
            ->firstOrFail();

        $response = $this->get('/test/polyglot-to-be-a1/step/compose');

        $this->assertSame('polyglot-there-is-there-are-a1', $test->filters['next_lesson_slug']);
        $this->assertNull($test->filters['previous_lesson_slug']);
        $response->assertOk();
    }

    public function test_lesson_two_seeder_works(): void
    {
        $this->seed(PolyglotThereIsThereAreLessonSeeder::class);

        $test = SavedGrammarTest::query()
            ->with('questionLinks')
            ->where('slug', 'polyglot-there-is-there-are-a1')
            ->firstOrFail();

        $questions = Question::query()
            ->whereIn('uuid', $test->questionLinks->pluck('question_uuid')->all())
            ->get();

        $this->assertSame('polyglot-there-is-there-are-a1', $test->slug);
        $this->assertSame('polyglot-to-be-a1', $test->filters['previous_lesson_slug']);
        $this->assertCount(24, $test->questionLinks);
        $this->assertTrue($questions->every(fn (Question $question) => $question->type === Question::TYPE_COMPOSE_TOKENS));

        $this->get('/test/polyglot-there-is-there-are-a1/step/compose')->assertOk();
    }

    public function test_json_backed_seeders_produce_linkable_course_graph(): void
    {
        $this->seed(PolyglotToBeLessonSeeder::class);
        $this->seed(PolyglotThereIsThereAreLessonSeeder::class);

        $lessonOne = SavedGrammarTest::query()
            ->where('slug', 'polyglot-to-be-a1')
            ->firstOrFail();
        $lessonTwo = SavedGrammarTest::query()
            ->where('slug', 'polyglot-there-is-there-are-a1')
            ->firstOrFail();

        $this->assertSame('polyglot-there-is-there-are-a1', $lessonOne->filters['next_lesson_slug']);
        $this->assertSame('polyglot-to-be-a1', $lessonTwo->filters['previous_lesson_slug']);
    }
}
