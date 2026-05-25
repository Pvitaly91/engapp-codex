<?php

namespace Database\Seeders\V3\FormalEnglish;

use App\Support\Database\JsonTestSeeder;

class FormalRegisterAndNominalisationBasicsAllLevelsV3Seeder extends JsonTestSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}
