<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Question;
use App\Models\SavedGrammarTest;
use App\Models\TextBlock;
use App\Services\SavedTestResolver;
use App\Services\TheoryPagePromptLinkedTestsService;
use Database\Seeders\Page_V3\Tenses\PastContinuous\PastContinuousCategorySeeder;
use Database\Seeders\Page_V3\Tenses\PastContinuous\PastContinuousFormsTheorySeeder;
use Database\Seeders\Page_V3\Tenses\PastContinuous\PastContinuousNegativesTheorySeeder;
use Database\Seeders\Page_V3\Tenses\PastContinuous\PastContinuousQuestionsTheorySeeder;
use Database\Seeders\Page_V3\Tenses\PastContinuous\PastContinuousTimeExpressionsTheorySeeder;
use Database\Seeders\Page_V3\Tenses\TensesCategorySeeder;
use Database\Seeders\V3\Polyglot\PolyglotPastContinuousFormsAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotPastContinuousLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotPastContinuousNegativesAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotPastContinuousQuestionsAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotPastContinuousTimeExpressionsAllLevelsLessonSeeder;
use Database\Seeders\V3\Tenses\PastContinuous\PastContinuousFormsAllLevelsV3Seeder;
use Database\Seeders\V3\Tenses\PastContinuous\PastContinuousNegativesAllLevelsV3Seeder;
use Database\Seeders\V3\Tenses\PastContinuous\PastContinuousQuestionsAllLevelsV3Seeder;
use Database\Seeders\V3\Tenses\PastContinuous\PastContinuousTimeExpressionsAllLevelsV3Seeder;
use Database\Seeders\V3\TheoryLinks\PastContinuousFormsTheoryLinksSeeder;
use Database\Seeders\V3\TheoryLinks\PastContinuousNegativesTheoryLinksSeeder;
use Database\Seeders\V3\TheoryLinks\PastContinuousQuestionsTheoryLinksSeeder;
use Database\Seeders\V3\TheoryLinks\PastContinuousTimeExpressionsTheoryLinksSeeder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class PastContinuousTheoryPageTestsSeedersTest extends TestCase
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

    public function test_past_continuous_pages_have_tests_and_theory_links(): void
    {
        $this->seedPastContinuousStack();

        $this->seed([
            PastContinuousFormsTheoryLinksSeeder::class,
            PastContinuousNegativesTheoryLinksSeeder::class,
            PastContinuousQuestionsTheoryLinksSeeder::class,
            PastContinuousTimeExpressionsTheoryLinksSeeder::class,
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

        $this->assertAuditReportsPastContinuousRoutesAsOk();
    }

    private function seedPastContinuousStack(): void
    {
        $this->seed([
            TensesCategorySeeder::class,
            PastContinuousCategorySeeder::class,
            PastContinuousFormsTheorySeeder::class,
            PastContinuousNegativesTheorySeeder::class,
            PastContinuousQuestionsTheorySeeder::class,
            PastContinuousTimeExpressionsTheorySeeder::class,
            PastContinuousFormsAllLevelsV3Seeder::class,
            PastContinuousNegativesAllLevelsV3Seeder::class,
            PastContinuousQuestionsAllLevelsV3Seeder::class,
            PastContinuousTimeExpressionsAllLevelsV3Seeder::class,
            PolyglotPastContinuousFormsAllLevelsLessonSeeder::class,
            PolyglotPastContinuousNegativesAllLevelsLessonSeeder::class,
            PolyglotPastContinuousQuestionsAllLevelsLessonSeeder::class,
            PolyglotPastContinuousTimeExpressionsAllLevelsLessonSeeder::class,
            PolyglotPastContinuousLessonSeeder::class,
        ]);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function cases(): array
    {
        return [
            'forms' => [
                'route' => '/theory/tenses/past-continuous/past-continuous-forms',
                'page_slug' => 'past-continuous-forms',
                'direct_slug' => 'polyglot-past-continuous-forms-all-levels',
                'v3_seeder' => PastContinuousFormsAllLevelsV3Seeder::class,
                'polyglot_seeder' => PolyglotPastContinuousFormsAllLevelsLessonSeeder::class,
                'mixed_filter_seeders' => [
                    PastContinuousFormsAllLevelsV3Seeder::class,
                    PolyglotPastContinuousFormsAllLevelsLessonSeeder::class,
                ],
                'covered_seeders' => [
                    PastContinuousFormsAllLevelsV3Seeder::class,
                    PolyglotPastContinuousFormsAllLevelsLessonSeeder::class,
                    PolyglotPastContinuousLessonSeeder::class,
                ],
                'covered_seeder_counts' => [
                    PastContinuousFormsAllLevelsV3Seeder::class => 72,
                    PolyglotPastContinuousFormsAllLevelsLessonSeeder::class => 72,
                    PolyglotPastContinuousLessonSeeder::class => 24,
                ],
                'links_seeder' => PastContinuousFormsTheoryLinksSeeder::class,
            ],
            'negatives' => [
                'route' => '/theory/tenses/past-continuous/past-continuous-negatives',
                'page_slug' => 'past-continuous-negatives',
                'direct_slug' => 'polyglot-past-continuous-negatives-all-levels',
                'v3_seeder' => PastContinuousNegativesAllLevelsV3Seeder::class,
                'polyglot_seeder' => PolyglotPastContinuousNegativesAllLevelsLessonSeeder::class,
                'mixed_filter_seeders' => [
                    PastContinuousNegativesAllLevelsV3Seeder::class,
                    PolyglotPastContinuousNegativesAllLevelsLessonSeeder::class,
                ],
                'covered_seeders' => [
                    PastContinuousNegativesAllLevelsV3Seeder::class,
                    PolyglotPastContinuousNegativesAllLevelsLessonSeeder::class,
                ],
                'links_seeder' => PastContinuousNegativesTheoryLinksSeeder::class,
            ],
            'questions' => [
                'route' => '/theory/tenses/past-continuous/past-continuous-questions',
                'page_slug' => 'past-continuous-questions',
                'direct_slug' => 'polyglot-past-continuous-questions-all-levels',
                'v3_seeder' => PastContinuousQuestionsAllLevelsV3Seeder::class,
                'polyglot_seeder' => PolyglotPastContinuousQuestionsAllLevelsLessonSeeder::class,
                'mixed_filter_seeders' => [
                    PastContinuousQuestionsAllLevelsV3Seeder::class,
                    PolyglotPastContinuousQuestionsAllLevelsLessonSeeder::class,
                ],
                'covered_seeders' => [
                    PastContinuousQuestionsAllLevelsV3Seeder::class,
                    PolyglotPastContinuousQuestionsAllLevelsLessonSeeder::class,
                ],
                'links_seeder' => PastContinuousQuestionsTheoryLinksSeeder::class,
            ],
            'time_expressions' => [
                'route' => '/theory/tenses/past-continuous/past-continuous-time-expressions',
                'page_slug' => 'past-continuous-time-expressions',
                'direct_slug' => 'polyglot-past-continuous-time-expressions-all-levels',
                'v3_seeder' => PastContinuousTimeExpressionsAllLevelsV3Seeder::class,
                'polyglot_seeder' => PolyglotPastContinuousTimeExpressionsAllLevelsLessonSeeder::class,
                'mixed_filter_seeders' => [
                    PastContinuousTimeExpressionsAllLevelsV3Seeder::class,
                    PolyglotPastContinuousTimeExpressionsAllLevelsLessonSeeder::class,
                ],
                'covered_seeders' => [
                    PastContinuousTimeExpressionsAllLevelsV3Seeder::class,
                    PolyglotPastContinuousTimeExpressionsAllLevelsLessonSeeder::class,
                ],
                'links_seeder' => PastContinuousTimeExpressionsTheoryLinksSeeder::class,
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

    private function assertAuditReportsPastContinuousRoutesAsOk(): void
    {
        $jsonPath = storage_path('framework/testing/past-continuous-theory-links-audit.json');
        $markdownPath = storage_path('framework/testing/past-continuous-theory-links-audit.md');
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
