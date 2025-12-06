<?php

namespace Database\Seeders\Pages\QuestionsNegations;

use Database\Seeders\Pages\Concerns\GrammarPageSeeder;

abstract class QuestionsNegationsPageSeeder extends GrammarPageSeeder
{
    protected function category(): array
    {
        return [
            'slug' => '8',
            'title' => 'Питальні речення та заперечення',
            'language' => 'uk',
        ];
    }
}
