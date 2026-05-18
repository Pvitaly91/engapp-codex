<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Question;
use App\Models\SavedGrammarTest;
use App\Models\TextBlock;
use App\Services\SavedTestResolver;
use App\Services\TheoryPagePromptLinkedTestsService;
use Database\Seeders\Page_V3\FutureForms\FutureFormsCategorySeeder;
use Database\Seeders\Page_V3\FutureForms\FutureFormsWillVsBeGoingToTheorySeeder;
use Database\Seeders\Page_V3\FutureForms\FutureFormsPresentContinuousForFutureTheorySeeder;
use Database\Seeders\Page_V3\FutureForms\FutureFormsChoosingTheRightFutureFormTheorySeeder;
use Database\Seeders\Page_V3\FutureForms\FutureFormsFuturePerfectVsFutureContinuousTheorySeeder;
use Database\Seeders\Page_V3\FutureForms\FutureFormsFuturePerfectVsFuturePerfectContinuousTheorySeeder;
use Database\Seeders\Page_V3\FutureForms\FutureFormsFutureContinuousVsFuturePerfectContinuousTheorySeeder;
use Database\Seeders\V3\AI\ChatGpt\WillVsBeGoingToFutureFormsAllLevelsV3Seeder;
use Database\Seeders\V3\FutureForms\PresentContinuousForFutureAllLevelsV3Seeder;
use Database\Seeders\V3\FutureForms\ChoosingTheRightFutureFormAllLevelsV3Seeder;
use Database\Seeders\V3\FutureForms\FuturePerfectVsFutureContinuousAllLevelsV3Seeder;
use Database\Seeders\V3\FutureForms\FuturePerfectVsFuturePerfectContinuousAllLevelsV3Seeder;
use Database\Seeders\V3\FutureForms\FutureContinuousVsFuturePerfectContinuousAllLevelsV3Seeder;
use Database\Seeders\V3\Polyglot\PolyglotWillVsBeGoingToAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotPresentContinuousForFutureAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotChoosingTheRightFutureFormAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotFuturePerfectVsFutureContinuousAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotFuturePerfectVsFuturePerfectContinuousAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotFutureContinuousVsFuturePerfectContinuousAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotFutureSimpleWillLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotBeGoingToLessonSeeder;
use Database\Seeders\V3\TheoryLinks\WillVsBeGoingToTheoryLinksSeeder;
use Database\Seeders\V3\TheoryLinks\PresentContinuousForFutureTheoryLinksSeeder;
use Database\Seeders\V3\TheoryLinks\ChoosingTheRightFutureFormTheoryLinksSeeder;
use Database\Seeders\V3\TheoryLinks\FuturePerfectVsFutureContinuousTheoryLinksSeeder;
use Database\Seeders\V3\TheoryLinks\FuturePerfectVsFuturePerfectContinuousTheoryLinksSeeder;
use Database\Seeders\V3\TheoryLinks\FutureContinuousVsFuturePerfectContinuousTheoryLinksSeeder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class FutureFormsTheoryPageTestsSeedersTest extends TestCase
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

    public function test_future_forms_pages_have_tests_and_theory_links(): void
    {
        $this->seedFutureFormsStack();

        $this->seed([
            WillVsBeGoingToTheoryLinksSeeder::class,
            PresentContinuousForFutureTheoryLinksSeeder::class,
            ChoosingTheRightFutureFormTheoryLinksSeeder::class,
            FuturePerfectVsFutureContinuousTheoryLinksSeeder::class,
            FuturePerfectVsFuturePerfectContinuousTheoryLinksSeeder::class,
            FutureContinuousVsFuturePerfectContinuousTheoryLinksSeeder::class,
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

        $this->assertAuditReportsFutureFormsRoutesAsOk();
    }

    private function seedFutureFormsStack(): void
    {
        $this->seed([
            FutureFormsCategorySeeder::class,
            FutureFormsWillVsBeGoingToTheorySeeder::class,
            FutureFormsPresentContinuousForFutureTheorySeeder::class,
            FutureFormsChoosingTheRightFutureFormTheorySeeder::class,
            FutureFormsFuturePerfectVsFutureContinuousTheorySeeder::class,
            FutureFormsFuturePerfectVsFuturePerfectContinuousTheorySeeder::class,
            FutureFormsFutureContinuousVsFuturePerfectContinuousTheorySeeder::class,
            WillVsBeGoingToFutureFormsAllLevelsV3Seeder::class,
            PresentContinuousForFutureAllLevelsV3Seeder::class,
            ChoosingTheRightFutureFormAllLevelsV3Seeder::class,
            FuturePerfectVsFutureContinuousAllLevelsV3Seeder::class,
            FuturePerfectVsFuturePerfectContinuousAllLevelsV3Seeder::class,
            FutureContinuousVsFuturePerfectContinuousAllLevelsV3Seeder::class,
            PolyglotWillVsBeGoingToAllLevelsLessonSeeder::class,
            PolyglotPresentContinuousForFutureAllLevelsLessonSeeder::class,
            PolyglotChoosingTheRightFutureFormAllLevelsLessonSeeder::class,
            PolyglotFuturePerfectVsFutureContinuousAllLevelsLessonSeeder::class,
            PolyglotFuturePerfectVsFuturePerfectContinuousAllLevelsLessonSeeder::class,
            PolyglotFutureContinuousVsFuturePerfectContinuousAllLevelsLessonSeeder::class,
            PolyglotFutureSimpleWillLessonSeeder::class,
            PolyglotBeGoingToLessonSeeder::class,
        ]);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function cases(): array
    {
        return [
            'will-vs-be-going-to' => [
                'route' => '/theory/maibutni-formy/will-vs-be-going-to',
                'page_slug' => 'will-vs-be-going-to',
                'direct_slug' => 'polyglot-will-vs-be-going-to-all-levels',
                'v3_seeder' => WillVsBeGoingToFutureFormsAllLevelsV3Seeder::class,
                'polyglot_seeder' => PolyglotWillVsBeGoingToAllLevelsLessonSeeder::class,
                'mixed_filter_seeders' => [WillVsBeGoingToFutureFormsAllLevelsV3Seeder::class, PolyglotWillVsBeGoingToAllLevelsLessonSeeder::class],
                'covered_seeders' => [WillVsBeGoingToFutureFormsAllLevelsV3Seeder::class, PolyglotWillVsBeGoingToAllLevelsLessonSeeder::class, PolyglotFutureSimpleWillLessonSeeder::class, PolyglotBeGoingToLessonSeeder::class],
                'covered_seeder_counts' => [PolyglotFutureSimpleWillLessonSeeder::class => 24, PolyglotBeGoingToLessonSeeder::class => 24],
                'links_seeder' => WillVsBeGoingToTheoryLinksSeeder::class,
            ],
            'present-continuous-for-future' => [
                'route' => '/theory/maibutni-formy/present-continuous-for-future',
                'page_slug' => 'present-continuous-for-future',
                'direct_slug' => 'polyglot-present-continuous-for-future-all-levels',
                'v3_seeder' => PresentContinuousForFutureAllLevelsV3Seeder::class,
                'polyglot_seeder' => PolyglotPresentContinuousForFutureAllLevelsLessonSeeder::class,
                'mixed_filter_seeders' => [PresentContinuousForFutureAllLevelsV3Seeder::class, PolyglotPresentContinuousForFutureAllLevelsLessonSeeder::class],
                'covered_seeders' => [PresentContinuousForFutureAllLevelsV3Seeder::class, PolyglotPresentContinuousForFutureAllLevelsLessonSeeder::class],
                'links_seeder' => PresentContinuousForFutureTheoryLinksSeeder::class,
            ],
            'choosing-the-right-future-form' => [
                'route' => '/theory/maibutni-formy/choosing-the-right-future-form',
                'page_slug' => 'choosing-the-right-future-form',
                'direct_slug' => 'polyglot-choosing-the-right-future-form-all-levels',
                'v3_seeder' => ChoosingTheRightFutureFormAllLevelsV3Seeder::class,
                'polyglot_seeder' => PolyglotChoosingTheRightFutureFormAllLevelsLessonSeeder::class,
                'mixed_filter_seeders' => [ChoosingTheRightFutureFormAllLevelsV3Seeder::class, PolyglotChoosingTheRightFutureFormAllLevelsLessonSeeder::class],
                'covered_seeders' => [ChoosingTheRightFutureFormAllLevelsV3Seeder::class, PolyglotChoosingTheRightFutureFormAllLevelsLessonSeeder::class],
                'links_seeder' => ChoosingTheRightFutureFormTheoryLinksSeeder::class,
            ],
            'future-perfect-vs-future-continuous' => [
                'route' => '/theory/maibutni-formy/future-perfect-vs-future-continuous',
                'page_slug' => 'future-perfect-vs-future-continuous',
                'direct_slug' => 'polyglot-future-perfect-vs-future-continuous-all-levels',
                'v3_seeder' => FuturePerfectVsFutureContinuousAllLevelsV3Seeder::class,
                'polyglot_seeder' => PolyglotFuturePerfectVsFutureContinuousAllLevelsLessonSeeder::class,
                'mixed_filter_seeders' => [FuturePerfectVsFutureContinuousAllLevelsV3Seeder::class, PolyglotFuturePerfectVsFutureContinuousAllLevelsLessonSeeder::class],
                'covered_seeders' => [FuturePerfectVsFutureContinuousAllLevelsV3Seeder::class, PolyglotFuturePerfectVsFutureContinuousAllLevelsLessonSeeder::class],
                'links_seeder' => FuturePerfectVsFutureContinuousTheoryLinksSeeder::class,
            ],
            'future-perfect-vs-future-perfect-continuous' => [
                'route' => '/theory/maibutni-formy/future-perfect-vs-future-perfect-continuous',
                'page_slug' => 'future-perfect-vs-future-perfect-continuous',
                'direct_slug' => 'polyglot-future-perfect-vs-future-perfect-continuous-all-levels',
                'v3_seeder' => FuturePerfectVsFuturePerfectContinuousAllLevelsV3Seeder::class,
                'polyglot_seeder' => PolyglotFuturePerfectVsFuturePerfectContinuousAllLevelsLessonSeeder::class,
                'mixed_filter_seeders' => [FuturePerfectVsFuturePerfectContinuousAllLevelsV3Seeder::class, PolyglotFuturePerfectVsFuturePerfectContinuousAllLevelsLessonSeeder::class],
                'covered_seeders' => [FuturePerfectVsFuturePerfectContinuousAllLevelsV3Seeder::class, PolyglotFuturePerfectVsFuturePerfectContinuousAllLevelsLessonSeeder::class],
                'links_seeder' => FuturePerfectVsFuturePerfectContinuousTheoryLinksSeeder::class,
            ],
            'future-continuous-vs-future-perfect-continuous' => [
                'route' => '/theory/maibutni-formy/future-continuous-vs-future-perfect-continuous',
                'page_slug' => 'future-continuous-vs-future-perfect-continuous',
                'direct_slug' => 'polyglot-future-continuous-vs-future-perfect-continuous-all-levels',
                'v3_seeder' => FutureContinuousVsFuturePerfectContinuousAllLevelsV3Seeder::class,
                'polyglot_seeder' => PolyglotFutureContinuousVsFuturePerfectContinuousAllLevelsLessonSeeder::class,
                'mixed_filter_seeders' => [FutureContinuousVsFuturePerfectContinuousAllLevelsV3Seeder::class, PolyglotFutureContinuousVsFuturePerfectContinuousAllLevelsLessonSeeder::class],
                'covered_seeders' => [FutureContinuousVsFuturePerfectContinuousAllLevelsV3Seeder::class, PolyglotFutureContinuousVsFuturePerfectContinuousAllLevelsLessonSeeder::class],
                'links_seeder' => FutureContinuousVsFuturePerfectContinuousTheoryLinksSeeder::class,
            ],
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

    private function assertAuditReportsFutureFormsRoutesAsOk(): void
    {
        $jsonPath = storage_path('framework/testing/future-forms-theory-links-audit.json');
        $markdownPath = storage_path('framework/testing/future-forms-theory-links-audit.md');
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
