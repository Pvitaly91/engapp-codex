<?php

namespace Database\Seeders\V3\V2;

use App\Support\Database\JsonTestSeeder;

class FirstConditionalChooseABCV3Seeder extends JsonTestSeeder
{
    protected function definitionPath(): string
    {
        return database_path('seeders/V3/definitions/V2/first_conditional_choose_a_b_c_v2.json');
    }
}
