<?php

namespace Database\Seeders\V3\FormalEnglish;

// Generated Formal English quality bank; edit canonical author JSON instead.
use App\Support\Database\JsonTestSeeder;

class UkrainianMixedFeqParaphraseAndReformulationQualityStandardSeeder extends JsonTestSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}
