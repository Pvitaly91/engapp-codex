<?php

namespace Database\Seeders\Page_V3\BasicGrammar\WordOrder;

use App\Support\Database\JsonPageSeeder;

class WordOrderVerbsObjectsTheorySeeder extends JsonPageSeeder
{
    protected function definitionPath(): string
    {
        return database_path('seeders/Page_V3/definitions/word_order_verbs_objects_theory.json');
    }
}
