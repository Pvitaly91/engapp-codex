<?php

namespace Database\Seeders\V3\FutureForms;

use App\Support\Database\JsonTestSeeder;

class ChoosingTheRightFutureFormAllLevelsV3Seeder extends JsonTestSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}
