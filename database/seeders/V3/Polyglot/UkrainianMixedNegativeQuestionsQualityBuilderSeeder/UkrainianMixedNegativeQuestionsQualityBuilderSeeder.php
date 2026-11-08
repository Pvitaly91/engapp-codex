<?php

namespace Database\Seeders\V3\Polyglot;

// Generated Questions/Negations quality bank; edit canonical author JSON instead.
use App\Support\Database\JsonTestSeeder;

class UkrainianMixedNegativeQuestionsQualityBuilderSeeder extends JsonTestSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}
