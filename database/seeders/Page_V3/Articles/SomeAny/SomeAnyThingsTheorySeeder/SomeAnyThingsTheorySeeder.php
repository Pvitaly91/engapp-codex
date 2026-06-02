<?php

namespace Database\Seeders\Page_V3\Articles\SomeAny;

use App\Support\Database\JsonPageSeeder;

class SomeAnyThingsTheorySeeder extends JsonPageSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}
