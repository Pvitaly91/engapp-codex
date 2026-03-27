<?php

namespace Database\Seeders\V3\IA\ChatGptPro;

use App\Support\Database\JsonTestSeeder;

class PassiveVoiceAllTensesV3QuestionsOnlySeeder extends JsonTestSeeder
{
    protected function definitionPath(): string
    {
        return database_path('seeders/V3/definitions/IA/ChatGptPro/passive_voice_all_tenses_v3_questions_only.json');
    }
}
