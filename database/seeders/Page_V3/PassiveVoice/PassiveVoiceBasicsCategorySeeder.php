<?php

namespace Database\Seeders\Page_V3\PassiveVoice;

use App\Support\Database\JsonPageSeeder;

class PassiveVoiceBasicsCategorySeeder extends JsonPageSeeder
{
    protected function definitionPath(): string
    {
        return database_path('seeders/Page_V3/definitions/passive_voice_basics_category.json');
    }
}
