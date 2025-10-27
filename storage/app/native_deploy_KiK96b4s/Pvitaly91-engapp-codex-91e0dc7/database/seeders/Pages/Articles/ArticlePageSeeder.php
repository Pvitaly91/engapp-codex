<?php

namespace Database\Seeders\Pages\Articles;

use Database\Seeders\Pages\Concerns\GrammarPageSeeder;

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
