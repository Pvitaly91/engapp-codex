<?php

namespace Database\Seeders\V3\Conditionals;

use App\Support\Database\JsonTestSeeder;

class MixedConditionalsAllLevelsV3Seeder extends JsonTestSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}
