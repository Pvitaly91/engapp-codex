<?php

namespace Database\Seeders\Page_V3\PassiveVoiceV2;

use App\Support\Database\JsonPageSeeder;

class PassiveVoiceV2CategorySeeder extends JsonPageSeeder
{
    protected function definitionPath(): string
    {
        return database_path('seeders/Page_V3/definitions/passive_voice_v2_category.json');
    }
}
