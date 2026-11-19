<?php

namespace Database\Seeders\V3\Polyglot;

// Generated Passive Voice quality bank; edit canonical author JSON instead.
use App\Support\Database\JsonTestSeeder;

class UkrainianMixedPassiveVoicePastSimpleQualityBuilderSeeder extends JsonTestSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}
