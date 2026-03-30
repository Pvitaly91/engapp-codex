<?php

namespace Database\Seeders\Page_V3\Articles\SomeAny;

use App\Support\Database\JsonPageSeeder;

class SomeAnyPlacesTheorySeeder extends JsonPageSeeder
{
    protected function definitionPath(): string
    {
        return database_path('seeders/Page_V3/definitions/some_any_places_theory.json');
    }
}
