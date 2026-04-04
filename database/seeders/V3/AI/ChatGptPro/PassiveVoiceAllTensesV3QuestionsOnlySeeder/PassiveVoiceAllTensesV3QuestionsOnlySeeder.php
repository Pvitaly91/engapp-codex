<?php

namespace Database\Seeders\V3\AI\ChatGptPro;

use App\Support\Database\JsonTestSeeder;

class PassiveVoiceAllTensesV3QuestionsOnlySeeder extends JsonTestSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}
