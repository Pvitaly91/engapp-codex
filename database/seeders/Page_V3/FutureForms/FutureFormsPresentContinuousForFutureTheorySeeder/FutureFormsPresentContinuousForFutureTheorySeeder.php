<?php

namespace Database\Seeders\Page_V3\FutureForms;

use App\Support\Database\JsonPageSeeder;

class FutureFormsPresentContinuousForFutureTheorySeeder extends JsonPageSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}
