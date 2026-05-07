<?php

namespace Database\Seeders\V3\Polyglot;

use App\Support\Database\V3FullGrammarTheoryLinksSeederBase;

/**
 * Theory links for the Opus46 V3 "Verb to Be: Questions & Short Answers" seeder.
 * Anchors on Verb-to-Be: Questions as the primary topic; classifier adds
 * articles, possessives, passive voice, plural nouns, conjunctions etc. —
 * full grammar breakdown (see V3FullGrammarTheoryLinksSeederBase).
 */
class V3VerbToBeQuestionsAndShortAnswersOpus46TheoryLinksSeeder extends V3FullGrammarTheoryLinksSeederBase
{
    protected function questionSeederClass(): string
    {
        return 'Database\\Seeders\\V3\\AI\\Copilot\\Opus46\\VerbToBeQuestionsAndShortAnswersV3QuestionsOnlySeeder';
    }

    protected function primaryFeature(): string
    {
        return 'verb_to_be_questions';
    }
}
