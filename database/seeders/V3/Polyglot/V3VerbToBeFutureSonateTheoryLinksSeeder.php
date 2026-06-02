<?php

namespace Database\Seeders\V3\Polyglot;

use App\Support\Database\V3FullGrammarTheoryLinksSeederBase;

/**
 * Theory links for the Sonate V3 "Verb to Be: Future" seeder.
 * Same anchors as the Opus46 sibling — both feed the Mixed A1-C2 test on
 * /theory/verb-to-be/verb-to-be-future. Anchors on Verb-to-Be: Future as
 * the primary topic; classifier adds articles, possessives, passive
 * voice, plural nouns, conjunctions, etc. — full grammar breakdown
 * (see V3FullGrammarTheoryLinksSeederBase).
 */
class V3VerbToBeFutureSonateTheoryLinksSeeder extends V3FullGrammarTheoryLinksSeederBase
{
    protected function questionSeederClass(): string
    {
        return 'Database\\Seeders\\V3\\AI\\Copilot\\Sonate\\VerbToBeFutureV3QuestionsOnlySeeder';
    }

    protected function primaryFeature(): string
    {
        return 'verb_to_be_future';
    }
}
