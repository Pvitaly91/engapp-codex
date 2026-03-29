<?php

namespace Database\Seeders\Page_V3\PassiveVoice\InfinitivesGerund;

use App\Support\Database\JsonPageSeeder;

class PassiveVoicePassiveGerundTheorySeeder extends JsonPageSeeder
{
    protected function definitionPath(): string
    {
        return database_path('seeders/Page_V3/definitions/passive_voice_passive_gerund_theory.json');
    }
}
