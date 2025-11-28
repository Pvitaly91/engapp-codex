<?php

namespace Database\Seeders\Pages\Adjectives;

use Database\Seeders\Pages\Concerns\GrammarPageSeeder;

abstract class AdjectivePageSeeder extends GrammarPageSeeder
{
    protected function category(): array
    {
        return [
            'slug' => 'adjectives',
            'title' => 'Прикметники',
            'language' => 'uk',
        ];
    }
}
