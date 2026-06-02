<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Question;
use App\Models\SavedGrammarTest;
use App\Models\TextBlock;
use App\Services\SavedTestResolver;
use App\Services\TheoryPagePromptLinkedTestsService;
use Database\Seeders\Page_V3\PronounsDemonstratives\PronounsDemonstrativesCategorySeeder;
use Database\Seeders\Page_V3\PronounsDemonstratives\PronounsDemonstrativesPronounsTheorySeeder;
use Database\Seeders\Page_V3\PronounsDemonstratives\PronounsDemonstrativesPersonalObjectPronounsTheorySeeder;
use Database\Seeders\Page_V3\PronounsDemonstratives\PronounsDemonstrativesPossessiveAdjectivesVsPronounsTheorySeeder;
use Database\Seeders\Page_V3\PronounsDemonstratives\PronounsDemonstrativesThisThatTheseThoseTheorySeeder;
use Database\Seeders\V3\PronounsDemonstratives\PronounsTheoryAllLevelsV3Seeder;
use Database\Seeders\V3\PronounsDemonstratives\PersonalObjectPronounsAllLevelsV3Seeder;
use Database\Seeders\V3\PronounsDemonstratives\PossessiveAdjectivesVsPronounsAllLevelsV3Seeder;
use Database\Seeders\V3\PronounsDemonstratives\DemonstrativesThisThatTheseThoseAllLevelsV3Seeder;
use Database\Seeders\V3\PronounsDemonstrativesAllLevelsV3Seeder;
use Database\Seeders\V3\V2\IndefinitePronounsPracticeV3Seeder;
use Database\Seeders\V3\Polyglot\PolyglotPronounsTheoryAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotPersonalObjectPronounsAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotPossessiveAdjectivesVsPronounsAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotDemonstrativesThisThatTheseThoseAllLevelsLessonSeeder;
use Database\Seeders\V3\TheoryLinks\PronounsTheoryLinksSeeder;
use Database\Seeders\V3\TheoryLinks\PersonalObjectPronounsTheoryLinksSeeder;
use Database\Seeders\V3\TheoryLinks\PossessiveAdjectivesVsPronounsTheoryLinksSeeder;
use Database\Seeders\V3\TheoryLinks\DemonstrativesThisThatTheseThoseTheoryLinksSeeder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class PronounsDemonstrativesTheoryPageTestsSeedersTest extends TestCase
{
    use RebuildsComposeTestSchema;

    /**
     * @var array<class-string, int>
     */
    private array $currentCoveredSeederCounts = [];

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

    public function test_pronouns_demonstratives_pages_have_tests_and_theory_links(): void
    {
        $this->seedPronounsDemonstrativesStack();

        $this->seed([
            PronounsTheoryLinksSeeder::class,
            PersonalObjectPronounsTheoryLinksSeeder::class,
            PossessiveAdjectivesVsPronounsTheoryLinksSeeder::class,
            DemonstrativesThisThatTheseThoseTheoryLinksSeeder::class,
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

            $this->currentCoveredSeederCounts = $case['covered_seeder_counts'] ?? [];
            $this->assertEveryCoveredQuestionHasTheoryLinks($case['covered_seeders'], $caseName);
            $this->assertDirectSentenceBuilderQuestionsExposeTheoryBlocks($case['direct_slug'], $case['polyglot_seeder'], $caseName);
            $this->assertMixedResolvedQuestionsExposeTheoryBlocks($mixedTest, $case['mixed_filter_seeders'], $caseName);

            $questionUuids = Question::query()->whereIn('seeder', $case['covered_seeders'])->pluck('uuid');
            $firstRunPivotRows = DB::table('question_theory_text_blocks')->whereIn('question_uuid', $questionUuids)->count();
            $this->seed($case['links_seeder']);
            $this->assertSame($firstRunPivotRows, DB::table('question_theory_text_blocks')->whereIn('question_uuid', $questionUuids)->count(), $caseName . ': idempotency check failed.');
        }

        $this->assertAuditReportsPronounsDemonstrativesRoutesAsOk();
    }

    private function seedPronounsDemonstrativesStack(): void
    {
        $this->seed([
            PronounsDemonstrativesCategorySeeder::class,
            PronounsDemonstrativesPronounsTheorySeeder::class,
            PronounsDemonstrativesPersonalObjectPronounsTheorySeeder::class,
            PronounsDemonstrativesPossessiveAdjectivesVsPronounsTheorySeeder::class,
            PronounsDemonstrativesThisThatTheseThoseTheorySeeder::class,
            PronounsTheoryAllLevelsV3Seeder::class,
            PersonalObjectPronounsAllLevelsV3Seeder::class,
            PossessiveAdjectivesVsPronounsAllLevelsV3Seeder::class,
            DemonstrativesThisThatTheseThoseAllLevelsV3Seeder::class,
            PronounsDemonstrativesAllLevelsV3Seeder::class,
            IndefinitePronounsPracticeV3Seeder::class,
            PolyglotPronounsTheoryAllLevelsLessonSeeder::class,
            PolyglotPersonalObjectPronounsAllLevelsLessonSeeder::class,
            PolyglotPossessiveAdjectivesVsPronounsAllLevelsLessonSeeder::class,
            PolyglotDemonstrativesThisThatTheseThoseAllLevelsLessonSeeder::class,
        ]);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function cases(): array
    {
        return [
            'pronouns-theory' => [
                'route' => '/theory/zaimennyky-ta-vkazivni-slova/pronouns-theory',
                'page_slug' => 'pronouns-theory',
                'direct_slug' => 'polyglot-pronouns-theory-all-levels',
                'v3_seeder' => PronounsTheoryAllLevelsV3Seeder::class,
                'polyglot_seeder' => PolyglotPronounsTheoryAllLevelsLessonSeeder::class,
                'mixed_filter_seeders' => [PronounsTheoryAllLevelsV3Seeder::class, PolyglotPronounsTheoryAllLevelsLessonSeeder::class],
                'covered_seeders' => [PronounsTheoryAllLevelsV3Seeder::class, PolyglotPronounsTheoryAllLevelsLessonSeeder::class, PronounsDemonstrativesAllLevelsV3Seeder::class, IndefinitePronounsPracticeV3Seeder::class],
                'covered_seeder_counts' => [
                    PronounsTheoryAllLevelsV3Seeder::class => 72,
                    PolyglotPronounsTheoryAllLevelsLessonSeeder::class => 72,
                    PronounsDemonstrativesAllLevelsV3Seeder::class => 40,
                    IndefinitePronounsPracticeV3Seeder::class => 30,
                ],
                'links_seeder' => PronounsTheoryLinksSeeder::class,
            ],
            'personal-object-pronouns' => [
                'route' => '/theory/zaimennyky-ta-vkazivni-slova/personal-object-pronouns',
                'page_slug' => 'personal-object-pronouns',
                'direct_slug' => 'polyglot-personal-object-pronouns-all-levels',
                'v3_seeder' => PersonalObjectPronounsAllLevelsV3Seeder::class,
                'polyglot_seeder' => PolyglotPersonalObjectPronounsAllLevelsLessonSeeder::class,
                'mixed_filter_seeders' => [PersonalObjectPronounsAllLevelsV3Seeder::class, PolyglotPersonalObjectPronounsAllLevelsLessonSeeder::class],
                'covered_seeders' => [PersonalObjectPronounsAllLevelsV3Seeder::class, PolyglotPersonalObjectPronounsAllLevelsLessonSeeder::class],
                'links_seeder' => PersonalObjectPronounsTheoryLinksSeeder::class,
            ],
            'possessive-adjectives-vs-pronouns-my-mine-your-yours' => [
                'route' => '/theory/zaimennyky-ta-vkazivni-slova/possessive-adjectives-vs-pronouns-my-mine-your-yours',
                'page_slug' => 'possessive-adjectives-vs-pronouns-my-mine-your-yours',
                'direct_slug' => 'polyglot-possessive-adjectives-vs-pronouns-all-levels',
                'v3_seeder' => PossessiveAdjectivesVsPronounsAllLevelsV3Seeder::class,
                'polyglot_seeder' => PolyglotPossessiveAdjectivesVsPronounsAllLevelsLessonSeeder::class,
                'mixed_filter_seeders' => [PossessiveAdjectivesVsPronounsAllLevelsV3Seeder::class, PolyglotPossessiveAdjectivesVsPronounsAllLevelsLessonSeeder::class],
                'covered_seeders' => [PossessiveAdjectivesVsPronounsAllLevelsV3Seeder::class, PolyglotPossessiveAdjectivesVsPronounsAllLevelsLessonSeeder::class],
                'links_seeder' => PossessiveAdjectivesVsPronounsTheoryLinksSeeder::class,
            ],
            'demonstratives-this-that-these-those' => [
                'route' => '/theory/zaimennyky-ta-vkazivni-slova/demonstratives-this-that-these-those',
                'page_slug' => 'demonstratives-this-that-these-those',
                'direct_slug' => 'polyglot-demonstratives-this-that-these-those-all-levels',
                'v3_seeder' => DemonstrativesThisThatTheseThoseAllLevelsV3Seeder::class,
                'polyglot_seeder' => PolyglotDemonstrativesThisThatTheseThoseAllLevelsLessonSeeder::class,
                'mixed_filter_seeders' => [DemonstrativesThisThatTheseThoseAllLevelsV3Seeder::class, PolyglotDemonstrativesThisThatTheseThoseAllLevelsLessonSeeder::class],
                'covered_seeders' => [DemonstrativesThisThatTheseThoseAllLevelsV3Seeder::class, PolyglotDemonstrativesThisThatTheseThoseAllLevelsLessonSeeder::class],
                'links_seeder' => DemonstrativesThisThatTheseThoseTheoryLinksSeeder::class,
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
        $expectedCounts = $this->currentCoveredSeederCounts ?? [];
        foreach ($seederClasses as $seederClass) {
            $this->assertSame((int) ($expectedCounts[$seederClass] ?? 72), $questions->where('seeder', $seederClass)->count(), $caseName . ': ' . $seederClass);
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

    private function assertAuditReportsPronounsDemonstrativesRoutesAsOk(): void
    {
        $jsonPath = storage_path('framework/testing/pronouns-demonstratives-theory-links-audit.json');
        $markdownPath = storage_path('framework/testing/pronouns-demonstratives-theory-links-audit.md');
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
