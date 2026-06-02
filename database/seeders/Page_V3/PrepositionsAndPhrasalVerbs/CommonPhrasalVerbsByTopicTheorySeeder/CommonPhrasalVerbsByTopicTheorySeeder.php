<?php

namespace Database\Seeders\Page_V3\PrepositionsAndPhrasalVerbs;

use App\Support\Database\JsonPageSeeder;

class CommonPhrasalVerbsByTopicTheorySeeder extends JsonPageSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}
