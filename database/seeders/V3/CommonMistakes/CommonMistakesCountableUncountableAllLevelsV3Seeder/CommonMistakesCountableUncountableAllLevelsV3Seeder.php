<?php

namespace Database\Seeders\V3\CommonMistakes;

use App\Support\Database\JsonTestSeeder;

class CommonMistakesCountableUncountableAllLevelsV3Seeder extends JsonTestSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}
