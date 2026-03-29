<?php

namespace Database\Seeders\Page_V3\PassiveVoice\Tenses;

use App\Support\Database\JsonPageSeeder;

class PassiveVoiceFuturePerfectTheorySeeder extends JsonPageSeeder
{
    protected function definitionPath(): string
    {
        return database_path('seeders/Page_V3/definitions/passive_voice_future_perfect_theory.json');
    }
}
