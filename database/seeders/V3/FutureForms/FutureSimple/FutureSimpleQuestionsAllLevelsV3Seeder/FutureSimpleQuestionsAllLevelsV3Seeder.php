<?php

namespace Database\Seeders\V3\FutureForms\FutureSimple;

use App\Support\Database\JsonTestSeeder;

class FutureSimpleQuestionsAllLevelsV3Seeder extends JsonTestSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}

