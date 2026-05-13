<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Question;
use App\Models\TextBlock;
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
            $this->seed($case['question_seeders']);
            $this->seed($case['links_seeder']);

            $questions = Question::query()
                ->whereIn('seeder', $case['question_seeders'])
                ->get(['id', 'uuid', 'seeder', 'theory_text_block_uuid']);

            foreach ($case['question_seeders'] as $seederClass) {
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
                ->whereIn('seeder', $case['question_seeders'])
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
                    $case['question_seeders'],
                    $expectation['tags'],
                    $expectation['sort_orders'],
                    $caseName . ': ' . implode(', ', $expectation['tags'])
                );
            }

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
            $response->assertViewHas('topicTests', fn ($topicTests): bool => collect($topicTests)->isNotEmpty());
            $this->assertNotNull($page);
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
                'question_seeders' => [
                    SonateSentenceStructureSeeder::class,
                    Opus46SentenceStructureSeeder::class,
                ],
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
                'question_seeders' => [
                    SonateSentenceTypesSeeder::class,
                    Opus46SentenceTypesSeeder::class,
                ],
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
                'question_seeders' => [
                    SonateConjunctionsSeeder::class,
                    Opus46ConjunctionsSeeder::class,
                ],
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
                'question_seeders' => [
                    SonateImperativesSeeder::class,
                    Opus46ImperativesSeeder::class,
                ],
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

    private function normalizeKey(string $value): string
    {
        $normalized = Str::lower(trim($value));
        $normalized = str_replace(["'", '"', '`', '´', '’', '‘', '“', '”'], '', $normalized);
        $normalized = preg_replace('/[^\pL\pN]+/u', '_', $normalized) ?? $normalized;
        $normalized = preg_replace('/_+/u', '_', $normalized) ?? $normalized;

        return trim($normalized, '_');
    }
}
