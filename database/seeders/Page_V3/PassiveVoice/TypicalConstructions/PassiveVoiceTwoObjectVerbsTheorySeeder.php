<?php

namespace Database\Seeders\Page_V3\PassiveVoice\TypicalConstructions;

use App\Support\Database\JsonPageSeeder;

class PassiveVoiceTwoObjectVerbsTheorySeeder extends JsonPageSeeder
{
    protected function definitionPath(): string
    {
        return database_path('seeders/Page_V3/definitions/passive_voice_two_object_verbs_theory.json');
    }
}
