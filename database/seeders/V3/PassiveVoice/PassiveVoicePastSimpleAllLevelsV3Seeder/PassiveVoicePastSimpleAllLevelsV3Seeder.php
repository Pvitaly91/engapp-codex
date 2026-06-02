<?php

namespace Database\Seeders\V3\PassiveVoice;

use App\Support\Database\JsonTestSeeder;

class PassiveVoicePastSimpleAllLevelsV3Seeder extends JsonTestSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}
