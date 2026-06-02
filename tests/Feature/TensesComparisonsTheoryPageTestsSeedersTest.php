<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Question;
use App\Models\SavedGrammarTest;
use App\Models\TextBlock;
use App\Services\SavedTestResolver;
use App\Services\TheoryPagePromptLinkedTestsService;
use Database\Seeders\Page_V3\Tenses\TensesCategorySeeder;
use Database\Seeders\Page_V3\Tenses\TensesPresentSimpleVsPresentContinuousTheorySeeder;
use Database\Seeders\Page_V3\Tenses\TensesPastSimpleVsPastContinuousTheorySeeder;
use Database\Seeders\Page_V3\Tenses\TensesPresentPerfectVsPastSimpleTheorySeeder;
use Database\Seeders\Page_V3\Tenses\TensesPresentPerfectVsPresentPerfectContinuousTheorySeeder;
use Database\Seeders\Page_V3\Tenses\TensesPastPerfectVsPastPerfectContinuousTheorySeeder;
use Database\Seeders\V3\Polyglot\PolyglotPresentSimpleVsPresentContinuousAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotPastSimpleVsPastContinuousAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotPresentPerfectVsPastSimpleAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotPresentPerfectVsPresentPerfectContinuousAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotPastPerfectVsPastPerfectContinuousAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotPresentPerfectVsPastSimpleLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotPresentPerfectContinuousVsPresentPerfectLessonSeeder;
use Database\Seeders\V3\Tenses\Comparisons\PresentSimpleVsPresentContinuousAllLevelsV3Seeder;
use Database\Seeders\V3\Tenses\Comparisons\PastSimpleVsPastContinuousAllLevelsV3Seeder;
use Database\Seeders\V3\Tenses\Comparisons\PresentPerfectVsPastSimpleAllLevelsV3Seeder;
use Database\Seeders\V3\Tenses\Comparisons\PresentPerfectVsPresentPerfectContinuousAllLevelsV3Seeder;
use Database\Seeders\V3\Tenses\Comparisons\PastPerfectVsPastPerfectContinuousAllLevelsV3Seeder;
use Database\Seeders\V3\TheoryLinks\PresentSimpleVsPresentContinuousTheoryLinksSeeder;
use Database\Seeders\V3\TheoryLinks\PastSimpleVsPastContinuousTheoryLinksSeeder;
use Database\Seeders\V3\TheoryLinks\PresentPerfectVsPastSimpleTheoryLinksSeeder;
use Database\Seeders\V3\TheoryLinks\PresentPerfectVsPresentPerfectContinuousTheoryLinksSeeder;
use Database\Seeders\V3\TheoryLinks\PastPerfectVsPastPerfectContinuousTheoryLinksSeeder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class TensesComparisonsTheoryPageTestsSeedersTest extends TestCase
{
    use RebuildsComposeTestSchema;

    private const LEVELS = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'];

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'coming-soon.enabled' => false,
            'tests.tech_info_enabled' => false,
        ]);

        $this->rebuildComposeTestSchema();
    }

    public function test_tenses_comparison_pages_have_tests_and_theory_links(): void
    {
        $this->seedTensesComparisonStack();

        $this->seed([
            PresentSimpleVsPresentContinuousTheoryLinksSeeder::class,
            PastSimpleVsPastContinuousTheoryLinksSeeder::class,
            PresentPerfectVsPastSimpleTheoryLinksSeeder::class,
            PresentPerfectVsPresentPerfectContinuousTheoryLinksSeeder::class,
            PastPerfectVsPastPerfectContinuousTheoryLinksSeeder::class,
        ]);

        foreach ($this->cases() as $caseName => $case) {
            $page = Page::query()->where('slug', $case['page_slug'])->firstOrFail();
            $directTest = SavedGrammarTest::query()->where('slug', $case['direct_slug'])->first();

            $this->assertNotNull($directTest, $caseName . ': direct Sentence Builder test exists.');
            $this->assertQuestionCountByLevel($case['v3_seeder'], $caseName . ': V3');
            $this->assertQuestionCountByLevel($case['polyglot_seeder'], $caseName . ': Polyglot');

            $tests = app(TheoryPagePromptLinkedTestsService::class)->buildForPage($page);
            $mixedTest = $tests->firstWhere('slug', 'theory-page-' . $page->id . '-mixed-a1-c2');

            $this->assertNotNull($mixedTest, $caseName . ': Mixed A1-C2 test exists.');
            $this->assertTrue($mixedTest->isVirtual(), $caseName);

            $mixedSeederClasses = collect($mixedTest->filters['seeder_classes'] ?? [])->values();
            foreach ($case['mixed_filter_seeders'] as $expectedSeeder) {
                $this->assertTrue($mixedSeederClasses->contains($expectedSeeder), $caseName . ': ' . $expectedSeeder);
            }

            $this->assertEveryCoveredQuestionHasTheoryLinks($case['covered_seeders'], $caseName);
            $this->assertDirectSentenceBuilderQuestionsExposeTheoryBlocks($case['direct_slug'], $case['polyglot_seeder'], $caseName);
            $this->assertMixedResolvedQuestionsExposeTheoryBlocks($mixedTest, $case['mixed_filter_seeders'], $caseName);

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

        $this->assertAuditReportsTensesComparisonRoutesAsOk();
    }

    private function seedTensesComparisonStack(): void
    {
        $this->seed([
            TensesCategorySeeder::class,
            TensesPresentSimpleVsPresentContinuousTheorySeeder::class,
            TensesPastSimpleVsPastContinuousTheorySeeder::class,
            TensesPresentPerfectVsPastSimpleTheorySeeder::class,
            TensesPresentPerfectVsPresentPerfectContinuousTheorySeeder::class,
            TensesPastPerfectVsPastPerfectContinuousTheorySeeder::class,
            PresentSimpleVsPresentContinuousAllLevelsV3Seeder::class,
            PastSimpleVsPastContinuousAllLevelsV3Seeder::class,
            PresentPerfectVsPastSimpleAllLevelsV3Seeder::class,
            PresentPerfectVsPresentPerfectContinuousAllLevelsV3Seeder::class,
            PastPerfectVsPastPerfectContinuousAllLevelsV3Seeder::class,
            PolyglotPresentSimpleVsPresentContinuousAllLevelsLessonSeeder::class,
            PolyglotPastSimpleVsPastContinuousAllLevelsLessonSeeder::class,
            PolyglotPresentPerfectVsPastSimpleAllLevelsLessonSeeder::class,
            PolyglotPresentPerfectVsPresentPerfectContinuousAllLevelsLessonSeeder::class,
            PolyglotPastPerfectVsPastPerfectContinuousAllLevelsLessonSeeder::class,
            PolyglotPresentPerfectVsPastSimpleLessonSeeder::class,
            PolyglotPresentPerfectContinuousVsPresentPerfectLessonSeeder::class,
        ]);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function cases(): array
    {
        return [
            'present-simple-vs-present-continuous' => [
                'route' => '/theory/tenses/present-simple-vs-present-continuous',
                'page_slug' => 'present-simple-vs-present-continuous',
                'direct_slug' => 'polyglot-present-simple-vs-present-continuous-all-levels',
                'v3_seeder' => PresentSimpleVsPresentContinuousAllLevelsV3Seeder::class,
                'polyglot_seeder' => PolyglotPresentSimpleVsPresentContinuousAllLevelsLessonSeeder::class,
                'mixed_filter_seeders' => [
                    PresentSimpleVsPresentContinuousAllLevelsV3Seeder::class,
                    PolyglotPresentSimpleVsPresentContinuousAllLevelsLessonSeeder::class,
                ],
                'covered_seeders' => [
                    PresentSimpleVsPresentContinuousAllLevelsV3Seeder::class,
                    PolyglotPresentSimpleVsPresentContinuousAllLevelsLessonSeeder::class
                ],
                'links_seeder' => PresentSimpleVsPresentContinuousTheoryLinksSeeder::class,
            ],
            'past-simple-vs-past-continuous' => [
                'route' => '/theory/tenses/past-simple-vs-past-continuous',
                'page_slug' => 'past-simple-vs-past-continuous',
                'direct_slug' => 'polyglot-past-simple-vs-past-continuous-all-levels',
                'v3_seeder' => PastSimpleVsPastContinuousAllLevelsV3Seeder::class,
                'polyglot_seeder' => PolyglotPastSimpleVsPastContinuousAllLevelsLessonSeeder::class,
                'mixed_filter_seeders' => [
                    PastSimpleVsPastContinuousAllLevelsV3Seeder::class,
                    PolyglotPastSimpleVsPastContinuousAllLevelsLessonSeeder::class,
                ],
                'covered_seeders' => [
                    PastSimpleVsPastContinuousAllLevelsV3Seeder::class,
                    PolyglotPastSimpleVsPastContinuousAllLevelsLessonSeeder::class
                ],
                'links_seeder' => PastSimpleVsPastContinuousTheoryLinksSeeder::class,
            ],
            'present-perfect-vs-past-simple' => [
                'route' => '/theory/tenses/present-perfect-vs-past-simple',
                'page_slug' => 'present-perfect-vs-past-simple',
                'direct_slug' => 'polyglot-present-perfect-vs-past-simple-all-levels',
                'v3_seeder' => PresentPerfectVsPastSimpleAllLevelsV3Seeder::class,
                'polyglot_seeder' => PolyglotPresentPerfectVsPastSimpleAllLevelsLessonSeeder::class,
                'mixed_filter_seeders' => [
                    PresentPerfectVsPastSimpleAllLevelsV3Seeder::class,
                    PolyglotPresentPerfectVsPastSimpleAllLevelsLessonSeeder::class,
                ],
                'covered_seeders' => [
                    PresentPerfectVsPastSimpleAllLevelsV3Seeder::class,
                    PolyglotPresentPerfectVsPastSimpleAllLevelsLessonSeeder::class,
                    PolyglotPresentPerfectVsPastSimpleLessonSeeder::class
                ],
                'covered_seeder_counts' => [
                    PresentPerfectVsPastSimpleAllLevelsV3Seeder::class => 72,
                    PolyglotPresentPerfectVsPastSimpleAllLevelsLessonSeeder::class => 72,
                    PolyglotPresentPerfectVsPastSimpleLessonSeeder::class => 24,
                ],
                'links_seeder' => PresentPerfectVsPastSimpleTheoryLinksSeeder::class,
            ],
            'present-perfect-vs-present-perfect-continuous' => [
                'route' => '/theory/tenses/present-perfect-vs-present-perfect-continuous',
                'page_slug' => 'present-perfect-vs-present-perfect-continuous',
                'direct_slug' => 'polyglot-present-perfect-vs-present-perfect-continuous-all-levels',
                'v3_seeder' => PresentPerfectVsPresentPerfectContinuousAllLevelsV3Seeder::class,
                'polyglot_seeder' => PolyglotPresentPerfectVsPresentPerfectContinuousAllLevelsLessonSeeder::class,
                'mixed_filter_seeders' => [
                    PresentPerfectVsPresentPerfectContinuousAllLevelsV3Seeder::class,
                    PolyglotPresentPerfectVsPresentPerfectContinuousAllLevelsLessonSeeder::class,
                ],
                'covered_seeders' => [
                    PresentPerfectVsPresentPerfectContinuousAllLevelsV3Seeder::class,
                    PolyglotPresentPerfectVsPresentPerfectContinuousAllLevelsLessonSeeder::class,
                    PolyglotPresentPerfectContinuousVsPresentPerfectLessonSeeder::class
                ],
                'covered_seeder_counts' => [
                    PresentPerfectVsPresentPerfectContinuousAllLevelsV3Seeder::class => 72,
                    PolyglotPresentPerfectVsPresentPerfectContinuousAllLevelsLessonSeeder::class => 72,
                    PolyglotPresentPerfectContinuousVsPresentPerfectLessonSeeder::class => 24,
                ],
                'links_seeder' => PresentPerfectVsPresentPerfectContinuousTheoryLinksSeeder::class,
            ],
            'past-perfect-vs-past-perfect-continuous' => [
                'route' => '/theory/tenses/past-perfect-vs-past-perfect-continuous',
                'page_slug' => 'past-perfect-vs-past-perfect-continuous',
                'direct_slug' => 'polyglot-past-perfect-vs-past-perfect-continuous-all-levels',
                'v3_seeder' => PastPerfectVsPastPerfectContinuousAllLevelsV3Seeder::class,
                'polyglot_seeder' => PolyglotPastPerfectVsPastPerfectContinuousAllLevelsLessonSeeder::class,
                'mixed_filter_seeders' => [
                    PastPerfectVsPastPerfectContinuousAllLevelsV3Seeder::class,
                    PolyglotPastPerfectVsPastPerfectContinuousAllLevelsLessonSeeder::class,
                ],
                'covered_seeders' => [
                    PastPerfectVsPastPerfectContinuousAllLevelsV3Seeder::class,
                    PolyglotPastPerfectVsPastPerfectContinuousAllLevelsLessonSeeder::class
                ],
                'links_seeder' => PastPerfectVsPastPerfectContinuousTheoryLinksSeeder::class,
            ]
        ];
    }

    private function assertQuestionCountByLevel(string $seederClass, string $caseName): void
    {
        $questions = Question::query()
            ->where('seeder', $seederClass)
            ->get(['level']);

        $this->assertSame(72, $questions->count(), $caseName . ': total question count.');

        $counts = $questions->countBy('level');
        foreach (self::LEVELS as $level) {
            $this->assertSame(12, (int) $counts->get($level, 0), $caseName . ': ' . $level . ' count.');
        }
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
            $this->assertSame(
                $this->expectedCoveredSeederCount($caseName, $seederClass),
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

    private function expectedCoveredSeederCount(string $caseName, string $seederClass): int
    {
        $case = $this->cases()[$caseName] ?? [];
        $counts = is_array($case['covered_seeder_counts'] ?? null) ? $case['covered_seeder_counts'] : [];

        return (int) ($counts[$seederClass] ?? 72);
    }

    private function assertDirectSentenceBuilderQuestionsExposeTheoryBlocks(
        string $directSlug,
        string $polyglotSeeder,
        string $caseName
    ): void {
        $resolved = app(SavedTestResolver::class)->resolve($directSlug);
        $questions = app(SavedTestResolver::class)->loadQuestions($resolved, ['theoryTextBlocks']);

        $this->assertSame(72, $questions->count(), $caseName . ': direct Sentence Builder questions.');
        $this->assertTrue(
            $questions->every(fn (Question $question): bool => (string) $question->seeder === $polyglotSeeder),
            $caseName . ': direct Sentence Builder should use only the page-local Polyglot seeder.'
        );
        $this->assertTrue(
            $questions->every(fn (Question $question): bool => (string) $question->type === Question::TYPE_COMPOSE_TOKENS),
            $caseName . ': direct Sentence Builder questions should be compose mode.'
        );
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
        $filters['num_questions'] = 84;
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

        $this->assertSame(84, $questions->count(), $caseName . ': mixed resolved questions.');
        foreach ($coveredSeeders as $coveredSeeder) {
            $this->assertTrue(
                $questions->pluck('seeder')->contains($coveredSeeder),
                $caseName . ': mixed resolved questions should include ' . $coveredSeeder
            );
        }

        $this->assertTrue(
            $questions->contains(fn (Question $question): bool => (string) $question->type === Question::TYPE_COMPOSE_TOKENS),
            $caseName . ': mixed test should include compose questions.'
        );
        $this->assertTrue(
            $questions->contains(fn (Question $question): bool => (string) $question->type !== Question::TYPE_COMPOSE_TOKENS),
            $caseName . ': mixed test should include standard V3 questions.'
        );
        $this->assertTrue(
            $questions->every(fn (Question $question): bool => $question->theoryTextBlocks->isNotEmpty()),
            $caseName . ': mixed questions should expose theory blocks.'
        );
    }

    private function assertAuditReportsTensesComparisonRoutesAsOk(): void
    {
        $jsonPath = storage_path('framework/testing/tenses-comparisons-theory-links-audit.json');
        $markdownPath = storage_path('framework/testing/tenses-comparisons-theory-links-audit.md');
        File::delete([$jsonPath, $markdownPath]);

        $this->artisan('theory-pages:audit-tests-unification', [
            '--json' => $jsonPath,
            '--md' => $markdownPath,
        ])->assertExitCode(0);

        $audit = json_decode((string) File::get($jsonPath), true);
        $this->assertIsArray($audit);

        $rowsByRoute = collect($audit['pages'] ?? [])->keyBy('route');

        foreach ($this->cases() as $caseName => $case) {
            $row = $rowsByRoute->get($case['route']);

            $this->assertNotNull($row, $caseName . ': route should be present in audit.');
            $this->assertSame('OK', $row['status'] ?? null, $caseName . ': audit status.');
            $this->assertEmpty(
                collect($row['missing'] ?? [])->intersect([
                    'sentence_builder',
                    'mixed_a1_c2',
                    'v3_questions',
                    'polyglot_questions',
                    'theory_links',
                    'polyglot_theory_links',
                    'question_theory_text_blocks',
                ])->all(),
                $caseName . ': audit should not report missing items.'
            );
        }
    }
}
