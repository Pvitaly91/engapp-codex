<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Question;
use App\Models\SavedGrammarTest;
use App\Models\TextBlock;
use App\Services\SavedTestResolver;
use App\Services\TheoryPagePromptLinkedTestsService;
use Database\Seeders\Page_V3\Conditionals\AdvancedConditionalsTheorySeeder;
use Database\Seeders\Page_V3\Conditionals\ConditionalAlternativesAndNuanceTheorySeeder;
use Database\Seeders\Page_V3\Conditionals\ConditionalsCategorySeeder;
use Database\Seeders\Page_V3\Conditionals\ConditionalsWithUnlessProvidedAsLongAsTheorySeeder;
use Database\Seeders\Page_V3\PassiveVoice\PassiveReportingStructuresTheorySeeder;
use Database\Seeders\Page_V3\PassiveVoice\PassiveVoiceCategorySeeder;
use Database\Seeders\Page_V3\PassiveVoice\Tenses\PassiveVoiceFutureContinuousTheorySeeder;
use Database\Seeders\Page_V3\PassiveVoice\Tenses\PassiveVoiceTensesCategorySeeder;
use Database\Seeders\V3\Conditionals\AdvancedConditionalsAllLevelsV3Seeder;
use Database\Seeders\V3\Conditionals\ConditionalAlternativesAndNuanceAllLevelsV3Seeder;
use Database\Seeders\V3\Conditionals\ConditionalsWithUnlessProvidedAsLongAsAllLevelsV3Seeder;
use Database\Seeders\V3\PassiveVoice\PassiveReportingStructuresAllLevelsV3Seeder;
use Database\Seeders\V3\PassiveVoice\PassiveVoiceFutureContinuousAllLevelsV3Seeder;
use Database\Seeders\V3\Polyglot\PolyglotAdvancedConditionalsAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotAdvancedConditionalsC1LessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotConditionalAlternativesAndNuanceAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotConditionalAlternativesAndNuanceC2LessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotConditionalsWithUnlessProvidedAsLongAsAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotConditionalsWithUnlessProvidedAsLongAsB2LessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotPassiveReportingStructuresAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotPassiveReportingStructuresC1LessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotPassiveVoiceFutureContinuousAllLevelsLessonSeeder;
use Database\Seeders\V3\TheoryLinks\AdvancedConditionalsTheoryLinksSeeder;
use Database\Seeders\V3\TheoryLinks\ConditionalAlternativesAndNuanceTheoryLinksSeeder;
use Database\Seeders\V3\TheoryLinks\ConditionalsWithUnlessProvidedAsLongAsTheoryLinksSeeder;
use Database\Seeders\V3\TheoryLinks\PassiveReportingStructuresTheoryLinksSeeder;
use Database\Seeders\V3\TheoryLinks\PassiveVoiceFutureContinuousTheoryLinksSeeder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class TheoryPageMissingContentRepairTest extends TestCase
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

    public function test_missing_content_repair_pages_have_tests_and_theory_links(): void
    {
        $this->seedMissingContentRepairStack();

        $this->seed([
            PassiveVoiceFutureContinuousTheoryLinksSeeder::class,
            ConditionalsWithUnlessProvidedAsLongAsTheoryLinksSeeder::class,
            AdvancedConditionalsTheoryLinksSeeder::class,
            PassiveReportingStructuresTheoryLinksSeeder::class,
            ConditionalAlternativesAndNuanceTheoryLinksSeeder::class,
        ]);

        foreach ($this->cases() as $caseName => $case) {
            $page = Page::query()->where('slug', $case['page_slug'])->firstOrFail();
            $directTest = SavedGrammarTest::query()->where('slug', $case['direct_slug'])->first();

            $this->assertSame($case['category_slug'], $page->category?->slug, $caseName . ': category slug.');
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

        $this->assertAuditReportsMissingContentRepairRoutesAsOk();
    }

    private function seedMissingContentRepairStack(): void
    {
        $this->seed([
            PassiveVoiceCategorySeeder::class,
            PassiveVoiceTensesCategorySeeder::class,
            ConditionalsCategorySeeder::class,
            PassiveVoiceFutureContinuousTheorySeeder::class,
            ConditionalsWithUnlessProvidedAsLongAsTheorySeeder::class,
            AdvancedConditionalsTheorySeeder::class,
            PassiveReportingStructuresTheorySeeder::class,
            ConditionalAlternativesAndNuanceTheorySeeder::class,
            PassiveVoiceFutureContinuousAllLevelsV3Seeder::class,
            ConditionalsWithUnlessProvidedAsLongAsAllLevelsV3Seeder::class,
            AdvancedConditionalsAllLevelsV3Seeder::class,
            PassiveReportingStructuresAllLevelsV3Seeder::class,
            ConditionalAlternativesAndNuanceAllLevelsV3Seeder::class,
            PolyglotPassiveVoiceFutureContinuousAllLevelsLessonSeeder::class,
            PolyglotConditionalsWithUnlessProvidedAsLongAsAllLevelsLessonSeeder::class,
            PolyglotAdvancedConditionalsAllLevelsLessonSeeder::class,
            PolyglotPassiveReportingStructuresAllLevelsLessonSeeder::class,
            PolyglotConditionalAlternativesAndNuanceAllLevelsLessonSeeder::class,
            PolyglotConditionalsWithUnlessProvidedAsLongAsB2LessonSeeder::class,
            PolyglotAdvancedConditionalsC1LessonSeeder::class,
            PolyglotPassiveReportingStructuresC1LessonSeeder::class,
            PolyglotConditionalAlternativesAndNuanceC2LessonSeeder::class,
        ]);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function cases(): array
    {
        return [
            'passive-voice-future-continuous' => [
                'route' => '/theory/passive-voice/passive-voice-tenses/theory-passive-voice-future-continuous',
                'page_slug' => 'theory-passive-voice-future-continuous',
                'category_slug' => 'passive-voice-tenses',
                'direct_slug' => 'polyglot-passive-voice-future-continuous-all-levels',
                'v3_seeder' => PassiveVoiceFutureContinuousAllLevelsV3Seeder::class,
                'polyglot_seeder' => PolyglotPassiveVoiceFutureContinuousAllLevelsLessonSeeder::class,
                'mixed_filter_seeders' => [PassiveVoiceFutureContinuousAllLevelsV3Seeder::class, PolyglotPassiveVoiceFutureContinuousAllLevelsLessonSeeder::class],
                'covered_seeders' => [PassiveVoiceFutureContinuousAllLevelsV3Seeder::class, PolyglotPassiveVoiceFutureContinuousAllLevelsLessonSeeder::class],
                'links_seeder' => PassiveVoiceFutureContinuousTheoryLinksSeeder::class,
                'covered_seeder_counts' => [],
            ],
            'conditionals-with-unless-provided-as-long-as' => [
                'route' => '/theory/conditionals/conditionals-with-unless-provided-as-long-as',
                'page_slug' => 'conditionals-with-unless-provided-as-long-as',
                'category_slug' => 'conditionals',
                'direct_slug' => 'polyglot-conditionals-with-unless-provided-as-long-as-all-levels',
                'v3_seeder' => ConditionalsWithUnlessProvidedAsLongAsAllLevelsV3Seeder::class,
                'polyglot_seeder' => PolyglotConditionalsWithUnlessProvidedAsLongAsAllLevelsLessonSeeder::class,
                'mixed_filter_seeders' => [ConditionalsWithUnlessProvidedAsLongAsAllLevelsV3Seeder::class, PolyglotConditionalsWithUnlessProvidedAsLongAsAllLevelsLessonSeeder::class],
                'covered_seeders' => [ConditionalsWithUnlessProvidedAsLongAsAllLevelsV3Seeder::class, PolyglotConditionalsWithUnlessProvidedAsLongAsAllLevelsLessonSeeder::class, PolyglotConditionalsWithUnlessProvidedAsLongAsB2LessonSeeder::class],
                'links_seeder' => ConditionalsWithUnlessProvidedAsLongAsTheoryLinksSeeder::class,
                'covered_seeder_counts' => [PolyglotConditionalsWithUnlessProvidedAsLongAsB2LessonSeeder::class => 48],
            ],
            'advanced-conditionals' => [
                'route' => '/theory/conditionals/advanced-conditionals',
                'page_slug' => 'advanced-conditionals',
                'category_slug' => 'conditionals',
                'direct_slug' => 'polyglot-advanced-conditionals-all-levels',
                'v3_seeder' => AdvancedConditionalsAllLevelsV3Seeder::class,
                'polyglot_seeder' => PolyglotAdvancedConditionalsAllLevelsLessonSeeder::class,
                'mixed_filter_seeders' => [AdvancedConditionalsAllLevelsV3Seeder::class, PolyglotAdvancedConditionalsAllLevelsLessonSeeder::class],
                'covered_seeders' => [AdvancedConditionalsAllLevelsV3Seeder::class, PolyglotAdvancedConditionalsAllLevelsLessonSeeder::class, PolyglotAdvancedConditionalsC1LessonSeeder::class],
                'links_seeder' => AdvancedConditionalsTheoryLinksSeeder::class,
                'covered_seeder_counts' => [PolyglotAdvancedConditionalsC1LessonSeeder::class => 48],
            ],
            'passive-reporting-structures' => [
                'route' => '/theory/passive-voice/passive-reporting-structures',
                'page_slug' => 'passive-reporting-structures',
                'category_slug' => 'passive-voice',
                'direct_slug' => 'polyglot-passive-reporting-structures-all-levels',
                'v3_seeder' => PassiveReportingStructuresAllLevelsV3Seeder::class,
                'polyglot_seeder' => PolyglotPassiveReportingStructuresAllLevelsLessonSeeder::class,
                'mixed_filter_seeders' => [PassiveReportingStructuresAllLevelsV3Seeder::class, PolyglotPassiveReportingStructuresAllLevelsLessonSeeder::class],
                'covered_seeders' => [PassiveReportingStructuresAllLevelsV3Seeder::class, PolyglotPassiveReportingStructuresAllLevelsLessonSeeder::class, PolyglotPassiveReportingStructuresC1LessonSeeder::class],
                'links_seeder' => PassiveReportingStructuresTheoryLinksSeeder::class,
                'covered_seeder_counts' => [PolyglotPassiveReportingStructuresC1LessonSeeder::class => 48],
            ],
            'conditional-alternatives-and-nuance' => [
                'route' => '/theory/conditionals/conditional-alternatives-and-nuance',
                'page_slug' => 'conditional-alternatives-and-nuance',
                'category_slug' => 'conditionals',
                'direct_slug' => 'polyglot-conditional-alternatives-and-nuance-all-levels',
                'v3_seeder' => ConditionalAlternativesAndNuanceAllLevelsV3Seeder::class,
                'polyglot_seeder' => PolyglotConditionalAlternativesAndNuanceAllLevelsLessonSeeder::class,
                'mixed_filter_seeders' => [ConditionalAlternativesAndNuanceAllLevelsV3Seeder::class, PolyglotConditionalAlternativesAndNuanceAllLevelsLessonSeeder::class],
                'covered_seeders' => [ConditionalAlternativesAndNuanceAllLevelsV3Seeder::class, PolyglotConditionalAlternativesAndNuanceAllLevelsLessonSeeder::class, PolyglotConditionalAlternativesAndNuanceC2LessonSeeder::class],
                'links_seeder' => ConditionalAlternativesAndNuanceTheoryLinksSeeder::class,
                'covered_seeder_counts' => [PolyglotConditionalAlternativesAndNuanceC2LessonSeeder::class => 48],
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

    private function assertAuditReportsMissingContentRepairRoutesAsOk(): void
    {
        $jsonPath = storage_path('framework/testing/theory-page-missing-content-repair-audit.json');
        $markdownPath = storage_path('framework/testing/theory-page-missing-content-repair-audit.md');
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
