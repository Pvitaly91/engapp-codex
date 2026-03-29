<?php

namespace Database\Seeders\Page_V3\PassiveVoice\Advanced;

use App\Support\Database\JsonPageSeeder;

class PassiveVoiceInfinitivesGerundTheorySeeder extends JsonPageSeeder
{
    protected function definitionPath(): string
    {
        return database_path('seeders/Page_V3/definitions/passive_voice_infinitives_gerund_theory.json');
    }
}
