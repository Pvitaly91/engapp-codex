<?php

namespace Database\Seeders\Page_V3\PassiveVoice\Tenses;

use App\Support\Database\JsonPageSeeder;

class PassiveVoicePresentContinuousTheorySeeder extends JsonPageSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}