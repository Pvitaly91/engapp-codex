<?php

namespace Database\Seeders\Page_V3\PassiveVoice\ExtendedGrammar;

use App\Support\Database\JsonPageSeeder;

class PassiveVoiceExtendedGrammarKeyTensesTheorySeeder extends JsonPageSeeder
{
    protected function definitionPath(): string
    {
        return database_path('seeders/Page_V3/definitions/passive_voice_extended_grammar_key_tenses_theory.json');
    }
}
