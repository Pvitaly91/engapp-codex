<?php

namespace Database\Seeders\V3\NounsArticlesQuantity;

use App\Support\Database\JsonTestSeeder;

class CollectiveNounsAllLevelsV3Seeder extends JsonTestSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}
