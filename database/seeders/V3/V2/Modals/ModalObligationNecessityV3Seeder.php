<?php

namespace Database\Seeders\V3\V2\Modals;

use App\Support\Database\JsonTestSeeder;

class ModalObligationNecessityV3Seeder extends JsonTestSeeder
{
    protected function definitionPath(): string
    {
        return database_path('seeders/V3/definitions/V2/Modals/modal_obligation_necessity_v2.json');
    }
}
