<?php

namespace Database\Seeders\Page_v2\PronounsDemonstratives;

use Database\Seeders\Page_v2\Concerns\GrammarPageSeeder;

abstract class PronounsDemonstrativesPageSeeder extends GrammarPageSeeder
{
    protected function category(): array
    {
        return [
            'slug' => 'zaimennyky-ta-vkazivni-slova',
            'title' => 'Займенники та вказівні слова',
            'language' => 'uk',
        ];
    }
}
