<?php

namespace Database\Seeders\Pages\Questions;

use Database\Seeders\Pages\Concerns\GrammarPageSeeder;

abstract class QuestionPageSeeder extends GrammarPageSeeder
{
    protected function category(): array
    {
        return [
            'slug' => 'questions',
            'title' => 'Питальні речення',
            'language' => 'uk',
        ];
    }
}
