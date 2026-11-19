<?php

namespace Database\Seeders\V3\SentenceTransformations;

// Generated Sentence Transformations quality bank; edit canonical author JSON instead.
use App\Support\Database\JsonTestSeeder;

class UkrainianMixedStqArticlesQuantifiersQualityStandardSeeder extends JsonTestSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}
