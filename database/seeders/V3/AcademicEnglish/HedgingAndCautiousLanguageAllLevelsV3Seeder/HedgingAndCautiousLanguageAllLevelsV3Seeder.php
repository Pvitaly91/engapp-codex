<?php

namespace Database\Seeders\V3\AcademicEnglish;

use App\Support\Database\JsonTestSeeder;

class HedgingAndCautiousLanguageAllLevelsV3Seeder extends JsonTestSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}
