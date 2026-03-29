<?php

namespace Database\Seeders\Page_V3\PassiveVoice\TypicalConstructions;

use App\Support\Database\JsonPageSeeder;

class PassiveVoiceReportingImpersonalTheorySeeder extends JsonPageSeeder
{
    protected function definitionPath(): string
    {
        return database_path('seeders/Page_V3/definitions/passive_voice_reporting_impersonal_theory.json');
    }
}
