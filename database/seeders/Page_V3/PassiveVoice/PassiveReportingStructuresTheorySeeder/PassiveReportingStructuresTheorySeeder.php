<?php

namespace Database\Seeders\Page_V3\PassiveVoice;

use App\Support\Database\JsonPageSeeder;

class PassiveReportingStructuresTheorySeeder extends JsonPageSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}
