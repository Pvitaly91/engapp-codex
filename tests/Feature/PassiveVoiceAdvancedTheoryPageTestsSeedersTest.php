<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Question;
use App\Models\SavedGrammarTest;
use App\Models\TextBlock;
use App\Services\SavedTestResolver;
use App\Services\TheoryPagePromptLinkedTestsService;
use Database\Seeders\Page_V3\PassiveVoice\AdvancedPassiveVoiceTheorySeeder;
use Database\Seeders\Page_V3\PassiveVoice\ComplexPassiveAndCausativeTheorySeeder;
use Database\Seeders\Page_V3\PassiveVoice\ComplexPassiveImpersonalStyleTheorySeeder;
use Database\Seeders\Page_V3\PassiveVoice\InfinitivesGerund\PassiveVoiceInfinitivesGerundCategorySeeder;
use Database\Seeders\Page_V3\PassiveVoice\InfinitivesGerund\PassiveVoicePassiveInfinitiveTheorySeeder;
use Database\Seeders\Page_V3\PassiveVoice\PassiveVoiceCategorySeeder;
use Database\Seeders\V3\PassiveVoice\AdvancedPassiveVoiceAllLevelsV3Seeder;
use Database\Seeders\V3\PassiveVoice\ComplexPassiveAndCausativeAllLevelsV3Seeder;
use Database\Seeders\V3\PassiveVoice\ComplexPassiveImpersonalStyleAllLevelsV3Seeder;
use Database\Seeders\V3\PassiveVoice\PassiveVoicePassiveInfinitiveAllLevelsV3Seeder;
use Database\Seeders\V3\Polyglot\PolyglotAdvancedPassiveVoiceAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotAdvancedPassiveVoiceB2LessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotComplexPassiveAndCausativeAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotComplexPassiveAndCausativeC1LessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotComplexPassiveImpersonalStyleAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotComplexPassiveImpersonalStyleC2LessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotPassiveVoicePassiveInfinitiveAllLevelsLessonSeeder;
use Database\Seeders\V3\TheoryLinks\AdvancedPassiveVoiceTheoryLinksSeeder;
use Database\Seeders\V3\TheoryLinks\ComplexPassiveAndCausativeTheoryLinksSeeder;
use Database\Seeders\V3\TheoryLinks\ComplexPassiveImpersonalStyleTheoryLinksSeeder;
use Database\Seeders\V3\TheoryLinks\PassiveVoicePassiveInfinitiveTheoryLinksSeeder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class PassiveVoiceAdvancedTheoryPageTestsSeedersTest extends TestCase
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

    public function test_passive_voice_advanced_pages_have_tests_and_theory_links(): void
    {
        $this->seedPassiveVoiceAdvancedStack();

        $this->seed([
            AdvancedPassiveVoiceTheoryLinksSeeder::class,
            ComplexPassiveAndCausativeTheoryLinksSeeder::class,
            ComplexPassiveImpersonalStyleTheoryLinksSeeder::class,
            PassiveVoicePassiveInfinitiveTheoryLinksSeeder::class,
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

        $this->assertAuditReportsPassiveVoiceAdvancedRoutesAsOk();
    }

    private function seedPassiveVoiceAdvancedStack(): void
    {
        $this->seed([
            PassiveVoiceCategorySeeder::class,
            PassiveVoiceInfinitivesGerundCategorySeeder::class,
            AdvancedPassiveVoiceTheorySeeder::class,
            ComplexPassiveAndCausativeTheorySeeder::class,
            ComplexPassiveImpersonalStyleTheorySeeder::class,
            PassiveVoicePassiveInfinitiveTheorySeeder::class,
            AdvancedPassiveVoiceAllLevelsV3Seeder::class,
            ComplexPassiveAndCausativeAllLevelsV3Seeder::class,
            ComplexPassiveImpersonalStyleAllLevelsV3Seeder::class,
            PassiveVoicePassiveInfinitiveAllLevelsV3Seeder::class,
            PolyglotAdvancedPassiveVoiceAllLevelsLessonSeeder::class,
            PolyglotAdvancedPassiveVoiceB2LessonSeeder::class,
            PolyglotComplexPassiveAndCausativeAllLevelsLessonSeeder::class,
            PolyglotComplexPassiveAndCausativeC1LessonSeeder::class,
            PolyglotComplexPassiveImpersonalStyleAllLevelsLessonSeeder::class,
            PolyglotComplexPassiveImpersonalStyleC2LessonSeeder::class,
            PolyglotPassiveVoicePassiveInfinitiveAllLevelsLessonSeeder::class,
        ]);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function cases(): array
    {
        return [
            'advanced-passive-voice' => [
                'route' => '/theory/passive-voice/advanced-passive-voice',
                'page_slug' => 'advanced-passive-voice',
                'direct_slug' => 'polyglot-advanced-passive-voice-all-levels',
                'v3_seeder' => AdvancedPassiveVoiceAllLevelsV3Seeder::class,
                'polyglot_seeder' => PolyglotAdvancedPassiveVoiceAllLevelsLessonSeeder::class,
                'mixed_filter_seeders' => [AdvancedPassiveVoiceAllLevelsV3Seeder::class, PolyglotAdvancedPassiveVoiceAllLevelsLessonSeeder::class],
                'covered_seeders' => [AdvancedPassiveVoiceAllLevelsV3Seeder::class, PolyglotAdvancedPassiveVoiceAllLevelsLessonSeeder::class, PolyglotAdvancedPassiveVoiceB2LessonSeeder::class],
                'covered_question_counts' => [PolyglotAdvancedPassiveVoiceB2LessonSeeder::class => 48],
                'links_seeder' => AdvancedPassiveVoiceTheoryLinksSeeder::class,
            ],
            'complex-passive-and-causative' => [
                'route' => '/theory/passive-voice/complex-passive-and-causative',
                'page_slug' => 'complex-passive-and-causative',
                'direct_slug' => 'polyglot-complex-passive-and-causative-all-levels',
                'v3_seeder' => ComplexPassiveAndCausativeAllLevelsV3Seeder::class,
                'polyglot_seeder' => PolyglotComplexPassiveAndCausativeAllLevelsLessonSeeder::class,
                'mixed_filter_seeders' => [ComplexPassiveAndCausativeAllLevelsV3Seeder::class, PolyglotComplexPassiveAndCausativeAllLevelsLessonSeeder::class],
                'covered_seeders' => [ComplexPassiveAndCausativeAllLevelsV3Seeder::class, PolyglotComplexPassiveAndCausativeAllLevelsLessonSeeder::class, PolyglotComplexPassiveAndCausativeC1LessonSeeder::class],
                'covered_question_counts' => [PolyglotComplexPassiveAndCausativeC1LessonSeeder::class => 48],
                'links_seeder' => ComplexPassiveAndCausativeTheoryLinksSeeder::class,
            ],
            'complex-passive-impersonal-style' => [
                'route' => '/theory/passive-voice/complex-passive-impersonal-style',
                'page_slug' => 'complex-passive-impersonal-style',
                'direct_slug' => 'polyglot-complex-passive-impersonal-style-all-levels',
                'v3_seeder' => ComplexPassiveImpersonalStyleAllLevelsV3Seeder::class,
                'polyglot_seeder' => PolyglotComplexPassiveImpersonalStyleAllLevelsLessonSeeder::class,
                'mixed_filter_seeders' => [ComplexPassiveImpersonalStyleAllLevelsV3Seeder::class, PolyglotComplexPassiveImpersonalStyleAllLevelsLessonSeeder::class],
                'covered_seeders' => [ComplexPassiveImpersonalStyleAllLevelsV3Seeder::class, PolyglotComplexPassiveImpersonalStyleAllLevelsLessonSeeder::class, PolyglotComplexPassiveImpersonalStyleC2LessonSeeder::class],
                'covered_question_counts' => [PolyglotComplexPassiveImpersonalStyleC2LessonSeeder::class => 48],
                'links_seeder' => ComplexPassiveImpersonalStyleTheoryLinksSeeder::class,
            ],
            'passive-infinitive' => [
                'route' => '/theory/passive-voice/passive-voice-infinitives-gerund/theory-passive-voice-passive-infinitive',
                'page_slug' => 'theory-passive-voice-passive-infinitive',
                'direct_slug' => 'polyglot-passive-voice-passive-infinitive-all-levels',
                'v3_seeder' => PassiveVoicePassiveInfinitiveAllLevelsV3Seeder::class,
                'polyglot_seeder' => PolyglotPassiveVoicePassiveInfinitiveAllLevelsLessonSeeder::class,
                'mixed_filter_seeders' => [PassiveVoicePassiveInfinitiveAllLevelsV3Seeder::class, PolyglotPassiveVoicePassiveInfinitiveAllLevelsLessonSeeder::class],
                'covered_seeders' => [PassiveVoicePassiveInfinitiveAllLevelsV3Seeder::class, PolyglotPassiveVoicePassiveInfinitiveAllLevelsLessonSeeder::class],
                'links_seeder' => PassiveVoicePassiveInfinitiveTheoryLinksSeeder::class,
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

    private function assertAuditReportsPassiveVoiceAdvancedRoutesAsOk(): void
    {
        $jsonPath = storage_path('framework/testing/passive-voice-advanced-theory-links-audit.json');
        $markdownPath = storage_path('framework/testing/passive-voice-advanced-theory-links-audit.md');
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
