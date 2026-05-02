<?php

namespace Database\Seeders\V3\Polyglot;

use App\Models\Question;
use App\Models\TextBlock;
use App\Support\Database\QuestionUuidResolver;
use App\Support\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Links each question of the polyglot-to-be-a1 lesson (the A1 entry-point
 * lesson of the Polyglot English A1 course) to the most relevant theory
 * text blocks so the test UI renders contextual theory hints.
 *
 * Linkage is many-to-many through the question_theory_text_blocks pivot.
 * Affirmatives draw from "Verb to Be: Present Forms"; negatives add the
 * "Verb to Be: Negatives" page; questions add "Verb to Be: Questions"; and
 * sentences with a singular indefinite noun also surface a block from the
 * "Articles a / an / the" page.
 *
 * The legacy single Question.theory_text_block_uuid column is also
 * populated with the first block in the curated list.
 *
 * This seeder does not modify the lesson definition or any theory page seeder.
 */
class PolyglotToBeA1TheoryLinksSeeder extends Seeder
{
    /** Verb to Be: Present Forms — primary anchor of the lesson. */
    private const VTBP = 'Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\VerbToBePresentTheorySeeder';

    /** Cross-page anchors. */
    private const VTB_NEG = 'Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\VerbToBeNegativesTheorySeeder';
    private const VTB_Q = 'Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\VerbToBeQuestionsTheorySeeder';
    private const ARTICLES = 'Database\\Seeders\\Page_V3\\NounsArticlesQuantity\\NounsArticlesQuantityArticlesTheorySeeder';

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
     * @return array<string, array<int, array{0: string, 1: int}>>
     */
    private function questionMap(): array
    {
        // Sort orders inside each theory page (matching their definition.json):
        // VTBP: 1 hero, 2 forms-grid am/is/are, 3 usage-panels, 4 comparison-table
        // VTB_NEG: 1 hero, 2 forms, 3 usage, 4 affirmative vs negative table
        // VTB_Q: 1 hero, 2 forms (yes/no + WH), 3 short-answers usage, 4 short-answer table
        // ARTICLES: 1 hero, 2 three-articles forms-grid, 3 a vs an usage, 5 the usage

        // Curated block bundles for the four major sentence patterns.
        $affirmativeBundle = [
            [self::VTBP, 1],
            [self::VTBP, 2],
            [self::VTBP, 4],
            [self::VTBP, 3],
        ];
        $negativeBundle = [
            [self::VTB_NEG, 1],
            [self::VTB_NEG, 2],
            [self::VTBP, 2],
            [self::VTB_NEG, 4],
        ];
        $yesNoBundle = [
            [self::VTB_Q, 1],
            [self::VTB_Q, 2],
            [self::VTB_Q, 4],
            [self::VTBP, 2],
        ];
        $whBundle = [
            [self::VTB_Q, 1],
            [self::VTB_Q, 2],
            [self::VTBP, 2],
        ];
        $articlesAddon = [
            [self::ARTICLES, 1],
            [self::ARTICLES, 3],
        ];

        $withArticles = static function (array $bundle) use ($articlesAddon): array {
            return array_merge($bundle, $articlesAddon);
        };

        $map = [];

        // ───────── Batch 1: polyglot-to-be-q01..q24 ─────────
        // q01..q08 — affirmatives.
        for ($i = 1; $i <= 8; $i++) {
            $map[sprintf('polyglot-to-be-q%02d', $i)] = $affirmativeBundle;
        }
        // q07 "Я студент." / q08 "Вона лікар." — needs an article in English.
        $map['polyglot-to-be-q07'] = $withArticles($affirmativeBundle);
        $map['polyglot-to-be-q08'] = $withArticles($affirmativeBundle);
        // q09..q14 — negatives.
        for ($i = 9; $i <= 14; $i++) {
            $map[sprintf('polyglot-to-be-q%02d', $i)] = $negativeBundle;
        }
        // q15..q20 — yes/no questions.
        for ($i = 15; $i <= 20; $i++) {
            $map[sprintf('polyglot-to-be-q%02d', $i)] = $yesNoBundle;
        }
        // q21..q24 — WH-questions.
        for ($i = 21; $i <= 24; $i++) {
            $map[sprintf('polyglot-to-be-q%02d', $i)] = $whBundle;
        }

        // ───────── Batch 2: polyglot-to-be-a1-x-co47-001..024 ─────────
        $b2 = static fn (int $i): string => sprintf('polyglot-to-be-a1-x-co47-%03d', $i);
        // 001 affirmative.
        $map[$b2(1)] = $affirmativeBundle;
        // 002 "Ти мій друг" — affirmative + possessive.
        $map[$b2(2)] = $affirmativeBundle;
        // 003 "Вона вчителька" — needs an article.
        $map[$b2(3)] = $withArticles($affirmativeBundle);
        // 004 "Він не лікар" — negative + needs an article.
        $map[$b2(4)] = $withArticles($negativeBundle);
        // 005 affirmative.
        $map[$b2(5)] = $affirmativeBundle;
        // 006 negative.
        $map[$b2(6)] = $negativeBundle;
        // 007 "Це легко" — affirmative (it is).
        $map[$b2(7)] = $affirmativeBundle;
        // 008 negative.
        $map[$b2(8)] = $negativeBundle;
        // 009 yes/no question.
        $map[$b2(9)] = $yesNoBundle;
        // 010 yes/no question.
        $map[$b2(10)] = $yesNoBundle;
        // 011 "Ми студенти?" — yes/no with plural noun (no article).
        $map[$b2(11)] = $yesNoBundle;
        // 012 "Вони з України" — affirmative.
        $map[$b2(12)] = $affirmativeBundle;
        // 013 "Де вона?" — WH.
        $map[$b2(13)] = $whBundle;
        // 014 "Хто він?" — WH.
        $map[$b2(14)] = $whBundle;
        // 015 "Що це?" — WH.
        $map[$b2(15)] = $whBundle;
        // 016 "Скільки тобі років?" — How old (WH idiomatic).
        $map[$b2(16)] = $whBundle;
        // 017 "Мені десять років" — I am … years old (idiomatic affirmative).
        $map[$b2(17)] = $affirmativeBundle;
        // 018 "Йому холодно" — He is cold.
        $map[$b2(18)] = $affirmativeBundle;
        // 019 "Їй жарко" — She is hot.
        $map[$b2(19)] = $affirmativeBundle;
        // 020 "Нам не пізно" — It's not late for us.
        $map[$b2(20)] = $negativeBundle;
        // 021 "Вони в школі?" — yes/no.
        $map[$b2(21)] = $yesNoBundle;
        // 022 "Мій брат лікар" — affirmative + needs article (a doctor).
        $map[$b2(22)] = $withArticles($affirmativeBundle);
        // 023 "Мої батьки вдома" — plural affirmative.
        $map[$b2(23)] = $affirmativeBundle;
        // 024 "Ця кімната чиста" — needs the/this.
        $map[$b2(24)] = $withArticles($affirmativeBundle);

        // ───────── Batch 3: polyglot-to-be-a1-x2-co47-001..024 ─────────
        $b3 = static fn (int $i): string => sprintf('polyglot-to-be-a1-x2-co47-%03d', $i);
        // 001 "Я вчитель" — needs article (a teacher).
        $map[$b3(1)] = $withArticles($affirmativeBundle);
        // 002 affirmative.
        $map[$b3(2)] = $affirmativeBundle;
        // 003 affirmative + possessive.
        $map[$b3(3)] = $affirmativeBundle;
        // 004 affirmative.
        $map[$b3(4)] = $affirmativeBundle;
        // 005 plural affirmative.
        $map[$b3(5)] = $affirmativeBundle;
        // 006 affirmative.
        $map[$b3(6)] = $affirmativeBundle;
        // 007 "Книга нова" — needs article (the book / a book).
        $map[$b3(7)] = $withArticles($affirmativeBundle);
        // 008 "Машина червона" — needs article.
        $map[$b3(8)] = $withArticles($affirmativeBundle);
        // 009..012 negatives.
        $map[$b3(9)] = $negativeBundle;
        $map[$b3(10)] = $negativeBundle;
        $map[$b3(11)] = $negativeBundle;
        $map[$b3(12)] = $negativeBundle;
        // 013..016 yes/no questions.
        $map[$b3(13)] = $yesNoBundle;
        $map[$b3(14)] = $yesNoBundle;
        $map[$b3(15)] = $yesNoBundle;
        $map[$b3(16)] = $yesNoBundle;
        // 017..019 WH-questions.
        $map[$b3(17)] = $whBundle;
        $map[$b3(18)] = $whBundle;
        $map[$b3(19)] = $whBundle;
        // 020 "Кіт чорний" — needs the cat.
        $map[$b3(20)] = $withArticles($affirmativeBundle);
        // 021 "Собака милий" — needs the dog.
        $map[$b3(21)] = $withArticles($affirmativeBundle);
        // 022 "Сьогодні холодно" — affirmative.
        $map[$b3(22)] = $affirmativeBundle;
        // 023 "Моя сестра вдома" — affirmative + possessive.
        $map[$b3(23)] = $affirmativeBundle;
        // 024 "Мої друзі тут" — plural affirmative.
        $map[$b3(24)] = $affirmativeBundle;

        return $map;
    }
}
