<?php

namespace Database\Seeders\Page_V3\BasicGrammar\WordOrder;

use App\Support\Database\JsonPageSeeder;

class BasicWordOrderTheorySeeder extends JsonPageSeeder
{
    protected function definitionPath(): string
    {
        return database_path('seeders/Page_V3/definitions/basic_word_order_theory.json');
    }
}
