<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\PageCategory;
use App\Models\Question;
use App\Models\SavedGrammarTest;
use App\Services\PolyglotLessonImportService;
use App\Services\TheoryPagePromptLinkedTestsService;
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
        $this->assertSame('theory_page', data_get($test->filters, 'prompt_generator.source_type'));
        $this->assertSame(
            'there-is-there-are',
            data_get($test->filters, 'prompt_generator.theory_page.slug')
        );
    }

    public function test_import_service_persists_prompt_generator_filters(): void
    {
        $service = app(PolyglotLessonImportService::class);
        $payload = $this->loadLesson('polyglot-to-be-a1.json');
        $payload['test']['prompt_generator']['theory_page_id'] = 101;
        $payload['test']['prompt_generator']['theory_page_ids'] = [101];
        $payload['test']['prompt_generator']['theory_page']['id'] = 101;

        $service->import($payload, true, 'Database\\Seeders\\V3\\Polyglot\\PolyglotImportedToBeLessonSeeder');

        $test = SavedGrammarTest::query()
            ->where('slug', 'polyglot-to-be-a1')
            ->firstOrFail();

        $this->assertSame('theory_page', data_get($test->filters, 'prompt_generator.source_type'));
        $this->assertSame(101, data_get($test->filters, 'prompt_generator.theory_page_id'));
        $this->assertSame([101], data_get($test->filters, 'prompt_generator.theory_page_ids'));
        $this->assertSame(101, data_get($test->filters, 'prompt_generator.theory_page.id'));
        $this->assertSame(
            'verb-to-be-present',
            data_get($test->filters, 'prompt_generator.theory_page.slug')
        );
    }

    public function test_import_service_keeps_backward_compatibility_without_prompt_generator(): void
    {
        $service = app(PolyglotLessonImportService::class);
        $payload = $this->loadLesson('polyglot-to-be-a1.json');
        unset($payload['test']['prompt_generator']);

        $service->import($payload, true, 'Database\\Seeders\\V3\\Polyglot\\PolyglotImportedToBeLessonSeeder');

        $test = SavedGrammarTest::query()
            ->where('slug', 'polyglot-to-be-a1')
            ->firstOrFail();

        $this->assertSame('polyglot-english-a1', $test->filters['course_slug']);
        $this->assertSame(1, $test->filters['lesson_order']);
        $this->assertArrayNotHasKey('prompt_generator', $test->filters);
    }

    public function test_imported_polyglot_json_lesson_is_visible_to_theory_page_prompt_linked_tests_service(): void
    {
        $service = app(PolyglotLessonImportService::class);
        $linkedTests = app(TheoryPagePromptLinkedTestsService::class);
        [$category, $page] = $this->createTheoryPage(
            'Verb to Be: Present Forms',
            'verb-to-be-present',
            'verb-to-be',
            'Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\VerbToBePresentTheorySeeder'
        );
        $payload = $this->loadLesson('polyglot-to-be-a1.json');
        $payload['test']['prompt_generator']['theory_page_id'] = (int) $page->getKey();
        $payload['test']['prompt_generator']['theory_page_ids'] = [(int) $page->getKey()];
        $payload['test']['prompt_generator']['theory_page']['id'] = (int) $page->getKey();

        $service->import($payload, true, 'Database\\Seeders\\V3\\Polyglot\\PolyglotImportedToBeLessonSeeder');

        $directTests = $linkedTests->findForPage($page);
        $aggregatedTests = $linkedTests->buildForPage($page);

        $this->assertTrue($directTests->contains(fn ($test) => ($test->slug ?? null) === 'polyglot-to-be-a1'));
        $this->assertTrue($aggregatedTests->contains(
            fn ($test) => ($test->slug ?? null) === 'polyglot-to-be-a1'
        ));
        $this->assertFalse($aggregatedTests->contains(
            fn ($test) => str_starts_with((string) ($test->slug ?? ''), sprintf('theory-page-%d-', (int) $page->getKey()))
        ));
    }

    private function loadLesson(string $fileName): array
    {
        return json_decode(
            file_get_contents(database_path('seeders/V2/Polyglot/Data/'.$fileName)),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
    }

    /**
     * @return array{0: PageCategory, 1: Page}
     */
    private function createTheoryPage(
        string $title,
        string $slug,
        string $categorySlug,
        string $seederClass
    ): array {
        $category = PageCategory::create([
            'title' => 'Theory',
            'slug' => $categorySlug,
            'language' => 'uk',
            'type' => 'theory',
        ]);

        $page = Page::create([
            'title' => $title,
            'slug' => $slug,
            'type' => 'theory',
            'page_category_id' => $category->id,
            'seeder' => $seederClass,
        ]);

        return [$category, $page];
    }
}
