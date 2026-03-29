<?php

namespace Database\Seeders\Page_V3\BasicGrammar\WordOrder;

use App\Support\Database\JsonPageSeeder;

class AdvancedWordOrderEmphasisTheorySeeder extends JsonPageSeeder
{
    protected function definitionPath(): string
    {
        return database_path('seeders/Page_V3/definitions/advanced_word_order_emphasis_theory.json');
    }
}
