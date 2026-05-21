<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Question;
use App\Models\SavedGrammarTest;
use App\Models\TextBlock;
use App\Services\SavedTestResolver;
use App\Services\TheoryPagePromptLinkedTestsService;
use Database\Seeders\Page_V3\PassiveVoice\PassiveVoiceCategorySeeder;
use Database\Seeders\Page_V3\PassiveVoice\Tenses\PassiveVoiceFuturePerfectTheorySeeder;
use Database\Seeders\Page_V3\PassiveVoice\Tenses\PassiveVoicePastContinuousTheorySeeder;
use Database\Seeders\Page_V3\PassiveVoice\Tenses\PassiveVoicePastPerfectTheorySeeder;
use Database\Seeders\Page_V3\PassiveVoice\Tenses\PassiveVoicePresentContinuousTheorySeeder;
use Database\Seeders\Page_V3\PassiveVoice\Tenses\PassiveVoiceTensesCategorySeeder;
use Database\Seeders\V3\PassiveVoice\PassiveVoiceFuturePerfectAllLevelsV3Seeder;
use Database\Seeders\V3\PassiveVoice\PassiveVoicePastContinuousAllLevelsV3Seeder;
use Database\Seeders\V3\PassiveVoice\PassiveVoicePastPerfectAllLevelsV3Seeder;
use Database\Seeders\V3\PassiveVoice\PassiveVoicePresentContinuousAllLevelsV3Seeder;
use Database\Seeders\V3\Polyglot\PolyglotPassiveVoiceFuturePerfectAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotPassiveVoicePastContinuousAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotPassiveVoicePastPerfectAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotPassiveVoicePresentContinuousAllLevelsLessonSeeder;
use Database\Seeders\V3\TheoryLinks\PassiveVoiceFuturePerfectTheoryLinksSeeder;
use Database\Seeders\V3\TheoryLinks\PassiveVoicePastContinuousTheoryLinksSeeder;
use Database\Seeders\V3\TheoryLinks\PassiveVoicePastPerfectTheoryLinksSeeder;
use Database\Seeders\V3\TheoryLinks\PassiveVoicePresentContinuousTheoryLinksSeeder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class PassiveVoiceTensesPart2TheoryPageTestsSeedersTest extends TestCase
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

    public function test_passive_voice_tenses_part_two_pages_have_tests_and_theory_links(): void
    {
        $this->seedPassiveVoiceTensesPartTwoStack();

        $this->seed([
            PassiveVoicePresentContinuousTheoryLinksSeeder::class,
            PassiveVoicePastContinuousTheoryLinksSeeder::class,
            PassiveVoicePastPerfectTheoryLinksSeeder::class,
            PassiveVoiceFuturePerfectTheoryLinksSeeder::class,
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

        $this->assertAuditReportsPassiveVoiceTensesPartTwoRoutesAsOk();
    }

    private function seedPassiveVoiceTensesPartTwoStack(): void
    {
        $this->seed([
            PassiveVoiceCategorySeeder::class,
            PassiveVoiceTensesCategorySeeder::class,
            PassiveVoicePresentContinuousTheorySeeder::class,
            PassiveVoicePastContinuousTheorySeeder::class,
            PassiveVoicePastPerfectTheorySeeder::class,
            PassiveVoiceFuturePerfectTheorySeeder::class,
            PassiveVoicePresentContinuousAllLevelsV3Seeder::class,
            PassiveVoicePastContinuousAllLevelsV3Seeder::class,
            PassiveVoicePastPerfectAllLevelsV3Seeder::class,
            PassiveVoiceFuturePerfectAllLevelsV3Seeder::class,
            PolyglotPassiveVoicePresentContinuousAllLevelsLessonSeeder::class,
            PolyglotPassiveVoicePastContinuousAllLevelsLessonSeeder::class,
            PolyglotPassiveVoicePastPerfectAllLevelsLessonSeeder::class,
            PolyglotPassiveVoiceFuturePerfectAllLevelsLessonSeeder::class,
        ]);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function cases(): array
    {
        return [
            'present-continuous-passive' => [
                'route' => '/theory/passive-voice/passive-voice-tenses/theory-passive-voice-present-continuous',
                'page_slug' => 'theory-passive-voice-present-continuous',
                'direct_slug' => 'polyglot-passive-voice-present-continuous-all-levels',
                'v3_seeder' => PassiveVoicePresentContinuousAllLevelsV3Seeder::class,
                'polyglot_seeder' => PolyglotPassiveVoicePresentContinuousAllLevelsLessonSeeder::class,
                'mixed_filter_seeders' => [PassiveVoicePresentContinuousAllLevelsV3Seeder::class, PolyglotPassiveVoicePresentContinuousAllLevelsLessonSeeder::class],
                'covered_seeders' => [PassiveVoicePresentContinuousAllLevelsV3Seeder::class, PolyglotPassiveVoicePresentContinuousAllLevelsLessonSeeder::class],
                'links_seeder' => PassiveVoicePresentContinuousTheoryLinksSeeder::class,
            ],
            'past-continuous-passive' => [
                'route' => '/theory/passive-voice/passive-voice-tenses/theory-passive-voice-past-continuous',
                'page_slug' => 'theory-passive-voice-past-continuous',
                'direct_slug' => 'polyglot-passive-voice-past-continuous-all-levels',
                'v3_seeder' => PassiveVoicePastContinuousAllLevelsV3Seeder::class,
                'polyglot_seeder' => PolyglotPassiveVoicePastContinuousAllLevelsLessonSeeder::class,
                'mixed_filter_seeders' => [PassiveVoicePastContinuousAllLevelsV3Seeder::class, PolyglotPassiveVoicePastContinuousAllLevelsLessonSeeder::class],
                'covered_seeders' => [PassiveVoicePastContinuousAllLevelsV3Seeder::class, PolyglotPassiveVoicePastContinuousAllLevelsLessonSeeder::class],
                'links_seeder' => PassiveVoicePastContinuousTheoryLinksSeeder::class,
            ],
            'past-perfect-passive' => [
                'route' => '/theory/passive-voice/passive-voice-tenses/theory-passive-voice-past-perfect',
                'page_slug' => 'theory-passive-voice-past-perfect',
                'direct_slug' => 'polyglot-passive-voice-past-perfect-all-levels',
                'v3_seeder' => PassiveVoicePastPerfectAllLevelsV3Seeder::class,
                'polyglot_seeder' => PolyglotPassiveVoicePastPerfectAllLevelsLessonSeeder::class,
                'mixed_filter_seeders' => [PassiveVoicePastPerfectAllLevelsV3Seeder::class, PolyglotPassiveVoicePastPerfectAllLevelsLessonSeeder::class],
                'covered_seeders' => [PassiveVoicePastPerfectAllLevelsV3Seeder::class, PolyglotPassiveVoicePastPerfectAllLevelsLessonSeeder::class],
                'links_seeder' => PassiveVoicePastPerfectTheoryLinksSeeder::class,
            ],
            'future-perfect-passive' => [
                'route' => '/theory/passive-voice/passive-voice-tenses/theory-passive-voice-future-perfect',
                'page_slug' => 'theory-passive-voice-future-perfect',
                'direct_slug' => 'polyglot-passive-voice-future-perfect-all-levels',
                'v3_seeder' => PassiveVoiceFuturePerfectAllLevelsV3Seeder::class,
                'polyglot_seeder' => PolyglotPassiveVoiceFuturePerfectAllLevelsLessonSeeder::class,
                'mixed_filter_seeders' => [PassiveVoiceFuturePerfectAllLevelsV3Seeder::class, PolyglotPassiveVoiceFuturePerfectAllLevelsLessonSeeder::class],
                'covered_seeders' => [PassiveVoiceFuturePerfectAllLevelsV3Seeder::class, PolyglotPassiveVoiceFuturePerfectAllLevelsLessonSeeder::class],
                'links_seeder' => PassiveVoiceFuturePerfectTheoryLinksSeeder::class,
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

    private function assertAuditReportsPassiveVoiceTensesPartTwoRoutesAsOk(): void
    {
        $jsonPath = storage_path('framework/testing/passive-voice-tenses-part-2-theory-links-audit.json');
        $markdownPath = storage_path('framework/testing/passive-voice-tenses-part-2-theory-links-audit.md');
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
