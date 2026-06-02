<?php

namespace Database\Seeders\V3\Tenses\Comparisons;

use App\Support\Database\JsonTestSeeder;

class PresentPerfectVsPresentPerfectContinuousAllLevelsV3Seeder extends JsonTestSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}
