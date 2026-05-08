<?php

namespace Database\Seeders\V3\Polyglot;

use App\Support\Database\V3FullGrammarTheoryLinksSeederBase;

/**
 * Theory links for the Polyglot "Past Simple: to be (A1)" lesson seeder.
 * This Polyglot lesson is also surfaced in the /theory/verb-to-be-past
 * mixed test, so its 24 A1 questions need the same theory-hint coverage
 * as the V3 question seeders.
 *
 * Anchors on Verb-to-Be: Past Forms as the primary topic; the classifier
 * adds articles, possessives, plural nouns, conjunctions, etc. — full
 * grammar breakdown (see V3FullGrammarTheoryLinksSeederBase).
 */
class PolyglotPastSimpleToBeTheoryLinksSeeder extends V3FullGrammarTheoryLinksSeederBase
{
    protected function questionSeederClass(): string
    {
        return 'Database\\Seeders\\V3\\Polyglot\\PolyglotPastSimpleToBeLessonSeeder';
    }

    protected function primaryFeature(): string
    {
        return 'verb_to_be_past';
    }
}
