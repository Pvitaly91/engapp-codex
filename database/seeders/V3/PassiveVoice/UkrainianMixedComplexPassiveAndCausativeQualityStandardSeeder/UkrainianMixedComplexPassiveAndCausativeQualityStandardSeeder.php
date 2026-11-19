<?php

namespace Database\Seeders\V3\PassiveVoice;

// Generated Passive Voice quality bank; edit canonical author JSON instead.
use App\Support\Database\JsonTestSeeder;

class UkrainianMixedComplexPassiveAndCausativeQualityStandardSeeder extends JsonTestSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}
