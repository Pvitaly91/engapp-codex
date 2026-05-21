<?php

namespace Database\Seeders\V3\PassiveVoice;

use App\Support\Database\JsonTestSeeder;

class PassiveVoiceGetPassiveAllLevelsV3Seeder extends JsonTestSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}
