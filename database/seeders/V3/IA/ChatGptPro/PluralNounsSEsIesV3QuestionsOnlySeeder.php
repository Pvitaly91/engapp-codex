<?php

namespace Database\Seeders\V3\IA\ChatGptPro;

use App\Support\Database\JsonTestSeeder;

class PluralNounsSEsIesV3QuestionsOnlySeeder extends JsonTestSeeder
{
    protected function definitionPath(): string
    {
        return database_path('seeders/V3/definitions/IA/ChatGptPro/plural_nouns_s_es_ies_v3_questions_only.json');
    }
}
