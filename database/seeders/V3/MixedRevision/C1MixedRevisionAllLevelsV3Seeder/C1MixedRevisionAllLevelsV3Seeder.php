<?php

namespace Database\Seeders\V3\MixedRevision;

use App\Support\Database\JsonTestSeeder;

class C1MixedRevisionAllLevelsV3Seeder extends JsonTestSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}
