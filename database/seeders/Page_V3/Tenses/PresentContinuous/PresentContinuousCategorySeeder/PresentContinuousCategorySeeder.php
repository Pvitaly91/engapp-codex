<?php

namespace Database\Seeders\Page_V3\Tenses\PresentContinuous;

use App\Support\Database\JsonPageSeeder;

class PresentContinuousCategorySeeder extends JsonPageSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}
