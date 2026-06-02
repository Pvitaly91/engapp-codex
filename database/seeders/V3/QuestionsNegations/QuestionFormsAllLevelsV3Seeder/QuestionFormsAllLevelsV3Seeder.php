<?php

namespace Database\Seeders\V3\QuestionsNegations;

use App\Support\Database\JsonTestSeeder;

class QuestionFormsAllLevelsV3Seeder extends JsonTestSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}
