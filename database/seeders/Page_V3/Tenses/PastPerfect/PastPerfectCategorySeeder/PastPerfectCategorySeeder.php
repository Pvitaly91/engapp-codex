<?php

namespace Database\Seeders\Page_V3\Tenses\PastPerfect;

use App\Support\Database\JsonPageSeeder;

class PastPerfectCategorySeeder extends JsonPageSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}
