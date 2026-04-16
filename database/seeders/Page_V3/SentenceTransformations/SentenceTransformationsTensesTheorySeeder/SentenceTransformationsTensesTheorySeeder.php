<?php

namespace Database\Seeders\Page_V3\SentenceTransformations;

use App\Support\Database\JsonPageSeeder;

class SentenceTransformationsTensesTheorySeeder extends JsonPageSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}
