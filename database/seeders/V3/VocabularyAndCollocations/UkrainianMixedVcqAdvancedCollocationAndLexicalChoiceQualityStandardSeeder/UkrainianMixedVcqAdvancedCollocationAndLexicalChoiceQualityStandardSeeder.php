<?php

namespace Database\Seeders\V3\VocabularyAndCollocations;

// Generated Vocabulary and Collocations quality bank; edit canonical author JSON instead.
use App\Support\Database\JsonTestSeeder;

class UkrainianMixedVcqAdvancedCollocationAndLexicalChoiceQualityStandardSeeder extends JsonTestSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}
