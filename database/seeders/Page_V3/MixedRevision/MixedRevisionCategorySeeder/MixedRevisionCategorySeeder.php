<?php

namespace Database\Seeders\Page_V3\MixedRevision;

use App\Support\Database\JsonPageSeeder;

class MixedRevisionCategorySeeder extends JsonPageSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}
