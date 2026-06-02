<?php

namespace Database\Seeders\Page_V3\Tenses\PresentPerfect;

use App\Support\Database\JsonPageSeeder;

class PresentPerfectCategorySeeder extends JsonPageSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}
