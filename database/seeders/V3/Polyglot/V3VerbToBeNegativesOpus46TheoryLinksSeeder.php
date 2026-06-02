<?php

namespace Database\Seeders\V3\Polyglot;

use App\Support\Database\V3FullGrammarTheoryLinksSeederBase;

/**
 * Theory links for the Opus46 V3 "Verb to Be: Negatives" seeder.
 * Anchors on Verb-to-Be: Negatives as the primary topic; classifier adds
 * articles, possessives, passive voice, plural nouns, etc. — full
 * grammar breakdown (see V3FullGrammarTheoryLinksSeederBase).
 */
class V3VerbToBeNegativesOpus46TheoryLinksSeeder extends V3FullGrammarTheoryLinksSeederBase
{
    protected function questionSeederClass(): string
    {
        return 'Database\\Seeders\\V3\\AI\\Copilot\\Opus46\\VerbToBeNegativesV3QuestionsOnlySeeder';
    }

    protected function primaryFeature(): string
    {
        return 'verb_to_be_negatives';
    }
}
