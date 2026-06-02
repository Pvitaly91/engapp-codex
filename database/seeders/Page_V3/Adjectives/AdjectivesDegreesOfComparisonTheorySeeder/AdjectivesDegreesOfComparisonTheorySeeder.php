<?php

namespace Database\Seeders\Page_V3\Adjectives;

use App\Support\Database\JsonPageSeeder;

class AdjectivesDegreesOfComparisonTheorySeeder extends JsonPageSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}