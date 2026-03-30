<?php

namespace Database\Seeders\V3\V2;

use App\Support\Database\JsonTestSeeder;

class PastTimeClausesMixedTestV3Seeder extends JsonTestSeeder
{
    protected function definitionPath(): string
    {
        return database_path('seeders/V3/definitions/V2/past_time_clauses_mixed_test.json');
    }
}
