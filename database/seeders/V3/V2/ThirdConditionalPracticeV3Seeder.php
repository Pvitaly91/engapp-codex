<?php

namespace Database\Seeders\V3\V2;

use App\Support\Database\JsonTestSeeder;

class ThirdConditionalPracticeV3Seeder extends JsonTestSeeder
{
    protected function definitionPath(): string
    {
        return database_path('seeders/V3/definitions/V2/third_conditional_practice_v2.json');
    }
}
