<?php

namespace Database\Seeders\Page_V3\FutureForms;

use App\Support\Database\JsonPageSeeder;

class FutureFormsWillVsBeGoingToTheorySeeder extends JsonPageSeeder
{
    protected function definitionPath(): string
    {
        return database_path('seeders/Page_V3/definitions/future_forms_will_vs_be_going_to_theory.json');
    }
}
