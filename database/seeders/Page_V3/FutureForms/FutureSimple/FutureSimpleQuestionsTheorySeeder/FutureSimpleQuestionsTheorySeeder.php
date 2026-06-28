<?php

namespace Database\Seeders\Page_V3\FutureForms\FutureSimple;

use App\Support\Database\JsonPageSeeder;

class FutureSimpleQuestionsTheorySeeder extends JsonPageSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}
