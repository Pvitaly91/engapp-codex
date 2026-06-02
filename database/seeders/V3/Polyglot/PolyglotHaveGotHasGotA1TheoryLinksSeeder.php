<?php

namespace Database\Seeders\V3\Polyglot;

use App\Models\Question;
use App\Models\TextBlock;
use App\Support\Database\QuestionUuidResolver;
use App\Support\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Links each question of the polyglot-have-got-has-got-a1 lesson to the
 * most relevant theory text blocks of the "Have got / Has got" theory
 * page so the test UI renders contextual theory hints.
 *
 * Linkage is many-to-many through the question_theory_text_blocks pivot.
 * Affirmative / negative / question / WH bundles draw from the theory
 * page's forms grid, usage panels, comparison table and summary list.
 *
 * The legacy single Question.theory_text_block_uuid column is also
 * populated with the first block in the curated list for backward compat.
 *
 * This seeder does not modify the lesson definition or any theory page seeder.
 */
class PolyglotHaveGotHasGotA1TheoryLinksSeeder extends Seeder
{
    /** "Have got / Has got" theory page — primary anchor of the lesson. */
    private const HGHG = 'Database\\Seeders\\Page_V3\\BasicGrammar\\BasicGrammarHaveGotHasGotTheorySeeder';

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
     * Sort orders inside the "Have got / Has got" theory page:
     *   1 hero, 2 forms-grid, 3 usage-panels, 4 comparison-table,
     *   5 summary-list.
     *
     * @return array<string, array<int, array{0: string, 1: int}>>
     */
    private function questionMap(): array
    {
        $hero = [self::HGHG, 1];
        $forms = [self::HGHG, 2];
        $usage = [self::HGHG, 3];
        $comparison = [self::HGHG, 4];
        $summary = [self::HGHG, 5];

        // Bundles per sentence pattern.
        $affirmative = [$forms, $comparison, $hero];
        $negative = [$forms, $hero, $summary];
        $yesNo = [$forms, $usage, $comparison];
        $wh = [$forms, $usage, $summary];

        $map = [];

        // ───── Batch 1: polyglot-have-got-has-got-q01..q24 ─────
        // q01..q08 — affirmatives (have got / has got, singular & plural).
        for ($i = 1; $i <= 8; $i++) {
            $map[sprintf('polyglot-have-got-has-got-q%02d', $i)] = $affirmative;
        }
        // q09..q14 — negatives (haven't got / hasn't got).
        for ($i = 9; $i <= 14; $i++) {
            $map[sprintf('polyglot-have-got-has-got-q%02d', $i)] = $negative;
        }
        // q15..q20 — yes/no questions (Have/Has ... got?).
        for ($i = 15; $i <= 20; $i++) {
            $map[sprintf('polyglot-have-got-has-got-q%02d', $i)] = $yesNo;
        }
        // q21..q22 — "Що в тебе/нього є?" (what-questions).
        $map['polyglot-have-got-has-got-q21'] = $wh;
        $map['polyglot-have-got-has-got-q22'] = $wh;
        // q23 — "У кого є кіт?" (who-question).
        $map['polyglot-have-got-has-got-q23'] = $wh;
        // q24 — "Скільки книг у неї є?" (how-many-question).
        $map['polyglot-have-got-has-got-q24'] = $wh;

        return $map;
    }
}
