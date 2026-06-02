<?php

namespace Database\Seeders\Page_V3\Tenses\PresentPerfectContinuous;

use App\Support\Database\JsonPageSeeder;

class PresentPerfectContinuousCategorySeeder extends JsonPageSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}
