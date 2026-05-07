<?php

namespace Database\Seeders\V3\Polyglot;

use App\Support\Database\V3FullGrammarTheoryLinksSeederBase;

/**
 * Theory links for the Opus46 V3 "Verb to Be: Present Forms" seeder.
 * Produces a FULL grammar breakdown — see V3FullGrammarTheoryLinksSeederBase.
 */
class V3VerbToBePresentFormsOpus46TheoryLinksSeeder extends V3FullGrammarTheoryLinksSeederBase
{
    protected function questionSeederClass(): string
    {
        return 'Database\\Seeders\\V3\\AI\\Copilot\\Opus46\\VerbToBePresentFormsV3QuestionsOnlySeeder';
    }
}
