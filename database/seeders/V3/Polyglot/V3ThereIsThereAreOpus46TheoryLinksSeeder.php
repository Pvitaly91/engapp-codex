<?php

namespace Database\Seeders\V3\Polyglot;

use App\Support\Database\V3QuestionsTheoryLinksSeederBase;

/**
 * Theory links for the Opus46 V3 "There Is / There Are" question seeder.
 * Anchored to the "There Is / There Are" theory page; cross-page anchors
 * for past / future tense shifts, conditionals, and inversion patterns
 * common at higher levels.
 */
class V3ThereIsThereAreOpus46TheoryLinksSeeder extends V3QuestionsTheoryLinksSeederBase
{
    private const TITTA = 'Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\ThereIsThereAreTheorySeeder';
    private const VTBP_PAST = 'Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\VerbToBePastTheorySeeder';
    private const VTBF = 'Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\VerbToBeFutureTheorySeeder';
    private const SECOND_COND = 'Database\\Seeders\\Page_V3\\Conditionals\\ConditionalsSecondTheorySeeder';
    private const THIRD_COND = 'Database\\Seeders\\Page_V3\\Conditionals\\ConditionalsThirdTheorySeeder';
    private const INVERSION_NEG = 'Database\\Seeders\\Page_V3\\BasicGrammar\\WordOrder\\InversionAfterNegativeAdverbialsTheorySeeder';

    protected function questionSeederClass(): string
    {
        return 'Database\\Seeders\\V3\\AI\\Copilot\\Opus46\\ThereIsThereAreV3QuestionsOnlySeeder';
    }

    protected function bundleFor(array $bundleKeys): array
    {
        // There Is / There Are sort orders:
        // 1 hero, 2 forms-grid, 3 usage-panels, 4 comparison-table (sing vs plur),
        // 5 mistakes-grid, 6 summary-list.
        $bundle = [
            [self::TITTA, 2], [self::TITTA, 3], [self::TITTA, 4],
            [self::TITTA, 1], [self::TITTA, 5], [self::TITTA, 6],
        ];

        // there_is is the topic — most questions match it; classifier still
        // useful since it indicates the question definitely exercises this
        // exact pattern (vs being something edge-case).
        if (in_array('there_is', $bundleKeys, true)) {
            // Back the existential pattern with the underlying verb-to-be
            // paradigm at past + future for tense-shifted there is/was/will be.
            $bundle[] = [self::VTBP_PAST, 2];
            $bundle[] = [self::VTBF, 2];
        }

        if (in_array('inversion', $bundleKeys, true)) {
            $bundle[] = [self::INVERSION_NEG, 1];
            $bundle[] = [self::INVERSION_NEG, 2];
        }

        return $bundle;
    }
}
