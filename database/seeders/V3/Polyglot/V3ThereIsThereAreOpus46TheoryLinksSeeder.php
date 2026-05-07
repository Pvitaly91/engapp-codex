<?php

namespace Database\Seeders\V3\Polyglot;

use App\Support\Database\V3FullGrammarTheoryLinksSeederBase;

/**
 * Theory links for the Opus46 V3 "There Is / There Are" seeder.
 * Anchors on the existential There-Is/Are page as the primary topic;
 * classifier adds articles, possessives, plural nouns, quantifiers,
 * conjunctions, etc. — full grammar breakdown
 * (see V3FullGrammarTheoryLinksSeederBase).
 */
class V3ThereIsThereAreOpus46TheoryLinksSeeder extends V3FullGrammarTheoryLinksSeederBase
{
    protected function questionSeederClass(): string
    {
        return 'Database\\Seeders\\V3\\AI\\Copilot\\Opus46\\ThereIsThereAreV3QuestionsOnlySeeder';
    }

    protected function primaryFeature(): string
    {
        return 'there_is_there_are';
    }
}
