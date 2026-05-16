<?php

namespace Database\Seeders\V3\Tenses\PastContinuous;

use App\Support\Database\JsonTestSeeder;

class PastContinuousQuestionsAllLevelsV3Seeder extends JsonTestSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}
