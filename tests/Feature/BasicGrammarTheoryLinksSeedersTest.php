<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Question;
use App\Models\TextBlock;
use App\Services\TheoryPagePromptLinkedTestsService;
use Database\Seeders\Page_V3\BasicGrammar\BasicGrammarCategorySeeder;
use Database\Seeders\Page_V3\BasicGrammar\BasicGrammarConjunctionsTheorySeeder;
use Database\Seeders\Page_V3\BasicGrammar\BasicGrammarImperativesTheorySeeder;
use Database\Seeders\Page_V3\BasicGrammar\BasicGrammarSentenceStructureSvoTheorySeeder;
use Database\Seeders\Page_V3\BasicGrammar\BasicGrammarSentenceTypesTheorySeeder;
use Database\Seeders\V3\AI\Copilot\Opus46\ConjunctionsAndButOrBecauseSoV3QuestionsOnlySeeder as Opus46ConjunctionsSeeder;
use Database\Seeders\V3\AI\Copilot\Opus46\ImperativesNakazoviRecenniaV3QuestionsOnlySeeder as Opus46ImperativesSeeder;
use Database\Seeders\V3\AI\Copilot\Opus46\SentenceStructureBudovaRecenniaSvoV3QuestionsOnlySeeder as Opus46SentenceStructureSeeder;
use Database\Seeders\V3\AI\Copilot\Opus46\SentenceTypesVidiRecenV3QuestionsOnlySeeder as Opus46SentenceTypesSeeder;
use Database\Seeders\V3\AI\Copilot\Sonate\ConjunctionsAndButOrBecauseSoV3QuestionsOnlySeeder as SonateConjunctionsSeeder;
use Database\Seeders\V3\AI\Copilot\Sonate\ImperativesNakazoviRecenniaV3QuestionsOnlySeeder as SonateImperativesSeeder;
use Database\Seeders\V3\AI\Copilot\Sonate\SentenceStructureBudovaRecenniaSvoV3QuestionsOnlySeeder as SonateSentenceStructureSeeder;
use Database\Seeders\V3\AI\Copilot\Sonate\SentenceTypesVidiRecenV3QuestionsOnlySeeder as SonateSentenceTypesSeeder;
use Database\Seeders\V3\Polyglot\PolyglotConjunctionsAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotImperativesAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotSentenceStructureSvoAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotSentenceTypesAllLevelsLessonSeeder;
use Database\Seeders\V3\TheoryLinks\BasicGrammarConjunctionsTheoryLinksSeeder;
use Database\Seeders\V3\TheoryLinks\BasicGrammarImperativesTheoryLinksSeeder;
use Database\Seeders\V3\TheoryLinks\BasicGrammarSentenceStructureSvoTheoryLinksSeeder;
use Database\Seeders\V3\TheoryLinks\BasicGrammarSentenceTypesTheoryLinksSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class BasicGrammarTheoryLinksSeedersTest extends TestCase
{
    use RebuildsComposeTestSchema;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'coming-soon.enabled' => false,
            'tests.tech_info_enabled' => false,
        ]);
    }

    public function test_json_theory_link_seeders_link_basic_grammar_sentence_page_questions(): void
    {
        foreach ($this->basicGrammarTheoryLinkCases() as $caseName => $case) {
            $this->rebuildComposeTestSchema();

            $this->seed(BasicGrammarCategorySeeder::class);
            $this->seed($case['page_seeder']);
            $this->seed($case['covered_seeders']);
            $this->seed($case['links_seeder']);

            $questions = Question::query()
                ->whereIn('seeder', $case['covered_seeders'])
                ->get(['id', 'uuid', 'seeder', 'theory_text_block_uuid']);

            foreach ($case['covered_seeders'] as $seederClass) {
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
                $caseName . ': every covered question should have at least one theory link.'
            );

            $questions = Question::query()
                ->whereIn('seeder', $case['covered_seeders'])
                ->get(['id', 'uuid', 'seeder', 'theory_text_block_uuid']);

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

            foreach ($case['tag_expectations'] as $expectation) {
                $this->assertTaggedQuestionHasLinkedBlock(
                    $case['ai_seeders'],
                    $expectation['tags'],
                    $expectation['sort_orders'],
                    $caseName . ': ' . implode(', ', $expectation['tags'])
                );
            }

            $this->assertEveryQuestionFromSeedersHasTheoryLinks($case['ai_seeders'], $caseName . ': AI');
            $this->assertEveryQuestionFromSeedersHasTheoryLinks([$case['polyglot_seeder']], $caseName . ': Polyglot');

            $firstRunPivotRows = DB::table('question_theory_text_blocks')
                ->whereIn('question_uuid', $questions->pluck('uuid'))
                ->count();

            $this->seed($case['links_seeder']);

            $this->assertSame(
                $firstRunPivotRows,
                DB::table('question_theory_text_blocks')
                    ->whereIn('question_uuid', $questions->pluck('uuid'))
                    ->count(),
                $caseName . ': idempotency check failed.'
            );

            $page = Page::query()->where('slug', $case['page_slug'])->firstOrFail();
            $response = $this->get(route('theory.show', ['basic-grammar', $case['page_slug']]));

            $response->assertOk();
            $response->assertViewHas('topicTests', function ($topicTests) use ($case, $page): bool {
                $slugs = collect($topicTests)->pluck('slug');

                return $slugs->contains($case['direct_slug'])
                    && $slugs->contains('theory-page-' . $page->id . '-mixed-a1-c2');
            });

            $tests = app(TheoryPagePromptLinkedTestsService::class)->buildForPage($page);
            $mixedTest = $tests->firstWhere('slug', 'theory-page-' . $page->id . '-mixed-a1-c2');
            $this->assertNotNull($mixedTest, $caseName);
            $this->assertSame(
                collect($case['covered_seeders'])->sort()->values()->all(),
                collect($mixedTest->filters['seeder_classes'] ?? [])->sort()->values()->all(),
                $caseName . ': mixed test should include Sonate + Opus46 + Polyglot seeders.'
            );

            $directResponse = $this->get('/test/' . $case['direct_slug'] . '/step/compose');
            $directResponse->assertOk();
            $directResponse->assertSee(__('frontend.tests.question.show_theory'));

            $questionData = $directResponse->viewData('questionData');
            $this->assertIsArray($questionData, $caseName);
            $this->assertNotEmpty($questionData, $caseName);
            foreach ($questionData as $question) {
                $this->assertNotEmpty($question['theory_blocks'] ?? [], $caseName . ': direct Polyglot question should expose theory blocks.');
            }
        }
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function basicGrammarTheoryLinkCases(): array
    {
        return [
            'sentence_structure_svo' => [
                'page_slug' => 'sentence-structure-svo',
                'page_seeder' => BasicGrammarSentenceStructureSvoTheorySeeder::class,
                'ai_seeders' => [
                    SonateSentenceStructureSeeder::class,
                    Opus46SentenceStructureSeeder::class,
                ],
                'polyglot_seeder' => PolyglotSentenceStructureSvoAllLevelsLessonSeeder::class,
                'covered_seeders' => [
                    SonateSentenceStructureSeeder::class,
                    Opus46SentenceStructureSeeder::class,
                    PolyglotSentenceStructureSvoAllLevelsLessonSeeder::class,
                ],
                'direct_slug' => 'polyglot-sentence-structure-svo-all-levels',
                'links_seeder' => BasicGrammarSentenceStructureSvoTheoryLinksSeeder::class,
                'tag_expectations' => [
                    ['tags' => ['Basic SVO order', 'S-V-O word order'], 'sort_orders' => [6, 7]],
                    ['tags' => ['Subject identification'], 'sort_orders' => [3]],
                    ['tags' => ['Verb placement in SVO'], 'sort_orders' => [4]],
                    ['tags' => ['Object identification'], 'sort_orders' => [5]],
                    ['tags' => ['Negation in SVO', 'Negation in SVO structure'], 'sort_orders' => [10]],
                ],
            ],
            'sentence_types' => [
                'page_slug' => 'sentence-types',
                'page_seeder' => BasicGrammarSentenceTypesTheorySeeder::class,
                'ai_seeders' => [
                    SonateSentenceTypesSeeder::class,
                    Opus46SentenceTypesSeeder::class,
                ],
                'polyglot_seeder' => PolyglotSentenceTypesAllLevelsLessonSeeder::class,
                'covered_seeders' => [
                    SonateSentenceTypesSeeder::class,
                    Opus46SentenceTypesSeeder::class,
                    PolyglotSentenceTypesAllLevelsLessonSeeder::class,
                ],
                'direct_slug' => 'polyglot-sentence-types-all-levels',
                'links_seeder' => BasicGrammarSentenceTypesTheoryLinksSeeder::class,
                'tag_expectations' => [
                    ['tags' => ['Affirmative sentence structure'], 'sort_orders' => [3]],
                    ['tags' => ['Negation with do/does + not', 'Negative sentence formation'], 'sort_orders' => [4]],
                    ['tags' => ['Yes/No question with do/does', 'Yes/No question formation'], 'sort_orders' => [5]],
                    ['tags' => ['Affirmative imperative', 'Imperative sentence structure'], 'sort_orders' => [6]],
                ],
            ],
            'conjunctions' => [
                'page_slug' => 'basic-conjunctions-and-but-or-because-so',
                'page_seeder' => BasicGrammarConjunctionsTheorySeeder::class,
                'ai_seeders' => [
                    SonateConjunctionsSeeder::class,
                    Opus46ConjunctionsSeeder::class,
                ],
                'polyglot_seeder' => PolyglotConjunctionsAllLevelsLessonSeeder::class,
                'covered_seeders' => [
                    SonateConjunctionsSeeder::class,
                    Opus46ConjunctionsSeeder::class,
                    PolyglotConjunctionsAllLevelsLessonSeeder::class,
                ],
                'direct_slug' => 'polyglot-conjunctions-all-levels',
                'links_seeder' => BasicGrammarConjunctionsTheoryLinksSeeder::class,
                'tag_expectations' => [
                    ['tags' => ["Additive conjunction 'and'", 'conjunction_and'], 'sort_orders' => [3]],
                    ['tags' => ["Adversative conjunction 'but'", 'conjunction_but'], 'sort_orders' => [4]],
                    ['tags' => ["Alternative conjunction 'or'", 'conjunction_or'], 'sort_orders' => [5]],
                    ['tags' => ["Causal conjunction 'because'", 'conjunction_because'], 'sort_orders' => [6]],
                    ['tags' => ["Resultive conjunction 'so'", 'conjunction_so'], 'sort_orders' => [7]],
                ],
            ],
            'imperatives' => [
                'page_slug' => 'imperatives-sit-down-dont-open-it',
                'page_seeder' => BasicGrammarImperativesTheorySeeder::class,
                'ai_seeders' => [
                    SonateImperativesSeeder::class,
                    Opus46ImperativesSeeder::class,
                ],
                'polyglot_seeder' => PolyglotImperativesAllLevelsLessonSeeder::class,
                'covered_seeders' => [
                    SonateImperativesSeeder::class,
                    Opus46ImperativesSeeder::class,
                    PolyglotImperativesAllLevelsLessonSeeder::class,
                ],
                'direct_slug' => 'polyglot-imperatives-all-levels',
                'links_seeder' => BasicGrammarImperativesTheoryLinksSeeder::class,
                'tag_expectations' => [
                    ['tags' => ['Affirmative imperatives', 'Affirmative imperative (base form)'], 'sort_orders' => [4]],
                    ['tags' => ["Negative imperatives", "Negative imperative (Don't + base)"], 'sort_orders' => [5]],
                    ['tags' => ['Polite imperatives with please', 'Polite imperative with please'], 'sort_orders' => [6]],
                    ['tags' => ['Always / Never + imperative'], 'sort_orders' => [7]],
                ],
            ],
        ];
    }

    /**
     * @param  array<int, class-string>  $seederClasses
     * @param  array<int, string>  $tagNames
     * @param  array<int, int>  $expectedSortOrders
     */
    private function assertTaggedQuestionHasLinkedBlock(
        array $seederClasses,
        array $tagNames,
        array $expectedSortOrders,
        string $label
    ): void {
        $expectedTagKeys = collect($tagNames)
            ->map(fn (string $tagName): string => $this->normalizeKey($tagName))
            ->all();

        $question = Question::query()
            ->with('tags')
            ->whereIn('seeder', $seederClasses)
            ->get()
            ->first(function (Question $question) use ($expectedTagKeys): bool {
                return $question->tags->contains(function ($tag) use ($expectedTagKeys): bool {
                    return in_array($this->normalizeKey((string) $tag->name), $expectedTagKeys, true);
                });
            });

        $this->assertNotNull($question, $label);

        $linkedSortOrders = TextBlock::query()
            ->join(
                'question_theory_text_blocks',
                'text_blocks.uuid',
                '=',
                'question_theory_text_blocks.text_block_uuid'
            )
            ->where('question_theory_text_blocks.question_uuid', $question->uuid)
            ->pluck('text_blocks.sort_order')
            ->map(fn ($value): int => (int) $value)
            ->all();

        $this->assertNotEmpty(array_intersect($expectedSortOrders, $linkedSortOrders), $label);
    }

    /**
     * @param  array<int, class-string>  $seederClasses
     */
    private function assertEveryQuestionFromSeedersHasTheoryLinks(array $seederClasses, string $label): void
    {
        $questions = Question::query()
            ->whereIn('seeder', $seederClasses)
            ->get(['uuid', 'theory_text_block_uuid']);

        $this->assertGreaterThan(0, $questions->count(), $label);
        $this->assertSame($questions->count(), $questions->whereNotNull('theory_text_block_uuid')->count(), $label);

        $linkedQuestionCount = DB::table('question_theory_text_blocks')
            ->whereIn('question_uuid', $questions->pluck('uuid'))
            ->distinct()
            ->count('question_uuid');

        $this->assertSame($questions->count(), $linkedQuestionCount, $label);
    }

    private function normalizeKey(string $value): string
    {
        $normalized = Str::lower(trim($value));
        $normalized = str_replace(["'", '"', '`', '´', '’', '‘', '“', '”'], '', $normalized);
        $normalized = preg_replace('/[^\pL\pN]+/u', '_', $normalized) ?? $normalized;
        $normalized = preg_replace('/_+/u', '_', $normalized) ?? $normalized;

        return trim($normalized, '_');
    }
}
