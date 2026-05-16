<?php

namespace Database\Seeders\V3\Tenses\PresentContinuous;

use App\Support\Database\JsonTestSeeder;

class PresentContinuousQuestionsAllLevelsV3Seeder extends JsonTestSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}
