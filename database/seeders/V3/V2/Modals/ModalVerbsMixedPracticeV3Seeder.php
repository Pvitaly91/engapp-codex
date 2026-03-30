<?php

namespace Database\Seeders\V3\V2\Modals;

use App\Support\Database\JsonTestSeeder;

class ModalVerbsMixedPracticeV3Seeder extends JsonTestSeeder
{
    protected function definitionPath(): string
    {
        return database_path('seeders/V3/definitions/V2/Modals/modal_verbs_mixed_practice_v2.json');
    }
}
