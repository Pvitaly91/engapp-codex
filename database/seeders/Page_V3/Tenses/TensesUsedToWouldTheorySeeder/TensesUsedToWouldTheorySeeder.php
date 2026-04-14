<?php

namespace Database\Seeders\Page_V3\Tenses;

use App\Support\Database\JsonPageSeeder;

class TensesUsedToWouldTheorySeeder extends JsonPageSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}
