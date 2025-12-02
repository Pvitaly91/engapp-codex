<?php

namespace Database\Seeders\Pages\BasicGrammar;

use Database\Seeders\Pages\Concerns\GrammarPageSeeder;

abstract class BasicGrammarPageSeeder extends GrammarPageSeeder
{
    protected function category(): array
    {
        return [
            'slug' => 'basic-grammar',
            'title' => 'Базова граматика',
            'language' => 'uk',
        ];
    }
}
