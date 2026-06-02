<?php

namespace Database\Seeders\Page_v2\Adjectives;

use Database\Seeders\Page_v2\Concerns\GrammarPageSeeder;

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
