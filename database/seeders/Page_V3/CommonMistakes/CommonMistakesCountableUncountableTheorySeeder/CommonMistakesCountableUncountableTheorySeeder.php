<?php

namespace Database\Seeders\Page_V3\CommonMistakes;

use App\Support\Database\JsonPageSeeder;

class CommonMistakesCountableUncountableTheorySeeder extends JsonPageSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}
