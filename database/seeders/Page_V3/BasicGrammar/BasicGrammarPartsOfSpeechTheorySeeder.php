<?php

namespace Database\Seeders\Page_V3\BasicGrammar;

use App\Support\Database\JsonPageSeeder;

class BasicGrammarPartsOfSpeechTheorySeeder extends JsonPageSeeder
{
    protected function definitionPath(): string
    {
        return database_path('seeders/Page_V3/definitions/basic_grammar_parts_of_speech_theory.json');
    }
}
