<?php

namespace Database\Seeders\V3\PronounsDemonstratives;

use App\Support\Database\JsonTestSeeder;

class ReciprocalPronounsAllLevelsV3Seeder extends JsonTestSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}
