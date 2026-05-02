<?php

namespace Database\Seeders\V3\Polyglot;

use App\Models\Question;
use App\Models\TextBlock;
use App\Support\Database\QuestionUuidResolver;
use App\Support\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Links each question of the polyglot-there-is-there-are-a1 lesson to the
 * most relevant theory text blocks of the "There is / There are" theory
 * page so the test UI renders contextual theory hints.
 *
 * Linkage is many-to-many through the question_theory_text_blocks pivot.
 * Affirmative singular/plural draw from the forms grid + singular vs plural
 * comparison; negatives add the common-mistakes block; questions surface
 * the usage-panel block too.
 *
 * The legacy single Question.theory_text_block_uuid column is also
 * populated with the first block in the curated list for backward compat.
 *
 * This seeder does not modify the lesson definition or any theory page seeder.
 */
class PolyglotThereIsThereAreA1TheoryLinksSeeder extends Seeder
{
    /** "There is / There are" theory page — primary anchor of the lesson. */
    private const TITTA = 'Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\ThereIsThereAreTheorySeeder';

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
     * Sort orders inside the "There is / There are" theory page:
     *   1 hero, 2 forms-grid (main models),
     *   3 usage-panels (where it's especially useful),
     *   4 comparison-table (singular vs plural),
     *   5 mistakes-grid, 6 summary-list.
     *
     * @return array<string, array<int, array{0: string, 1: int}>>
     */
    private function questionMap(): array
    {
        $hero = [self::TITTA, 1];
        $forms = [self::TITTA, 2];
        $usage = [self::TITTA, 3];
        $comparison = [self::TITTA, 4];
        $mistakes = [self::TITTA, 5];
        $summary = [self::TITTA, 6];

        // Bundles per sentence pattern.
        $affirmative = [$forms, $comparison, $hero];
        $negative = [$forms, $comparison, $mistakes];
        $yesNo = [$forms, $usage, $comparison];
        $whWhat = [$forms, $usage, $comparison];
        $whHowMany = [$comparison, $forms, $summary];

        $map = [];

        // ───────── Batch 1: polyglot-there-is-are-q01..q24 ─────────
        // q01..q08 — affirmatives (singular q01..q04, plural q05..q08).
        for ($i = 1; $i <= 8; $i++) {
            $map[sprintf('polyglot-there-is-are-q%02d', $i)] = $affirmative;
        }
        // q09..q14 — negatives (singular q09..q12, plural q13..q14).
        for ($i = 9; $i <= 14; $i++) {
            $map[sprintf('polyglot-there-is-are-q%02d', $i)] = $negative;
        }
        // q15..q20 — yes/no questions.
        for ($i = 15; $i <= 20; $i++) {
            $map[sprintf('polyglot-there-is-are-q%02d', $i)] = $yesNo;
        }
        // q21..q22 — "Що є...?" (what is there).
        $map['polyglot-there-is-are-q21'] = $whWhat;
        $map['polyglot-there-is-are-q22'] = $whWhat;
        // q23..q24 — "Скільки..." (how many).
        $map['polyglot-there-is-are-q23'] = $whHowMany;
        $map['polyglot-there-is-are-q24'] = $whHowMany;

        // ───────── Batch 2: polyglot-there-is-are-a1-x-co47-001..048 ─────
        $b2 = static fn (int $i): string => sprintf('polyglot-there-is-are-a1-x-co47-%03d', $i);
        // 001..010 — singular affirmatives.
        for ($i = 1; $i <= 10; $i++) {
            $map[$b2($i)] = $affirmative;
        }
        // 011..020 — plural affirmatives.
        for ($i = 11; $i <= 20; $i++) {
            $map[$b2($i)] = $affirmative;
        }
        // 021..034 — negatives (mix of singular and plural).
        for ($i = 21; $i <= 34; $i++) {
            $map[$b2($i)] = $negative;
        }
        // 035..044 — yes/no questions (mix of singular and plural).
        for ($i = 35; $i <= 44; $i++) {
            $map[$b2($i)] = $yesNo;
        }
        // 045..046 — "Що є...?" what-questions.
        $map[$b2(45)] = $whWhat;
        $map[$b2(46)] = $whWhat;
        // 047..048 — "Скільки..." how-many questions.
        $map[$b2(47)] = $whHowMany;
        $map[$b2(48)] = $whHowMany;

        return $map;
    }
}
