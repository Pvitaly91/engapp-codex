<?php

namespace Database\Seeders\Page_V3\PassiveVoice\Tenses;

use App\Support\Database\JsonPageSeeder;

class PassiveVoicePastContinuousTheorySeeder extends JsonPageSeeder
{
    protected function definitionPath(): string
    {
        return database_path('seeders/Page_V3/definitions/passive_voice_past_continuous_theory.json');
    }
}
