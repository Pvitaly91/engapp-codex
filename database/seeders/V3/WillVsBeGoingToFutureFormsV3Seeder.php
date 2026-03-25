<?php

namespace Database\Seeders\V3;

use App\Support\Database\JsonTestSeeder;

class WillVsBeGoingToFutureFormsV3Seeder extends JsonTestSeeder
{
    protected function definitionPath(): string
    {
        return database_path('seeders/V3/definitions/will_vs_be_going_to_future_forms_v2.json');
    }
}
