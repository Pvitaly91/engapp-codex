<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Question;
use App\Models\SavedGrammarTest;
use App\Models\TextBlock;
use App\Services\SavedTestResolver;
use App\Services\TheoryPagePromptLinkedTestsService;
use Database\Seeders\Page_V3\PassiveVoice\Basics\PassiveVoiceCommonMistakesTheorySeeder;
use Database\Seeders\Page_V3\PassiveVoice\Basics\PassiveVoiceFormationRulesTheorySeeder;
use Database\Seeders\Page_V3\PassiveVoice\Basics\PassiveVoiceModalVerbsTheorySeeder;
use Database\Seeders\Page_V3\PassiveVoice\Basics\PassiveVoiceNegativesQuestionsTheorySeeder;
use Database\Seeders\Page_V3\PassiveVoice\PassiveVoiceCategorySeeder;
use Database\Seeders\V3\PassiveVoice\PassiveVoiceCommonMistakesAllLevelsV3Seeder;
use Database\Seeders\V3\PassiveVoice\PassiveVoiceFormationRulesAllLevelsV3Seeder;
use Database\Seeders\V3\PassiveVoice\PassiveVoiceModalVerbsAllLevelsV3Seeder;
use Database\Seeders\V3\PassiveVoice\PassiveVoiceNegativesQuestionsAllLevelsV3Seeder;
use Database\Seeders\V3\Polyglot\PolyglotPassiveVoiceBasicsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotPassiveVoiceCommonMistakesAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotPassiveVoiceFormationRulesAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotPassiveVoiceModalVerbsAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotPassiveVoiceWithModalsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotPassiveVoiceNegativesQuestionsAllLevelsLessonSeeder;
use Database\Seeders\V3\TheoryLinks\PassiveVoiceCommonMistakesTheoryLinksSeeder;
use Database\Seeders\V3\TheoryLinks\PassiveVoiceFormationRulesTheoryLinksSeeder;
use Database\Seeders\V3\TheoryLinks\PassiveVoiceModalVerbsTheoryLinksSeeder;
use Database\Seeders\V3\TheoryLinks\PassiveVoiceNegativesQuestionsTheoryLinksSeeder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class PassiveVoiceBasicsTheoryPageTestsSeedersTest extends TestCase
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

    public function test_passive_voice_basics_pages_have_tests_and_theory_links(): void
    {
        $this->seedPassiveVoiceBasicsStack();

        $this->seed([
            PassiveVoiceFormationRulesTheoryLinksSeeder::class,
            PassiveVoiceModalVerbsTheoryLinksSeeder::class,
            PassiveVoiceNegativesQuestionsTheoryLinksSeeder::class,
            PassiveVoiceCommonMistakesTheoryLinksSeeder::class,
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

        $this->assertAuditReportsPassiveVoiceBasicsRoutesAsOk();
    }

    private function seedPassiveVoiceBasicsStack(): void
    {
        $this->seed([
            PassiveVoiceCategorySeeder::class,
            PassiveVoiceFormationRulesTheorySeeder::class,
            PassiveVoiceModalVerbsTheorySeeder::class,
            PassiveVoiceNegativesQuestionsTheorySeeder::class,
            PassiveVoiceCommonMistakesTheorySeeder::class,
            PassiveVoiceFormationRulesAllLevelsV3Seeder::class,
            PassiveVoiceModalVerbsAllLevelsV3Seeder::class,
            PassiveVoiceNegativesQuestionsAllLevelsV3Seeder::class,
            PassiveVoiceCommonMistakesAllLevelsV3Seeder::class,
            PolyglotPassiveVoiceFormationRulesAllLevelsLessonSeeder::class,
            PolyglotPassiveVoiceBasicsLessonSeeder::class,
            PolyglotPassiveVoiceModalVerbsAllLevelsLessonSeeder::class,
            PolyglotPassiveVoiceWithModalsLessonSeeder::class,
            PolyglotPassiveVoiceNegativesQuestionsAllLevelsLessonSeeder::class,
            PolyglotPassiveVoiceCommonMistakesAllLevelsLessonSeeder::class,
        ]);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function cases(): array
    {
        return [
            'formation-rules' => [
                'route' => '/theory/passive-voice/theory-passive-voice-formation-rules',
                'page_slug' => 'theory-passive-voice-formation-rules',
                'direct_slug' => 'polyglot-passive-voice-formation-rules-all-levels',
                'v3_seeder' => PassiveVoiceFormationRulesAllLevelsV3Seeder::class,
                'polyglot_seeder' => PolyglotPassiveVoiceFormationRulesAllLevelsLessonSeeder::class,
                'mixed_filter_seeders' => [PassiveVoiceFormationRulesAllLevelsV3Seeder::class, PolyglotPassiveVoiceFormationRulesAllLevelsLessonSeeder::class],
                'covered_seeders' => [PassiveVoiceFormationRulesAllLevelsV3Seeder::class, PolyglotPassiveVoiceFormationRulesAllLevelsLessonSeeder::class, PolyglotPassiveVoiceBasicsLessonSeeder::class],
                'covered_question_counts' => [PolyglotPassiveVoiceBasicsLessonSeeder::class => 24],
                'links_seeder' => PassiveVoiceFormationRulesTheoryLinksSeeder::class,
            ],
            'modal-verbs' => [
                'route' => '/theory/passive-voice/theory-passive-voice-modal-verbs',
                'page_slug' => 'theory-passive-voice-modal-verbs',
                'direct_slug' => 'polyglot-passive-voice-modal-verbs-all-levels',
                'v3_seeder' => PassiveVoiceModalVerbsAllLevelsV3Seeder::class,
                'polyglot_seeder' => PolyglotPassiveVoiceModalVerbsAllLevelsLessonSeeder::class,
                'mixed_filter_seeders' => [PassiveVoiceModalVerbsAllLevelsV3Seeder::class, PolyglotPassiveVoiceModalVerbsAllLevelsLessonSeeder::class],
                'covered_seeders' => [PassiveVoiceModalVerbsAllLevelsV3Seeder::class, PolyglotPassiveVoiceModalVerbsAllLevelsLessonSeeder::class, PolyglotPassiveVoiceWithModalsLessonSeeder::class],
                'covered_question_counts' => [PolyglotPassiveVoiceWithModalsLessonSeeder::class => 24],
                'links_seeder' => PassiveVoiceModalVerbsTheoryLinksSeeder::class,
            ],
            'negatives-questions' => [
                'route' => '/theory/passive-voice/theory-passive-voice-negatives-questions',
                'page_slug' => 'theory-passive-voice-negatives-questions',
                'direct_slug' => 'polyglot-passive-voice-negatives-questions-all-levels',
                'v3_seeder' => PassiveVoiceNegativesQuestionsAllLevelsV3Seeder::class,
                'polyglot_seeder' => PolyglotPassiveVoiceNegativesQuestionsAllLevelsLessonSeeder::class,
                'mixed_filter_seeders' => [PassiveVoiceNegativesQuestionsAllLevelsV3Seeder::class, PolyglotPassiveVoiceNegativesQuestionsAllLevelsLessonSeeder::class],
                'covered_seeders' => [PassiveVoiceNegativesQuestionsAllLevelsV3Seeder::class, PolyglotPassiveVoiceNegativesQuestionsAllLevelsLessonSeeder::class],
                'links_seeder' => PassiveVoiceNegativesQuestionsTheoryLinksSeeder::class,
            ],
            'common-mistakes' => [
                'route' => '/theory/passive-voice/theory-passive-voice-common-mistakes',
                'page_slug' => 'theory-passive-voice-common-mistakes',
                'direct_slug' => 'polyglot-passive-voice-common-mistakes-all-levels',
                'v3_seeder' => PassiveVoiceCommonMistakesAllLevelsV3Seeder::class,
                'polyglot_seeder' => PolyglotPassiveVoiceCommonMistakesAllLevelsLessonSeeder::class,
                'mixed_filter_seeders' => [PassiveVoiceCommonMistakesAllLevelsV3Seeder::class, PolyglotPassiveVoiceCommonMistakesAllLevelsLessonSeeder::class],
                'covered_seeders' => [PassiveVoiceCommonMistakesAllLevelsV3Seeder::class, PolyglotPassiveVoiceCommonMistakesAllLevelsLessonSeeder::class],
                'links_seeder' => PassiveVoiceCommonMistakesTheoryLinksSeeder::class,
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

    private function assertAuditReportsPassiveVoiceBasicsRoutesAsOk(): void
    {
        $jsonPath = storage_path('framework/testing/passive-voice-basics-theory-links-audit.json');
        $markdownPath = storage_path('framework/testing/passive-voice-basics-theory-links-audit.md');
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
