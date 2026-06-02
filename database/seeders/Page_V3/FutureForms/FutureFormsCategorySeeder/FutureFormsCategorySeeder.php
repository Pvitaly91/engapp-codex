<?php

namespace Database\Seeders\Page_V3\FutureForms;

use App\Support\Database\JsonPageSeeder;

class FutureFormsCategorySeeder extends JsonPageSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}