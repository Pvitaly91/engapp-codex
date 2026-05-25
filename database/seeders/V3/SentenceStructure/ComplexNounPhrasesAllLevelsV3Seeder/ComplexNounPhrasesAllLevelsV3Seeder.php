<?php

namespace Database\Seeders\V3\SentenceStructure;

use App\Support\Database\JsonTestSeeder;

class ComplexNounPhrasesAllLevelsV3Seeder extends JsonTestSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}
