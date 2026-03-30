<?php

namespace Database\Seeders\V3\TestEnglish\A1;

use Database\Seeders\V3\Concerns\JsonTestSeeder;

class AAnPluralsSingularAndPluralFormsSeeder extends JsonTestSeeder
{
    protected function definitionPath(): string
    {
        return database_path('seeders/V3/json/TestEnglish/A1/test_v3_a_an_plurals_test_english.json');
    }
}
