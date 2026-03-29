<?php

namespace Database\Seeders\V3\V2;

use App\Support\Database\JsonTestSeeder;

class QuestionsDifferentTypesV3Seeder extends JsonTestSeeder
{
    protected function definitionPath(): string
    {
        return database_path('seeders/V3/definitions/V2/questions_different_types_v2.json');
    }
}
