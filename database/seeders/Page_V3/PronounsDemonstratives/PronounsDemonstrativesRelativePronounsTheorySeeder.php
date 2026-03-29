<?php

namespace Database\Seeders\Page_V3\PronounsDemonstratives;

use App\Support\Database\JsonPageSeeder;

class PronounsDemonstrativesRelativePronounsTheorySeeder extends JsonPageSeeder
{
    protected function definitionPath(): string
    {
        return database_path('seeders/Page_V3/definitions/pronouns_demonstratives_relative_pronouns_theory.json');
    }
}
