<?php

namespace Database\Seeders\V3\ModalVerbs;

use App\Support\Database\JsonTestSeeder;

class NeedNeedntDontHaveToAllLevelsV3Seeder extends JsonTestSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}
