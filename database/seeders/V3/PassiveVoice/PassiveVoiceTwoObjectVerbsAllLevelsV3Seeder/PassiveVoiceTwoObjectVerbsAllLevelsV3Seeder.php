<?php

namespace Database\Seeders\V3\PassiveVoice;

use App\Support\Database\JsonTestSeeder;

class PassiveVoiceTwoObjectVerbsAllLevelsV3Seeder extends JsonTestSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}
