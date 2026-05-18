<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Question;
use App\Models\SavedGrammarTest;
use App\Models\TextBlock;
use App\Services\SavedTestResolver;
use App\Services\TheoryPagePromptLinkedTestsService;
use Database\Seeders\Page_V3\FutureForms\FutureFormsCategorySeeder;
use Database\Seeders\Page_V3\FutureForms\FutureContinuous\FutureContinuousCategorySeeder;
use Database\Seeders\Page_V3\FutureForms\FuturePerfect\FuturePerfectCategorySeeder;
use Database\Seeders\Page_V3\FutureForms\FutureContinuous\FutureContinuousFormsTheorySeeder;
use Database\Seeders\Page_V3\FutureForms\FutureContinuous\FutureContinuousNegativesTheorySeeder;
use Database\Seeders\Page_V3\FutureForms\FutureContinuous\FutureContinuousQuestionsTheorySeeder;
use Database\Seeders\Page_V3\FutureForms\FutureContinuous\FutureContinuousTimeExpressionsTheorySeeder;
use Database\Seeders\Page_V3\FutureForms\FuturePerfect\FuturePerfectFormsTheorySeeder;
use Database\Seeders\Page_V3\FutureForms\FuturePerfect\FuturePerfectNegativesTheorySeeder;
use Database\Seeders\V3\FutureForms\FutureContinuous\FutureContinuousFormsAllLevelsV3Seeder;
use Database\Seeders\V3\FutureForms\FutureContinuous\FutureContinuousNegativesAllLevelsV3Seeder;
use Database\Seeders\V3\FutureForms\FutureContinuous\FutureContinuousQuestionsAllLevelsV3Seeder;
use Database\Seeders\V3\FutureForms\FutureContinuous\FutureContinuousTimeExpressionsAllLevelsV3Seeder;
use Database\Seeders\V3\FutureForms\FuturePerfect\FuturePerfectFormsAllLevelsV3Seeder;
use Database\Seeders\V3\FutureForms\FuturePerfect\FuturePerfectNegativesAllLevelsV3Seeder;
use Database\Seeders\V3\Polyglot\PolyglotFutureContinuousFormsAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotFutureContinuousNegativesAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotFutureContinuousQuestionsAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotFutureContinuousTimeExpressionsAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotFuturePerfectFormsAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotFuturePerfectNegativesAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotFutureContinuousBasicsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotFuturePerfectBasicsLessonSeeder;
use Database\Seeders\V3\TheoryLinks\FutureContinuousFormsTheoryLinksSeeder;
use Database\Seeders\V3\TheoryLinks\FutureContinuousNegativesTheoryLinksSeeder;
use Database\Seeders\V3\TheoryLinks\FutureContinuousQuestionsTheoryLinksSeeder;
use Database\Seeders\V3\TheoryLinks\FutureContinuousTimeExpressionsTheoryLinksSeeder;
use Database\Seeders\V3\TheoryLinks\FuturePerfectFormsTheoryLinksSeeder;
use Database\Seeders\V3\TheoryLinks\FuturePerfectNegativesTheoryLinksSeeder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class FutureFormsAdvancedTheoryPageTestsSeedersTest extends TestCase
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

    public function test_future_forms_advanced_pages_have_tests_and_theory_links(): void
    {
        $this->seedFutureFormsAdvancedStack();

        $this->seed([
            FutureContinuousFormsTheoryLinksSeeder::class,
            FutureContinuousNegativesTheoryLinksSeeder::class,
            FutureContinuousQuestionsTheoryLinksSeeder::class,
            FutureContinuousTimeExpressionsTheoryLinksSeeder::class,
            FuturePerfectFormsTheoryLinksSeeder::class,
            FuturePerfectNegativesTheoryLinksSeeder::class,
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

            $questionUuids = Question::query()->whereIn('seeder', $case['covered_seeders'])->pluck('uuid');
            $firstRunPivotRows = DB::table('question_theory_text_blocks')->whereIn('question_uuid', $questionUuids)->count();
            $this->seed($case['links_seeder']);
            $this->assertSame($firstRunPivotRows, DB::table('question_theory_text_blocks')->whereIn('question_uuid', $questionUuids)->count(), $caseName . ': idempotency check failed.');
        }

        $this->assertAuditReportsFutureFormsAdvancedRoutesAsOk();
    }

    private function seedFutureFormsAdvancedStack(): void
    {
        $this->seed([
            FutureFormsCategorySeeder::class,
            FutureContinuousCategorySeeder::class,
            FuturePerfectCategorySeeder::class,
            FutureContinuousFormsTheorySeeder::class,
            FutureContinuousNegativesTheorySeeder::class,
            FutureContinuousQuestionsTheorySeeder::class,
            FutureContinuousTimeExpressionsTheorySeeder::class,
            FuturePerfectFormsTheorySeeder::class,
            FuturePerfectNegativesTheorySeeder::class,
            FutureContinuousFormsAllLevelsV3Seeder::class,
            FutureContinuousNegativesAllLevelsV3Seeder::class,
            FutureContinuousQuestionsAllLevelsV3Seeder::class,
            FutureContinuousTimeExpressionsAllLevelsV3Seeder::class,
            FuturePerfectFormsAllLevelsV3Seeder::class,
            FuturePerfectNegativesAllLevelsV3Seeder::class,
            PolyglotFutureContinuousFormsAllLevelsLessonSeeder::class,
            PolyglotFutureContinuousNegativesAllLevelsLessonSeeder::class,
            PolyglotFutureContinuousQuestionsAllLevelsLessonSeeder::class,
            PolyglotFutureContinuousTimeExpressionsAllLevelsLessonSeeder::class,
            PolyglotFuturePerfectFormsAllLevelsLessonSeeder::class,
            PolyglotFuturePerfectNegativesAllLevelsLessonSeeder::class,
            PolyglotFutureContinuousBasicsLessonSeeder::class,
            PolyglotFuturePerfectBasicsLessonSeeder::class,
        ]);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function cases(): array
    {
        return [
            'future-continuous-forms' => [
                'route' => '/theory/maibutni-formy/future-continuous/future-continuous-forms',
                'page_slug' => 'future-continuous-forms',
                'direct_slug' => 'polyglot-future-continuous-forms-all-levels',
                'v3_seeder' => FutureContinuousFormsAllLevelsV3Seeder::class,
                'polyglot_seeder' => PolyglotFutureContinuousFormsAllLevelsLessonSeeder::class,
                'mixed_filter_seeders' => [FutureContinuousFormsAllLevelsV3Seeder::class, PolyglotFutureContinuousFormsAllLevelsLessonSeeder::class],
                'covered_seeders' => [FutureContinuousFormsAllLevelsV3Seeder::class, PolyglotFutureContinuousFormsAllLevelsLessonSeeder::class, PolyglotFutureContinuousBasicsLessonSeeder::class],
                'links_seeder' => FutureContinuousFormsTheoryLinksSeeder::class,
                'covered_seeder_counts' => [PolyglotFutureContinuousBasicsLessonSeeder::class => 24],
            ],
            'future-continuous-negatives' => [
                'route' => '/theory/maibutni-formy/future-continuous/future-continuous-negatives',
                'page_slug' => 'future-continuous-negatives',
                'direct_slug' => 'polyglot-future-continuous-negatives-all-levels',
                'v3_seeder' => FutureContinuousNegativesAllLevelsV3Seeder::class,
                'polyglot_seeder' => PolyglotFutureContinuousNegativesAllLevelsLessonSeeder::class,
                'mixed_filter_seeders' => [FutureContinuousNegativesAllLevelsV3Seeder::class, PolyglotFutureContinuousNegativesAllLevelsLessonSeeder::class],
                'covered_seeders' => [FutureContinuousNegativesAllLevelsV3Seeder::class, PolyglotFutureContinuousNegativesAllLevelsLessonSeeder::class],
                'links_seeder' => FutureContinuousNegativesTheoryLinksSeeder::class,
            ],
            'future-continuous-questions' => [
                'route' => '/theory/maibutni-formy/future-continuous/future-continuous-questions',
                'page_slug' => 'future-continuous-questions',
                'direct_slug' => 'polyglot-future-continuous-questions-all-levels',
                'v3_seeder' => FutureContinuousQuestionsAllLevelsV3Seeder::class,
                'polyglot_seeder' => PolyglotFutureContinuousQuestionsAllLevelsLessonSeeder::class,
                'mixed_filter_seeders' => [FutureContinuousQuestionsAllLevelsV3Seeder::class, PolyglotFutureContinuousQuestionsAllLevelsLessonSeeder::class],
                'covered_seeders' => [FutureContinuousQuestionsAllLevelsV3Seeder::class, PolyglotFutureContinuousQuestionsAllLevelsLessonSeeder::class],
                'links_seeder' => FutureContinuousQuestionsTheoryLinksSeeder::class,
            ],
            'future-continuous-time-expressions' => [
                'route' => '/theory/maibutni-formy/future-continuous/future-continuous-time-expressions',
                'page_slug' => 'future-continuous-time-expressions',
                'direct_slug' => 'polyglot-future-continuous-time-expressions-all-levels',
                'v3_seeder' => FutureContinuousTimeExpressionsAllLevelsV3Seeder::class,
                'polyglot_seeder' => PolyglotFutureContinuousTimeExpressionsAllLevelsLessonSeeder::class,
                'mixed_filter_seeders' => [FutureContinuousTimeExpressionsAllLevelsV3Seeder::class, PolyglotFutureContinuousTimeExpressionsAllLevelsLessonSeeder::class],
                'covered_seeders' => [FutureContinuousTimeExpressionsAllLevelsV3Seeder::class, PolyglotFutureContinuousTimeExpressionsAllLevelsLessonSeeder::class],
                'links_seeder' => FutureContinuousTimeExpressionsTheoryLinksSeeder::class,
            ],
            'future-perfect-forms' => [
                'route' => '/theory/maibutni-formy/future-perfect/future-perfect-forms',
                'page_slug' => 'future-perfect-forms',
                'direct_slug' => 'polyglot-future-perfect-forms-all-levels',
                'v3_seeder' => FuturePerfectFormsAllLevelsV3Seeder::class,
                'polyglot_seeder' => PolyglotFuturePerfectFormsAllLevelsLessonSeeder::class,
                'mixed_filter_seeders' => [FuturePerfectFormsAllLevelsV3Seeder::class, PolyglotFuturePerfectFormsAllLevelsLessonSeeder::class],
                'covered_seeders' => [FuturePerfectFormsAllLevelsV3Seeder::class, PolyglotFuturePerfectFormsAllLevelsLessonSeeder::class, PolyglotFuturePerfectBasicsLessonSeeder::class],
                'links_seeder' => FuturePerfectFormsTheoryLinksSeeder::class,
                'covered_seeder_counts' => [PolyglotFuturePerfectBasicsLessonSeeder::class => 24],
            ],
            'future-perfect-negatives' => [
                'route' => '/theory/maibutni-formy/future-perfect/future-perfect-negatives',
                'page_slug' => 'future-perfect-negatives',
                'direct_slug' => 'polyglot-future-perfect-negatives-all-levels',
                'v3_seeder' => FuturePerfectNegativesAllLevelsV3Seeder::class,
                'polyglot_seeder' => PolyglotFuturePerfectNegativesAllLevelsLessonSeeder::class,
                'mixed_filter_seeders' => [FuturePerfectNegativesAllLevelsV3Seeder::class, PolyglotFuturePerfectNegativesAllLevelsLessonSeeder::class],
                'covered_seeders' => [FuturePerfectNegativesAllLevelsV3Seeder::class, PolyglotFuturePerfectNegativesAllLevelsLessonSeeder::class],
                'links_seeder' => FuturePerfectNegativesTheoryLinksSeeder::class,
            ]
        ];
    }

    private function assertQuestionCountByLevel(string $seederClass, string $caseName): void
    {
        $questions = Question::query()->where('seeder', $seederClass)->get(['level']);
        $this->assertSame(72, $questions->count(), $caseName . ': total question count.');
        $counts = $questions->countBy('level');
        foreach (self::LEVELS as $level) {
            $this->assertSame(12, (int) $counts->get($level, 0), $caseName . ': ' . $level . ' count.');
        }
    }

    /**
     * @param array<int, class-string> $seederClasses
     */
    private function assertEveryCoveredQuestionHasTheoryLinks(array $seederClasses, string $caseName): void
    {
        $questions = Question::query()->whereIn('seeder', $seederClasses)->get(['id', 'uuid', 'seeder', 'theory_text_block_uuid']);
        foreach ($seederClasses as $seederClass) {
            $this->assertSame($this->expectedCoveredSeederCount($caseName, $seederClass), $questions->where('seeder', $seederClass)->count(), $caseName . ': ' . $seederClass);
        }
        $pivotRows = DB::table('question_theory_text_blocks')->whereIn('question_uuid', $questions->pluck('uuid'))->get();
        $this->assertSame($questions->count(), $pivotRows->pluck('question_uuid')->unique()->count(), $caseName . ': every covered question should have pivot rows.');
        $this->assertSame($questions->count(), $questions->whereNotNull('theory_text_block_uuid')->count(), $caseName . ': every covered question should set the legacy theory_text_block_uuid.');
        $linkedBlockUuids = $pivotRows->pluck('text_block_uuid')->unique()->values();
        $this->assertSame($linkedBlockUuids->count(), TextBlock::query()->whereIn('uuid', $linkedBlockUuids->all())->count(), $caseName . ': every linked text_block_uuid should exist.');
        $legacyBlockUuids = $questions->pluck('theory_text_block_uuid')->filter()->unique()->values();
        $this->assertSame($legacyBlockUuids->count(), TextBlock::query()->whereIn('uuid', $legacyBlockUuids->all())->count(), $caseName . ': every legacy text_block_uuid should exist.');
    }

    private function expectedCoveredSeederCount(string $caseName, string $seederClass): int
    {
        $case = $this->cases()[$caseName] ?? [];
        $counts = is_array($case['covered_seeder_counts'] ?? null) ? $case['covered_seeder_counts'] : [];

        return (int) ($counts[$seederClass] ?? 72);
    }

    private function assertDirectSentenceBuilderQuestionsExposeTheoryBlocks(string $directSlug, string $polyglotSeeder, string $caseName): void
    {
        $resolved = app(SavedTestResolver::class)->resolve($directSlug);
        $questions = app(SavedTestResolver::class)->loadQuestions($resolved, ['theoryTextBlocks']);
        $this->assertSame(72, $questions->count(), $caseName . ': direct Sentence Builder questions.');
        $this->assertTrue($questions->every(fn (Question $question): bool => (string) $question->seeder === $polyglotSeeder), $caseName . ': direct Sentence Builder should use only the page-local Polyglot seeder.');
        $this->assertTrue($questions->every(fn (Question $question): bool => (string) $question->type === Question::TYPE_COMPOSE_TOKENS), $caseName . ': direct Sentence Builder questions should be compose mode.');
        $this->assertTrue($questions->every(fn (Question $question): bool => $question->theoryTextBlocks->isNotEmpty()), $caseName . ': direct Sentence Builder questions should expose theory blocks.');
    }

    /**
     * @param array<int, class-string> $coveredSeeders
     */
    private function assertMixedResolvedQuestionsExposeTheoryBlocks(object $mixedTest, array $coveredSeeders, string $caseName): void
    {
        $filters = $mixedTest->filters ?? [];
        $filters['num_questions'] = 84;
        $filters['randomize_filtered'] = false;
        $query = http_build_query(['filters' => base64_encode((string) json_encode($filters, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)), 'name' => $mixedTest->name]);
        $request = Request::create('/test/' . $mixedTest->slug . '?' . $query, 'GET');
        $this->app->instance('request', $request);
        $resolver = app(SavedTestResolver::class);
        $resolved = $resolver->resolve($mixedTest->slug);
        $questions = $resolver->loadQuestions($resolved, ['theoryTextBlocks']);
        $this->assertSame(84, $questions->count(), $caseName . ': mixed resolved questions.');
        foreach ($coveredSeeders as $coveredSeeder) {
            $this->assertTrue($questions->pluck('seeder')->contains($coveredSeeder), $caseName . ': mixed resolved questions should include ' . $coveredSeeder);
        }
        $this->assertTrue($questions->contains(fn (Question $question): bool => (string) $question->type === Question::TYPE_COMPOSE_TOKENS), $caseName . ': mixed test should include compose questions.');
        $this->assertTrue($questions->contains(fn (Question $question): bool => (string) $question->type !== Question::TYPE_COMPOSE_TOKENS), $caseName . ': mixed test should include standard V3 questions.');
        $this->assertTrue($questions->every(fn (Question $question): bool => $question->theoryTextBlocks->isNotEmpty()), $caseName . ': mixed questions should expose theory blocks.');
    }

    private function assertAuditReportsFutureFormsAdvancedRoutesAsOk(): void
    {
        $jsonPath = storage_path('framework/testing/future-forms-advanced-theory-links-audit.json');
        $markdownPath = storage_path('framework/testing/future-forms-advanced-theory-links-audit.md');
        File::delete([$jsonPath, $markdownPath]);
        $this->artisan('theory-pages:audit-tests-unification', ['--json' => $jsonPath, '--md' => $markdownPath])->assertExitCode(0);
        $audit = json_decode((string) File::get($jsonPath), true);
        $this->assertIsArray($audit);
        $rowsByRoute = collect($audit['pages'] ?? [])->keyBy('route');
        foreach ($this->cases() as $caseName => $case) {
            $row = $rowsByRoute->get($case['route']);
            $this->assertNotNull($row, $caseName . ': route should be present in audit.');
            $this->assertSame('OK', $row['status'] ?? null, $caseName . ': audit status.');
            $this->assertEmpty(collect($row['missing'] ?? [])->intersect(['sentence_builder', 'mixed_a1_c2', 'v3_questions', 'polyglot_questions', 'theory_links', 'polyglot_theory_links', 'question_theory_text_blocks'])->all(), $caseName . ': audit should not report missing items.');
        }
    }
}
