<?php

namespace Database\Seeders\Pages\Questions\TypesOfQuestions;

use Database\Seeders\Pages\Questions\QuestionPageSeeder;

abstract class TypesOfQuestionsPageSeeder extends QuestionPageSeeder
{
    public function categorySlug(): string
    {
        return 'types-of-questions';
    }
}
