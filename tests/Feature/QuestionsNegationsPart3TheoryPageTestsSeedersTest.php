<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Question;
use App\Models\SavedGrammarTest;
use App\Models\TextBlock;
use App\Services\SavedTestResolver;
use App\Services\TheoryPagePromptLinkedTestsService;
use Database\Seeders\Page_V3\QuestionsNegations\QuestionsNegationsCategorySeeder;
use Database\Seeders\Page_V3\QuestionsNegations\QuestionsNegationsDoubleNegativesTheorySeeder;
use Database\Seeders\Page_V3\QuestionsNegations\QuestionsNegationsNegativeFormsTheorySeeder;
use Database\Seeders\Page_V3\QuestionsNegations\TypesOfQuestions\TypesOfQuestionsCategorySeeder;
use Database\Seeders\Page_V3\QuestionsNegations\TypesOfQuestions\TypesOfQuestionsEmbeddedQuestionsTheorySeeder;
use Database\Seeders\Page_V3\QuestionsNegations\TypesOfQuestions\TypesOfQuestionsIndirectQuestionsTheorySeeder;
use Database\Seeders\V3\Polyglot\PolyglotDoubleNegativesAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotEmbeddedQuestionsAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotIndirectQuestionsAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotNegativeFormsAllLevelsLessonSeeder;
use Database\Seeders\V3\QuestionsNegations\DoubleNegativesAllLevelsV3Seeder;
use Database\Seeders\V3\QuestionsNegations\EmbeddedQuestionsAllLevelsV3Seeder;
use Database\Seeders\V3\QuestionsNegations\IndirectQuestionsAllLevelsV3Seeder;
use Database\Seeders\V3\QuestionsNegations\NegativeFormsAllLevelsV3Seeder;
use Database\Seeders\V3\TheoryLinks\DoubleNegativesTheoryLinksSeeder;
use Database\Seeders\V3\TheoryLinks\EmbeddedQuestionsTheoryLinksSeeder;
use Database\Seeders\V3\TheoryLinks\IndirectQuestionsTheoryLinksSeeder;
use Database\Seeders\V3\TheoryLinks\NegativeFormsTheoryLinksSeeder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class QuestionsNegationsPart3TheoryPageTestsSeedersTest extends TestCase
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

    public function test_questions_negations_part_three_pages_have_tests_and_theory_links(): void
    {
        $this->seedQuestionsNegationsPartThreeStack();

        $this->seed([
            EmbeddedQuestionsTheoryLinksSeeder::class,
            IndirectQuestionsTheoryLinksSeeder::class,
            NegativeFormsTheoryLinksSeeder::class,
            DoubleNegativesTheoryLinksSeeder::class,
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

        $this->assertAuditReportsQuestionsNegationsPartThreeRoutesAsOk();
    }

    private function seedQuestionsNegationsPartThreeStack(): void
    {
        $this->seed([
            QuestionsNegationsCategorySeeder::class,
            TypesOfQuestionsCategorySeeder::class,
            TypesOfQuestionsEmbeddedQuestionsTheorySeeder::class,
            TypesOfQuestionsIndirectQuestionsTheorySeeder::class,
            QuestionsNegationsNegativeFormsTheorySeeder::class,
            QuestionsNegationsDoubleNegativesTheorySeeder::class,
            EmbeddedQuestionsAllLevelsV3Seeder::class,
            IndirectQuestionsAllLevelsV3Seeder::class,
            NegativeFormsAllLevelsV3Seeder::class,
            DoubleNegativesAllLevelsV3Seeder::class,
            PolyglotEmbeddedQuestionsAllLevelsLessonSeeder::class,
            PolyglotIndirectQuestionsAllLevelsLessonSeeder::class,
            PolyglotNegativeFormsAllLevelsLessonSeeder::class,
            PolyglotDoubleNegativesAllLevelsLessonSeeder::class,
        ]);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function cases(): array
    {
        return [
            'embedded-questions' => [
                'route' => '/theory/pytalni-rechennia-ta-zaperechennia/types-of-questions/embedded-questions',
                'page_slug' => 'embedded-questions',
                'direct_slug' => 'polyglot-embedded-questions-all-levels',
                'v3_seeder' => EmbeddedQuestionsAllLevelsV3Seeder::class,
                'polyglot_seeder' => PolyglotEmbeddedQuestionsAllLevelsLessonSeeder::class,
                'mixed_filter_seeders' => [EmbeddedQuestionsAllLevelsV3Seeder::class, PolyglotEmbeddedQuestionsAllLevelsLessonSeeder::class],
                'covered_seeders' => [EmbeddedQuestionsAllLevelsV3Seeder::class, PolyglotEmbeddedQuestionsAllLevelsLessonSeeder::class],
                'links_seeder' => EmbeddedQuestionsTheoryLinksSeeder::class,
            ],
            'indirect-questions' => [
                'route' => '/theory/pytalni-rechennia-ta-zaperechennia/types-of-questions/indirect-questions',
                'page_slug' => 'indirect-questions',
                'direct_slug' => 'polyglot-indirect-questions-all-levels',
                'v3_seeder' => IndirectQuestionsAllLevelsV3Seeder::class,
                'polyglot_seeder' => PolyglotIndirectQuestionsAllLevelsLessonSeeder::class,
                'mixed_filter_seeders' => [IndirectQuestionsAllLevelsV3Seeder::class, PolyglotIndirectQuestionsAllLevelsLessonSeeder::class],
                'covered_seeders' => [IndirectQuestionsAllLevelsV3Seeder::class, PolyglotIndirectQuestionsAllLevelsLessonSeeder::class],
                'links_seeder' => IndirectQuestionsTheoryLinksSeeder::class,
            ],
            'negative-forms' => [
                'route' => '/theory/pytalni-rechennia-ta-zaperechennia/negative-forms',
                'page_slug' => 'negative-forms',
                'direct_slug' => 'polyglot-negative-forms-all-levels',
                'v3_seeder' => NegativeFormsAllLevelsV3Seeder::class,
                'polyglot_seeder' => PolyglotNegativeFormsAllLevelsLessonSeeder::class,
                'mixed_filter_seeders' => [NegativeFormsAllLevelsV3Seeder::class, PolyglotNegativeFormsAllLevelsLessonSeeder::class],
                'covered_seeders' => [NegativeFormsAllLevelsV3Seeder::class, PolyglotNegativeFormsAllLevelsLessonSeeder::class],
                'links_seeder' => NegativeFormsTheoryLinksSeeder::class,
            ],
            'double-negatives' => [
                'route' => '/theory/pytalni-rechennia-ta-zaperechennia/double-negatives',
                'page_slug' => 'double-negatives',
                'direct_slug' => 'polyglot-double-negatives-all-levels',
                'v3_seeder' => DoubleNegativesAllLevelsV3Seeder::class,
                'polyglot_seeder' => PolyglotDoubleNegativesAllLevelsLessonSeeder::class,
                'mixed_filter_seeders' => [DoubleNegativesAllLevelsV3Seeder::class, PolyglotDoubleNegativesAllLevelsLessonSeeder::class],
                'covered_seeders' => [DoubleNegativesAllLevelsV3Seeder::class, PolyglotDoubleNegativesAllLevelsLessonSeeder::class],
                'links_seeder' => DoubleNegativesTheoryLinksSeeder::class,
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

    private function assertAuditReportsQuestionsNegationsPartThreeRoutesAsOk(): void
    {
        $jsonPath = storage_path('framework/testing/questions-negations-part-3-theory-links-audit.json');
        $markdownPath = storage_path('framework/testing/questions-negations-part-3-theory-links-audit.md');
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
