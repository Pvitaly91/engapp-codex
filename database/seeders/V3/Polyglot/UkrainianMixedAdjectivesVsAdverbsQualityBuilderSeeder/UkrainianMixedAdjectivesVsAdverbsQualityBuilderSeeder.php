<?php

namespace Database\Seeders\V3\Polyglot;

// Generated Adjectives/Adverbs quality bank; edit canonical author JSON instead.
use App\Support\Database\JsonTestSeeder;

class UkrainianMixedAdjectivesVsAdverbsQualityBuilderSeeder extends JsonTestSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}
