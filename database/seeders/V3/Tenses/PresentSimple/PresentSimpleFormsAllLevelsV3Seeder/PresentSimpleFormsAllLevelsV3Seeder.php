<?php

namespace Database\Seeders\V3\Tenses\PresentSimple;

use App\Support\Database\JsonTestSeeder;

class PresentSimpleFormsAllLevelsV3Seeder extends JsonTestSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}
