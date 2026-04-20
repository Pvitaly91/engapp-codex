<?php

namespace Tests\Feature;

use App\Models\Question;
use App\Models\SavedGrammarTest;
use App\Services\PolyglotLessonImportService;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class PolyglotLessonImportServiceTest extends TestCase
{
    use RebuildsComposeTestSchema;

    protected function setUp(): void
    {
        parent::setUp();

        $this->rebuildComposeTestSchema();
    }

    public function test_import_service_is_idempotent(): void
    {
        $service = app(PolyglotLessonImportService::class);
        $path = database_path('seeders/V2/Polyglot/Data/polyglot-there-is-there-are-a1.json');

        $service->importFromFile($path, true);
        $service->importFromFile($path, true);

        $uuids = collect(range(1, 24))
            ->map(fn (int $number) => sprintf('polyglot-there-is-are-q%02d', $number))
            ->all();

        $test = SavedGrammarTest::query()
            ->with('questionLinks')
            ->where('slug', 'polyglot-there-is-there-are-a1')
            ->firstOrFail();

        $this->assertSame(1, SavedGrammarTest::query()->where('slug', 'polyglot-there-is-there-are-a1')->count());
        $this->assertSame(24, Question::query()->whereIn('uuid', $uuids)->count());
        $this->assertCount(24, $test->questionLinks);
        $this->assertSame($uuids, $test->questionLinks->pluck('question_uuid')->all());
        $this->assertSame(2, $test->filters['payload_version']);
    }
}
