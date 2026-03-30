<?php

namespace Database\Seeders\Page_V3\NounsArticlesQuantity;

use Database\Seeders\Page_V3\Concerns\JsonGrammarPageSeeder;

abstract class NounsArticlesQuantityPageSeeder extends JsonGrammarPageSeeder
{
    protected function sourcePath(): string
    {
        return database_path('seeders/Page_V3/json/NounsArticlesQuantity/page_v3_plural_nouns_seed_pack.json');
    }

    protected function category(): ?array
    {
        return $this->resolvedCategoryFromSource() ?? [
            'slug' => 'imennyky-artykli-ta-kilkist',
            'title' => 'Іменники, артиклі та кількість',
            'language' => 'uk',
        ];
    }
}
