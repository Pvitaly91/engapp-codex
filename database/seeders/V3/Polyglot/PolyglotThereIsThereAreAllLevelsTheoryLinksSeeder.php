<?php

namespace Database\Seeders\V3\Polyglot;

use App\Models\Question;
use App\Models\TextBlock;
use App\Support\Database\QuestionUuidResolver;
use App\Support\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Links each question of polyglot-there-is-there-are-all-levels (the
 * full A1-C2 variant surfaced under /theory/verb-to-be → "There Is /
 * There Are") to the most relevant text blocks of the "There Is /
 * There Are" theory page so the test UI renders contextual theory hints.
 *
 * Cross-page anchors (for tense / mood shifts that go beyond present
 * "there is / there are"):
 *  - Verb to Be: Past Forms — for "був парк / було багато людей" (B1).
 *  - Verb to Be: Future — for "буде зустріч / буде новий торговий центр" (B1).
 *  - Conditionals 2nd / 3rd — for hypothetical "було б менше аварій",
 *    "Якби було більше часу..." (C1-C2).
 *  - Inversion After Negative Adverbials — for "Рідко коли...",
 *    "Ніколи ще не було..." (C2 fronting).
 */
class PolyglotThereIsThereAreAllLevelsTheoryLinksSeeder extends Seeder
{
    /** "There Is / There Are" theory page — primary anchor of the test. */
    private const TITTA = 'Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\ThereIsThereAreTheorySeeder';

    /** Cross-page anchors. */
    private const VTBP_PAST = 'Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\VerbToBePastTheorySeeder';
    private const VTBF = 'Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\VerbToBeFutureTheorySeeder';
    private const SECOND_COND = 'Database\\Seeders\\Page_V3\\Conditionals\\ConditionalsSecondTheorySeeder';
    private const THIRD_COND = 'Database\\Seeders\\Page_V3\\Conditionals\\ConditionalsThirdTheorySeeder';
    private const INVERSION_NEG = 'Database\\Seeders\\Page_V3\\BasicGrammar\\WordOrder\\InversionAfterNegativeAdverbialsTheorySeeder';

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
     * Sort orders inside the "There Is / There Are" page:
     *   1 hero, 2 forms-grid, 3 usage-panels (where it is useful),
     *   4 comparison-table (singular vs plural), 5 mistakes-grid, 6 summary-list.
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
        $whHowMany = [$comparison, $forms, $summary];
        $past = [$forms, $comparison, [self::VTBP_PAST, 1], [self::VTBP_PAST, 2]];
        $pastNegative = [$forms, $comparison, $mistakes, [self::VTBP_PAST, 3]];
        $pastQuestion = [$forms, $usage, [self::VTBP_PAST, 4], [self::VTBP_PAST, 1]];
        $future = [$forms, $hero, [self::VTBF, 1], [self::VTBF, 2]];
        $hedgedAffirmative = [$forms, $usage, $summary, $hero];
        $with2ndCond = [[self::SECOND_COND, 1], [self::SECOND_COND, 2], $forms, $hero];
        $with3rdCond = [[self::THIRD_COND, 1], [self::THIRD_COND, 2], $forms, $hero];
        $withInversion = [[self::INVERSION_NEG, 1], [self::INVERSION_NEG, 2], $forms, $hero];

        $map = [];

        // ───────── A1 (12) — singular + plural affirmatives, uncountable ──
        // Note: the seeder definition has only 11 valid entries (no q02 in DB).
        for ($i = 1; $i <= 12; $i++) {
            $map[sprintf('poly-thia-a1-%02d', $i)] = $affirmative;
        }

        // ───────── A2 (12) — negatives, yes/no, uncountable ──
        $map['poly-thia-a2-01'] = $negative;              // У холодильнику немає молока.
        $map['poly-thia-a2-02'] = $negative;              // У коридорі немає стільців.
        $map['poly-thia-a2-03'] = $yesNo;                 // Чи є неподалік лікарня?
        $map['poly-thia-a2-04'] = $yesNo;                 // ...якісь магазини?
        $map['poly-thia-a2-05'] = $affirmative;           // У склянці є вода.
        $map['poly-thia-a2-06'] = $affirmative;           // У парку є діти.
        $map['poly-thia-a2-07'] = $yesNo;                 // У мисці є цукор?
        $map['poly-thia-a2-08'] = $yesNo;                 // ...якісь автобуси?
        $map['poly-thia-a2-09'] = $negative;              // На столі немає хліба.
        $map['poly-thia-a2-10'] = $negative;              // ...немає вікон.
        $map['poly-thia-a2-11'] = $yesNo;                 // ...чи є біля станції аптека?
        // a2-12 не існує у списку — лишимо запасний бандл якщо є.
        $map['poly-thia-a2-12'] = $affirmative;

        // ───────── B1 (12) — past + future + mixed ──
        $map['poly-thia-b1-01'] = $past;                  // ...був великий парк, коли я був дитиною.
        $map['poly-thia-b1-02'] = $past;                  // ...на концерті було багато людей.
        $map['poly-thia-b1-03'] = $pastQuestion;          // Учора...були якісь проблеми...?
        $map['poly-thia-b1-04'] = $pastNegative;          // ...не було електрики.
        $map['poly-thia-b1-05'] = $future;                // Завтра буде зустріч.
        $map['poly-thia-b1-06'] = $past;                  // Минулого місяця...сталося кілька аварій.
        $map['poly-thia-b1-07'] = $future;                // Наступного року...буде новий торговий центр.
        $map['poly-thia-b1-08'] = $past;                  // Минулої ночі...було багато шуму.
        $map['poly-thia-b1-09'] = $pastQuestion;          // ...десять років тому був кінотеатр?
        $map['poly-thia-b1-10'] = $future;                // На роботі будуть певні зміни.
        $map['poly-thia-b1-11'] = $pastNegative;          // Учора не вистачило часу...
        $map['poly-thia-b1-12'] = $past;                  // Минулого літа...було багато туристів.

        // ───────── B2 (12) — present perfect-feel, hedged style ──
        $map['poly-thia-b2-01'] = $past;                  // Цього місяця було багато дощу.
        $map['poly-thia-b2-02'] = $past;                  // ...було кілька скарг...
        $map['poly-thia-b2-03'] = $pastQuestion;          // ...були якісь покращення?
        $map['poly-thia-b2-04'] = $pastNegative;          // Досі не було жодного прогресу...
        $map['poly-thia-b2-05'] = $past;                  // Цього кварталу було надто багато затримок.
        $map['poly-thia-b2-06'] = $pastQuestion;          // Чи були якісь оновлення політики?
        $map['poly-thia-b2-07'] = $hedgedAffirmative;     // Здається, у галузі бракує...
        $map['poly-thia-b2-08'] = $hedgedAffirmative;     // Здається, у нас є кілька...
        $map['poly-thia-b2-09'] = $hedgedAffirmative;     // Останнім часом спостерігається... зростання.
        $map['poly-thia-b2-10'] = $past;                  // ...були деякі цікаві події.
        $map['poly-thia-b2-11'] = $hedgedAffirmative;     // Здається, зростає занепокоєння...
        $map['poly-thia-b2-12'] = $past;                  // Із січня рівень безробіття... знизився.

        // ───────── C1 (12) — hedged, conditional, formal ──
        $map['poly-thia-c1-01'] = $hedgedAffirmative;     // Здається, ...сталося непорозуміння.
        $map['poly-thia-c1-02'] = $with3rdCond;           // Мали б бути суворіші правила, якби...
        $map['poly-thia-c1-03'] = $negative;              // Немає сенсу заперечувати...
        $map['poly-thia-c1-04'] = $hedgedAffirmative;     // Кажуть, що ...є таємний хід.
        $map['poly-thia-c1-05'] = $affirmative;           // Залишається кілька невирішених питань.
        $map['poly-thia-c1-06'] = $affirmative;           // ...існують суттєві відмінності.
        $map['poly-thia-c1-07'] = $hedgedAffirmative;     // Схоже, ...стався значний зсув.
        $map['poly-thia-c1-08'] = $negative;              // Не обов’язково має бути конфлікт...
        $map['poly-thia-c1-09'] = $hedgedAffirmative;     // Вважають, що ...є понад тисяча видів.
        $map['poly-thia-c1-10'] = $with2ndCond;           // Якби обмеження дотримувалися, аварій було б менше.
        $map['poly-thia-c1-11'] = $past;                  // Серед дослідників сформувався консенсус.
        $map['poly-thia-c1-12'] = $negative;              // Неможливо передбачити, яким буде результат.

        // ───────── C2 (12) — inversion, conditional, formal narration ──
        $map['poly-thia-c2-01'] = $future;                // Настане час, коли кожна імперія муситиме впасти.
        $map['poly-thia-c2-02'] = $with2ndCond;           // У разі раптового спаду економіка ... постраждала б.
        $map['poly-thia-c2-03'] = $withInversion;         // Рідко коли серед критиків була така одностайність.
        $map['poly-thia-c2-04'] = $past;                  // ...розгорілася запекла дискусія...
        $map['poly-thia-c2-05'] = $withInversion;         // Ніколи ще не було такої нагальної потреби...
        $map['poly-thia-c2-06'] = $past;                  // З попелу поставав новий рух...
        $map['poly-thia-c2-07'] = [$forms, $usage, $comparison, [self::SECOND_COND, 2]]; // Якщо будуть будь-які заперечення...
        $map['poly-thia-c2-08'] = $past;                  // ...панувала атмосфера обережного оптимізму.
        $map['poly-thia-c2-09'] = $withInversion;         // Рідко коли справа привертала таку широку увагу...
        $map['poly-thia-c2-10'] = $past;                  // ...існувала давня традиція гостинності.
        $map['poly-thia-c2-11'] = $with3rdCond;           // Якби було більше часу, комітет міг би...
        $map['poly-thia-c2-12'] = $past;                  // Після цього настав період... процвітання...

        return $map;
    }
}
