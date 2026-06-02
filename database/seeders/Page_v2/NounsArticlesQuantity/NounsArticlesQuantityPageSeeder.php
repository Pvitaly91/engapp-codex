<?php

namespace Database\Seeders\Page_v2\NounsArticlesQuantity;

use Database\Seeders\Page_v2\Concerns\GrammarPageSeeder;

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
