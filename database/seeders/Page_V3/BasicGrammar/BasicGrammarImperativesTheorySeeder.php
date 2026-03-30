<?php

namespace Database\Seeders\Page_V3\BasicGrammar;

use App\Support\Database\JsonPageSeeder;

class BasicGrammarImperativesTheorySeeder extends JsonPageSeeder
{
    protected function definitionPath(): string
    {
        return database_path('seeders/Page_V3/definitions/basic_grammar_imperatives_theory.json');
    }
}
