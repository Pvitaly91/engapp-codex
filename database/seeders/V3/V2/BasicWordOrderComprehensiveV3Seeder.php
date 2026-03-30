<?php

namespace Database\Seeders\V3\V2;

use App\Support\Database\JsonTestSeeder;

class BasicWordOrderComprehensiveV3Seeder extends JsonTestSeeder
{
    protected function definitionPath(): string
    {
        return database_path('seeders/V3/definitions/V2/basic_word_order_comprehensive_v2.json');
    }
}
