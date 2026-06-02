<?php

namespace Database\Seeders\Page_V3\BasicGrammar\VerbToBe;

use App\Support\Database\JsonPageSeeder;

class VerbToBeFutureTheorySeeder extends JsonPageSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}
