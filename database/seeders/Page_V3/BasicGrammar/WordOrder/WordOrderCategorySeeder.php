<?php

namespace Database\Seeders\Page_V3\BasicGrammar\WordOrder;

use App\Support\Database\JsonPageSeeder;

class WordOrderCategorySeeder extends JsonPageSeeder
{
    protected function definitionPath(): string
    {
        return database_path('seeders/Page_V3/definitions/word_order_category.json');
    }
}
