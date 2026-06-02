<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Question;
use App\Models\SavedGrammarTest;
use App\Models\TextBlock;
use App\Services\SavedTestResolver;
use App\Services\TheoryPagePromptLinkedTestsService;
use Database\Seeders\Page_V3\VocabularyAndCollocations\AdvancedCollocationAndLexicalChoiceTheorySeeder;
use Database\Seeders\V3\Polyglot\PolyglotAdvancedCollocationAndLexicalChoiceAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotAdvancedCollocationAndLexicalChoiceC2LessonSeeder;
use Database\Seeders\V3\TheoryLinks\AdvancedCollocationAndLexicalChoiceTheoryLinksSeeder;
use Database\Seeders\V3\VocabularyAndCollocations\AdvancedCollocationAndLexicalChoiceAllLevelsV3Seeder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class VocabularyCollocationsTheoryPageTestsSeedersTest extends TestCase
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

    public function test_advanced_collocation_page_has_tests_and_theory_links(): void
    {
        $this->seedVocabularyCollocationsStack();

        $this->seed(AdvancedCollocationAndLexicalChoiceTheoryLinksSeeder::class);

        $page = Page::query()->where('slug', 'advanced-collocation-and-lexical-choice')->firstOrFail();
        $directTest = SavedGrammarTest::query()->where('slug', 'polyglot-advanced-collocation-and-lexical-choice-all-levels')->first();

        $this->assertNotNull($directTest, 'Direct Sentence Builder test exists.');
        $this->assertQuestionCountByLevel(AdvancedCollocationAndLexicalChoiceAllLevelsV3Seeder::class, 'V3');
        $this->assertQuestionCountByLevel(PolyglotAdvancedCollocationAndLexicalChoiceAllLevelsLessonSeeder::class, 'Polyglot');

        $tests = app(TheoryPagePromptLinkedTestsService::class)->buildForPage($page);
        $mixedTest = $tests->firstWhere('slug', 'theory-page-' . $page->id . '-mixed-a1-c2');

        $this->assertNotNull($mixedTest, 'Mixed A1-C2 test exists.');
        $this->assertTrue($mixedTest->isVirtual());

        $mixedSeederClasses = collect($mixedTest->filters['seeder_classes'] ?? [])->values();
        foreach ($this->mixedFilterSeeders() as $expectedSeeder) {
            $this->assertTrue($mixedSeederClasses->contains($expectedSeeder), $expectedSeeder);
        }

        $this->assertEveryCoveredQuestionHasTheoryLinks($this->coveredSeeders());
        $this->assertDirectSentenceBuilderQuestionsExposeTheoryBlocks();
        $this->assertMixedResolvedQuestionsExposeTheoryBlocks($mixedTest);

        $questionUuids = Question::query()->whereIn('seeder', $this->coveredSeeders())->pluck('uuid');
        $firstRunPivotRows = DB::table('question_theory_text_blocks')->whereIn('question_uuid', $questionUuids)->count();
        $this->seed(AdvancedCollocationAndLexicalChoiceTheoryLinksSeeder::class);
        $this->assertSame($firstRunPivotRows, DB::table('question_theory_text_blocks')->whereIn('question_uuid', $questionUuids)->count(), 'Theory link seeder should be idempotent.');

        $this->assertAuditReportsAdvancedCollocationRouteAsOk();
    }

    private function seedVocabularyCollocationsStack(): void
    {
        $this->seed([
            AdvancedCollocationAndLexicalChoiceTheorySeeder::class,
            AdvancedCollocationAndLexicalChoiceAllLevelsV3Seeder::class,
            PolyglotAdvancedCollocationAndLexicalChoiceAllLevelsLessonSeeder::class,
            PolyglotAdvancedCollocationAndLexicalChoiceC2LessonSeeder::class,
        ]);
    }

    /**
     * @return array<int, class-string>
     */
    private function mixedFilterSeeders(): array
    {
        return [
            AdvancedCollocationAndLexicalChoiceAllLevelsV3Seeder::class,
            PolyglotAdvancedCollocationAndLexicalChoiceAllLevelsLessonSeeder::class,
        ];
    }

    /**
     * @return array<int, class-string>
     */
    private function coveredSeeders(): array
    {
        return [
            AdvancedCollocationAndLexicalChoiceAllLevelsV3Seeder::class,
            PolyglotAdvancedCollocationAndLexicalChoiceAllLevelsLessonSeeder::class,
            PolyglotAdvancedCollocationAndLexicalChoiceC2LessonSeeder::class,
        ];
    }

    private function assertQuestionCountByLevel(string $seederClass, string $label): void
    {
        $questions = Question::query()->where('seeder', $seederClass)->get(['level']);
        $this->assertSame(72, $questions->count(), $label . ': total question count.');

        $counts = $questions->countBy('level');
        foreach (self::LEVELS as $level) {
            $this->assertSame(12, (int) $counts->get($level, 0), $label . ': ' . $level . ' count.');
        }
    }

    /**
     * @param array<int, class-string> $seederClasses
     */
    private function assertEveryCoveredQuestionHasTheoryLinks(array $seederClasses): void
    {
        $questions = Question::query()->whereIn('seeder', $seederClasses)->get(['id', 'uuid', 'seeder', 'theory_text_block_uuid']);

        $this->assertSame(72, $questions->where('seeder', AdvancedCollocationAndLexicalChoiceAllLevelsV3Seeder::class)->count(), 'V3 covered count.');
        $this->assertSame(72, $questions->where('seeder', PolyglotAdvancedCollocationAndLexicalChoiceAllLevelsLessonSeeder::class)->count(), 'Polyglot covered count.');
        $this->assertSame(48, $questions->where('seeder', PolyglotAdvancedCollocationAndLexicalChoiceC2LessonSeeder::class)->count(), 'Legacy C2 covered count.');

        $pivotRows = DB::table('question_theory_text_blocks')->whereIn('question_uuid', $questions->pluck('uuid'))->get();

        $this->assertSame($questions->count(), $pivotRows->pluck('question_uuid')->unique()->count(), 'Every covered question should have pivot rows.');
        $this->assertSame($questions->count(), $questions->whereNotNull('theory_text_block_uuid')->count(), 'Every covered question should set theory_text_block_uuid.');

        $linkedBlockUuids = $pivotRows->pluck('text_block_uuid')->unique()->values();
        $this->assertSame($linkedBlockUuids->count(), TextBlock::query()->whereIn('uuid', $linkedBlockUuids->all())->count(), 'Every linked text_block_uuid should exist.');

        $legacyBlockUuids = $questions->pluck('theory_text_block_uuid')->filter()->unique()->values();
        $this->assertSame($legacyBlockUuids->count(), TextBlock::query()->whereIn('uuid', $legacyBlockUuids->all())->count(), 'Every legacy text_block_uuid should exist.');
    }

    private function assertDirectSentenceBuilderQuestionsExposeTheoryBlocks(): void
    {
        $resolved = app(SavedTestResolver::class)->resolve('polyglot-advanced-collocation-and-lexical-choice-all-levels');
        $questions = app(SavedTestResolver::class)->loadQuestions($resolved, ['theoryTextBlocks']);

        $this->assertSame(72, $questions->count(), 'Direct Sentence Builder questions.');
        $this->assertTrue($questions->every(fn (Question $question): bool => (string) $question->seeder === PolyglotAdvancedCollocationAndLexicalChoiceAllLevelsLessonSeeder::class), 'Direct Sentence Builder should use only the all-levels Polyglot seeder.');
        $this->assertTrue($questions->every(fn (Question $question): bool => (string) $question->type === Question::TYPE_COMPOSE_TOKENS), 'Direct Sentence Builder questions should be compose mode.');
        $this->assertTrue($questions->every(fn (Question $question): bool => $question->theoryTextBlocks->isNotEmpty()), 'Direct Sentence Builder questions should expose theory blocks.');
    }

    private function assertMixedResolvedQuestionsExposeTheoryBlocks(object $mixedTest): void
    {
        $filters = $mixedTest->filters ?? [];
        $filters['num_questions'] = 84;
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

        $this->assertSame(84, $questions->count(), 'Mixed resolved questions.');
        foreach ($this->mixedFilterSeeders() as $coveredSeeder) {
            $this->assertTrue($questions->pluck('seeder')->contains($coveredSeeder), 'Mixed resolved questions should include ' . $coveredSeeder);
        }

        $this->assertTrue($questions->contains(fn (Question $question): bool => (string) $question->type === Question::TYPE_COMPOSE_TOKENS), 'Mixed test should include compose questions.');
        $this->assertTrue($questions->contains(fn (Question $question): bool => (string) $question->type !== Question::TYPE_COMPOSE_TOKENS), 'Mixed test should include standard V3 questions.');
        $this->assertTrue($questions->every(fn (Question $question): bool => $question->theoryTextBlocks->isNotEmpty()), 'Mixed questions should expose theory blocks.');
    }

    private function assertAuditReportsAdvancedCollocationRouteAsOk(): void
    {
        $jsonPath = storage_path('framework/testing/vocabulary-collocations-theory-links-audit.json');
        $markdownPath = storage_path('framework/testing/vocabulary-collocations-theory-links-audit.md');

        File::delete([$jsonPath, $markdownPath]);

        $this->artisan('theory-pages:audit-tests-unification', ['--json' => $jsonPath, '--md' => $markdownPath])->assertExitCode(0);

        $audit = json_decode((string) File::get($jsonPath), true);
        $this->assertIsArray($audit);

        $row = collect($audit['pages'] ?? [])->keyBy('route')->get('/theory/vocabulary-and-collocations/advanced-collocation-and-lexical-choice');

        $this->assertNotNull($row, 'Route should be present in audit.');
        $this->assertSame('OK', $row['status'] ?? null, 'Audit status.');
        $this->assertEmpty(collect($row['missing'] ?? [])->intersect([
            'sentence_builder',
            'mixed_a1_c2',
            'v3_questions',
            'polyglot_questions',
            'theory_links',
            'polyglot_theory_links',
            'question_theory_text_blocks',
        ])->all(), 'Audit should not report missing items.');
    }
}
