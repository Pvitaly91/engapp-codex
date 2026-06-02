<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Question;
use App\Models\SavedGrammarTest;
use App\Models\TextBlock;
use App\Services\SavedTestResolver;
use App\Services\TheoryPagePromptLinkedTestsService;
use Database\Seeders\Page_V3\ModalVerbs\ModalVerbsCategorySeeder;
use Database\Seeders\Page_V3\ModalVerbs\ModalVerbsDeductionTheorySeeder;
use Database\Seeders\Page_V3\ModalVerbs\ModalVerbsMayMightTheorySeeder;
use Database\Seeders\Page_V3\ModalVerbs\ModalVerbsPastModalsTheorySeeder;
use Database\Seeders\Page_V3\ModalVerbs\ModalVerbsRequestsOffersSuggestionsTheorySeeder;
use Database\Seeders\V3\ModalVerbs\ModalVerbsMayMightAllLevelsV3Seeder;
use Database\Seeders\V3\ModalVerbs\ModalVerbsPastModalsAllLevelsV3Seeder;
use Database\Seeders\V3\ModalVerbs\ModalVerbsRequestsOffersSuggestionsAllLevelsV3Seeder;
use Database\Seeders\V3\ModalVerbs\ModalsOfDeductionAllLevelsV3Seeder;
use Database\Seeders\V3\Polyglot\PolyglotModalPerfectDeductionAndRegretB2LessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotModalVerbsMayMightAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotModalVerbsOfDeductionLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotModalVerbsPastModalsAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotModalVerbsRequestsOffersSuggestionsAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotModalsOfDeductionAllLevelsLessonSeeder;
use Database\Seeders\V3\TheoryLinks\ModalVerbsMayMightTheoryLinksSeeder;
use Database\Seeders\V3\TheoryLinks\ModalVerbsPastModalsTheoryLinksSeeder;
use Database\Seeders\V3\TheoryLinks\ModalVerbsRequestsOffersSuggestionsTheoryLinksSeeder;
use Database\Seeders\V3\TheoryLinks\ModalsOfDeductionTheoryLinksSeeder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class ModalVerbsPart2TheoryPageTestsSeedersTest extends TestCase
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

    public function test_modal_verbs_part_2_pages_have_tests_and_theory_links(): void
    {
        $this->seedModalVerbsPart2Stack();

        $this->seed([
            ModalVerbsMayMightTheoryLinksSeeder::class,
            ModalsOfDeductionTheoryLinksSeeder::class,
            ModalVerbsPastModalsTheoryLinksSeeder::class,
            ModalVerbsRequestsOffersSuggestionsTheoryLinksSeeder::class,
        ]);

        foreach ($this->cases() as $caseName => $case) {
            $page = Page::query()->where('slug', $case['page_slug'])->firstOrFail();
            $directTest = SavedGrammarTest::query()->where('slug', $case['direct_slug'])->first();

            $this->assertSame('modal-verbs', $page->category?->slug, $caseName . ': category slug.');
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

        $this->assertAuditReportsModalVerbsPart2RoutesAsOk();
    }

    private function seedModalVerbsPart2Stack(): void
    {
        $this->seed([
            ModalVerbsCategorySeeder::class,
            ModalVerbsMayMightTheorySeeder::class,
            ModalVerbsDeductionTheorySeeder::class,
            ModalVerbsPastModalsTheorySeeder::class,
            ModalVerbsRequestsOffersSuggestionsTheorySeeder::class,
            ModalVerbsMayMightAllLevelsV3Seeder::class,
            ModalsOfDeductionAllLevelsV3Seeder::class,
            ModalVerbsPastModalsAllLevelsV3Seeder::class,
            ModalVerbsRequestsOffersSuggestionsAllLevelsV3Seeder::class,
            PolyglotModalVerbsMayMightAllLevelsLessonSeeder::class,
            PolyglotModalsOfDeductionAllLevelsLessonSeeder::class,
            PolyglotModalVerbsPastModalsAllLevelsLessonSeeder::class,
            PolyglotModalVerbsRequestsOffersSuggestionsAllLevelsLessonSeeder::class,
            PolyglotModalVerbsOfDeductionLessonSeeder::class,
            PolyglotModalPerfectDeductionAndRegretB2LessonSeeder::class,
        ]);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function cases(): array
    {
        return [
            'may-might' => [
                'route' => '/theory/modal-verbs/may-might',
                'page_slug' => 'may-might',
                'direct_slug' => 'polyglot-modal-verbs-may-might-all-levels',
                'v3_seeder' => ModalVerbsMayMightAllLevelsV3Seeder::class,
                'polyglot_seeder' => PolyglotModalVerbsMayMightAllLevelsLessonSeeder::class,
                'mixed_filter_seeders' => [ModalVerbsMayMightAllLevelsV3Seeder::class, PolyglotModalVerbsMayMightAllLevelsLessonSeeder::class],
                'covered_seeders' => [ModalVerbsMayMightAllLevelsV3Seeder::class, PolyglotModalVerbsMayMightAllLevelsLessonSeeder::class],
                'links_seeder' => ModalVerbsMayMightTheoryLinksSeeder::class,
            ],
            'modals-of-deduction' => [
                'route' => '/theory/modal-verbs/modals-of-deduction',
                'page_slug' => 'modals-of-deduction',
                'direct_slug' => 'polyglot-modals-of-deduction-all-levels',
                'v3_seeder' => ModalsOfDeductionAllLevelsV3Seeder::class,
                'polyglot_seeder' => PolyglotModalsOfDeductionAllLevelsLessonSeeder::class,
                'mixed_filter_seeders' => [ModalsOfDeductionAllLevelsV3Seeder::class, PolyglotModalsOfDeductionAllLevelsLessonSeeder::class],
                'covered_seeders' => [ModalsOfDeductionAllLevelsV3Seeder::class, PolyglotModalsOfDeductionAllLevelsLessonSeeder::class, PolyglotModalVerbsOfDeductionLessonSeeder::class],
                'links_seeder' => ModalsOfDeductionTheoryLinksSeeder::class,
                'covered_seeder_counts' => [PolyglotModalVerbsOfDeductionLessonSeeder::class => 48],
            ],
            'past-modals' => [
                'route' => '/theory/modal-verbs/past-modals',
                'page_slug' => 'past-modals',
                'direct_slug' => 'polyglot-modal-verbs-past-modals-all-levels',
                'v3_seeder' => ModalVerbsPastModalsAllLevelsV3Seeder::class,
                'polyglot_seeder' => PolyglotModalVerbsPastModalsAllLevelsLessonSeeder::class,
                'mixed_filter_seeders' => [ModalVerbsPastModalsAllLevelsV3Seeder::class, PolyglotModalVerbsPastModalsAllLevelsLessonSeeder::class],
                'covered_seeders' => [ModalVerbsPastModalsAllLevelsV3Seeder::class, PolyglotModalVerbsPastModalsAllLevelsLessonSeeder::class, PolyglotModalPerfectDeductionAndRegretB2LessonSeeder::class],
                'links_seeder' => ModalVerbsPastModalsTheoryLinksSeeder::class,
                'covered_seeder_counts' => [PolyglotModalPerfectDeductionAndRegretB2LessonSeeder::class => 48],
            ],
            'requests-offers-suggestions' => [
                'route' => '/theory/modal-verbs/requests-offers-suggestions',
                'page_slug' => 'requests-offers-suggestions',
                'direct_slug' => 'polyglot-modal-verbs-requests-offers-suggestions-all-levels',
                'v3_seeder' => ModalVerbsRequestsOffersSuggestionsAllLevelsV3Seeder::class,
                'polyglot_seeder' => PolyglotModalVerbsRequestsOffersSuggestionsAllLevelsLessonSeeder::class,
                'mixed_filter_seeders' => [ModalVerbsRequestsOffersSuggestionsAllLevelsV3Seeder::class, PolyglotModalVerbsRequestsOffersSuggestionsAllLevelsLessonSeeder::class],
                'covered_seeders' => [ModalVerbsRequestsOffersSuggestionsAllLevelsV3Seeder::class, PolyglotModalVerbsRequestsOffersSuggestionsAllLevelsLessonSeeder::class],
                'links_seeder' => ModalVerbsRequestsOffersSuggestionsTheoryLinksSeeder::class,
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
        $this->assertSame($questions->count(), $questions->whereNotNull('theory_text_block_uuid')->count(), $caseName . ': every covered question should set theory_text_block_uuid.');

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
        $this->assertTrue($questions->every(fn (Question $question): bool => (string) $question->seeder === $polyglotSeeder), $caseName . ': direct Sentence Builder should use only the page-local all-levels Polyglot seeder.');
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

    private function assertAuditReportsModalVerbsPart2RoutesAsOk(): void
    {
        $jsonPath = storage_path('framework/testing/modal-verbs-part-2-theory-links-audit.json');
        $markdownPath = storage_path('framework/testing/modal-verbs-part-2-theory-links-audit.md');
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
