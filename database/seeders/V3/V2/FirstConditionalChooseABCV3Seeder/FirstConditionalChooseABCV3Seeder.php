<?php

namespace Database\Seeders\V3\V2;

use App\Support\Database\JsonTestSeeder;

class FirstConditionalChooseABCV3Seeder extends JsonTestSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}
