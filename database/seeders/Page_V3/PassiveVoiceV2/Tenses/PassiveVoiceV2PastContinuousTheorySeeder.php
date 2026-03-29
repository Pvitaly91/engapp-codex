<?php

namespace Database\Seeders\Page_V3\PassiveVoiceV2\Tenses;

use App\Support\Database\JsonPageSeeder;

class PassiveVoiceV2PastContinuousTheorySeeder extends JsonPageSeeder
{
    protected function definitionPath(): string
    {
        return database_path('seeders/Page_V3/definitions/passive_voice_v2_past_continuous_theory.json');
    }
}
