<?php

namespace Database\Seeders\V3\Polyglot;

use App\Models\Question;
use App\Models\TextBlock;
use App\Support\Database\QuestionUuidResolver;
use App\Support\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Links each question of polyglot-verb-to-be-future-all-levels (surfaced
 * under /theory/verb-to-be → "Verb to Be: Future") to the most relevant
 * text blocks of the "Verb to Be: Future" theory page so the test UI
 * renders contextual theory hints.
 *
 * Cross-page anchors (added only for questions whose grammar genuinely
 * extends beyond simple will-be):
 *  - First / Second / Third Conditionals — for if-clauses + future / past forms.
 *  - Future Simple Passive — for "буде завершено / буде доставлено" patterns.
 *  - Inversion After Negative Adverbials — for fronting / cleft patterns
 *    common in C2 ("Ледь оголошення було зроблено...", "Рідко коли...").
 *
 * Sort orders inside the "Verb to Be: Future" theory page:
 *   1 hero, 2 forms-grid (will be), 3 comparison-table (negatives),
 *   4 usage-panels (questions), 5 comparison-table (short answers),
 *   6 mistakes-grid, 7 summary-list.
 */
class PolyglotVerbToBeFutureAllLevelsTheoryLinksSeeder extends Seeder
{
    /** "Verb to Be: Future" theory page — primary anchor of the test. */
    private const VTBF = 'Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\VerbToBeFutureTheorySeeder';

    /** Cross-page anchors. */
    private const FIRST_COND = 'Database\\Seeders\\Page_V3\\Conditionals\\ConditionalsFirstTheorySeeder';
    private const SECOND_COND = 'Database\\Seeders\\Page_V3\\Conditionals\\ConditionalsSecondTheorySeeder';
    private const THIRD_COND = 'Database\\Seeders\\Page_V3\\Conditionals\\ConditionalsThirdTheorySeeder';
    private const PASSIVE_FUTURE = 'Database\\Seeders\\Page_V3\\PassiveVoice\\Tenses\\PassiveVoiceFutureSimpleTheorySeeder';
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
     * @return array<string, array<int, array{0: string, 1: int}>>
     */
    private function questionMap(): array
    {
        $hero = [self::VTBF, 1];
        $forms = [self::VTBF, 2];
        $negatives = [self::VTBF, 3];
        $questions = [self::VTBF, 4];
        $shortAnswers = [self::VTBF, 5];
        $mistakes = [self::VTBF, 6];
        $summary = [self::VTBF, 7];

        // Bundles per sentence pattern.
        $affirmative = [$forms, $hero, $summary];
        $negative = [$negatives, $forms, $hero];
        $yesNo = [$questions, $shortAnswers, $forms];
        $wh = [$questions, $forms, $summary];
        $with1stCond = [[self::FIRST_COND, 1], [self::FIRST_COND, 2], $forms, $hero];
        $with2ndCond = [[self::SECOND_COND, 1], [self::SECOND_COND, 2], $forms, $hero];
        $with3rdCond = [[self::THIRD_COND, 1], [self::THIRD_COND, 2], $forms, $hero];
        $withPassive = [[self::PASSIVE_FUTURE, 1], [self::PASSIVE_FUTURE, 2], $forms, $hero];
        $withInversion = [[self::INVERSION_NEG, 1], [self::INVERSION_NEG, 2], $forms, $hero];

        $map = [];

        // ───────── A1 (12) — simple will-be affirmatives ──
        for ($i = 1; $i <= 12; $i++) {
            $map[sprintf('poly-vtbfu-a1-%02d', $i)] = $affirmative;
        }

        // ───────── A2 (12) — WH-questions, yes/no with short answers, mixed ──
        $map['poly-vtbfu-a2-01'] = $wh;                   // Де ти будеш...?
        $map['poly-vtbfu-a2-02'] = $affirmative;          // Думаю, фільм буде цікавим.
        $map['poly-vtbfu-a2-03'] = $wh;                   // Коли результати будуть готові?
        $map['poly-vtbfu-a2-04'] = $affirmative;          // ...виповниться вісімнадцять.
        $map['poly-vtbfu-a2-05'] = $yesNo;                // Тест буде складний? — Так, буде.
        $map['poly-vtbfu-a2-06'] = $yesNo;                // Ти будеш у спортзалі? — Ні, не буду.
        $map['poly-vtbfu-a2-07'] = $wh;                   // Скільки триватиме поїздка?
        $map['poly-vtbfu-a2-08'] = $affirmative;          // ...буде новий парк.
        $map['poly-vtbfu-a2-09'] = $negative;             // Завтра домашнього завдання не буде.
        $map['poly-vtbfu-a2-10'] = $wh;                   // Хто буде наступним капітаном?
        $map['poly-vtbfu-a2-11'] = $affirmative;          // Обіцяю, я буду обережним.
        $map['poly-vtbfu-a2-12'] = $affirmative;          // Діти втомляться після поїздки.

        // ───────── B1 (12) — first conditional, passive future, mixed ──
        $map['poly-vtbfu-b1-01'] = $with1stCond;          // Якщо піде дощ, дороги будуть слизькими.
        $map['poly-vtbfu-b1-02'] = $with1stCond;          // Коли ти приїдеш...
        $map['poly-vtbfu-b1-03'] = $withPassive;          // буде завершено до грудня.
        $map['poly-vtbfu-b1-04'] = $affirmative;          // Вона зрадіє...
        $map['poly-vtbfu-b1-05'] = $affirmative;          // ...будуть у літаку. (Future Continuous)
        $map['poly-vtbfu-b1-06'] = $affirmative;          // ...проєкт буде успішним.
        $map['poly-vtbfu-b1-07'] = $affirmative;          // ...буде вільною.
        $map['poly-vtbfu-b1-08'] = $with1stCond;          // Якщо ти не вчитимешся...
        $map['poly-vtbfu-b1-09'] = $withPassive;          // Пакунок буде доставлено.
        $map['poly-vtbfu-b1-10'] = $affirmative;          // ...вона буде в аеропорту...
        $map['poly-vtbfu-b1-11'] = $affirmative;          // Часу вистачить...
        $map['poly-vtbfu-b1-12'] = $with1stCond;          // Якщо ти не подзвониш...

        // ───────── B2 (12) — passive future, conditional, formal predictions ──
        $map['poly-vtbfu-b2-01'] = $affirmative;          // ...буде основним джерелом...
        $map['poly-vtbfu-b2-02'] = $withPassive;          // Делегатів повідомлять...
        $map['poly-vtbfu-b2-03'] = $negative;             // ...не буде таким, як ми очікували.
        $map['poly-vtbfu-b2-04'] = $with1stCond;          // Якщо фінансування схвалять...
        $map['poly-vtbfu-b2-05'] = $withPassive;          // Усіх кандидатів повідомлять...
        $map['poly-vtbfu-b2-06'] = $negative;             // Малоймовірно, що... будуть простими.
        $map['poly-vtbfu-b2-07'] = $withPassive;          // ...не буде ухвалено, доки...
        $map['poly-vtbfu-b2-08'] = $affirmative;          // ...дані будуть доступні...
        $map['poly-vtbfu-b2-09'] = $with1stCond;          // Якщо команда дотримуватиметься...
        $map['poly-vtbfu-b2-10'] = $affirmative;          // ...повинні будуть подати...
        $map['poly-vtbfu-b2-11'] = $negative;             // Сумніваюся, що ремонт буде покритий...
        $map['poly-vtbfu-b2-12'] = $negative;             // ...не буде змінено, якщо не виникне...

        // ───────── C1 (12) — formal embedded, passive, hedged ──
        $map['poly-vtbfu-c1-01'] = [$questions, $forms, $summary, $hero]; // Ще належить з’ясувати, чи будуть...
        $map['poly-vtbfu-c1-02'] = $withPassive;          // ...договір буде обов’язковим...
        $map['poly-vtbfu-c1-03'] = $withPassive;          // ...злиття буде завершено...
        $map['poly-vtbfu-c1-04'] = $negative;             // ...навряд чи залишиться хоч якийсь слід...
        $map['poly-vtbfu-c1-05'] = $withPassive;          // ...буде вжито всіх запобіжних заходів.
        $map['poly-vtbfu-c1-06'] = $withPassive;          // Те, наскільки політику буде реалізовано...
        $map['poly-vtbfu-c1-07'] = $with1stCond;          // ...якщо витрати й далі зростатимуть.
        $map['poly-vtbfu-c1-08'] = [$negatives, $forms, [self::INVERSION_NEG, 1], $hero]; // За жодних обставин дедлайн не буде продовжено.
        $map['poly-vtbfu-c1-09'] = $affirmative;          // ...відбудеться пресконференція.
        $map['poly-vtbfu-c1-10'] = $affirmative;          // Якою б... не була..., нагляд залишатиметься...
        $map['poly-vtbfu-c1-11'] = [$negatives, $forms, [self::FIRST_COND, 1], $hero]; // Поки... не завершиться, ...не будуть.
        $map['poly-vtbfu-c1-12'] = $affirmative;          // ...інфраструктура застаріє.

        // ───────── C2 (12) — passive future, mandative, inversion ──
        $map['poly-vtbfu-c2-01'] = $withPassive;          // ...буде присуджено компенсацію.
        $map['poly-vtbfu-c2-02'] = [$negatives, $forms, [self::INVERSION_NEG, 1], $hero]; // більше ніколи не заплющуватиме очі...
        $map['poly-vtbfu-c2-03'] = $withPassive;          // ...буде докладено всіх зусиль...
        $map['poly-vtbfu-c2-04'] = $affirmative;          // Вкрай важливо, щоб ... можна було застосовувати... (subjunctive)
        $map['poly-vtbfu-c2-05'] = $withInversion;        // Ледь оголошення було зроблено, як стало зрозуміло...
        $map['poly-vtbfu-c2-06'] = $withInversion;        // Настільки складними будуть правила, що...
        $map['poly-vtbfu-c2-07'] = $withPassive;          // ...щоб переглянуту рамку було ухвалено... (mandative passive)
        $map['poly-vtbfu-c2-08'] = $affirmative;          // Як би там не було, проєкт буде завершено вчасно.
        $map['poly-vtbfu-c2-09'] = $affirmative;          // Незалежно від того, чи виявиться це здійсненним...
        $map['poly-vtbfu-c2-10'] = $withInversion;        // Рідко коли зміну політики зустрічатимуть з... схваленням.
        $map['poly-vtbfu-c2-11'] = $withPassive;          // ...всі дані буде анонімізовано перед публікацією.
        $map['poly-vtbfu-c2-12'] = $withInversion;        // Щойно інфраструктуру буде створено, як попит різко зросте.

        return $map;
    }
}
