<?php

namespace Database\Seeders\Page_v2\Articles;

use Database\Seeders\Page_v2\Concerns\GrammarPageSeeder;

abstract class ArticlePageSeeder extends GrammarPageSeeder
{
    protected function category(): array
    {
        return [
            'slug' => 'articles-and-quantifiers',
            'title' => 'Артиклі та кількісні слова',
            'language' => 'uk',
        ];
    }
}
