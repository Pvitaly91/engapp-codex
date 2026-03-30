<?php

namespace Database\Seeders\Page_v2\FutureForms;

use Database\Seeders\Page_v2\Concerns\GrammarPageSeeder;

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
