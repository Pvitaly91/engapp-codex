<?php

namespace Database\Seeders\Page_V3\FutureForms;

use App\Support\Database\JsonPageSeeder;

class FutureFormsCategorySeeder extends JsonPageSeeder
{
    protected function definitionPath(): string
    {
        return database_path('seeders/Page_V3/definitions/future_forms_category.json');
    }
}
