<?php

namespace Database\Seeders\V3\Tenses\PastSimple;

use App\Support\Database\JsonTestSeeder;

class PastSimpleTimeExpressionsAllLevelsV3Seeder extends JsonTestSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}
