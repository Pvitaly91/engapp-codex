<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Question;
use App\Models\SavedGrammarTest;
use App\Models\TextBlock;
use App\Services\SavedTestResolver;
use App\Services\TheoryPagePromptLinkedTestsService;
use Database\Seeders\Page_V3\BasicGrammar\WordOrder\AdvancedFrontingAndEmphasisTheorySeeder;
use Database\Seeders\Page_V3\BasicGrammar\WordOrder\InversionAfterNegativeAdverbialsTheorySeeder;
use Database\Seeders\Page_V3\BasicGrammar\WordOrder\InversionBasicsTheorySeeder;
use Database\Seeders\Page_V3\BasicGrammar\WordOrder\WordOrderCategorySeeder;
use Database\Seeders\V3\Polyglot\PolyglotAdvancedFrontingAndEmphasisAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotAdvancedFrontingAndEmphasisC2LessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotInversionAfterAdverbialsC1LessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotInversionAfterNegativeAdverbialsAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotInversionBasicsAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotInversionBasicsB2LessonSeeder;
use Database\Seeders\V3\TheoryLinks\AdvancedFrontingAndEmphasisTheoryLinksSeeder;
use Database\Seeders\V3\TheoryLinks\InversionAfterNegativeAdverbialsTheoryLinksSeeder;
use Database\Seeders\V3\TheoryLinks\InversionBasicsTheoryLinksSeeder;
use Database\Seeders\V3\WordOrder\AdvancedFrontingAndEmphasisAllLevelsV3Seeder;
use Database\Seeders\V3\WordOrder\InversionAfterNegativeAdverbialsAllLevelsV3Seeder;
use Database\Seeders\V3\WordOrder\InversionBasicsAllLevelsV3Seeder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class WordOrderAdvancedTheoryPageTestsSeedersTest extends TestCase
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

    public function test_advanced_word_order_pages_have_tests_and_theory_links(): void
    {
        $this->seedWordOrderAdvancedStack();

        $this->seed([
            InversionBasicsTheoryLinksSeeder::class,
            InversionAfterNegativeAdverbialsTheoryLinksSeeder::class,
            AdvancedFrontingAndEmphasisTheoryLinksSeeder::class,
        ]);

        foreach ($this->cases() as $caseName => $case) {
            $page = Page::query()->where('slug', $case['page_slug'])->firstOrFail();
            $directTest = SavedGrammarTest::query()->where('slug', $case['direct_slug'])->first();

            $this->assertSame('word-order', $page->category?->slug, $caseName . ': category slug.');
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

        $this->assertAuditReportsWordOrderAdvancedRoutesAsOk();
    }

    private function seedWordOrderAdvancedStack(): void
    {
        $this->seed([
            WordOrderCategorySeeder::class,
            InversionBasicsTheorySeeder::class,
            InversionAfterNegativeAdverbialsTheorySeeder::class,
            AdvancedFrontingAndEmphasisTheorySeeder::class,
            InversionBasicsAllLevelsV3Seeder::class,
            InversionAfterNegativeAdverbialsAllLevelsV3Seeder::class,
            AdvancedFrontingAndEmphasisAllLevelsV3Seeder::class,
            PolyglotInversionBasicsAllLevelsLessonSeeder::class,
            PolyglotInversionAfterNegativeAdverbialsAllLevelsLessonSeeder::class,
            PolyglotAdvancedFrontingAndEmphasisAllLevelsLessonSeeder::class,
            PolyglotInversionBasicsB2LessonSeeder::class,
            PolyglotInversionAfterAdverbialsC1LessonSeeder::class,
            PolyglotAdvancedFrontingAndEmphasisC2LessonSeeder::class,
        ]);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function cases(): array
    {
        return [
            'inversion-basics' => [
                'route' => '/theory/word-order/inversion-basics',
                'page_slug' => 'inversion-basics',
                'direct_slug' => 'polyglot-inversion-basics-all-levels',
                'v3_seeder' => InversionBasicsAllLevelsV3Seeder::class,
                'polyglot_seeder' => PolyglotInversionBasicsAllLevelsLessonSeeder::class,
                'mixed_filter_seeders' => [InversionBasicsAllLevelsV3Seeder::class, PolyglotInversionBasicsAllLevelsLessonSeeder::class],
                'covered_seeders' => [InversionBasicsAllLevelsV3Seeder::class, PolyglotInversionBasicsAllLevelsLessonSeeder::class, PolyglotInversionBasicsB2LessonSeeder::class],
                'links_seeder' => InversionBasicsTheoryLinksSeeder::class,
                'covered_seeder_counts' => [PolyglotInversionBasicsB2LessonSeeder::class => 48],
            ],
            'inversion-after-negative-adverbials' => [
                'route' => '/theory/word-order/inversion-after-negative-adverbials',
                'page_slug' => 'inversion-after-negative-adverbials',
                'direct_slug' => 'polyglot-inversion-after-negative-adverbials-all-levels',
                'v3_seeder' => InversionAfterNegativeAdverbialsAllLevelsV3Seeder::class,
                'polyglot_seeder' => PolyglotInversionAfterNegativeAdverbialsAllLevelsLessonSeeder::class,
                'mixed_filter_seeders' => [InversionAfterNegativeAdverbialsAllLevelsV3Seeder::class, PolyglotInversionAfterNegativeAdverbialsAllLevelsLessonSeeder::class],
                'covered_seeders' => [InversionAfterNegativeAdverbialsAllLevelsV3Seeder::class, PolyglotInversionAfterNegativeAdverbialsAllLevelsLessonSeeder::class, PolyglotInversionAfterAdverbialsC1LessonSeeder::class],
                'links_seeder' => InversionAfterNegativeAdverbialsTheoryLinksSeeder::class,
                'covered_seeder_counts' => [PolyglotInversionAfterAdverbialsC1LessonSeeder::class => 48],
            ],
            'advanced-fronting-and-emphasis' => [
                'route' => '/theory/word-order/advanced-fronting-and-emphasis',
                'page_slug' => 'advanced-fronting-and-emphasis',
                'direct_slug' => 'polyglot-advanced-fronting-and-emphasis-all-levels',
                'v3_seeder' => AdvancedFrontingAndEmphasisAllLevelsV3Seeder::class,
                'polyglot_seeder' => PolyglotAdvancedFrontingAndEmphasisAllLevelsLessonSeeder::class,
                'mixed_filter_seeders' => [AdvancedFrontingAndEmphasisAllLevelsV3Seeder::class, PolyglotAdvancedFrontingAndEmphasisAllLevelsLessonSeeder::class],
                'covered_seeders' => [AdvancedFrontingAndEmphasisAllLevelsV3Seeder::class, PolyglotAdvancedFrontingAndEmphasisAllLevelsLessonSeeder::class, PolyglotAdvancedFrontingAndEmphasisC2LessonSeeder::class],
                'links_seeder' => AdvancedFrontingAndEmphasisTheoryLinksSeeder::class,
                'covered_seeder_counts' => [PolyglotAdvancedFrontingAndEmphasisC2LessonSeeder::class => 48],
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

    private function assertAuditReportsWordOrderAdvancedRoutesAsOk(): void
    {
        $jsonPath = storage_path('framework/testing/word-order-advanced-theory-links-audit.json');
        $markdownPath = storage_path('framework/testing/word-order-advanced-theory-links-audit.md');
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
