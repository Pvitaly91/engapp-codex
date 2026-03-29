<?php

namespace Database\Seeders\Page_V3\PronounsDemonstratives;

use App\Support\Database\JsonPageSeeder;

class PronounsDemonstrativesPersonalObjectPronounsTheorySeeder extends JsonPageSeeder
{
    protected function definitionPath(): string
    {
        return database_path('seeders/Page_V3/definitions/pronouns_demonstratives_personal_object_pronouns_theory.json');
    }
}
