<?php

namespace Database\Seeders\V3\FutureForms\FutureContinuous;

use App\Support\Database\JsonTestSeeder;

class FutureContinuousTimeExpressionsAllLevelsV3Seeder extends JsonTestSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}
