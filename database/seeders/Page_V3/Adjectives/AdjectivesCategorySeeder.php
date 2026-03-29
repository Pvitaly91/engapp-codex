<?php

namespace Database\Seeders\Page_V3\Adjectives;

use App\Support\Database\JsonPageSeeder;

class AdjectivesCategorySeeder extends JsonPageSeeder
{
    protected function definitionPath(): string
    {
        return database_path('seeders/Page_V3/definitions/adjectives_category.json');
    }
}
