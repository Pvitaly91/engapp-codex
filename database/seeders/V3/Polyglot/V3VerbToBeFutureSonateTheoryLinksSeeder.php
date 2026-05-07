<?php

namespace Database\Seeders\V3\Polyglot;

use App\Support\Database\V3QuestionsTheoryLinksSeederBase;

/**
 * Theory links for the Sonate V3 "Verb to Be: Future" question seeder.
 * Same anchors as the Opus46 sibling — both feed the same mixed test on
 * /theory/verb-to-be/verb-to-be-future.
 */
class V3VerbToBeFutureSonateTheoryLinksSeeder extends V3QuestionsTheoryLinksSeederBase
{
    private const VTBF = 'Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\VerbToBeFutureTheorySeeder';
    private const PASSIVE_FUTURE = 'Database\\Seeders\\Page_V3\\PassiveVoice\\Tenses\\PassiveVoiceFutureSimpleTheorySeeder';
    private const FIRST_COND = 'Database\\Seeders\\Page_V3\\Conditionals\\ConditionalsFirstTheorySeeder';
    private const INVERSION_NEG = 'Database\\Seeders\\Page_V3\\BasicGrammar\\WordOrder\\InversionAfterNegativeAdverbialsTheorySeeder';
    private const TITTA = 'Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\ThereIsThereAreTheorySeeder';

    protected function questionSeederClass(): string
    {
        return 'Database\\Seeders\\V3\\AI\\Copilot\\Sonate\\VerbToBeFutureV3QuestionsOnlySeeder';
    }

    protected function bundleFor(array $bundleKeys): array
    {
        $bundle = [
            [self::VTBF, 2], [self::VTBF, 3], [self::VTBF, 4],
            [self::VTBF, 1], [self::VTBF, 5], [self::VTBF, 6], [self::VTBF, 7],
        ];

        if (in_array('there_is', $bundleKeys, true)) {
            $bundle[] = [self::TITTA, 1];
            $bundle[] = [self::TITTA, 2];
        }

        if (in_array('inversion', $bundleKeys, true)) {
            $bundle[] = [self::INVERSION_NEG, 1];
            $bundle[] = [self::INVERSION_NEG, 2];
        }

        return $bundle;
    }
}
