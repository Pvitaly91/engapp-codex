<?php

namespace Database\Seeders\Page_V3\ArticlesAndQuantifiers;

use App\Support\Database\JsonPageSeeder;

class PrecisionWithArticlesAndDeterminersTheorySeeder extends JsonPageSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}
