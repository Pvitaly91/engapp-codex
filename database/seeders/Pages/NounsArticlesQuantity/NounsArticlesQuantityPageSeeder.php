<?php

namespace Database\Seeders\Pages\NounsArticlesQuantity;

use Database\Seeders\Pages\Concerns\GrammarPageSeeder;

abstract class NounsArticlesQuantityPageSeeder extends GrammarPageSeeder
{
    protected function category(): array
    {
        return [
            'slug' => 'imennyky-artykli-ta-kilkist',
            'title' => 'Іменники, артиклі та кількість',
            'language' => 'uk',
        ];
    }
}
