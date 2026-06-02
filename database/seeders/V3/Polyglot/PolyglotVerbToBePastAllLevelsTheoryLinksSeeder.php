<?php

namespace Database\Seeders\V3\Polyglot;

use App\Models\Question;
use App\Models\TextBlock;
use App\Support\Database\QuestionUuidResolver;
use App\Support\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Links each question of the polyglot-verb-to-be-past-all-levels lesson
 * to the most relevant text blocks of multiple theory pages so the test
 * UI can render a complete grammar breakdown of each sentence.
 *
 * Linkage is many-to-many through the question_theory_text_blocks pivot.
 * Simple A1-B1 questions usually map to a single block from the main
 * "Verb to Be: Past Forms" page. Higher-level B2-C2 sentences combine
 * blocks from several pages — Past Continuous, Past Passive,
 * Conditionals, Subjunctive, Inversion — because they exercise multiple
 * grammar topics at once.
 *
 * For backward compatibility the legacy single Question.theory_text_block_uuid
 * column is also populated with the first block in the curated list.
 *
 * This seeder does not modify the lesson definition or any theory page seeder.
 */
class PolyglotVerbToBePastAllLevelsTheoryLinksSeeder extends Seeder
{
    /** Verb to Be: Past Forms — the page the lesson is anchored to. */
    private const VTBP = 'Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\VerbToBePastTheorySeeder';

    /** Cross-page anchors used for higher-level questions. */
    private const PAST_CONTINUOUS = 'Database\\Seeders\\Page_V3\\Tenses\\PastContinuous\\PastContinuousFormsTheorySeeder';
    private const PASSIVE_PAST_SIMPLE = 'Database\\Seeders\\Page_V3\\PassiveVoice\\Tenses\\PassiveVoicePastSimpleTheorySeeder';
    private const SECOND_CONDITIONAL = 'Database\\Seeders\\Page_V3\\Conditionals\\ConditionalsSecondTheorySeeder';
    private const THIRD_CONDITIONAL = 'Database\\Seeders\\Page_V3\\Conditionals\\ConditionalsThirdTheorySeeder';
    private const WISH_IF_ONLY = 'Database\\Seeders\\Page_V3\\Conditionals\\ConditionalsWishIfOnlyTheorySeeder';
    private const SUBJUNCTIVE = 'Database\\Seeders\\Page_V3\\FormalEnglish\\SubjunctiveAndFormalStructuresTheorySeeder';
    private const INVERSION_NEG = 'Database\\Seeders\\Page_V3\\BasicGrammar\\WordOrder\\InversionAfterNegativeAdverbialsTheorySeeder';

    public function run(): void
    {
        $resolver = app(QuestionUuidResolver::class);
        $blockCache = [];
        $now = now();
        $missingQuestions = [];

        foreach ($this->questionMap() as $questionUuid => $blockSpecs) {
            $persistentUuid = $resolver->toPersistent($questionUuid);
            $question = Question::query()->where('uuid', $persistentUuid)->first();

            if (! $question) {
                $missingQuestions[] = $persistentUuid;

                continue;
            }

            $blockUuids = [];
            $seenBlockUuids = [];

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

                if (isset($seenBlockUuids[$block->uuid])) {
                    continue;
                }

                $seenBlockUuids[$block->uuid] = true;
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

        if ($missingQuestions !== [] && $this->command !== null) {
            $this->command->warn(sprintf(
                'Skipped %d question(s) not present in DB: %s',
                count($missingQuestions),
                implode(', ', array_slice($missingQuestions, 0, 5))
                . (count($missingQuestions) > 5 ? '...' : '')
            ));
        }
    }

    /**
     * Map of question UUID to an ordered list of [seederClass, sort_order]
     * tuples. The first tuple becomes the primary block; the rest are
     * stacked together in the test hint as a full grammar breakdown.
     *
     * Sort orders inside the Verb to Be: Past Forms page:
     *   1 hero, 2 forms-grid, 3 negatives, 4 questions, 5 short answers,
     *   6 mistakes-grid, 7 summary-list.
     *
     * @return array<string, array<int, array{0: string, 1: int}>>
     */
    private function questionMap(): array
    {
        $map = [];

        // Common reusable shortcuts inside the main page.
        $vtbpHero = [self::VTBP, 1];
        $vtbpForms = [self::VTBP, 2];
        $vtbpNeg = [self::VTBP, 3];
        $vtbpQ = [self::VTBP, 4];
        $vtbpShort = [self::VTBP, 5];
        $vtbpMistakes = [self::VTBP, 6];
        $vtbpSummary = [self::VTBP, 7];

        // ───────── A1: simple was/were affirmatives ─────────
        for ($i = 1; $i <= 12; $i++) {
            $map[sprintf('poly-vtbpa-a1-%02d', $i)] = [$vtbpForms];
        }

        // ───────── A2 1-6: negatives ─────────
        for ($i = 1; $i <= 6; $i++) {
            $map[sprintf('poly-vtbpa-a2-%02d', $i)] = [$vtbpNeg, $vtbpForms, $vtbpMistakes];
        }
        // ───────── A2 7-12: yes/no questions + short answers ─────────
        for ($i = 7; $i <= 12; $i++) {
            $map[sprintf('poly-vtbpa-a2-%02d', $i)] = [$vtbpQ, $vtbpForms, $vtbpShort];
        }

        // ───────── B1 1-6: WH-questions ─────────
        for ($i = 1; $i <= 6; $i++) {
            $map[sprintf('poly-vtbpa-b1-%02d', $i)] = [$vtbpQ, $vtbpForms];
        }
        // B1 7-8: there was/were affirmatives.
        $map['poly-vtbpa-b1-07'] = [$vtbpForms, $vtbpHero];
        $map['poly-vtbpa-b1-08'] = [$vtbpForms, $vtbpHero];
        // B1-09: "Were there...?".
        $map['poly-vtbpa-b1-09'] = [$vtbpQ, $vtbpForms];
        // B1-10: "There was no...".
        $map['poly-vtbpa-b1-10'] = [$vtbpNeg, $vtbpForms];
        // B1 11-12: simple affirmatives.
        $map['poly-vtbpa-b1-11'] = [$vtbpForms];
        $map['poly-vtbpa-b1-12'] = [$vtbpForms];

        // ───────── B2 1-6: Past Continuous (was/were + V-ing) ─────────
        // Verb-to-be forms + Past Continuous theory page.
        for ($i = 1; $i <= 6; $i++) {
            $map[sprintf('poly-vtbpa-b2-%02d', $i)] = [
                [self::PAST_CONTINUOUS, 1],   // hero
                [self::PAST_CONTINUOUS, 2],   // forms (be + V-ing)
                $vtbpForms,                   // was vs were paradigm
                [self::PAST_CONTINUOUS, 3],   // usage
            ];
        }
        // ───────── B2 7-12: Past Passive (was/were + V3) ─────────
        for ($i = 7; $i <= 12; $i++) {
            $map[sprintf('poly-vtbpa-b2-%02d', $i)] = [
                [self::PASSIVE_PAST_SIMPLE, 1],  // hero
                [self::PASSIVE_PAST_SIMPLE, 2],  // was/were choice
                [self::PASSIVE_PAST_SIMPLE, 3],  // affirmative passive
                $vtbpForms,
            ];
        }

        // ───────── C1 1-6: subjunctive (if I were / as if + past) ─────────
        // C1-01 — "Якби я був на твоєму місці..." (2nd conditional, were)
        $map['poly-vtbpa-c1-01'] = [
            [self::SECOND_CONDITIONAL, 1],   // hero
            [self::SECOND_CONDITIONAL, 2],   // forms
            $vtbpForms,
            [self::SUBJUNCTIVE, 1],
        ];
        // C1-02 — "...так, ніби вона керівниця..." (as if + present, idiomatic with were)
        $map['poly-vtbpa-c1-02'] = [
            [self::SUBJUNCTIVE, 1],
            [self::SECOND_CONDITIONAL, 2],
            $vtbpForms,
        ];
        // C1-03 — "Хотілося б, щоб я міг..." (wish + past)
        $map['poly-vtbpa-c1-03'] = [
            [self::WISH_IF_ONLY, 1],
            [self::WISH_IF_ONLY, 2],
            $vtbpForms,
        ];
        // C1-04 — "Він поводиться так, ніби..." (as if)
        $map['poly-vtbpa-c1-04'] = [
            [self::SUBJUNCTIVE, 1],
            [self::SECOND_CONDITIONAL, 2],
            $vtbpForms,
        ];
        // C1-05 — "Якби погода була кращою..." (2nd conditional)
        $map['poly-vtbpa-c1-05'] = [
            [self::SECOND_CONDITIONAL, 1],
            [self::SECOND_CONDITIONAL, 2],
            $vtbpForms,
        ];
        // C1-06 — "Уявімо, що вона про це дізналася б..." (2nd conditional)
        $map['poly-vtbpa-c1-06'] = [
            [self::SECOND_CONDITIONAL, 1],
            [self::SECOND_CONDITIONAL, 2],
            $vtbpForms,
        ];

        // ───────── C1 7-12: formal complex sentences ─────────
        // C1-07 — formal narration, simple past + complex clause.
        $map['poly-vtbpa-c1-07'] = [$vtbpForms, $vtbpHero, $vtbpSummary];
        // C1-08 — "Присяжні були одностайні..." simple were.
        $map['poly-vtbpa-c1-08'] = [$vtbpForms, $vtbpHero];
        // C1-09 — "Поліцію викликали..." (Past Passive).
        $map['poly-vtbpa-c1-09'] = [
            [self::PASSIVE_PAST_SIMPLE, 1],
            [self::PASSIVE_PAST_SIMPLE, 2],
            $vtbpForms,
        ];
        // C1-10 — "Математика була..." simple was.
        $map['poly-vtbpa-c1-10'] = [$vtbpForms, $vtbpHero];
        // C1-11 — "Критерії... були чітко викладені..." (Past Passive).
        $map['poly-vtbpa-c1-11'] = [
            [self::PASSIVE_PAST_SIMPLE, 1],
            [self::PASSIVE_PAST_SIMPLE, 2],
            $vtbpForms,
        ];
        // C1-12 — "Явища... були неочікуваними" simple were.
        $map['poly-vtbpa-c1-12'] = [$vtbpForms, $vtbpHero, $vtbpSummary];

        // ───────── C2: advanced (inversion, subjunctive, fronting) ─────────
        // C2-01 — "Якби не її швидка реакція..." (3rd conditional inversion).
        $map['poly-vtbpa-c2-01'] = [
            [self::THIRD_CONDITIONAL, 1],
            [self::THIRD_CONDITIONAL, 2],
            [self::INVERSION_NEG, 1],
            $vtbpForms,
        ];
        // C2-02 — formal "the plans were fundamentally flawed".
        $map['poly-vtbpa-c2-02'] = [$vtbpForms, $vtbpHero, [self::SUBJUNCTIVE, 1]];
        // C2-03 — "О, якби було можливо..." (wish / if only)
        $map['poly-vtbpa-c2-03'] = [
            [self::WISH_IF_ONLY, 1],
            [self::WISH_IF_ONLY, 2],
            $vtbpForms,
        ];
        // C2-04 — "Він наполягав, щоб кожну деталь перевірили..." (mandative subjunctive + past passive)
        $map['poly-vtbpa-c2-04'] = [
            [self::SUBJUNCTIVE, 1],
            [self::PASSIVE_PAST_SIMPLE, 1],
            $vtbpForms,
        ];
        // C2-05 — "Вкрай важливо, щоб дані були перевірені..." (mandative + past passive subjunctive)
        $map['poly-vtbpa-c2-05'] = [
            [self::SUBJUNCTIVE, 1],
            [self::PASSIVE_PAST_SIMPLE, 1],
            [self::PASSIVE_PAST_SIMPLE, 2],
            $vtbpForms,
        ];
        // C2-06 — "Професор говорив так, ніби ця теорія була..." (as if + past)
        $map['poly-vtbpa-c2-06'] = [
            [self::SUBJUNCTIVE, 1],
            [self::SECOND_CONDITIONAL, 2],
            $vtbpForms,
        ];
        // C2-07 — "Рідко коли промову так погано сприймала публіка." (rarely-fronted, past passive feel)
        $map['poly-vtbpa-c2-07'] = [
            [self::INVERSION_NEG, 1],
            [self::PASSIVE_PAST_SIMPLE, 1],
            $vtbpForms,
        ];
        // C2-08 — "Міст був не лише небезпечним, а й..." (not only ... but also)
        $map['poly-vtbpa-c2-08'] = [
            [self::INVERSION_NEG, 1],
            $vtbpForms,
            $vtbpHero,
        ];
        // C2-09 — "Рідко коли наслідки таких рішень..." (rarely inversion)
        $map['poly-vtbpa-c2-09'] = [
            [self::INVERSION_NEG, 1],
            $vtbpForms,
            $vtbpHero,
        ];
        // C2-10 — "...вже перевіряє влада" (past continuous passive vibe + complex clause)
        $map['poly-vtbpa-c2-10'] = [
            [self::PASSIVE_PAST_SIMPLE, 1],
            $vtbpForms,
            $vtbpHero,
        ];
        // C2-11 — "Ледве було зроблено оголошення..." (Hardly...had + past passive)
        $map['poly-vtbpa-c2-11'] = [
            [self::INVERSION_NEG, 1],
            [self::PASSIVE_PAST_SIMPLE, 1],
            $vtbpForms,
        ];
        // C2-12 — "За жодних обставин... не мали бути притягнуті..." (under no circumstances + passive subjunctive)
        $map['poly-vtbpa-c2-12'] = [
            [self::INVERSION_NEG, 1],
            [self::SUBJUNCTIVE, 1],
            [self::PASSIVE_PAST_SIMPLE, 1],
            $vtbpForms,
        ];

        return $map;
    }
}
