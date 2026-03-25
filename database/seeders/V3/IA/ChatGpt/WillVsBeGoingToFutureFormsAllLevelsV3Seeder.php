<?php

namespace Database\Seeders\V3\IA\ChatGpt;

use App\Support\Database\JsonTestSeeder;

class WillVsBeGoingToFutureFormsAllLevelsV3Seeder extends JsonTestSeeder
{
    protected function definitionPath(): string
    {
        return database_path('seeders/V3/definitions/IA/ChatGpt/will_vs_be_going_to_future_forms_all_levels.json');
    }
}
