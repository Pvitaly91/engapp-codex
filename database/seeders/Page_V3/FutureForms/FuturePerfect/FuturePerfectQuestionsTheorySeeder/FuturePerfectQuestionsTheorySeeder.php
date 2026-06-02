<?php

namespace Database\Seeders\Page_V3\FutureForms\FuturePerfect;

use App\Support\Database\JsonPageSeeder;

class FuturePerfectQuestionsTheorySeeder extends JsonPageSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}
