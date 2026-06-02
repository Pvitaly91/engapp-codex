<?php

namespace Database\Seeders\Page_V3\VocabularyAndCollocations;

use App\Support\Database\JsonPageSeeder;

class AdvancedCollocationAndLexicalChoiceTheorySeeder extends JsonPageSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}
