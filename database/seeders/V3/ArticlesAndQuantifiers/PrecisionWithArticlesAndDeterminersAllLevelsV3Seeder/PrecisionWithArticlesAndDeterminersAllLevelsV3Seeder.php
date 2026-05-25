<?php

namespace Database\Seeders\V3\ArticlesAndQuantifiers;

use App\Support\Database\JsonTestSeeder;

class PrecisionWithArticlesAndDeterminersAllLevelsV3Seeder extends JsonTestSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}
