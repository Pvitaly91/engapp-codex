<?php

namespace Database\Seeders\Pages\Tenses;

use Database\Seeders\Pages\Concerns\GrammarPageSeeder;

abstract class TensePageSeeder extends GrammarPageSeeder
{
    protected function category(): array
    {
        return [
            'slug' => 'tenses',
            'title' => 'Часи',
            'language' => 'uk',
        ];
    }
}
