<?php

namespace Database\Seeders\V3;

use App\Support\Database\JsonTestSeeder;

class PassiveVoiceAllTensesV3Seeder extends JsonTestSeeder
{
    protected function definitionPath(): string
    {
        return database_path('seeders/V3/definitions/passive_voice_all_tenses_v2.json');
    }
}
