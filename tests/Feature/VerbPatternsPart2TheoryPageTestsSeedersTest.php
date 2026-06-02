<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Question;
use App\Models\SavedGrammarTest;
use App\Models\TextBlock;
use App\Services\SavedTestResolver;
use App\Services\TheoryPagePromptLinkedTestsService;
use Database\Seeders\Page_V3\VerbPatterns\VerbPatternsBareInfinitiveTheorySeeder;
use Database\Seeders\Page_V3\VerbPatterns\VerbPatternsBeUsedToGetUsedToUsedToTheorySeeder;
use Database\Seeders\Page_V3\VerbPatterns\VerbPatternsCategorySeeder;
use Database\Seeders\Page_V3\VerbPatterns\VerbPatternsGerundVsInfinitiveTheorySeeder;
use Database\Seeders\Page_V3\VerbPatterns\VerbPatternsStopRememberForgetTryRegretTheorySeeder;
use Database\Seeders\V3\Polyglot\PolyglotBareInfinitiveAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotBeUsedToGetUsedToUsedToAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotGerundVsInfinitiveAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotGerundVsInfinitiveLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotStopRememberForgetTryRegretAllLevelsLessonSeeder;
use Database\Seeders\V3\TheoryLinks\BareInfinitiveTheoryLinksSeeder;
use Database\Seeders\V3\TheoryLinks\BeUsedToGetUsedToUsedToTheoryLinksSeeder;
use Database\Seeders\V3\TheoryLinks\GerundVsInfinitiveTheoryLinksSeeder;
use Database\Seeders\V3\TheoryLinks\StopRememberForgetTryRegretTheoryLinksSeeder;
use Database\Seeders\V3\VerbPatterns\BareInfinitiveAllLevelsV3Seeder;
use Database\Seeders\V3\VerbPatterns\BeUsedToGetUsedToUsedToAllLevelsV3Seeder;
use Database\Seeders\V3\VerbPatterns\GerundVsInfinitiveAllLevelsV3Seeder;
use Database\Seeders\V3\VerbPatterns\StopRememberForgetTryRegretAllLevelsV3Seeder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class VerbPatternsPart2TheoryPageTestsSeedersTest extends TestCase
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

    public function test_verb_patterns_part_two_pages_have_tests_and_theory_links(): void
    {
        $this->seedVerbPatternsPartTwoStack();

        $this->seed([
            GerundVsInfinitiveTheoryLinksSeeder::class,
            BareInfinitiveTheoryLinksSeeder::class,
            StopRememberForgetTryRegretTheoryLinksSeeder::class,
            BeUsedToGetUsedToUsedToTheoryLinksSeeder::class,
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

            $this->assertEveryCoveredQuestionHasTheoryLinks($case['covered_seeders'], $caseName, $case['covered_question_counts'] ?? []);
            $this->assertDirectSentenceBuilderQuestionsExposeTheoryBlocks($case['direct_slug'], $case['polyglot_seeder'], $caseName);
            $this->assertMixedResolvedQuestionsExposeTheoryBlocks($mixedTest, $case['mixed_filter_seeders'], $caseName);

            $questionUuids = Question::query()->whereIn('seeder', $case['covered_seeders'])->pluck('uuid');
            $firstRunPivotRows = DB::table('question_theory_text_blocks')->whereIn('question_uuid', $questionUuids)->count();
            $this->seed($case['links_seeder']);
            $this->assertSame($firstRunPivotRows, DB::table('question_theory_text_blocks')->whereIn('question_uuid', $questionUuids)->count(), $caseName . ': idempotency check failed.');
        }

        $this->assertAuditReportsVerbPatternsPartTwoRoutesAsOk();
    }

    private function seedVerbPatternsPartTwoStack(): void
    {
        $this->seed([
            VerbPatternsCategorySeeder::class,
            VerbPatternsGerundVsInfinitiveTheorySeeder::class,
            VerbPatternsBareInfinitiveTheorySeeder::class,
            VerbPatternsStopRememberForgetTryRegretTheorySeeder::class,
            VerbPatternsBeUsedToGetUsedToUsedToTheorySeeder::class,
            GerundVsInfinitiveAllLevelsV3Seeder::class,
            BareInfinitiveAllLevelsV3Seeder::class,
            StopRememberForgetTryRegretAllLevelsV3Seeder::class,
            BeUsedToGetUsedToUsedToAllLevelsV3Seeder::class,
            PolyglotGerundVsInfinitiveAllLevelsLessonSeeder::class,
            PolyglotGerundVsInfinitiveLessonSeeder::class,
            PolyglotBareInfinitiveAllLevelsLessonSeeder::class,
            PolyglotStopRememberForgetTryRegretAllLevelsLessonSeeder::class,
            PolyglotBeUsedToGetUsedToUsedToAllLevelsLessonSeeder::class,
        ]);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function cases(): array
    {
        return [
            'gerund-vs-infinitive' => [
                'route' => '/theory/verb-patterns/gerund-vs-infinitive',
                'page_slug' => 'gerund-vs-infinitive',
                'direct_slug' => 'polyglot-gerund-vs-infinitive-all-levels',
                'v3_seeder' => GerundVsInfinitiveAllLevelsV3Seeder::class,
                'polyglot_seeder' => PolyglotGerundVsInfinitiveAllLevelsLessonSeeder::class,
                'mixed_filter_seeders' => [GerundVsInfinitiveAllLevelsV3Seeder::class, PolyglotGerundVsInfinitiveAllLevelsLessonSeeder::class],
                'covered_seeders' => [GerundVsInfinitiveAllLevelsV3Seeder::class, PolyglotGerundVsInfinitiveAllLevelsLessonSeeder::class, PolyglotGerundVsInfinitiveLessonSeeder::class],
                'covered_question_counts' => [PolyglotGerundVsInfinitiveLessonSeeder::class => 24],
                'links_seeder' => GerundVsInfinitiveTheoryLinksSeeder::class,
            ],
            'bare-infinitive' => [
                'route' => '/theory/verb-patterns/bare-infinitive',
                'page_slug' => 'bare-infinitive',
                'direct_slug' => 'polyglot-bare-infinitive-all-levels',
                'v3_seeder' => BareInfinitiveAllLevelsV3Seeder::class,
                'polyglot_seeder' => PolyglotBareInfinitiveAllLevelsLessonSeeder::class,
                'mixed_filter_seeders' => [BareInfinitiveAllLevelsV3Seeder::class, PolyglotBareInfinitiveAllLevelsLessonSeeder::class],
                'covered_seeders' => [BareInfinitiveAllLevelsV3Seeder::class, PolyglotBareInfinitiveAllLevelsLessonSeeder::class],
                'links_seeder' => BareInfinitiveTheoryLinksSeeder::class,
            ],
            'stop-remember-forget-try-regret' => [
                'route' => '/theory/verb-patterns/stop-remember-forget-try-regret',
                'page_slug' => 'stop-remember-forget-try-regret',
                'direct_slug' => 'polyglot-stop-remember-forget-try-regret-all-levels',
                'v3_seeder' => StopRememberForgetTryRegretAllLevelsV3Seeder::class,
                'polyglot_seeder' => PolyglotStopRememberForgetTryRegretAllLevelsLessonSeeder::class,
                'mixed_filter_seeders' => [StopRememberForgetTryRegretAllLevelsV3Seeder::class, PolyglotStopRememberForgetTryRegretAllLevelsLessonSeeder::class],
                'covered_seeders' => [StopRememberForgetTryRegretAllLevelsV3Seeder::class, PolyglotStopRememberForgetTryRegretAllLevelsLessonSeeder::class],
                'links_seeder' => StopRememberForgetTryRegretTheoryLinksSeeder::class,
            ],
            'be-used-to-get-used-to-used-to' => [
                'route' => '/theory/verb-patterns/be-used-to-get-used-to-used-to',
                'page_slug' => 'be-used-to-get-used-to-used-to',
                'direct_slug' => 'polyglot-be-used-to-get-used-to-used-to-all-levels',
                'v3_seeder' => BeUsedToGetUsedToUsedToAllLevelsV3Seeder::class,
                'polyglot_seeder' => PolyglotBeUsedToGetUsedToUsedToAllLevelsLessonSeeder::class,
                'mixed_filter_seeders' => [BeUsedToGetUsedToUsedToAllLevelsV3Seeder::class, PolyglotBeUsedToGetUsedToUsedToAllLevelsLessonSeeder::class],
                'covered_seeders' => [BeUsedToGetUsedToUsedToAllLevelsV3Seeder::class, PolyglotBeUsedToGetUsedToUsedToAllLevelsLessonSeeder::class],
                'links_seeder' => BeUsedToGetUsedToUsedToTheoryLinksSeeder::class,
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
     * @param array<class-string, int> $expectedCounts
     */
    private function assertEveryCoveredQuestionHasTheoryLinks(array $seederClasses, string $caseName, array $expectedCounts = []): void
    {
        $questions = Question::query()->whereIn('seeder', $seederClasses)->get(['id', 'uuid', 'seeder', 'theory_text_block_uuid']);
        foreach ($seederClasses as $seederClass) {
            $this->assertSame($expectedCounts[$seederClass] ?? 72, $questions->where('seeder', $seederClass)->count(), $caseName . ': ' . $seederClass);
        }
        $pivotRows = DB::table('question_theory_text_blocks')->whereIn('question_uuid', $questions->pluck('uuid'))->get();
        $this->assertSame($questions->count(), $pivotRows->pluck('question_uuid')->unique()->count(), $caseName . ': every covered question should have pivot rows.');
        $this->assertSame($questions->count(), $questions->whereNotNull('theory_text_block_uuid')->count(), $caseName . ': every covered question should set the legacy theory_text_block_uuid.');
        $linkedBlockUuids = $pivotRows->pluck('text_block_uuid')->unique()->values();
        $this->assertSame($linkedBlockUuids->count(), TextBlock::query()->whereIn('uuid', $linkedBlockUuids->all())->count(), $caseName . ': every linked text_block_uuid should exist.');
        $legacyBlockUuids = $questions->pluck('theory_text_block_uuid')->filter()->unique()->values();
        $this->assertSame($legacyBlockUuids->count(), TextBlock::query()->whereIn('uuid', $legacyBlockUuids->all())->count(), $caseName . ': every legacy text_block_uuid should exist.');
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

    private function assertAuditReportsVerbPatternsPartTwoRoutesAsOk(): void
    {
        $jsonPath = storage_path('framework/testing/verb-patterns-part-2-theory-links-audit.json');
        $markdownPath = storage_path('framework/testing/verb-patterns-part-2-theory-links-audit.md');
        File::delete([$jsonPath, $markdownPath]);
        $this->artisan('theory-pages:audit-tests-unification', ['--json' => $jsonPath, '--md' => $markdownPath])->assertExitCode(0);
        $audit = json_decode((string) File::get($jsonPath), true);
        $this->assertIsArray($audit);
        $rowsByRoute = collect($audit['pages'] ?? [])->keyBy('route');
        foreach ($this->cases() as $caseName => $case) {
            $row = $rowsByRoute->get($case['route']);
            $this->assertNotNull($row, $caseName . ': route should be present in audit.');
            $this->assertSame('OK', $row['status'] ?? null, $caseName . ': audit status.');
            foreach (['sentence_builder', 'mixed_a1_c2', 'v3_questions', 'polyglot_questions', 'theory_links', 'polyglot_theory_links', 'question_theory_text_blocks'] as $missingKey) {
                $this->assertNotContains($missingKey, $row['missing'] ?? [], $caseName . ': missing ' . $missingKey);
            }
        }
    }
}
