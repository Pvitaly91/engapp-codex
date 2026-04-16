<?php

namespace Database\Seeders\Page_V3\VerbPatterns;

use App\Support\Database\JsonPageSeeder;

class VerbPatternsBareInfinitiveTheorySeeder extends JsonPageSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}
