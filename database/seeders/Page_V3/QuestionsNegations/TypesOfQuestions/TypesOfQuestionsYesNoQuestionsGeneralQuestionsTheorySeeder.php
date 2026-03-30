<?php

namespace Database\Seeders\Page_V3\QuestionsNegations\TypesOfQuestions;

use App\Support\Database\JsonPageSeeder;

class TypesOfQuestionsYesNoQuestionsGeneralQuestionsTheorySeeder extends JsonPageSeeder
{
    protected function definitionPath(): string
    {
        return database_path('seeders/Page_V3/definitions/types_of_questions_yes_no_questions_general_questions_theory.json');
    }
}
