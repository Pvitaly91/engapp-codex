<?php

namespace Database\Seeders\V3\WordOrder;

use App\Support\Database\JsonTestSeeder;

class InversionAfterNegativeAdverbialsAllLevelsV3Seeder extends JsonTestSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}
