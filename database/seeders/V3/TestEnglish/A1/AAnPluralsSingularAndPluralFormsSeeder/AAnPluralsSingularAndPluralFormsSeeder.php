<?php

namespace Database\Seeders\V3\TestEnglish\A1;

use Database\Seeders\V3\Concerns\JsonTestSeeder;

class AAnPluralsSingularAndPluralFormsSeeder extends JsonTestSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}
