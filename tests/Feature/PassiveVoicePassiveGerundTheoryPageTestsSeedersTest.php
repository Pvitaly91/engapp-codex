<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Question;
use App\Models\SavedGrammarTest;
use App\Models\TextBlock;
use App\Services\SavedTestResolver;
use App\Services\TheoryPagePromptLinkedTestsService;
use Database\Seeders\Page_V3\PassiveVoice\InfinitivesGerund\PassiveVoiceInfinitivesGerundCategorySeeder;
use Database\Seeders\Page_V3\PassiveVoice\InfinitivesGerund\PassiveVoicePassiveGerundTheorySeeder;
use Database\Seeders\Page_V3\PassiveVoice\PassiveVoiceCategorySeeder;
use Database\Seeders\V3\PassiveVoice\PassiveVoicePassiveGerundAllLevelsV3Seeder;
use Database\Seeders\V3\Polyglot\PolyglotPassiveVoicePassiveGerundAllLevelsLessonSeeder;
use Database\Seeders\V3\TheoryLinks\PassiveVoicePassiveGerundTheoryLinksSeeder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class PassiveVoicePassiveGerundTheoryPageTestsSeedersTest extends TestCase
{
    use RebuildsComposeTestSchema;

    private const LEVELS = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'];
    private const ROUTE = '/theory/passive-voice/passive-voice-infinitives-gerund/theory-passive-voice-passive-gerund';
    private const PAGE_SLUG = 'theory-passive-voice-passive-gerund';
    private const DIRECT_SLUG = 'polyglot-passive-voice-passive-gerund-all-levels';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'coming-soon.enabled' => false,
            'tests.tech_info_enabled' => false,
        ]);

        $this->rebuildComposeTestSchema();
    }

    public function test_passive_voice_passive_gerund_page_has_tests_and_theory_links(): void
    {
        $this->seedPassiveGerundStack();
        $this->seed(PassiveVoicePassiveGerundTheoryLinksSeeder::class);

        $page = Page::query()->where('slug', self::PAGE_SLUG)->firstOrFail();
        $directTest = SavedGrammarTest::query()->where('slug', self::DIRECT_SLUG)->first();

        $this->assertNotNull($directTest, 'direct Sentence Builder test exists.');
        $this->assertQuestionCountByLevel(PassiveVoicePassiveGerundAllLevelsV3Seeder::class, 'V3');
        $this->assertQuestionCountByLevel(PolyglotPassiveVoicePassiveGerundAllLevelsLessonSeeder::class, 'Polyglot');

        $tests = app(TheoryPagePromptLinkedTestsService::class)->buildForPage($page);
        $mixedTest = $tests->firstWhere('slug', 'theory-page-' . $page->id . '-mixed-a1-c2');

        $this->assertNotNull($mixedTest, 'Mixed A1-C2 test exists.');
        $this->assertTrue($mixedTest->isVirtual());

        $mixedSeederClasses = collect($mixedTest->filters['seeder_classes'] ?? [])->values();
        foreach ([PassiveVoicePassiveGerundAllLevelsV3Seeder::class, PolyglotPassiveVoicePassiveGerundAllLevelsLessonSeeder::class] as $expectedSeeder) {
            $this->assertTrue($mixedSeederClasses->contains($expectedSeeder), $expectedSeeder);
        }

        $coveredSeeders = [PassiveVoicePassiveGerundAllLevelsV3Seeder::class, PolyglotPassiveVoicePassiveGerundAllLevelsLessonSeeder::class];
        $this->assertEveryCoveredQuestionHasTheoryLinks($coveredSeeders);
        $this->assertDirectSentenceBuilderQuestionsExposeTheoryBlocks();
        $this->assertMixedResolvedQuestionsExposeTheoryBlocks($mixedTest, $coveredSeeders);

        $questionUuids = Question::query()->whereIn('seeder', $coveredSeeders)->pluck('uuid');
        $firstRunPivotRows = DB::table('question_theory_text_blocks')->whereIn('question_uuid', $questionUuids)->count();
        $this->seed(PassiveVoicePassiveGerundTheoryLinksSeeder::class);
        $this->assertSame($firstRunPivotRows, DB::table('question_theory_text_blocks')->whereIn('question_uuid', $questionUuids)->count(), 'idempotency check failed.');

        $this->assertAuditReportsPassiveGerundRouteAsOk();
    }

    private function seedPassiveGerundStack(): void
    {
        $this->seed([
            PassiveVoiceCategorySeeder::class,
            PassiveVoiceInfinitivesGerundCategorySeeder::class,
            PassiveVoicePassiveGerundTheorySeeder::class,
            PassiveVoicePassiveGerundAllLevelsV3Seeder::class,
            PolyglotPassiveVoicePassiveGerundAllLevelsLessonSeeder::class,
        ]);
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
    private function assertEveryCoveredQuestionHasTheoryLinks(array $seederClasses): void
    {
        $questions = Question::query()->whereIn('seeder', $seederClasses)->get(['id', 'uuid', 'seeder', 'theory_text_block_uuid']);
        foreach ($seederClasses as $seederClass) {
            $this->assertSame(72, $questions->where('seeder', $seederClass)->count(), $seederClass);
        }
        $pivotRows = DB::table('question_theory_text_blocks')->whereIn('question_uuid', $questions->pluck('uuid'))->get();
        $this->assertSame($questions->count(), $pivotRows->pluck('question_uuid')->unique()->count(), 'every covered question should have pivot rows.');
        $this->assertSame($questions->count(), $questions->whereNotNull('theory_text_block_uuid')->count(), 'every covered question should set legacy theory_text_block_uuid.');
        $linkedBlockUuids = $pivotRows->pluck('text_block_uuid')->unique()->values();
        $this->assertSame($linkedBlockUuids->count(), TextBlock::query()->whereIn('uuid', $linkedBlockUuids->all())->count(), 'every linked text_block_uuid should exist.');
        $legacyBlockUuids = $questions->pluck('theory_text_block_uuid')->filter()->unique()->values();
        $this->assertSame($legacyBlockUuids->count(), TextBlock::query()->whereIn('uuid', $legacyBlockUuids->all())->count(), 'every legacy text_block_uuid should exist.');
    }

    private function assertDirectSentenceBuilderQuestionsExposeTheoryBlocks(): void
    {
        $resolved = app(SavedTestResolver::class)->resolve(self::DIRECT_SLUG);
        $questions = app(SavedTestResolver::class)->loadQuestions($resolved, ['theoryTextBlocks']);
        $this->assertSame(72, $questions->count(), 'direct Sentence Builder questions.');
        $this->assertTrue($questions->every(fn (Question $question): bool => (string) $question->seeder === PolyglotPassiveVoicePassiveGerundAllLevelsLessonSeeder::class), 'direct Sentence Builder should use only page-local Polyglot seeder.');
        $this->assertTrue($questions->every(fn (Question $question): bool => (string) $question->type === Question::TYPE_COMPOSE_TOKENS), 'direct Sentence Builder questions should be compose mode.');
        $this->assertTrue($questions->every(fn (Question $question): bool => $question->theoryTextBlocks->isNotEmpty()), 'direct Sentence Builder questions should expose theory blocks.');
    }

    /**
     * @param array<int, class-string> $coveredSeeders
     */
    private function assertMixedResolvedQuestionsExposeTheoryBlocks(object $mixedTest, array $coveredSeeders): void
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
        $this->assertSame(84, $questions->count(), 'mixed resolved questions.');
        foreach ($coveredSeeders as $coveredSeeder) {
            $this->assertTrue($questions->pluck('seeder')->contains($coveredSeeder), 'mixed resolved questions should include ' . $coveredSeeder);
        }
        $this->assertTrue($questions->contains(fn (Question $question): bool => (string) $question->type === Question::TYPE_COMPOSE_TOKENS), 'mixed test should include compose questions.');
        $this->assertTrue($questions->contains(fn (Question $question): bool => (string) $question->type !== Question::TYPE_COMPOSE_TOKENS), 'mixed test should include standard V3 questions.');
        $this->assertTrue($questions->every(fn (Question $question): bool => $question->theoryTextBlocks->isNotEmpty()), 'mixed questions should expose theory blocks.');
    }

    private function assertAuditReportsPassiveGerundRouteAsOk(): void
    {
        $jsonPath = storage_path('framework/testing/passive-voice-passive-gerund-theory-links-audit.json');
        $markdownPath = storage_path('framework/testing/passive-voice-passive-gerund-theory-links-audit.md');
        File::delete([$jsonPath, $markdownPath]);
        $this->artisan('theory-pages:audit-tests-unification', ['--json' => $jsonPath, '--md' => $markdownPath])->assertExitCode(0);
        $audit = json_decode((string) File::get($jsonPath), true);
        $this->assertIsArray($audit);
        $row = collect($audit['pages'] ?? [])->firstWhere('route', self::ROUTE);
        $this->assertNotNull($row, 'route should be present in audit.');
        $this->assertSame('OK', $row['status'] ?? null, 'audit status.');
        foreach (['sentence_builder', 'mixed_a1_c2', 'v3_questions', 'polyglot_questions', 'theory_links', 'polyglot_theory_links', 'question_theory_text_blocks'] as $missingKey) {
            $this->assertNotContains($missingKey, $row['missing'] ?? [], 'missing ' . $missingKey);
        }
    }
}
