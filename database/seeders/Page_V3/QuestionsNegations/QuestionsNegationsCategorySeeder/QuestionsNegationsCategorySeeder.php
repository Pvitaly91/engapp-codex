<?php

namespace Database\Seeders\Page_V3\QuestionsNegations;

use App\Support\Database\JsonPageSeeder;

class QuestionsNegationsCategorySeeder extends JsonPageSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}