<?php

namespace Database\Seeders\V3\Tenses\Extra;

use App\Support\Database\JsonTestSeeder;

class NarrativeTensesAllLevelsV3Seeder extends JsonTestSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}
