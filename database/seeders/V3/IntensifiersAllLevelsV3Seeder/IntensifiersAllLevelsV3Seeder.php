<?php

namespace Database\Seeders\V3;

use App\Support\Database\JsonTestSeeder;

class IntensifiersAllLevelsV3Seeder extends JsonTestSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}
