<?php

namespace Database\Seeders\Page_V3\QuestionsNegations\TypesOfQuestions;

use App\Support\Database\JsonPageSeeder;

class TypesOfQuestionsQuestionTagsDisjunctiveQuestionsTheorySeeder extends JsonPageSeeder
{
    protected function definitionPath(): string
    {
        return database_path('seeders/Page_V3/definitions/types_of_questions_question_tags_disjunctive_questions_theory.json');
    }
}
