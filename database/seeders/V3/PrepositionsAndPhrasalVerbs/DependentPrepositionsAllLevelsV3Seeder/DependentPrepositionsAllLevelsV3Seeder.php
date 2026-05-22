<?php

namespace Database\Seeders\V3\PrepositionsAndPhrasalVerbs;

use App\Support\Database\JsonTestSeeder;

class DependentPrepositionsAllLevelsV3Seeder extends JsonTestSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}
