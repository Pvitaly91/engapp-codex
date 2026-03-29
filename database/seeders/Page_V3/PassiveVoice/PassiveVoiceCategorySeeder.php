<?php

namespace Database\Seeders\Page_V3\PassiveVoice;

use App\Support\Database\JsonPageSeeder;

class PassiveVoiceCategorySeeder extends JsonPageSeeder
{
    protected function definitionPath(): string
    {
        return database_path('seeders/Page_V3/definitions/passive_voice_v2_category.json');
    }
}
