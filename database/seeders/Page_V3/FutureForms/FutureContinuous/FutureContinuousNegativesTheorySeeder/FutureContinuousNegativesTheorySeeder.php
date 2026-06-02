<?php

namespace Database\Seeders\Page_V3\FutureForms\FutureContinuous;

use App\Support\Database\JsonPageSeeder;

class FutureContinuousNegativesTheorySeeder extends JsonPageSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}
