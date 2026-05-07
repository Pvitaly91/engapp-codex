<?php

namespace Database\Seeders\V3\Polyglot;

use App\Models\Question;
use App\Models\TextBlock;
use App\Support\Database\QuestionUuidResolver;
use App\Support\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Links each question of the polyglot-verb-to-be-present-all-levels test
 * (the canonical "Verb to Be" entry test surfaced under /theory/verb-to-be)
 * to the most relevant text blocks of the "Verb to Be: Present Forms"
 * theory page so the test UI renders contextual theory hints — same UX
 * as the Polyglot lessons under courses.
 *
 * Linkage is many-to-many through the question_theory_text_blocks pivot.
 * Higher-level sentences pull blocks from cross-page anchors when the
 * pattern goes beyond basic to be (Articles a/an/the for sentences with
 * indefinite singular nouns; Inversion Basics for cleft / fronting
 * patterns frequent in C2).
 *
 * For backward compatibility the legacy single Question.theory_text_block_uuid
 * column is also populated with the first block in the curated list.
 *
 * This seeder does not modify the lesson definition or any theory page seeder.
 */
class PolyglotVerbToBePresentAllLevelsTheoryLinksSeeder extends Seeder
{
    /** "Verb to Be: Present Forms" theory page — primary anchor of the test. */
    private const VTBP = 'Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\VerbToBePresentTheorySeeder';

    /** "There Is / There Are" — used for sentences like "Книга на столі". */
    private const TITTA = 'Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\ThereIsThereAreTheorySeeder';

    /** "Articles a / an / the" — used when the English version needs an article. */
    private const ARTICLES = 'Database\\Seeders\\Page_V3\\NounsArticlesQuantity\\NounsArticlesQuantityArticlesTheorySeeder';

    /** "Inversion Basics" — used for cleft / fronting / nor inversions in C2. */
    private const INVERSION = 'Database\\Seeders\\Page_V3\\BasicGrammar\\WordOrder\\InversionBasicsTheorySeeder';

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
     * Sort orders inside the "Verb to Be: Present Forms" theory page:
     *   1 hero, 2 forms-grid (am / is / are), 3 usage-panels (when to use),
     *   4 comparison-table (singular vs plural), 5 mistakes-grid, 6 summary.
     *
     * @return array<string, array<int, array{0: string, 1: int}>>
     */
    private function questionMap(): array
    {
        $hero = [self::VTBP, 1];
        $forms = [self::VTBP, 2];
        $usage = [self::VTBP, 3];
        $comparison = [self::VTBP, 4];
        $mistakes = [self::VTBP, 5];
        $summary = [self::VTBP, 6];

        // Cross-page anchors.
        $articlesHero = [self::ARTICLES, 1];
        $articlesAOrAn = [self::ARTICLES, 3];
        $thereIsHero = [self::TITTA, 1];
        $thereIsForms = [self::TITTA, 2];
        $inversionHero = [self::INVERSION, 1];
        $inversionRules = [self::INVERSION, 2];

        // Bundles per sentence pattern.
        $affirmative = [$forms, $usage, $comparison, $hero];
        $withArticles = array_merge($affirmative, [$articlesHero, $articlesAOrAn]);
        $withThereIs = array_merge($affirmative, [$thereIsHero, $thereIsForms]);
        $withInversion = [$inversionHero, $inversionRules, $forms, $hero];
        $advanced = [$forms, $usage, $hero, $summary];

        $map = [];

        // ───────── A1 (12) — basic affirmatives ─────────
        $map['poly-vtbpr-a1-01'] = $affirmative;                  // (text missing in DB)
        $map['poly-vtbpr-a1-02'] = $affirmative;                  // Я щасливий сьогодні.
        $map['poly-vtbpr-a1-03'] = $affirmative;                  // Я з України.
        $map['poly-vtbpr-a1-04'] = $affirmative;                  // Мені десять років.
        $map['poly-vtbpr-a1-05'] = $withArticles;                 // Вона вчителька. (a teacher)
        $map['poly-vtbpr-a1-06'] = $affirmative;                  // Він мій друг. (possessive)
        $map['poly-vtbpr-a1-07'] = $withArticles;                 // Це кіт. (a cat / This is a cat)
        $map['poly-vtbpr-a1-08'] = $withThereIs;                  // Книга на столі. (The book is / There is a book)
        $map['poly-vtbpr-a1-09'] = $affirmative;                  // Моя мама вдома.
        $map['poly-vtbpr-a1-10'] = $affirmative;                  // Надворі холодно. (it is cold)
        $map['poly-vtbpr-a1-11'] = $affirmative;                  // Ти дуже добрий.
        $map['poly-vtbpr-a1-12'] = $affirmative;                  // Ми в парку.

        // ───────── A2 (12) — affirmatives with adjectives, professions, locations ──
        $map['poly-vtbpr-a2-01'] = $affirmative;                  // Я справді зайнятий сьогодні.
        $map['poly-vtbpr-a2-02'] = $withArticles;                 // Вона справді хороша співачка.
        $map['poly-vtbpr-a2-03'] = $affirmative;                  // Вони чекають на тебе надворі. (mostly continuous, but treat as affirmative)
        $map['poly-vtbpr-a2-04'] = $affirmative;                  // Він з Бразилії.
        $map['poly-vtbpr-a2-05'] = $withArticles;                 // Вона наша нова керівниця проєкту.
        $map['poly-vtbpr-a2-06'] = $affirmative;                  // Вони готові до фінальної репетиції.
        $map['poly-vtbpr-a2-07'] = $affirmative;                  // Я впевнений, що це правильна адреса.
        $map['poly-vtbpr-a2-08'] = $affirmative;                  // Надворі достатньо тепло...
        $map['poly-vtbpr-a2-09'] = $affirmative;                  // Ти завжди вчасно...
        $map['poly-vtbpr-a2-10'] = $affirmative;                  // Її офіс на другому поверсі.
        $map['poly-vtbpr-a2-11'] = $affirmative;                  // Вони наші нові сусіди з Варшави.
        $map['poly-vtbpr-a2-12'] = $affirmative;                  // Новий ноутбук напрочуд легкий.

        // ───────── B1 (12) — extended affirmatives, possessives, plural & uncountable ──
        $map['poly-vtbpr-b1-01'] = $affirmative;                  // Попри тиск, я спокійний...
        $map['poly-vtbpr-b1-02'] = $affirmative;                  // Він відповідальний...
        $map['poly-vtbpr-b1-03'] = $affirmative;                  // Вони серед найнадійніших постачальників...
        $map['poly-vtbpr-b1-04'] = $affirmative;                  // Найближча лікарня...
        $map['poly-vtbpr-b1-05'] = $affirmative;                  // Ті люди на фото — мої колишні колеги.
        $map['poly-vtbpr-b1-06'] = $affirmative;                  // Вона незвично тиха під час нарад.
        $map['poly-vtbpr-b1-07'] = $affirmative;                  // Наша найбільша перевага — гнучкість.
        $map['poly-vtbpr-b1-08'] = $affirmative;                  // Твої бабуся й дідусь...
        $map['poly-vtbpr-b1-09'] = $affirmative;                  // Ця новина досить дивна.
        $map['poly-vtbpr-b1-10'] = $affirmative;                  // Математика — мій улюблений предмет.
        $map['poly-vtbpr-b1-11'] = [$comparison, $forms, $mistakes, $hero]; // Ножиці на столі. (plural-only noun → "scissors are")
        $map['poly-vtbpr-b1-12'] = [$comparison, $forms, $mistakes, $hero]; // Його штани сині. (plural-only)

        // ───────── B2 (12) — complex subject phrases, abstract topics ──
        for ($i = 1; $i <= 12; $i++) {
            $map[sprintf('poly-vtbpr-b2-%02d', $i)] = $advanced;
        }

        // ───────── C1 (12) — formal style, passive-feel constructions ──
        for ($i = 1; $i <= 12; $i++) {
            $map[sprintf('poly-vtbpr-c1-%02d', $i)] = $advanced;
        }

        // ───────── C2 (12) — many cleft / fronting / inversion structures ──
        $map['poly-vtbpr-c2-01'] = $withInversion;                // Блаженні ті, хто... (Blessed are those)
        $map['poly-vtbpr-c2-02'] = $withInversion;                // Минулі ті дні... (Gone are the days)
        $map['poly-vtbpr-c2-03'] = $withInversion;                // Такий парадокс... (Such is the paradox)
        $map['poly-vtbpr-c2-04'] = $withInversion;                // Рідкісні випадки... (Rare are the cases)
        $map['poly-vtbpr-c2-05'] = $advanced;                     // Відповідальність...
        $map['poly-vtbpr-c2-06'] = $advanced;                     // Наслідки... є далекосяжними.
        $map['poly-vtbpr-c2-07'] = $withThereIs;                  // Є вагомі підстави... (There are good grounds)
        $map['poly-vtbpr-c2-08'] = $advanced;                     // Гіпотезу...викладено... (passive feel, but uses is)
        $map['poly-vtbpr-c2-09'] = $withInversion;                // Ані якість, ані естетика... (Neither X nor Y is)
        $map['poly-vtbpr-c2-10'] = $advanced;                     // Етика... є предметом дискусій.
        $map['poly-vtbpr-c2-11'] = $advanced;                     // Кір і досі є серйозною проблемою.
        $map['poly-vtbpr-c2-12'] = $advanced;                     // Місцезнаходження... невідоме.

        return $map;
    }
}
