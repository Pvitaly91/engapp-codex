<?php

namespace Database\Seeders\Page_V3\RelativeClauses;

use App\Support\Database\JsonPageSeeder;

class ComplexRelativeClausesTheorySeeder extends JsonPageSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}
