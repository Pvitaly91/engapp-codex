<?php

namespace Database\Seeders\Page_V3\PassiveVoice\SpecialCases;

use App\Support\Database\JsonPageSeeder;

class PassiveVoiceByPhraseTheorySeeder extends JsonPageSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}