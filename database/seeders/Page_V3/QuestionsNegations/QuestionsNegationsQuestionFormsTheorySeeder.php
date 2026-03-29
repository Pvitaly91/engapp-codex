<?php

namespace Database\Seeders\Page_V3\QuestionsNegations;

use App\Support\Database\JsonPageSeeder;

class QuestionsNegationsQuestionFormsTheorySeeder extends JsonPageSeeder
{
    protected function definitionPath(): string
    {
        return database_path('seeders/Page_V3/definitions/questions_negations_question_forms_theory.json');
    }
}
