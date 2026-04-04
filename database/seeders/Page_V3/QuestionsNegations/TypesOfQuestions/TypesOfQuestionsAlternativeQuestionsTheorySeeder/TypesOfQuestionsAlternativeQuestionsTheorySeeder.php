<?php

namespace Database\Seeders\Page_V3\QuestionsNegations\TypesOfQuestions;

use App\Support\Database\JsonPageSeeder;

class TypesOfQuestionsAlternativeQuestionsTheorySeeder extends JsonPageSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}