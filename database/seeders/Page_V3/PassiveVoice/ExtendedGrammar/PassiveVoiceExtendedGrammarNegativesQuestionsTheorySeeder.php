<?php

namespace Database\Seeders\Page_V3\PassiveVoice\ExtendedGrammar;

use App\Support\Database\JsonPageSeeder;

class PassiveVoiceExtendedGrammarNegativesQuestionsTheorySeeder extends JsonPageSeeder
{
    protected function definitionPath(): string
    {
        return database_path('seeders/Page_V3/definitions/passive_voice_extended_grammar_negatives_questions_theory.json');
    }
}
