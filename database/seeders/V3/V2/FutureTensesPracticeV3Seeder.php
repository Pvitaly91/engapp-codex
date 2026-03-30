<?php

namespace Database\Seeders\V3\V2;

use App\Support\Database\JsonTestSeeder;

class FutureTensesPracticeV3Seeder extends JsonTestSeeder
{
    protected function definitionPath(): string
    {
        return database_path('seeders/V3/definitions/V2/future_tenses_practice_v2.json');
    }
}
