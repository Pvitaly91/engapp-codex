<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Question;
use App\Models\SavedGrammarTest;
use App\Models\TextBlock;
use App\Services\SavedTestResolver;
use App\Services\TheoryPagePromptLinkedTestsService;
use Database\Seeders\Page_V3\SentenceStructure\CleftSentencesBasicsTheorySeeder;
use Database\Seeders\Page_V3\SentenceStructure\CleftSentencesEmphasisTheorySeeder;
use Database\Seeders\Page_V3\SentenceStructure\ComplexNounPhrasesTheorySeeder;
use Database\Seeders\Page_V3\SentenceStructure\EllipsisSubstitutionAndReferenceTheorySeeder;
use Database\Seeders\V3\Polyglot\PolyglotCleftSentencesBasicsAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotCleftSentencesBasicsB2LessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotCleftSentencesEmphasisAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotCleftSentencesEmphasisC1LessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotComplexNounPhrasesAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotComplexNounPhrasesC2LessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotEllipsisSubstitutionAndReferenceAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotEllipsisSubstitutionAndReferenceC2LessonSeeder;
use Database\Seeders\V3\SentenceStructure\CleftSentencesBasicsAllLevelsV3Seeder;
use Database\Seeders\V3\SentenceStructure\CleftSentencesEmphasisAllLevelsV3Seeder;
use Database\Seeders\V3\SentenceStructure\ComplexNounPhrasesAllLevelsV3Seeder;
use Database\Seeders\V3\SentenceStructure\EllipsisSubstitutionAndReferenceAllLevelsV3Seeder;
use Database\Seeders\V3\TheoryLinks\CleftSentencesBasicsTheoryLinksSeeder;
use Database\Seeders\V3\TheoryLinks\CleftSentencesEmphasisTheoryLinksSeeder;
use Database\Seeders\V3\TheoryLinks\ComplexNounPhrasesTheoryLinksSeeder;
use Database\Seeders\V3\TheoryLinks\EllipsisSubstitutionAndReferenceTheoryLinksSeeder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class SentenceStructureTheoryPageTestsSeedersTest extends TestCase
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

    public function test_sentence_structure_pages_have_tests_and_theory_links(): void
    {
        $this->seedSentenceStructureStack();

        $this->seed([
            CleftSentencesBasicsTheoryLinksSeeder::class,
            CleftSentencesEmphasisTheoryLinksSeeder::class,
            EllipsisSubstitutionAndReferenceTheoryLinksSeeder::class,
            ComplexNounPhrasesTheoryLinksSeeder::class,
        ]);

        foreach ($this->cases() as $caseName => $case) {
            $page = Page::query()->where('slug', $case['page_slug'])->firstOrFail();
            $directTest = SavedGrammarTest::query()->where('slug', $case['direct_slug'])->first();

            $this->assertSame('sentence-structure', $page->category?->slug, $caseName . ': category slug.');
            $this->assertNotNull($directTest, $caseName . ': direct Sentence Builder test exists.');
            $this->assertQuestionCountByLevel($case['v3_seeder'], $caseName . ': V3');
            $this->assertQuestionCountByLevel($case['polyglot_seeder'], $caseName . ': Polyglot');

            $tests = app(TheoryPagePromptLinkedTestsService::class)->buildForPage($page);
            $mixedTest = $tests->firstWhere('slug', 'theory-page-' . $page->id . '-mixed-a1-c2');

            $this->assertNotNull($mixedTest, $caseName . ': Mixed A1-C2 test exists.');
            $this->assertTrue($mixedTest->isVirtual(), $caseName . ': mixed test should be virtual.');

            $mixedSeederClasses = collect($mixedTest->filters['seeder_classes'] ?? [])->values();
            foreach ($case['mixed_filter_seeders'] as $expectedSeeder) {
                $this->assertTrue($mixedSeederClasses->contains($expectedSeeder), $caseName . ': mixed filters include ' . $expectedSeeder);
            }

            $promptGenerator = $mixedTest->filters['prompt_generator'] ?? [];
            $this->assertSame('theory_page', data_get($promptGenerator, 'source_type'), $caseName . ': mixed prompt generator source.');
            $this->assertSame($case['page_slug'], data_get($promptGenerator, 'theory_page.slug'), $caseName . ': mixed prompt generator page slug.');
            $this->assertSame('sentence-structure', data_get($promptGenerator, 'theory_page.category_slug_path'), $caseName . ': mixed prompt generator category.');

            $this->assertEveryCoveredQuestionHasTheoryLinks($case['covered_seeders'], $caseName);
            $this->assertDirectSentenceBuilderQuestionsExposeTheoryBlocks($case['direct_slug'], $case['polyglot_seeder'], $caseName);
            $this->assertMixedResolvedQuestionsExposeTheoryBlocks($mixedTest, $case['mixed_filter_seeders'], $caseName);

            $questionUuids = Question::query()->whereIn('seeder', $case['covered_seeders'])->pluck('uuid');
            $firstRunPivotRows = DB::table('question_theory_text_blocks')->whereIn('question_uuid', $questionUuids)->count();
            $this->seed($case['links_seeder']);
            $this->assertSame($firstRunPivotRows, DB::table('question_theory_text_blocks')->whereIn('question_uuid', $questionUuids)->count(), $caseName . ': idempotency check failed.');
        }

        $this->assertAuditReportsSentenceStructureRoutesAsOk();
    }

    private function seedSentenceStructureStack(): void
    {
        // There is no separate Page_V3 SentenceStructure category seeder in this repo;
        // these page seeders define/use the sentence-structure category directly.
        $this->seed([
            CleftSentencesBasicsTheorySeeder::class,
            CleftSentencesEmphasisTheorySeeder::class,
            EllipsisSubstitutionAndReferenceTheorySeeder::class,
            ComplexNounPhrasesTheorySeeder::class,
            CleftSentencesBasicsAllLevelsV3Seeder::class,
            CleftSentencesEmphasisAllLevelsV3Seeder::class,
            EllipsisSubstitutionAndReferenceAllLevelsV3Seeder::class,
            ComplexNounPhrasesAllLevelsV3Seeder::class,
            PolyglotCleftSentencesBasicsAllLevelsLessonSeeder::class,
            PolyglotCleftSentencesEmphasisAllLevelsLessonSeeder::class,
            PolyglotEllipsisSubstitutionAndReferenceAllLevelsLessonSeeder::class,
            PolyglotComplexNounPhrasesAllLevelsLessonSeeder::class,
            PolyglotCleftSentencesBasicsB2LessonSeeder::class,
            PolyglotCleftSentencesEmphasisC1LessonSeeder::class,
            PolyglotEllipsisSubstitutionAndReferenceC2LessonSeeder::class,
            PolyglotComplexNounPhrasesC2LessonSeeder::class,
        ]);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function cases(): array
    {
        return [
            'cleft-sentences-basics' => [
                'route' => '/theory/sentence-structure/cleft-sentences-basics',
                'page_slug' => 'cleft-sentences-basics',
                'direct_slug' => 'polyglot-cleft-sentences-basics-all-levels',
                'v3_seeder' => CleftSentencesBasicsAllLevelsV3Seeder::class,
                'polyglot_seeder' => PolyglotCleftSentencesBasicsAllLevelsLessonSeeder::class,
                'legacy_seeder' => PolyglotCleftSentencesBasicsB2LessonSeeder::class,
                'mixed_filter_seeders' => [CleftSentencesBasicsAllLevelsV3Seeder::class, PolyglotCleftSentencesBasicsAllLevelsLessonSeeder::class],
                'covered_seeders' => [CleftSentencesBasicsAllLevelsV3Seeder::class, PolyglotCleftSentencesBasicsAllLevelsLessonSeeder::class, PolyglotCleftSentencesBasicsB2LessonSeeder::class],
                'links_seeder' => CleftSentencesBasicsTheoryLinksSeeder::class,
                'covered_seeder_counts' => [PolyglotCleftSentencesBasicsB2LessonSeeder::class => 48],
            ],
            'cleft-sentences-emphasis' => [
                'route' => '/theory/sentence-structure/cleft-sentences-emphasis',
                'page_slug' => 'cleft-sentences-emphasis',
                'direct_slug' => 'polyglot-cleft-sentences-emphasis-all-levels',
                'v3_seeder' => CleftSentencesEmphasisAllLevelsV3Seeder::class,
                'polyglot_seeder' => PolyglotCleftSentencesEmphasisAllLevelsLessonSeeder::class,
                'legacy_seeder' => PolyglotCleftSentencesEmphasisC1LessonSeeder::class,
                'mixed_filter_seeders' => [CleftSentencesEmphasisAllLevelsV3Seeder::class, PolyglotCleftSentencesEmphasisAllLevelsLessonSeeder::class],
                'covered_seeders' => [CleftSentencesEmphasisAllLevelsV3Seeder::class, PolyglotCleftSentencesEmphasisAllLevelsLessonSeeder::class, PolyglotCleftSentencesEmphasisC1LessonSeeder::class],
                'links_seeder' => CleftSentencesEmphasisTheoryLinksSeeder::class,
                'covered_seeder_counts' => [PolyglotCleftSentencesEmphasisC1LessonSeeder::class => 48],
            ],
            'ellipsis-substitution-and-reference' => [
                'route' => '/theory/sentence-structure/ellipsis-substitution-and-reference',
                'page_slug' => 'ellipsis-substitution-and-reference',
                'direct_slug' => 'polyglot-ellipsis-substitution-and-reference-all-levels',
                'v3_seeder' => EllipsisSubstitutionAndReferenceAllLevelsV3Seeder::class,
                'polyglot_seeder' => PolyglotEllipsisSubstitutionAndReferenceAllLevelsLessonSeeder::class,
                'legacy_seeder' => PolyglotEllipsisSubstitutionAndReferenceC2LessonSeeder::class,
                'mixed_filter_seeders' => [EllipsisSubstitutionAndReferenceAllLevelsV3Seeder::class, PolyglotEllipsisSubstitutionAndReferenceAllLevelsLessonSeeder::class],
                'covered_seeders' => [EllipsisSubstitutionAndReferenceAllLevelsV3Seeder::class, PolyglotEllipsisSubstitutionAndReferenceAllLevelsLessonSeeder::class, PolyglotEllipsisSubstitutionAndReferenceC2LessonSeeder::class],
                'links_seeder' => EllipsisSubstitutionAndReferenceTheoryLinksSeeder::class,
                'covered_seeder_counts' => [PolyglotEllipsisSubstitutionAndReferenceC2LessonSeeder::class => 48],
            ],
            'complex-noun-phrases' => [
                'route' => '/theory/sentence-structure/complex-noun-phrases',
                'page_slug' => 'complex-noun-phrases',
                'direct_slug' => 'polyglot-complex-noun-phrases-all-levels',
                'v3_seeder' => ComplexNounPhrasesAllLevelsV3Seeder::class,
                'polyglot_seeder' => PolyglotComplexNounPhrasesAllLevelsLessonSeeder::class,
                'legacy_seeder' => PolyglotComplexNounPhrasesC2LessonSeeder::class,
                'mixed_filter_seeders' => [ComplexNounPhrasesAllLevelsV3Seeder::class, PolyglotComplexNounPhrasesAllLevelsLessonSeeder::class],
                'covered_seeders' => [ComplexNounPhrasesAllLevelsV3Seeder::class, PolyglotComplexNounPhrasesAllLevelsLessonSeeder::class, PolyglotComplexNounPhrasesC2LessonSeeder::class],
                'links_seeder' => ComplexNounPhrasesTheoryLinksSeeder::class,
                'covered_seeder_counts' => [PolyglotComplexNounPhrasesC2LessonSeeder::class => 48],
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

    private function assertAuditReportsSentenceStructureRoutesAsOk(): void
    {
        $jsonPath = storage_path('framework/testing/sentence-structure-theory-links-audit.json');
        $markdownPath = storage_path('framework/testing/sentence-structure-theory-links-audit.md');
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
