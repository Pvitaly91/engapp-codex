<?php

namespace Database\Seeders\V3\Polyglot;

use App\Support\Database\V3QuestionsTheoryLinksSeederBase;

/**
 * Theory links for the Opus46 V3 "Verb to Be: Questions and Short Answers"
 * question seeder. Anchored to the "Verb to Be: Questions" theory page;
 * pulls in Negatives for tag questions and negative-leading questions,
 * Present Forms as the underlying paradigm reference.
 */
class V3VerbToBeQuestionsAndShortAnswersOpus46TheoryLinksSeeder extends V3QuestionsTheoryLinksSeederBase
{
    private const VTB_Q = 'Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\VerbToBeQuestionsTheorySeeder';
    private const VTB_NEG = 'Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\VerbToBeNegativesTheorySeeder';
    private const VTBP = 'Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\VerbToBePresentTheorySeeder';
    private const TITTA = 'Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\ThereIsThereAreTheorySeeder';
    private const INVERSION = 'Database\\Seeders\\Page_V3\\BasicGrammar\\WordOrder\\InversionBasicsTheorySeeder';

    protected function questionSeederClass(): string
    {
        return 'Database\\Seeders\\V3\\AI\\Copilot\\Opus46\\VerbToBeQuestionsAndShortAnswersV3QuestionsOnlySeeder';
    }

    protected function bundleFor(array $bundleKeys): array
    {
        // Verb to Be: Questions sort orders:
        // 1 hero, 2 forms-grid (моделі питання), 3 usage-panels (короткі відповіді),
        // 4 comparison-table (питання + відповідь), 5 mistakes-grid, 6 summary-list.
        $bundle = [
            [self::VTB_Q, 2], [self::VTB_Q, 3], [self::VTB_Q, 4],
            [self::VTB_Q, 1], [self::VTB_Q, 5], [self::VTB_Q, 6],
            [self::VTBP, 2],
        ];

        if (in_array('there_is', $bundleKeys, true)) {
            $bundle[] = [self::TITTA, 1];
            $bundle[] = [self::TITTA, 2];
            $bundle[] = [self::TITTA, 4];
        }

        if (in_array('inversion', $bundleKeys, true)) {
            $bundle[] = [self::INVERSION, 1];
            $bundle[] = [self::INVERSION, 2];
        }

        return $bundle;
    }
}
