<?php

namespace Database\Seeders\Page_V3\ModalVerbs;

use App\Support\Database\JsonPageSeeder;

class ModalVerbsCategorySeeder extends JsonPageSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}
