<?php

namespace Database\Seeders\Page_V3\NounsArticlesQuantity;

use App\Support\Database\JsonPageSeeder;

class NounsArticlesQuantityArticlesTheorySeeder extends JsonPageSeeder
{
    protected function definitionPath(): string
    {
        return database_path('seeders/Page_V3/definitions/nouns_articles_quantity_articles_theory.json');
    }
}
