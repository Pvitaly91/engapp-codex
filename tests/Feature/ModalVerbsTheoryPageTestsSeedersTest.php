<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Question;
use App\Models\SavedGrammarTest;
use App\Models\TextBlock;
use App\Services\SavedTestResolver;
use App\Services\TheoryPagePromptLinkedTestsService;
use Database\Seeders\Page_V3\ModalVerbs\ModalVerbsCategorySeeder;
use Database\Seeders\Page_V3\ModalVerbs\ModalVerbsCanCouldTheorySeeder;
use Database\Seeders\Page_V3\ModalVerbs\ModalVerbsMustHaveToTheorySeeder;
use Database\Seeders\Page_V3\ModalVerbs\ModalVerbsShouldOughtToTheorySeeder;
use Database\Seeders\Page_V3\ModalVerbs\ModalVerbsNeedNeedntDontHaveToTheorySeeder;
use Database\Seeders\Page_V3\ModalVerbs\ModalPerfectAndDeductionTheorySeeder;
use Database\Seeders\Page_V3\ModalVerbs\SubtleModalMeaningsTheorySeeder;
use Database\Seeders\V3\ModalVerbs\CanCouldAllLevelsV3Seeder;
use Database\Seeders\V3\ModalVerbs\MustHaveToAllLevelsV3Seeder;
use Database\Seeders\V3\ModalVerbs\ShouldOughtToAllLevelsV3Seeder;
use Database\Seeders\V3\ModalVerbs\NeedNeedntDontHaveToAllLevelsV3Seeder;
use Database\Seeders\V3\ModalVerbs\ModalPerfectAndDeductionAllLevelsV3Seeder;
use Database\Seeders\V3\ModalVerbs\SubtleModalMeaningsAllLevelsV3Seeder;
use Database\Seeders\V3\Polyglot\PolyglotCanCouldAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotMustHaveToAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotShouldOughtToAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotNeedNeedntDontHaveToAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotModalPerfectAndDeductionAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotSubtleModalMeaningsAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotCanCannotLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotMustHaveToLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotShouldOughtToLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotModalPerfectAndDeductionC1LessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotSubtleModalMeaningsC2LessonSeeder;
use Database\Seeders\V3\TheoryLinks\CanCouldTheoryLinksSeeder;
use Database\Seeders\V3\TheoryLinks\MustHaveToTheoryLinksSeeder;
use Database\Seeders\V3\TheoryLinks\ShouldOughtToTheoryLinksSeeder;
use Database\Seeders\V3\TheoryLinks\NeedNeedntDontHaveToTheoryLinksSeeder;
use Database\Seeders\V3\TheoryLinks\ModalPerfectAndDeductionTheoryLinksSeeder;
use Database\Seeders\V3\TheoryLinks\SubtleModalMeaningsTheoryLinksSeeder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class ModalVerbsTheoryPageTestsSeedersTest extends TestCase
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

    public function test_modal_verbs_pages_have_tests_and_theory_links(): void
    {
        $this->seedModalVerbsStack();

        $this->seed([
            CanCouldTheoryLinksSeeder::class,
            MustHaveToTheoryLinksSeeder::class,
            ShouldOughtToTheoryLinksSeeder::class,
            NeedNeedntDontHaveToTheoryLinksSeeder::class,
            ModalPerfectAndDeductionTheoryLinksSeeder::class,
            SubtleModalMeaningsTheoryLinksSeeder::class,
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

        $this->assertAuditReportsModalVerbRoutesAsOk();
    }

    private function seedModalVerbsStack(): void
    {
        $this->seed([
            ModalVerbsCategorySeeder::class,
            ModalVerbsCanCouldTheorySeeder::class,
            ModalVerbsMustHaveToTheorySeeder::class,
            ModalVerbsShouldOughtToTheorySeeder::class,
            ModalVerbsNeedNeedntDontHaveToTheorySeeder::class,
            ModalPerfectAndDeductionTheorySeeder::class,
            SubtleModalMeaningsTheorySeeder::class,
            CanCouldAllLevelsV3Seeder::class,
            MustHaveToAllLevelsV3Seeder::class,
            ShouldOughtToAllLevelsV3Seeder::class,
            NeedNeedntDontHaveToAllLevelsV3Seeder::class,
            ModalPerfectAndDeductionAllLevelsV3Seeder::class,
            SubtleModalMeaningsAllLevelsV3Seeder::class,
            PolyglotCanCouldAllLevelsLessonSeeder::class,
            PolyglotMustHaveToAllLevelsLessonSeeder::class,
            PolyglotShouldOughtToAllLevelsLessonSeeder::class,
            PolyglotNeedNeedntDontHaveToAllLevelsLessonSeeder::class,
            PolyglotModalPerfectAndDeductionAllLevelsLessonSeeder::class,
            PolyglotSubtleModalMeaningsAllLevelsLessonSeeder::class,
            PolyglotCanCannotLessonSeeder::class,
            PolyglotMustHaveToLessonSeeder::class,
            PolyglotShouldOughtToLessonSeeder::class,
            PolyglotModalPerfectAndDeductionC1LessonSeeder::class,
            PolyglotSubtleModalMeaningsC2LessonSeeder::class,
        ]);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function cases(): array
    {
        return [
            'can-could' => [
                'route' => '/theory/modal-verbs/can-could',
                'page_slug' => 'can-could',
                'direct_slug' => 'polyglot-can-could-all-levels',
                'v3_seeder' => CanCouldAllLevelsV3Seeder::class,
                'polyglot_seeder' => PolyglotCanCouldAllLevelsLessonSeeder::class,
                'mixed_filter_seeders' => [
                    CanCouldAllLevelsV3Seeder::class,
                    PolyglotCanCouldAllLevelsLessonSeeder::class,
                ],
                'covered_seeders' => [
                    CanCouldAllLevelsV3Seeder::class,
                    PolyglotCanCouldAllLevelsLessonSeeder::class,
                    PolyglotCanCannotLessonSeeder::class,
                ],
                'covered_seeder_counts' => [
                    PolyglotCanCannotLessonSeeder::class => 24,
                ],
                'links_seeder' => CanCouldTheoryLinksSeeder::class,
            ],
            'must-have-to' => [
                'route' => '/theory/modal-verbs/must-have-to',
                'page_slug' => 'must-have-to',
                'direct_slug' => 'polyglot-must-have-to-all-levels',
                'v3_seeder' => MustHaveToAllLevelsV3Seeder::class,
                'polyglot_seeder' => PolyglotMustHaveToAllLevelsLessonSeeder::class,
                'mixed_filter_seeders' => [
                    MustHaveToAllLevelsV3Seeder::class,
                    PolyglotMustHaveToAllLevelsLessonSeeder::class,
                ],
                'covered_seeders' => [
                    MustHaveToAllLevelsV3Seeder::class,
                    PolyglotMustHaveToAllLevelsLessonSeeder::class,
                    PolyglotMustHaveToLessonSeeder::class,
                ],
                'covered_seeder_counts' => [
                    PolyglotMustHaveToLessonSeeder::class => 24,
                ],
                'links_seeder' => MustHaveToTheoryLinksSeeder::class,
            ],
            'should-ought-to' => [
                'route' => '/theory/modal-verbs/should-ought-to',
                'page_slug' => 'should-ought-to',
                'direct_slug' => 'polyglot-should-ought-to-all-levels',
                'v3_seeder' => ShouldOughtToAllLevelsV3Seeder::class,
                'polyglot_seeder' => PolyglotShouldOughtToAllLevelsLessonSeeder::class,
                'mixed_filter_seeders' => [
                    ShouldOughtToAllLevelsV3Seeder::class,
                    PolyglotShouldOughtToAllLevelsLessonSeeder::class,
                ],
                'covered_seeders' => [
                    ShouldOughtToAllLevelsV3Seeder::class,
                    PolyglotShouldOughtToAllLevelsLessonSeeder::class,
                    PolyglotShouldOughtToLessonSeeder::class,
                ],
                'covered_seeder_counts' => [
                    PolyglotShouldOughtToLessonSeeder::class => 24,
                ],
                'links_seeder' => ShouldOughtToTheoryLinksSeeder::class,
            ],
            'need-neednt-dont-have-to' => [
                'route' => '/theory/modal-verbs/need-neednt-dont-have-to',
                'page_slug' => 'need-neednt-dont-have-to',
                'direct_slug' => 'polyglot-need-neednt-dont-have-to-all-levels',
                'v3_seeder' => NeedNeedntDontHaveToAllLevelsV3Seeder::class,
                'polyglot_seeder' => PolyglotNeedNeedntDontHaveToAllLevelsLessonSeeder::class,
                'mixed_filter_seeders' => [
                    NeedNeedntDontHaveToAllLevelsV3Seeder::class,
                    PolyglotNeedNeedntDontHaveToAllLevelsLessonSeeder::class,
                ],
                'covered_seeders' => [
                    NeedNeedntDontHaveToAllLevelsV3Seeder::class,
                    PolyglotNeedNeedntDontHaveToAllLevelsLessonSeeder::class,
                ],
                'links_seeder' => NeedNeedntDontHaveToTheoryLinksSeeder::class,
            ],
            'modal-perfect-and-deduction' => [
                'route' => '/theory/modal-verbs/modal-perfect-and-deduction',
                'page_slug' => 'modal-perfect-and-deduction',
                'direct_slug' => 'polyglot-modal-perfect-and-deduction-all-levels',
                'v3_seeder' => ModalPerfectAndDeductionAllLevelsV3Seeder::class,
                'polyglot_seeder' => PolyglotModalPerfectAndDeductionAllLevelsLessonSeeder::class,
                'mixed_filter_seeders' => [
                    ModalPerfectAndDeductionAllLevelsV3Seeder::class,
                    PolyglotModalPerfectAndDeductionAllLevelsLessonSeeder::class,
                ],
                'covered_seeders' => [
                    ModalPerfectAndDeductionAllLevelsV3Seeder::class,
                    PolyglotModalPerfectAndDeductionAllLevelsLessonSeeder::class,
                    PolyglotModalPerfectAndDeductionC1LessonSeeder::class,
                ],
                'covered_seeder_counts' => [
                    PolyglotModalPerfectAndDeductionC1LessonSeeder::class => 48,
                ],
                'links_seeder' => ModalPerfectAndDeductionTheoryLinksSeeder::class,
            ],
            'subtle-modal-meanings' => [
                'route' => '/theory/modal-verbs/subtle-modal-meanings',
                'page_slug' => 'subtle-modal-meanings',
                'direct_slug' => 'polyglot-subtle-modal-meanings-all-levels',
                'v3_seeder' => SubtleModalMeaningsAllLevelsV3Seeder::class,
                'polyglot_seeder' => PolyglotSubtleModalMeaningsAllLevelsLessonSeeder::class,
                'mixed_filter_seeders' => [
                    SubtleModalMeaningsAllLevelsV3Seeder::class,
                    PolyglotSubtleModalMeaningsAllLevelsLessonSeeder::class,
                ],
                'covered_seeders' => [
                    SubtleModalMeaningsAllLevelsV3Seeder::class,
                    PolyglotSubtleModalMeaningsAllLevelsLessonSeeder::class,
                    PolyglotSubtleModalMeaningsC2LessonSeeder::class,
                ],
                'covered_seeder_counts' => [
                    PolyglotSubtleModalMeaningsC2LessonSeeder::class => 48,
                ],
                'links_seeder' => SubtleModalMeaningsTheoryLinksSeeder::class,
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
     * @param  array<int, class-string>  $seederClasses
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
     * @param  array<int, class-string>  $coveredSeeders
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

    private function assertAuditReportsModalVerbRoutesAsOk(): void
    {
        $jsonPath = storage_path('framework/testing/modal-verbs-theory-links-audit.json');
        $markdownPath = storage_path('framework/testing/modal-verbs-theory-links-audit.md');
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
