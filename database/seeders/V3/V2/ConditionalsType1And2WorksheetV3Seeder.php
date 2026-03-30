<?php

namespace Database\Seeders\V3\V2;

use App\Support\Database\JsonTestSeeder;

class ConditionalsType1And2WorksheetV3Seeder extends JsonTestSeeder
{
    protected function definitionPath(): string
    {
        return database_path('seeders/V3/definitions/V2/conditionals_type1_and2_worksheet_v2.json');
    }
}
