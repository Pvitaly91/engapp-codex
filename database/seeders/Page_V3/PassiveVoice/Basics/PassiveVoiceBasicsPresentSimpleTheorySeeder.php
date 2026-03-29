<?php

namespace Database\Seeders\Page_V3\PassiveVoice\Basics;

use App\Support\Database\JsonPageSeeder;

class PassiveVoiceBasicsPresentSimpleTheorySeeder extends JsonPageSeeder
{
    protected function definitionPath(): string
    {
        return database_path('seeders/Page_V3/definitions/passive_voice_basics_present_simple_theory.json');
    }
}
