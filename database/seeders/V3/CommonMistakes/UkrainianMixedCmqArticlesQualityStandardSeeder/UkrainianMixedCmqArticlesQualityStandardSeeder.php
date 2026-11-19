<?php

namespace Database\Seeders\V3\CommonMistakes;

// Generated Common Mistakes quality bank; edit canonical author JSON instead.
use App\Support\Database\JsonTestSeeder;

class UkrainianMixedCmqArticlesQualityStandardSeeder extends JsonTestSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}
