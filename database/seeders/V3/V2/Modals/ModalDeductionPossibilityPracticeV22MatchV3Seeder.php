<?php

namespace Database\Seeders\V3\V2\Modals;

use App\Support\Database\JsonTestSeeder;

class ModalDeductionPossibilityPracticeV22MatchV3Seeder extends JsonTestSeeder
{
    protected function definitionPath(): string
    {
        return database_path('seeders/V3/definitions/V2/Modals/modal_deduction_possibility_practice_v22_match.json');
    }
}
