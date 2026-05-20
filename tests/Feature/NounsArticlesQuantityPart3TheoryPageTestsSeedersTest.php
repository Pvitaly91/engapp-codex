<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Question;
use App\Models\SavedGrammarTest;
use App\Models\TextBlock;
use App\Services\SavedTestResolver;
use App\Services\TheoryPagePromptLinkedTestsService;
use Database\Seeders\Page_V3\NounsArticlesQuantity\NounsArticlesQuantityAnotherOtherTheOtherTheorySeeder;
use Database\Seeders\Page_V3\NounsArticlesQuantity\NounsArticlesQuantityCategorySeeder;
use Database\Seeders\Page_V3\NounsArticlesQuantity\NounsArticlesQuantityCollectiveNounsTheorySeeder;
use Database\Seeders\Page_V3\NounsArticlesQuantity\NounsArticlesQuantityIrregularPluralsTheorySeeder;
use Database\Seeders\Page_V3\NounsArticlesQuantity\NounsArticlesQuantityNoNoneNeitherEitherTheorySeeder;
use Database\Seeders\V3\NounsArticlesQuantity\AnotherOtherTheOtherAllLevelsV3Seeder;
use Database\Seeders\V3\NounsArticlesQuantity\CollectiveNounsAllLevelsV3Seeder;
use Database\Seeders\V3\NounsArticlesQuantity\IrregularPluralsAllLevelsV3Seeder;
use Database\Seeders\V3\NounsArticlesQuantity\NoNoneNeitherEitherAllLevelsV3Seeder;
use Database\Seeders\V3\Polyglot\PolyglotAnotherOtherTheOtherAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotCollectiveNounsAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotIrregularPluralsAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotNoNoneNeitherEitherAllLevelsLessonSeeder;
use Database\Seeders\V3\TheoryLinks\AnotherOtherTheOtherTheoryLinksSeeder;
use Database\Seeders\V3\TheoryLinks\CollectiveNounsTheoryLinksSeeder;
use Database\Seeders\V3\TheoryLinks\IrregularPluralsTheoryLinksSeeder;
use Database\Seeders\V3\TheoryLinks\NoNoneNeitherEitherTheoryLinksSeeder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class NounsArticlesQuantityPart3TheoryPageTestsSeedersTest extends TestCase
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

    public function test_nouns_articles_quantity_part_3_pages_have_tests_and_theory_links(): void
    {
        $this->seedNounsArticlesQuantityPart3Stack();

        $this->seed([
            IrregularPluralsTheoryLinksSeeder::class,
            CollectiveNounsTheoryLinksSeeder::class,
            AnotherOtherTheOtherTheoryLinksSeeder::class,
            NoNoneNeitherEitherTheoryLinksSeeder::class,
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

        $this->assertAuditReportsNounsArticlesQuantityPart3RoutesAsOk();
    }

    private function seedNounsArticlesQuantityPart3Stack(): void
    {
        $this->seed([
            NounsArticlesQuantityCategorySeeder::class,
            NounsArticlesQuantityIrregularPluralsTheorySeeder::class,
            NounsArticlesQuantityCollectiveNounsTheorySeeder::class,
            NounsArticlesQuantityAnotherOtherTheOtherTheorySeeder::class,
            NounsArticlesQuantityNoNoneNeitherEitherTheorySeeder::class,
            IrregularPluralsAllLevelsV3Seeder::class,
            CollectiveNounsAllLevelsV3Seeder::class,
            AnotherOtherTheOtherAllLevelsV3Seeder::class,
            NoNoneNeitherEitherAllLevelsV3Seeder::class,
            PolyglotIrregularPluralsAllLevelsLessonSeeder::class,
            PolyglotCollectiveNounsAllLevelsLessonSeeder::class,
            PolyglotAnotherOtherTheOtherAllLevelsLessonSeeder::class,
            PolyglotNoNoneNeitherEitherAllLevelsLessonSeeder::class,
        ]);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function cases(): array
    {
        return [
            'irregular-plurals' => [
                'route' => '/theory/imennyky-artykli-ta-kilkist/irregular-plurals',
                'page_slug' => 'irregular-plurals',
                'direct_slug' => 'polyglot-irregular-plurals-all-levels',
                'v3_seeder' => IrregularPluralsAllLevelsV3Seeder::class,
                'polyglot_seeder' => PolyglotIrregularPluralsAllLevelsLessonSeeder::class,
                'mixed_filter_seeders' => [IrregularPluralsAllLevelsV3Seeder::class, PolyglotIrregularPluralsAllLevelsLessonSeeder::class],
                'covered_seeders' => [IrregularPluralsAllLevelsV3Seeder::class, PolyglotIrregularPluralsAllLevelsLessonSeeder::class],
                'links_seeder' => IrregularPluralsTheoryLinksSeeder::class,
            ],
            'collective-nouns' => [
                'route' => '/theory/imennyky-artykli-ta-kilkist/collective-nouns',
                'page_slug' => 'collective-nouns',
                'direct_slug' => 'polyglot-collective-nouns-all-levels',
                'v3_seeder' => CollectiveNounsAllLevelsV3Seeder::class,
                'polyglot_seeder' => PolyglotCollectiveNounsAllLevelsLessonSeeder::class,
                'mixed_filter_seeders' => [CollectiveNounsAllLevelsV3Seeder::class, PolyglotCollectiveNounsAllLevelsLessonSeeder::class],
                'covered_seeders' => [CollectiveNounsAllLevelsV3Seeder::class, PolyglotCollectiveNounsAllLevelsLessonSeeder::class],
                'links_seeder' => CollectiveNounsTheoryLinksSeeder::class,
            ],
            'another-other-the-other' => [
                'route' => '/theory/imennyky-artykli-ta-kilkist/another-other-the-other',
                'page_slug' => 'another-other-the-other',
                'direct_slug' => 'polyglot-another-other-the-other-all-levels',
                'v3_seeder' => AnotherOtherTheOtherAllLevelsV3Seeder::class,
                'polyglot_seeder' => PolyglotAnotherOtherTheOtherAllLevelsLessonSeeder::class,
                'mixed_filter_seeders' => [AnotherOtherTheOtherAllLevelsV3Seeder::class, PolyglotAnotherOtherTheOtherAllLevelsLessonSeeder::class],
                'covered_seeders' => [AnotherOtherTheOtherAllLevelsV3Seeder::class, PolyglotAnotherOtherTheOtherAllLevelsLessonSeeder::class],
                'links_seeder' => AnotherOtherTheOtherTheoryLinksSeeder::class,
            ],
            'no-none-neither-either' => [
                'route' => '/theory/imennyky-artykli-ta-kilkist/no-none-neither-either',
                'page_slug' => 'no-none-neither-either',
                'direct_slug' => 'polyglot-no-none-neither-either-all-levels',
                'v3_seeder' => NoNoneNeitherEitherAllLevelsV3Seeder::class,
                'polyglot_seeder' => PolyglotNoNoneNeitherEitherAllLevelsLessonSeeder::class,
                'mixed_filter_seeders' => [NoNoneNeitherEitherAllLevelsV3Seeder::class, PolyglotNoNoneNeitherEitherAllLevelsLessonSeeder::class],
                'covered_seeders' => [NoNoneNeitherEitherAllLevelsV3Seeder::class, PolyglotNoNoneNeitherEitherAllLevelsLessonSeeder::class],
                'links_seeder' => NoNoneNeitherEitherTheoryLinksSeeder::class,
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
            $this->assertSame(72, $questions->where('seeder', $seederClass)->count(), $caseName . ': ' . $seederClass);
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

    private function assertAuditReportsNounsArticlesQuantityPart3RoutesAsOk(): void
    {
        $jsonPath = storage_path('framework/testing/nouns-articles-quantity-part-3-theory-links-audit.json');
        $markdownPath = storage_path('framework/testing/nouns-articles-quantity-part-3-theory-links-audit.md');
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
