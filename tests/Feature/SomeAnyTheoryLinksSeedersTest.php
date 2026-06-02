<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Question;
use App\Models\SavedGrammarTest;
use App\Models\TextBlock;
use App\Services\SavedTestResolver;
use App\Services\TheoryPagePromptLinkedTestsService;
use Database\Seeders\Page_V3\Articles\SomeAny\SomeAnyCategorySeeder;
use Database\Seeders\Page_V3\Articles\SomeAny\SomeAnyPeopleTheorySeeder;
use Database\Seeders\Page_V3\Articles\SomeAny\SomeAnyPlacesTheorySeeder;
use Database\Seeders\Page_V3\Articles\SomeAny\SomeAnyThingsTheorySeeder;
use Database\Seeders\V3\Polyglot\PolyglotSomeAnyLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotSomeAnyPeopleAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotSomeAnyPlacesAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotSomeAnyThingsAllLevelsLessonSeeder;
use Database\Seeders\V3\SomeAnyPeopleAllLevelsV3Seeder;
use Database\Seeders\V3\SomeAnyPlacesAllLevelsV3Seeder;
use Database\Seeders\V3\SomeAnyPlacesPeopleThingsAllLevelsV3Seeder;
use Database\Seeders\V3\SomeAnyThingsAllLevelsV3Seeder;
use Database\Seeders\V3\TheoryLinks\SomeAnyPeopleTheoryLinksSeeder;
use Database\Seeders\V3\TheoryLinks\SomeAnyPlacesTheoryLinksSeeder;
use Database\Seeders\V3\TheoryLinks\SomeAnyThingsTheoryLinksSeeder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class SomeAnyTheoryLinksSeedersTest extends TestCase
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

    public function test_some_any_theory_links_seeders_link_direct_polyglot_and_mixed_questions(): void
    {
        $this->seedSomeAnyStack();

        $this->seed([
            SomeAnyPeopleTheoryLinksSeeder::class,
            SomeAnyPlacesTheoryLinksSeeder::class,
            SomeAnyThingsTheoryLinksSeeder::class,
        ]);

        foreach ($this->cases() as $caseName => $case) {
            $page = Page::query()->where('slug', $case['page_slug'])->firstOrFail();
            $directTest = SavedGrammarTest::query()->where('slug', $case['direct_slug'])->first();

            $this->assertNotNull($directTest, $caseName . ': direct Sentence Builder test exists.');

            $tests = app(TheoryPagePromptLinkedTestsService::class)->buildForPage($page);
            $mixedTest = $tests->firstWhere('slug', 'theory-page-' . $page->id . '-mixed-a1-c2');

            $this->assertNotNull($mixedTest, $caseName . ': Mixed A1-C2 test exists.');
            $this->assertTrue($mixedTest->isVirtual(), $caseName);

            $mixedSeederClasses = collect($mixedTest->filters['seeder_classes'] ?? [])->values();
            foreach ($case['mixed_filter_seeders'] as $expectedSeeder) {
                $this->assertTrue($mixedSeederClasses->contains($expectedSeeder), $caseName . ': ' . $expectedSeeder);
            }

            $this->assertEveryCoveredQuestionHasTheoryLinks($case['covered_seeders'], $caseName);
            $this->assertDirectSentenceBuilderQuestionsExposeTheoryBlocks($case['direct_slug'], $caseName);
            $this->assertMixedResolvedQuestionsExposeTheoryBlocks($mixedTest, $case['covered_seeders'], $caseName);

            $questionUuids = Question::query()
                ->whereIn('seeder', $case['covered_seeders'])
                ->pluck('uuid');

            $firstRunPivotRows = DB::table('question_theory_text_blocks')
                ->whereIn('question_uuid', $questionUuids)
                ->count();

            $this->seed($case['links_seeder']);

            $this->assertSame(
                $firstRunPivotRows,
                DB::table('question_theory_text_blocks')
                    ->whereIn('question_uuid', $questionUuids)
                    ->count(),
                $caseName . ': idempotency check failed.'
            );
        }

        $this->assertAuditReportsSomeAnyRoutesAsOk();
    }

    private function seedSomeAnyStack(): void
    {
        $this->seed([
            SomeAnyCategorySeeder::class,
            SomeAnyPeopleTheorySeeder::class,
            SomeAnyPlacesTheorySeeder::class,
            SomeAnyThingsTheorySeeder::class,
            SomeAnyPeopleAllLevelsV3Seeder::class,
            SomeAnyPlacesAllLevelsV3Seeder::class,
            SomeAnyThingsAllLevelsV3Seeder::class,
            SomeAnyPlacesPeopleThingsAllLevelsV3Seeder::class,
            PolyglotSomeAnyLessonSeeder::class,
            PolyglotSomeAnyPeopleAllLevelsLessonSeeder::class,
            PolyglotSomeAnyPlacesAllLevelsLessonSeeder::class,
            PolyglotSomeAnyThingsAllLevelsLessonSeeder::class,
        ]);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function cases(): array
    {
        return [
            'people' => [
                'route' => '/theory/some-any/theory-some-any-people',
                'page_slug' => 'theory-some-any-people',
                'direct_slug' => 'polyglot-some-any-people-all-levels',
                'mixed_filter_seeders' => [
                    SomeAnyPeopleAllLevelsV3Seeder::class,
                    PolyglotSomeAnyPeopleAllLevelsLessonSeeder::class,
                ],
                'covered_seeders' => [
                    SomeAnyPeopleAllLevelsV3Seeder::class,
                    PolyglotSomeAnyPeopleAllLevelsLessonSeeder::class,
                ],
                'links_seeder' => SomeAnyPeopleTheoryLinksSeeder::class,
            ],
            'places' => [
                'route' => '/theory/some-any/theory-some-any-places',
                'page_slug' => 'theory-some-any-places',
                'direct_slug' => 'polyglot-some-any-places-all-levels',
                'mixed_filter_seeders' => [
                    SomeAnyPlacesAllLevelsV3Seeder::class,
                    PolyglotSomeAnyPlacesAllLevelsLessonSeeder::class,
                ],
                'covered_seeders' => [
                    SomeAnyPlacesAllLevelsV3Seeder::class,
                    PolyglotSomeAnyPlacesAllLevelsLessonSeeder::class,
                ],
                'links_seeder' => SomeAnyPlacesTheoryLinksSeeder::class,
            ],
            'things' => [
                'route' => '/theory/some-any/theory-some-any-things',
                'page_slug' => 'theory-some-any-things',
                'direct_slug' => 'polyglot-some-any-things-all-levels',
                'mixed_filter_seeders' => [
                    SomeAnyThingsAllLevelsV3Seeder::class,
                    PolyglotSomeAnyThingsAllLevelsLessonSeeder::class,
                ],
                'covered_seeders' => [
                    SomeAnyThingsAllLevelsV3Seeder::class,
                    PolyglotSomeAnyThingsAllLevelsLessonSeeder::class,
                    PolyglotSomeAnyLessonSeeder::class,
                ],
                'links_seeder' => SomeAnyThingsTheoryLinksSeeder::class,
            ],
        ];
    }

    /**
     * @param  array<int, class-string>  $seederClasses
     */
    private function assertEveryCoveredQuestionHasTheoryLinks(array $seederClasses, string $caseName): void
    {
        $questions = Question::query()
            ->whereIn('seeder', $seederClasses)
            ->get(['id', 'uuid', 'seeder', 'theory_text_block_uuid']);

        foreach ($seederClasses as $seederClass) {
            $this->assertGreaterThan(
                0,
                $questions->where('seeder', $seederClass)->count(),
                $caseName . ': ' . $seederClass
            );
        }

        $pivotRows = DB::table('question_theory_text_blocks')
            ->whereIn('question_uuid', $questions->pluck('uuid'))
            ->get();

        $this->assertSame(
            $questions->count(),
            $pivotRows->pluck('question_uuid')->unique()->count(),
            $caseName . ': every covered question should have pivot rows.'
        );

        $this->assertSame(
            $questions->count(),
            $questions->whereNotNull('theory_text_block_uuid')->count(),
            $caseName . ': every covered question should set the legacy theory_text_block_uuid.'
        );

        $linkedBlockUuids = $pivotRows->pluck('text_block_uuid')->unique()->values();
        $this->assertSame(
            $linkedBlockUuids->count(),
            TextBlock::query()->whereIn('uuid', $linkedBlockUuids->all())->count(),
            $caseName . ': every linked text_block_uuid should exist.'
        );

        $legacyBlockUuids = $questions->pluck('theory_text_block_uuid')->filter()->unique()->values();
        $this->assertSame(
            $legacyBlockUuids->count(),
            TextBlock::query()->whereIn('uuid', $legacyBlockUuids->all())->count(),
            $caseName . ': every legacy text_block_uuid should exist.'
        );
    }

    private function assertDirectSentenceBuilderQuestionsExposeTheoryBlocks(string $directSlug, string $caseName): void
    {
        $resolved = app(SavedTestResolver::class)->resolve($directSlug);
        $questions = app(SavedTestResolver::class)->loadQuestions($resolved, ['theoryTextBlocks']);

        $this->assertGreaterThan(0, $questions->count(), $caseName . ': direct Sentence Builder questions.');
        $this->assertTrue(
            $questions->every(fn (Question $question): bool => $question->theoryTextBlocks->isNotEmpty()),
            $caseName . ': direct Sentence Builder questions should expose theory blocks.'
        );
    }

    /**
     * @param  array<int, class-string>  $coveredSeeders
     */
    private function assertMixedResolvedQuestionsExposeTheoryBlocks(object $mixedTest, array $coveredSeeders, string $caseName): void
    {
        $filters = $mixedTest->filters ?? [];
        $filters['num_questions'] = 48;
        $filters['randomize_filtered'] = false;

        $query = http_build_query([
            'filters' => base64_encode((string) json_encode($filters, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)),
            'name' => $mixedTest->name,
        ]);

        $request = Request::create('/test/' . $mixedTest->slug . '?' . $query, 'GET');
        $this->app->instance('request', $request);

        $resolver = app(SavedTestResolver::class);
        $resolved = $resolver->resolve($mixedTest->slug);
        $questions = $resolver->loadQuestions($resolved, ['theoryTextBlocks']);

        $this->assertGreaterThan(0, $questions->count(), $caseName . ': mixed resolved questions.');
        foreach ($coveredSeeders as $coveredSeeder) {
            $this->assertTrue(
                $questions->pluck('seeder')->contains($coveredSeeder),
                $caseName . ': mixed resolved questions should include ' . $coveredSeeder
            );
        }

        $this->assertTrue(
            $questions->every(fn (Question $question): bool => $question->theoryTextBlocks->isNotEmpty()),
            $caseName . ': mixed questions should expose theory blocks.'
        );
    }

    private function assertAuditReportsSomeAnyRoutesAsOk(): void
    {
        $jsonPath = storage_path('framework/testing/some-any-theory-links-audit.json');
        $markdownPath = storage_path('framework/testing/some-any-theory-links-audit.md');
        File::delete([$jsonPath, $markdownPath]);

        $this->artisan('theory-pages:audit-tests-unification', [
            '--json' => $jsonPath,
            '--md' => $markdownPath,
        ])->assertExitCode(0);

        $audit = json_decode((string) File::get($jsonPath), true);
        $this->assertIsArray($audit);

        $rows = collect($audit['pages'] ?? [])->keyBy('route');

        foreach ($this->cases() as $caseName => $case) {
            $row = $rows->get($case['route']);

            $this->assertNotNull($row, $caseName . ': route should be present in audit.');
            $this->assertSame('OK', $row['status'] ?? null, $caseName . ': audit status.');
            $this->assertEmpty(
                collect($row['missing'] ?? [])->intersect([
                    'theory_links',
                    'polyglot_theory_links',
                    'question_theory_text_blocks',
                ])->all(),
                $caseName . ': audit should not report missing theory links.'
            );
        }
    }
}
