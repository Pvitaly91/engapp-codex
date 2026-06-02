<?php

namespace Database\Seeders\V3\ClausesAndLinkingWords;

use App\Support\Database\JsonTestSeeder;

class NonDefiningRelativeClausesAllLevelsV3Seeder extends JsonTestSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}
