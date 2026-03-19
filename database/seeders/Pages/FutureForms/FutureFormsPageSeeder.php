<?php

namespace Database\Seeders\Pages\FutureForms;

use Database\Seeders\Pages\Concerns\GrammarPageSeeder;

abstract class FutureFormsPageSeeder extends GrammarPageSeeder
{
    protected function category(): array
    {
        return [
            'slug' => 'maibutni-formy',
            'title' => 'Майбутні форми',
            'language' => 'uk',
        ];
    }
}
