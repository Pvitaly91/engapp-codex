<?php

namespace Database\Seeders\Page_V3\Adjectives;

use App\Support\Database\JsonPageSeeder;

class AdjectivesDegreesOfComparisonTheorySeeder extends JsonPageSeeder
{
    protected function definitionPath(): string
    {
        return database_path('seeders/Page_V3/definitions/adjectives_degrees_of_comparison_theory.json');
    }
}
