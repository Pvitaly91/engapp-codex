<?php

namespace Database\Seeders\V3\FutureForms\FuturePerfectContinuous;

use App\Support\Database\JsonTestSeeder;

class FuturePerfectContinuousTimeExpressionsAllLevelsV3Seeder extends JsonTestSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}
