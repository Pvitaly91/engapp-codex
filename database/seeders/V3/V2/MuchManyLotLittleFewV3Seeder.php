<?php

namespace Database\Seeders\V3\V2;

use App\Support\Database\JsonTestSeeder;

class MuchManyLotLittleFewV3Seeder extends JsonTestSeeder
{
    protected function definitionPath(): string
    {
        return database_path('seeders/V3/definitions/V2/much_many_lot_little_few_v2.json');
    }
}
