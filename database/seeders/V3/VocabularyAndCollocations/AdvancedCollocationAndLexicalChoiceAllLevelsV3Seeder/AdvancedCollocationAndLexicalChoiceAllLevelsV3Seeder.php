<?php

namespace Database\Seeders\V3\VocabularyAndCollocations;

use App\Support\Database\JsonTestSeeder;

class AdvancedCollocationAndLexicalChoiceAllLevelsV3Seeder extends JsonTestSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}
