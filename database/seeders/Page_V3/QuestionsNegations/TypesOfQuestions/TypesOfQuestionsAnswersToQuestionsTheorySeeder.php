<?php

namespace Database\Seeders\Page_V3\QuestionsNegations\TypesOfQuestions;

use App\Support\Database\JsonPageSeeder;

class TypesOfQuestionsAnswersToQuestionsTheorySeeder extends JsonPageSeeder
{
    protected function definitionPath(): string
    {
        return database_path('seeders/Page_V3/definitions/types_of_questions_answers_to_questions_theory.json');
    }
}
