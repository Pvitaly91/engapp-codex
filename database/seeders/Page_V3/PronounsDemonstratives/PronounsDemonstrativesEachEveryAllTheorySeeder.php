<?php

namespace Database\Seeders\Page_V3\PronounsDemonstratives;

use App\Support\Database\JsonPageSeeder;

class PronounsDemonstrativesEachEveryAllTheorySeeder extends JsonPageSeeder
{
    protected function definitionPath(): string
    {
        return database_path('seeders/Page_V3/definitions/pronouns_demonstratives_each_every_all_theory.json');
    }
}
