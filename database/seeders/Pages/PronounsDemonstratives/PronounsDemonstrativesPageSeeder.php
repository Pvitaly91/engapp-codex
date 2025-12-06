<?php

namespace Database\Seeders\Pages\PronounsDemonstratives;

use Database\Seeders\Pages\Concerns\GrammarPageSeeder;

abstract class PronounsDemonstrativesPageSeeder extends GrammarPageSeeder
{
    protected function category(): array
    {
        return [
            'slug' => '3',
            'title' => 'Займенники та вказівні слова',
            'language' => 'uk',
        ];
    }
}
