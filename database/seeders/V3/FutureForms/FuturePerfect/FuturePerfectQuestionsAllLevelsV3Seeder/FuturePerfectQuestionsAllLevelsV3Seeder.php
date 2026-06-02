<?php

namespace Database\Seeders\V3\FutureForms\FuturePerfect;

use App\Support\Database\JsonTestSeeder;

class FuturePerfectQuestionsAllLevelsV3Seeder extends JsonTestSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}
