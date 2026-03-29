<?php

namespace Database\Seeders\Page_V3\PassiveVoice\Basics;

use App\Support\Database\JsonPageSeeder;

class PassiveVoiceBasicsFormV3TheorySeeder extends JsonPageSeeder
{
    protected function definitionPath(): string
    {
        return database_path('seeders/Page_V3/definitions/passive_voice_basics_form_v3_theory.json');
    }
}
