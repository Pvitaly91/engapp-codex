<?php

namespace Database\Seeders\Page_v2\BasicGrammar;

use Database\Seeders\Page_v2\Concerns\GrammarPageSeeder;

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
