<?php

namespace Database\Seeders\Page_V3\PassiveVoiceV2\Basics;

use App\Support\Database\JsonPageSeeder;

class PassiveVoiceV2TwoObjectVerbsTheorySeeder extends JsonPageSeeder
{
    protected function definitionPath(): string
    {
        return database_path('seeders/Page_V3/definitions/passive_voice_two_object_verbs_theory.json');
    }
}
