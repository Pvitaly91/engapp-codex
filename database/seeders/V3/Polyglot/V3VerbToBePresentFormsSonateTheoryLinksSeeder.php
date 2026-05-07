<?php

namespace Database\Seeders\V3\Polyglot;

use App\Support\Database\V3FullGrammarTheoryLinksSeederBase;

/**
 * Theory links for the Sonate V3 "Verb to Be: Present Forms" seeder.
 * Produces a FULL grammar breakdown — see V3FullGrammarTheoryLinksSeederBase.
 *
 * This is the seeder that produced "The proceeds from the annual charity
 * auction ___ donated to a local children's hospital." — without this
 * upgrade the question would only get the basic "to be" paradigm hint;
 * with it, the panel now also surfaces Articles, Adjective order,
 * Possessive 's, Passive voice, Plural nouns, etc. — every grammar
 * topic that appears in the sentence.
 */
class V3VerbToBePresentFormsSonateTheoryLinksSeeder extends V3FullGrammarTheoryLinksSeederBase
{
    protected function questionSeederClass(): string
    {
        return 'Database\\Seeders\\V3\\AI\\Copilot\\Sonate\\VerbToBePresentFormsV3QuestionsOnlySeeder';
    }
}
