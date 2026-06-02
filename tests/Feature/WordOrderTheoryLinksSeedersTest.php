<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Question;
use App\Models\SavedGrammarTest;
use App\Models\TextBlock;
use App\Services\SavedTestResolver;
use App\Services\TheoryPagePromptLinkedTestsService;
use Database\Seeders\Page_V3\BasicGrammar\BasicGrammarCategorySeeder;
use Database\Seeders\Page_V3\BasicGrammar\WordOrder\AdvancedWordOrderEmphasisTheorySeeder;
use Database\Seeders\Page_V3\BasicGrammar\WordOrder\BasicWordOrderTheorySeeder;
use Database\Seeders\Page_V3\BasicGrammar\WordOrder\WordOrderAdverbsAdverbialsTheorySeeder;
use Database\Seeders\Page_V3\BasicGrammar\WordOrder\WordOrderCategorySeeder;
use Database\Seeders\Page_V3\BasicGrammar\WordOrder\WordOrderQuestionsNegativesTheorySeeder;
use Database\Seeders\Page_V3\BasicGrammar\WordOrder\WordOrderVerbsObjectsTheorySeeder;
use Database\Seeders\V3\AdvancedWordOrderEmphasisAllLevelsV3Seeder;
use Database\Seeders\V3\BasicWordOrderAllLevelsV3Seeder;
use Database\Seeders\V3\Polyglot\PolyglotAdvancedWordOrderEmphasisAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotBasicWordOrderAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotWordOrderAdverbsAdverbialsAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotWordOrderQuestionsNegativesAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotWordOrderVerbsObjectsAllLevelsLessonSeeder;
use Database\Seeders\V3\TheoryLinks\WordOrderAdvancedEmphasisTheoryLinksSeeder;
use Database\Seeders\V3\TheoryLinks\WordOrderAdverbsAdverbialsTheoryLinksSeeder;
use Database\Seeders\V3\TheoryLinks\WordOrderBasicWordOrderTheoryLinksSeeder;
use Database\Seeders\V3\TheoryLinks\WordOrderQuestionsNegativesTheoryLinksSeeder;
use Database\Seeders\V3\TheoryLinks\WordOrderVerbsObjectsTheoryLinksSeeder;
use Database\Seeders\V3\WordOrderAdverbsAdverbialsAllLevelsV3Seeder;
use Database\Seeders\V3\WordOrderQuestionsNegativesAllLevelsV3Seeder;
use Database\Seeders\V3\WordOrderVerbsObjectsAllLevelsV3Seeder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class WordOrderTheoryLinksSeedersTest extends TestCase
{
    use RebuildsComposeTestSchema;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'coming-soon.enabled' => false,
            'tests.tech_info_enabled' => false,
        ]);

        $this->rebuildComposeTestSchema();
    }

    public function test_word_order_theory_links_seeders_link_direct_polyglot_and_mixed_questions(): void
    {
        $this->seedWordOrderStack();

        $this->seed([
            WordOrderBasicWordOrderTheoryLinksSeeder::class,
            WordOrderQuestionsNegativesTheoryLinksSeeder::class,
            WordOrderAdverbsAdverbialsTheoryLinksSeeder::class,
            WordOrderVerbsObjectsTheoryLinksSeeder::class,
            WordOrderAdvancedEmphasisTheoryLinksSeeder::class,
        ]);

        foreach ($this->cases() as $caseName => $case) {
            $page = Page::query()->where('slug', $case['page_slug'])->firstOrFail();
            $directTest = SavedGrammarTest::query()->where('slug', $case['direct_slug'])->first();

            $this->assertNotNull($directTest, $caseName . ': direct Sentence Builder test exists.');

            $tests = app(TheoryPagePromptLinkedTestsService::class)->buildForPage($page);
            $mixedTest = $tests->firstWhere('slug', 'theory-page-' . $page->id . '-mixed-a1-c2');

            $this->assertNotNull($mixedTest, $caseName . ': Mixed A1-C2 test exists.');
            $this->assertTrue($mixedTest->isVirtual(), $caseName);

            $mixedSeederClasses = collect($mixedTest->filters['seeder_classes'] ?? [])->values();
            foreach ($case['mixed_filter_seeders'] as $expectedSeeder) {
                $this->assertTrue($mixedSeederClasses->contains($expectedSeeder), $caseName . ': ' . $expectedSeeder);
            }

            $this->assertEveryCoveredQuestionHasTheoryLinks($case['covered_seeders'], $caseName);
            $this->assertDirectSentenceBuilderQuestionsExposeTheoryBlocks($case['direct_slug'], $caseName);
            $this->assertMixedResolvedQuestionsExposeTheoryBlocks($mixedTest, $case['covered_seeders'], $caseName);

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

        $this->assertAuditReportsWordOrderRoutesAsOk();
    }

    private function seedWordOrderStack(): void
    {
        $this->seed([
            BasicGrammarCategorySeeder::class,
            WordOrderCategorySeeder::class,
            BasicWordOrderTheorySeeder::class,
            WordOrderQuestionsNegativesTheorySeeder::class,
            WordOrderAdverbsAdverbialsTheorySeeder::class,
            WordOrderVerbsObjectsTheorySeeder::class,
            AdvancedWordOrderEmphasisTheorySeeder::class,
            BasicWordOrderAllLevelsV3Seeder::class,
            WordOrderQuestionsNegativesAllLevelsV3Seeder::class,
            WordOrderAdverbsAdverbialsAllLevelsV3Seeder::class,
            WordOrderVerbsObjectsAllLevelsV3Seeder::class,
            AdvancedWordOrderEmphasisAllLevelsV3Seeder::class,
            PolyglotBasicWordOrderAllLevelsLessonSeeder::class,
            PolyglotWordOrderQuestionsNegativesAllLevelsLessonSeeder::class,
            PolyglotWordOrderAdverbsAdverbialsAllLevelsLessonSeeder::class,
            PolyglotWordOrderVerbsObjectsAllLevelsLessonSeeder::class,
            PolyglotAdvancedWordOrderEmphasisAllLevelsLessonSeeder::class,
        ]);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function cases(): array
    {
        return [
            'basic_word_order' => [
                'route' => '/theory/word-order/theory-basic-word-order',
                'page_slug' => 'theory-basic-word-order',
                'direct_slug' => 'polyglot-basic-word-order-all-levels',
                'mixed_filter_seeders' => [
                    BasicWordOrderAllLevelsV3Seeder::class,
                    PolyglotBasicWordOrderAllLevelsLessonSeeder::class,
                ],
                'covered_seeders' => [
                    BasicWordOrderAllLevelsV3Seeder::class,
                    PolyglotBasicWordOrderAllLevelsLessonSeeder::class,
                ],
                'links_seeder' => WordOrderBasicWordOrderTheoryLinksSeeder::class,
            ],
            'questions_negatives' => [
                'route' => '/theory/word-order/theory-word-order-questions-negatives',
                'page_slug' => 'theory-word-order-questions-negatives',
                'direct_slug' => 'polyglot-word-order-questions-negatives-all-levels',
                'mixed_filter_seeders' => [
                    WordOrderQuestionsNegativesAllLevelsV3Seeder::class,
                    PolyglotWordOrderQuestionsNegativesAllLevelsLessonSeeder::class,
                ],
                'covered_seeders' => [
                    WordOrderQuestionsNegativesAllLevelsV3Seeder::class,
                    PolyglotWordOrderQuestionsNegativesAllLevelsLessonSeeder::class,
                ],
                'links_seeder' => WordOrderQuestionsNegativesTheoryLinksSeeder::class,
            ],
            'adverbs_adverbials' => [
                'route' => '/theory/word-order/theory-word-order-adverbs-adverbials',
                'page_slug' => 'theory-word-order-adverbs-adverbials',
                'direct_slug' => 'polyglot-word-order-adverbs-adverbials-all-levels',
                'mixed_filter_seeders' => [
                    WordOrderAdverbsAdverbialsAllLevelsV3Seeder::class,
                    PolyglotWordOrderAdverbsAdverbialsAllLevelsLessonSeeder::class,
                ],
                'covered_seeders' => [
                    WordOrderAdverbsAdverbialsAllLevelsV3Seeder::class,
                    PolyglotWordOrderAdverbsAdverbialsAllLevelsLessonSeeder::class,
                ],
                'links_seeder' => WordOrderAdverbsAdverbialsTheoryLinksSeeder::class,
            ],
            'verbs_objects' => [
                'route' => '/theory/word-order/theory-word-order-verbs-objects',
                'page_slug' => 'theory-word-order-verbs-objects',
                'direct_slug' => 'polyglot-word-order-verbs-objects-all-levels',
                'mixed_filter_seeders' => [
                    WordOrderVerbsObjectsAllLevelsV3Seeder::class,
                    PolyglotWordOrderVerbsObjectsAllLevelsLessonSeeder::class,
                ],
                'covered_seeders' => [
                    WordOrderVerbsObjectsAllLevelsV3Seeder::class,
                    PolyglotWordOrderVerbsObjectsAllLevelsLessonSeeder::class,
                ],
                'links_seeder' => WordOrderVerbsObjectsTheoryLinksSeeder::class,
            ],
            'advanced_emphasis' => [
                'route' => '/theory/word-order/theory-advanced-word-order-emphasis',
                'page_slug' => 'theory-advanced-word-order-emphasis',
                'direct_slug' => 'polyglot-advanced-word-order-emphasis-all-levels',
                'mixed_filter_seeders' => [
                    AdvancedWordOrderEmphasisAllLevelsV3Seeder::class,
                    PolyglotAdvancedWordOrderEmphasisAllLevelsLessonSeeder::class,
                ],
                'covered_seeders' => [
                    AdvancedWordOrderEmphasisAllLevelsV3Seeder::class,
                    PolyglotAdvancedWordOrderEmphasisAllLevelsLessonSeeder::class,
                ],
                'links_seeder' => WordOrderAdvancedEmphasisTheoryLinksSeeder::class,
            ],
        ];
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
            $this->assertGreaterThan(
                0,
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

    private function assertDirectSentenceBuilderQuestionsExposeTheoryBlocks(string $directSlug, string $caseName): void
    {
        $resolved = app(SavedTestResolver::class)->resolve($directSlug);
        $questions = app(SavedTestResolver::class)->loadQuestions($resolved, ['theoryTextBlocks']);

        $this->assertGreaterThan(0, $questions->count(), $caseName . ': direct Sentence Builder questions.');
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
        $filters['num_questions'] = 48;
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

        $this->assertGreaterThan(0, $questions->count(), $caseName . ': mixed resolved questions.');
        foreach ($coveredSeeders as $coveredSeeder) {
            $this->assertTrue(
                $questions->pluck('seeder')->contains($coveredSeeder),
                $caseName . ': mixed resolved questions should include ' . $coveredSeeder
            );
        }

        $this->assertTrue(
            $questions->every(fn (Question $question): bool => $question->theoryTextBlocks->isNotEmpty()),
            $caseName . ': mixed questions should expose theory blocks.'
        );
    }

    private function assertAuditReportsWordOrderRoutesAsOk(): void
    {
        $jsonPath = storage_path('framework/testing/word-order-theory-links-audit.json');
        $markdownPath = storage_path('framework/testing/word-order-theory-links-audit.md');
        File::delete([$jsonPath, $markdownPath]);

        $this->artisan('theory-pages:audit-tests-unification', [
            '--json' => $jsonPath,
            '--md' => $markdownPath,
        ])->assertExitCode(0);

        $audit = json_decode((string) File::get($jsonPath), true);
        $this->assertIsArray($audit);

        $auditRows = collect($audit['pages'] ?? []);
        $rowsByRoute = $auditRows->keyBy('route');
        $rowsBySlug = $auditRows->keyBy('page_slug');

        foreach ($this->cases() as $caseName => $case) {
            $row = $rowsByRoute->get($case['route']) ?? $rowsBySlug->get($case['page_slug']);

            $this->assertNotNull($row, $caseName . ': route should be present in audit.');
            $this->assertSame('OK', $row['status'] ?? null, $caseName . ': audit status.');
            $this->assertEmpty(
                collect($row['missing'] ?? [])->intersect([
                    'theory_links',
                    'polyglot_theory_links',
                    'question_theory_text_blocks',
                ])->all(),
                $caseName . ': audit should not report missing theory links.'
            );
        }
    }
}
