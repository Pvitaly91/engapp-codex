<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Question;
use App\Models\SavedGrammarTest;
use App\Models\TextBlock;
use App\Services\SavedTestResolver;
use App\Services\TheoryPagePromptLinkedTestsService;
use Database\Seeders\Page_V3\Tenses\PastPerfect\PastPerfectCategorySeeder;
use Database\Seeders\Page_V3\Tenses\PastPerfect\PastPerfectFormsTheorySeeder;
use Database\Seeders\Page_V3\Tenses\PastPerfect\PastPerfectNegativesTheorySeeder;
use Database\Seeders\Page_V3\Tenses\PastPerfect\PastPerfectQuestionsTheorySeeder;
use Database\Seeders\Page_V3\Tenses\PastPerfect\PastPerfectTimeExpressionsTheorySeeder;
use Database\Seeders\Page_V3\Tenses\TensesCategorySeeder;
use Database\Seeders\V3\Polyglot\PolyglotPastPerfectBasicsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotPastPerfectFormsAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotPastPerfectNegativesAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotPastPerfectQuestionsAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotPastPerfectTimeExpressionsAllLevelsLessonSeeder;
use Database\Seeders\V3\Tenses\PastPerfect\PastPerfectFormsAllLevelsV3Seeder;
use Database\Seeders\V3\Tenses\PastPerfect\PastPerfectNegativesAllLevelsV3Seeder;
use Database\Seeders\V3\Tenses\PastPerfect\PastPerfectQuestionsAllLevelsV3Seeder;
use Database\Seeders\V3\Tenses\PastPerfect\PastPerfectTimeExpressionsAllLevelsV3Seeder;
use Database\Seeders\V3\TheoryLinks\PastPerfectFormsTheoryLinksSeeder;
use Database\Seeders\V3\TheoryLinks\PastPerfectNegativesTheoryLinksSeeder;
use Database\Seeders\V3\TheoryLinks\PastPerfectQuestionsTheoryLinksSeeder;
use Database\Seeders\V3\TheoryLinks\PastPerfectTimeExpressionsTheoryLinksSeeder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class PastPerfectTheoryPageTestsSeedersTest extends TestCase
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

    public function test_past_perfect_pages_have_tests_and_theory_links(): void
    {
        $this->seedPastPerfectStack();

        $this->seed([
            PastPerfectFormsTheoryLinksSeeder::class,
            PastPerfectNegativesTheoryLinksSeeder::class,
            PastPerfectQuestionsTheoryLinksSeeder::class,
            PastPerfectTimeExpressionsTheoryLinksSeeder::class,
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

        $this->assertAuditReportsPastPerfectRoutesAsOk();
    }

    private function seedPastPerfectStack(): void
    {
        $this->seed([
            TensesCategorySeeder::class,
            PastPerfectCategorySeeder::class,
            PastPerfectFormsTheorySeeder::class,
            PastPerfectNegativesTheorySeeder::class,
            PastPerfectQuestionsTheorySeeder::class,
            PastPerfectTimeExpressionsTheorySeeder::class,
            PastPerfectFormsAllLevelsV3Seeder::class,
            PastPerfectNegativesAllLevelsV3Seeder::class,
            PastPerfectQuestionsAllLevelsV3Seeder::class,
            PastPerfectTimeExpressionsAllLevelsV3Seeder::class,
            PolyglotPastPerfectFormsAllLevelsLessonSeeder::class,
            PolyglotPastPerfectNegativesAllLevelsLessonSeeder::class,
            PolyglotPastPerfectQuestionsAllLevelsLessonSeeder::class,
            PolyglotPastPerfectTimeExpressionsAllLevelsLessonSeeder::class,
            PolyglotPastPerfectBasicsLessonSeeder::class,
        ]);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function cases(): array
    {
        return [
            'forms' => [
                'route' => '/theory/tenses/past-perfect/past-perfect-forms',
                'page_slug' => 'past-perfect-forms',
                'direct_slug' => 'polyglot-past-perfect-forms-all-levels',
                'v3_seeder' => PastPerfectFormsAllLevelsV3Seeder::class,
                'polyglot_seeder' => PolyglotPastPerfectFormsAllLevelsLessonSeeder::class,
                'mixed_filter_seeders' => [
                    PastPerfectFormsAllLevelsV3Seeder::class,
                    PolyglotPastPerfectFormsAllLevelsLessonSeeder::class,
                ],
                'covered_seeders' => [
                    PastPerfectFormsAllLevelsV3Seeder::class,
                    PolyglotPastPerfectFormsAllLevelsLessonSeeder::class,
                    PolyglotPastPerfectBasicsLessonSeeder::class,
                ],
                'covered_seeder_counts' => [
                    PastPerfectFormsAllLevelsV3Seeder::class => 72,
                    PolyglotPastPerfectFormsAllLevelsLessonSeeder::class => 72,
                    PolyglotPastPerfectBasicsLessonSeeder::class => 24,
                ],
                'links_seeder' => PastPerfectFormsTheoryLinksSeeder::class,
            ],
            'negatives' => [
                'route' => '/theory/tenses/past-perfect/past-perfect-negatives',
                'page_slug' => 'past-perfect-negatives',
                'direct_slug' => 'polyglot-past-perfect-negatives-all-levels',
                'v3_seeder' => PastPerfectNegativesAllLevelsV3Seeder::class,
                'polyglot_seeder' => PolyglotPastPerfectNegativesAllLevelsLessonSeeder::class,
                'mixed_filter_seeders' => [
                    PastPerfectNegativesAllLevelsV3Seeder::class,
                    PolyglotPastPerfectNegativesAllLevelsLessonSeeder::class,
                ],
                'covered_seeders' => [
                    PastPerfectNegativesAllLevelsV3Seeder::class,
                    PolyglotPastPerfectNegativesAllLevelsLessonSeeder::class,
                ],
                'links_seeder' => PastPerfectNegativesTheoryLinksSeeder::class,
            ],
            'questions' => [
                'route' => '/theory/tenses/past-perfect/past-perfect-questions',
                'page_slug' => 'past-perfect-questions',
                'direct_slug' => 'polyglot-past-perfect-questions-all-levels',
                'v3_seeder' => PastPerfectQuestionsAllLevelsV3Seeder::class,
                'polyglot_seeder' => PolyglotPastPerfectQuestionsAllLevelsLessonSeeder::class,
                'mixed_filter_seeders' => [
                    PastPerfectQuestionsAllLevelsV3Seeder::class,
                    PolyglotPastPerfectQuestionsAllLevelsLessonSeeder::class,
                ],
                'covered_seeders' => [
                    PastPerfectQuestionsAllLevelsV3Seeder::class,
                    PolyglotPastPerfectQuestionsAllLevelsLessonSeeder::class,
                ],
                'links_seeder' => PastPerfectQuestionsTheoryLinksSeeder::class,
            ],
            'time_expressions' => [
                'route' => '/theory/tenses/past-perfect/past-perfect-time-expressions',
                'page_slug' => 'past-perfect-time-expressions',
                'direct_slug' => 'polyglot-past-perfect-time-expressions-all-levels',
                'v3_seeder' => PastPerfectTimeExpressionsAllLevelsV3Seeder::class,
                'polyglot_seeder' => PolyglotPastPerfectTimeExpressionsAllLevelsLessonSeeder::class,
                'mixed_filter_seeders' => [
                    PastPerfectTimeExpressionsAllLevelsV3Seeder::class,
                    PolyglotPastPerfectTimeExpressionsAllLevelsLessonSeeder::class,
                ],
                'covered_seeders' => [
                    PastPerfectTimeExpressionsAllLevelsV3Seeder::class,
                    PolyglotPastPerfectTimeExpressionsAllLevelsLessonSeeder::class,
                ],
                'links_seeder' => PastPerfectTimeExpressionsTheoryLinksSeeder::class,
            ],
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

    private function assertAuditReportsPastPerfectRoutesAsOk(): void
    {
        $jsonPath = storage_path('framework/testing/past-perfect-theory-links-audit.json');
        $markdownPath = storage_path('framework/testing/past-perfect-theory-links-audit.md');
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
