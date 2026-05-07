<?php

namespace Database\Seeders\V3\Polyglot;

use App\Models\Question;
use App\Models\TextBlock;
use App\Support\Database\QuestionUuidResolver;
use App\Support\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Links each question of polyglot-verb-to-be-questions-all-levels (surfaced
 * under /theory/verb-to-be → "Verb to Be: Questions and Short Answers") to
 * the most relevant text blocks of the "Verb to Be: Questions" theory page
 * so the test UI renders contextual theory hints.
 *
 * Cross-page anchors:
 *  - Verb to Be: Negatives — surfaced for tag questions "..., чи не так?",
 *    negative-leading questions "Хіба не..." and short answers "— Ні, ...".
 *  - Verb to Be: Present Forms — surfaced as the underlying am/is/are
 *    paradigm reference at higher levels.
 *
 * For backward compatibility the legacy single Question.theory_text_block_uuid
 * column is also populated with the first block in the curated list.
 *
 * This seeder does not modify the lesson definition or any theory page seeder.
 */
class PolyglotVerbToBeQuestionsAllLevelsTheoryLinksSeeder extends Seeder
{
    /** "Verb to Be: Questions and Short Answers" theory page — primary anchor. */
    private const VTB_Q = 'Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\VerbToBeQuestionsTheorySeeder';

    /** "Verb to Be: Negatives" — used for tag questions and negative questions. */
    private const VTB_NEG = 'Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\VerbToBeNegativesTheorySeeder';

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
                ->where('question_uuid', $question->uuid)
                ->delete();

            $rows = [];
            foreach ($blockUuids as $position => $blockUuid) {
                $rows[] = [
                    'question_uuid' => $question->uuid,
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
     * Sort orders inside the "Verb to Be: Questions" theory page:
     *   1 hero, 2 forms-grid (Базові моделі питання), 3 usage-panels (короткі відповіді),
     *   4 comparison-table (Питання і коротка відповідь), 5 mistakes-grid, 6 summary-list.
     *
     * @return array<string, array<int, array{0: string, 1: int}>>
     */
    private function questionMap(): array
    {
        $qHero = [self::VTB_Q, 1];
        $qForms = [self::VTB_Q, 2];
        $qUsage = [self::VTB_Q, 3];
        $qComparison = [self::VTB_Q, 4];
        $qMistakes = [self::VTB_Q, 5];
        $qSummary = [self::VTB_Q, 6];

        $negForms = [self::VTB_NEG, 2];
        $negComparison = [self::VTB_NEG, 4];
        $negHero = [self::VTB_NEG, 1];

        $vtbpForms = [self::VTBP, 2];
        $vtbpHero = [self::VTBP, 1];

        // Bundles per sentence pattern.
        $simpleQ = [$qForms, $qComparison, $qHero];                    // base yes/no
        $qWithShortAnswer = [$qForms, $qUsage, $qComparison, $qHero];  // — Так / Ні
        $whQ = [$qForms, $qHero, $qSummary];                            // Where, Why, How, Who
        $qWithTag = [$qForms, $qComparison, $negForms, $negHero, $qHero]; // ..., чи не так?
        $negativeLeadQ = [$qForms, $negForms, $negComparison, $qHero]; // Хіба не...?
        $embeddedQ = [$qForms, $qUsage, $qSummary, $qHero, $vtbpForms]; // indirect: Я не знаю, чи...
        $advancedQ = [$qForms, $qUsage, $qHero, $qSummary, $vtbpHero];

        $map = [];

        // ───────── A1 (12) — basic yes/no, then short-answer pairs ──
        for ($i = 1; $i <= 8; $i++) {
            $map[sprintf('poly-vtbqs-a1-%02d', $i)] = $simpleQ;
        }
        // q09 "...— Так, вона вдома.", q10 "...— Ні, я не втомився.", q12 "...— Так, маєш."
        $map['poly-vtbqs-a1-09'] = $qWithShortAnswer;
        $map['poly-vtbqs-a1-10'] = $qWithShortAnswer;
        $map['poly-vtbqs-a1-11'] = $simpleQ;
        $map['poly-vtbqs-a1-12'] = $qWithShortAnswer;

        // ───────── A2 (12) — yes/no with short answers ──
        for ($i = 1; $i <= 12; $i++) {
            $map[sprintf('poly-vtbqs-a2-%02d', $i)] = $qWithShortAnswer;
        }

        // ───────── B1 (12) — yes/no, WH, negative-lead questions ──
        $map['poly-vtbqs-b1-01'] = $simpleQ;
        $map['poly-vtbqs-b1-02'] = $simpleQ;
        $map['poly-vtbqs-b1-03'] = $simpleQ;
        $map['poly-vtbqs-b1-04'] = $negativeLeadQ;     // "Хіба вона сьогодні не хвора?"
        $map['poly-vtbqs-b1-05'] = $whQ;               // "Де найближча..."
        $map['poly-vtbqs-b1-06'] = $whQ;               // "Як твої колеги?"
        $map['poly-vtbqs-b1-07'] = $simpleQ;
        $map['poly-vtbqs-b1-08'] = $whQ;               // "Хто відповідальний..."
        $map['poly-vtbqs-b1-09'] = $whQ;               // "Чому вікна відчинені?"
        $map['poly-vtbqs-b1-10'] = $simpleQ;
        $map['poly-vtbqs-b1-11'] = $negativeLeadQ;     // "Хіба ти не боїшся..."
        $map['poly-vtbqs-b1-12'] = $whQ;               // "Яка головна причина..."

        // ───────── B2 (12) — tag questions, embedded questions ──
        $map['poly-vtbqs-b2-01'] = $qWithTag;          // "..., чи не так?"
        $map['poly-vtbqs-b2-02'] = $qWithTag;
        $map['poly-vtbqs-b2-03'] = $embeddedQ;         // "Мені цікаво, чи знає вона..."
        $map['poly-vtbqs-b2-04'] = $embeddedQ;         // "Ти знаєш, де офіс?"
        $map['poly-vtbqs-b2-05'] = $simpleQ;
        $map['poly-vtbqs-b2-06'] = $qWithTag;          // "..., чи не так?"
        $map['poly-vtbqs-b2-07'] = $embeddedQ;         // "Можеш пояснити, у чому проблема?"
        $map['poly-vtbqs-b2-08'] = $embeddedQ;         // "Можливо, що потяг запізнюється?"
        $map['poly-vtbqs-b2-09'] = $qWithTag;          // "Вона не прийде, чи не так?"
        $map['poly-vtbqs-b2-10'] = $embeddedQ;
        $map['poly-vtbqs-b2-11'] = $qWithTag;          // "Ми не запізнилися, чи не так?"
        $map['poly-vtbqs-b2-12'] = $simpleQ;

        // ───────── C1 (12) — formal embedded + tag questions ──
        $map['poly-vtbqs-c1-01'] = $negativeLeadQ;     // "Хіба не правда, що..."
        $map['poly-vtbqs-c1-02'] = $embeddedQ;         // "Можна запитати, чи..."
        $map['poly-vtbqs-c1-03'] = $qWithTag;          // "..., чи не так?"
        $map['poly-vtbqs-c1-04'] = $advancedQ;
        $map['poly-vtbqs-c1-05'] = $embeddedQ;         // "Питання полягає в тому, чи..."
        $map['poly-vtbqs-c1-06'] = $negativeLeadQ;     // "Хіба теперішніх... недостатньо..."
        $map['poly-vtbqs-c1-07'] = $embeddedQ;
        $map['poly-vtbqs-c1-08'] = $qWithTag;          // "..., правда?"
        $map['poly-vtbqs-c1-09'] = $advancedQ;
        $map['poly-vtbqs-c1-10'] = $embeddedQ;         // "Вона запитала, чи..."
        $map['poly-vtbqs-c1-11'] = $qWithTag;          // "..., чи не так?"
        $map['poly-vtbqs-c1-12'] = $embeddedQ;

        // ───────── C2 (12) — advanced rhetorical / embedded / tag questions ──
        $map['poly-vtbqs-c2-01'] = $negativeLeadQ;     // "Хіба не очевидно..."
        $map['poly-vtbqs-c2-02'] = $embeddedQ;
        $map['poly-vtbqs-c2-03'] = $negativeLeadQ;     // "..., хіба не є вона..."
        $map['poly-vtbqs-c2-04'] = $embeddedQ;
        $map['poly-vtbqs-c2-05'] = $qWithTag;          // "..., чи не так?"
        $map['poly-vtbqs-c2-06'] = $negativeLeadQ;     // "Хіба протиставлення..."
        $map['poly-vtbqs-c2-07'] = $embeddedQ;
        $map['poly-vtbqs-c2-08'] = $qWithTag;          // "..., чи не так?"
        $map['poly-vtbqs-c2-09'] = $embeddedQ;
        $map['poly-vtbqs-c2-10'] = $negativeLeadQ;     // "Хіба не парадоксально..."
        $map['poly-vtbqs-c2-11'] = $qWithTag;          // "..., спірні, чи не так?"
        $map['poly-vtbqs-c2-12'] = $embeddedQ;

        return $map;
    }
}
