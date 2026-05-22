<?php

namespace Database\Seeders\V3\VerbPatterns;

use App\Support\Database\JsonTestSeeder;

class GerundVsInfinitiveAllLevelsV3Seeder extends JsonTestSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}
