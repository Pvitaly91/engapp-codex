<?php

namespace Database\Seeders\V3\Adjectives;

// Generated Adjectives/Adverbs quality bank; edit canonical author JSON instead.
use App\Support\Database\JsonTestSeeder;

class UkrainianMixedOrderOfAdjectivesQualityStandardSeeder extends JsonTestSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}
