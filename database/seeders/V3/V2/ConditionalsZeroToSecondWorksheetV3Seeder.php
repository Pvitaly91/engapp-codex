<?php

namespace Database\Seeders\V3\V2;

use App\Support\Database\JsonTestSeeder;

class ConditionalsZeroToSecondWorksheetV3Seeder extends JsonTestSeeder
{
    protected function definitionPath(): string
    {
        return database_path('seeders/V3/definitions/V2/conditionals_zero_to_second_worksheet_v2.json');
    }
}
