<?php

namespace Database\Seeders\Page_V3\PassiveVoice\Tenses;

use App\Support\Database\JsonPageSeeder;

class PassiveVoiceTensesCategorySeeder extends JsonPageSeeder
{
    protected function definitionPath(): string
    {
        return database_path('seeders/Page_V3/definitions/passive_voice_tenses_category.json');
    }
}
