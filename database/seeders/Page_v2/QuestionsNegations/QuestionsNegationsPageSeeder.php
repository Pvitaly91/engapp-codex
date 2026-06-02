<?php

namespace Database\Seeders\Page_v2\QuestionsNegations;

use Database\Seeders\Page_v2\Concerns\GrammarPageSeeder;

abstract class QuestionsNegationsPageSeeder extends GrammarPageSeeder
{
    protected function category(): array
    {
        return [
            'slug' => 'pytalni-rechennia-ta-zaperechennia',
            'title' => 'Питальні речення та заперечення',
            'language' => 'uk',
        ];
    }
}
