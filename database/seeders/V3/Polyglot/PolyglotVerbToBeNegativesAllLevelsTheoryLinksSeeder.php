<?php

namespace Database\Seeders\V3\Polyglot;

use App\Models\Question;
use App\Models\TextBlock;
use App\Support\Database\QuestionUuidResolver;
use App\Support\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Links each question of polyglot-verb-to-be-negatives-all-levels (surfaced
 * under /theory/verb-to-be → "Verb to Be: Negatives") to the most relevant
 * text blocks of the "Verb to Be: Negatives" theory page so the test UI
 * renders contextual theory hints — same UX as Polyglot lessons.
 *
 * Cross-page anchors:
 *  - Verb to Be: Questions — surfaced for sentences containing tag questions
 *    ("..., чи не так?") or yes/no answers ("— Ні, я не йду.").
 *  - Verb to Be: Present Forms — surfaced as a fallback overview for the
 *    base am/is/are paradigm at higher levels.
 *
 * For backward compatibility the legacy single Question.theory_text_block_uuid
 * column is also populated with the first block in the curated list.
 *
 * This seeder does not modify the lesson definition or any theory page seeder.
 */
class PolyglotVerbToBeNegativesAllLevelsTheoryLinksSeeder extends Seeder
{
    /** "Verb to Be: Negatives" theory page — primary anchor of the test. */
    private const VTB_NEG = 'Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\VerbToBeNegativesTheorySeeder';

    /** "Verb to Be: Questions and Short Answers" — used for tag questions and short answers. */
    private const VTB_Q = 'Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\VerbToBeQuestionsTheorySeeder';

    /** "Verb to Be: Present Forms" — used as the underlying am/is/are paradigm reference. */
    private const VTBP = 'Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\VerbToBePresentTheorySeeder';

    public function run(): void
    {
        $resolver = app(QuestionUuidResolver::class);
        $blockCache = [];
        $now = now();
        $missing = [];

        foreach ($this->questionMap() as $questionUuid => $blockSpecs) {
            $persistentUuid = $resolver->toPersistent($questionUuid);
            $question = Question::query()->where('uuid', $persistentUuid)->first();

            if (! $question) {
                $missing[] = $persistentUuid;

                continue;
            }

            $blockUuids = [];
            $seen = [];

            foreach ($blockSpecs as [$seederClass, $sortOrder]) {
                $cacheKey = $seederClass . '#' . $sortOrder;

                if (! array_key_exists($cacheKey, $blockCache)) {
                    $blockCache[$cacheKey] = TextBlock::query()
                        ->where('seeder', $seederClass)
                        ->where('sort_order', $sortOrder)
                        ->first();
                }

                $block = $blockCache[$cacheKey];

                if (! $block) {
                    throw new RuntimeException(sprintf(
                        'Theory text block missing: seeder=%s sort_order=%d. Run that page seeder first.',
                        $seederClass,
                        $sortOrder
                    ));
                }

                if (isset($seen[$block->uuid])) {
                    continue;
                }

                $seen[$block->uuid] = true;
                $blockUuids[] = $block->uuid;
            }

            DB::table('question_theory_text_blocks')
                ->where('question_id', $question->id)
                ->delete();

            $rows = [];
            foreach ($blockUuids as $position => $blockUuid) {
                $rows[] = [
                    'question_id' => $question->id,
                    'text_block_uuid' => $blockUuid,
                    'position' => $position,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if ($rows !== []) {
                DB::table('question_theory_text_blocks')->insert($rows);
            }

            $question->theory_text_block_uuid = $blockUuids[0] ?? null;
            $question->save();
        }

        if ($missing !== [] && $this->command !== null) {
            $this->command->warn(sprintf(
                'Skipped %d question(s) not present in DB: %s',
                count($missing),
                implode(', ', array_slice($missing, 0, 5))
                . (count($missing) > 5 ? '...' : '')
            ));
        }
    }

    /**
     * Sort orders inside the "Verb to Be: Negatives" theory page:
     *   1 hero, 2 forms-grid, 3 usage-panels, 4 comparison-table,
     *   5 mistakes-grid, 6 summary-list.
     *
     * @return array<string, array<int, array{0: string, 1: int}>>
     */
    private function questionMap(): array
    {
        $negHero = [self::VTB_NEG, 1];
        $negForms = [self::VTB_NEG, 2];
        $negUsage = [self::VTB_NEG, 3];
        $negComparison = [self::VTB_NEG, 4];
        $negMistakes = [self::VTB_NEG, 5];
        $negSummary = [self::VTB_NEG, 6];

        $qHero = [self::VTB_Q, 1];
        $qForms = [self::VTB_Q, 2];
        $qShortAnswers = [self::VTB_Q, 4];

        $vtbpForms = [self::VTBP, 2];
        $vtbpHero = [self::VTBP, 1];

        // Bundles per sentence pattern.
        $simpleNeg = [$negForms, $negComparison, $negHero];
        $extendedNeg = [$negForms, $negUsage, $negComparison, $negHero];
        $negWithMistakes = [$negForms, $negComparison, $negMistakes, $negHero];
        $negWithShortAnswer = [$negForms, $negComparison, $qShortAnswers, $negHero];
        $negWithTag = [$negForms, $negComparison, $qForms, $qHero, $negHero];
        $advancedNeg = [$negForms, $negUsage, $negSummary, $negHero, $vtbpForms];

        $map = [];

        // ───────── A1 (12) — basic negatives "I am not / he isn't / they aren't" ──
        for ($i = 1; $i <= 12; $i++) {
            $map[sprintf('poly-vtbng-a1-%02d', $i)] = $simpleNeg;
        }

        // ───────── A2 (12) — extended negatives + first short-answer pairs (q09-q11) ──
        for ($i = 1; $i <= 8; $i++) {
            $map[sprintf('poly-vtbng-a2-%02d', $i)] = $extendedNeg;
        }
        // q09 "...— Ні, він не легкий.", q10 "...— Ні, я не йду.", q11 "...— Ні, вони не французи."
        $map['poly-vtbng-a2-09'] = $negWithShortAnswer;
        $map['poly-vtbng-a2-10'] = $negWithShortAnswer;
        $map['poly-vtbng-a2-11'] = $negWithShortAnswer;
        $map['poly-vtbng-a2-12'] = $extendedNeg;

        // ───────── B1 (12) — varied negatives, plural-only nouns, abstract subjects ──
        for ($i = 1; $i <= 10; $i++) {
            $map[sprintf('poly-vtbng-b1-%02d', $i)] = $negWithMistakes;
        }
        // b1-11 "Штани недостатньо довгі." — plural-only noun + negation of degree.
        $map['poly-vtbng-b1-11'] = [$negComparison, $negForms, $negMistakes, $negHero];
        $map['poly-vtbng-b1-12'] = $negWithMistakes;

        // ───────── B2 (12) — formal negatives, several with tag questions "..., чи не так?" ──
        // q01 "..., чи не так?", q02 "..., чи не так?", q09 "..., чи не так?".
        $map['poly-vtbng-b2-01'] = $negWithTag;
        $map['poly-vtbng-b2-02'] = $negWithTag;
        $map['poly-vtbng-b2-03'] = $extendedNeg;
        $map['poly-vtbng-b2-04'] = $extendedNeg;
        $map['poly-vtbng-b2-05'] = $extendedNeg;
        $map['poly-vtbng-b2-06'] = $advancedNeg;          // passive-feel "не підкріплені доказами"
        $map['poly-vtbng-b2-07'] = $extendedNeg;
        $map['poly-vtbng-b2-08'] = $advancedNeg;
        $map['poly-vtbng-b2-09'] = $negWithTag;
        $map['poly-vtbng-b2-10'] = $extendedNeg;
        $map['poly-vtbng-b2-11'] = $extendedNeg;
        $map['poly-vtbng-b2-12'] = $extendedNeg;

        // ───────── C1 (12) — formal complex negatives ──
        for ($i = 1; $i <= 12; $i++) {
            $map[sprintf('poly-vtbng-c1-%02d', $i)] = $advancedNeg;
        }

        // ───────── C2 (12) — advanced formal negatives, hedged style ──
        for ($i = 1; $i <= 12; $i++) {
            $map[sprintf('poly-vtbng-c2-%02d', $i)] = $advancedNeg;
        }

        return $map;
    }
}
