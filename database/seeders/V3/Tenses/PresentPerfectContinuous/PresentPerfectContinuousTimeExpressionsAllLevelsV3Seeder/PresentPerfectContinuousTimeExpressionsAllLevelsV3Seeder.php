<?php

namespace Database\Seeders\V3\Tenses\PresentPerfectContinuous;

use App\Support\Database\JsonTestSeeder;

class PresentPerfectContinuousTimeExpressionsAllLevelsV3Seeder extends JsonTestSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}
