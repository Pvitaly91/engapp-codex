<?php

namespace Database\Seeders\V3\BasicGrammar;

use App\Support\Database\JsonTestSeeder;

class ImperativesAllLevelsV3Seeder extends JsonTestSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}
