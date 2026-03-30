<?php

namespace Database\Seeders\Page_V3\NounsArticlesQuantity;

use Database\Seeders\Page_V3\Concerns\JsonPageCategorySeeder;

class NounsArticlesQuantityCategorySeeder extends JsonPageCategorySeeder
{
    protected function sourcePath(): string
    {
        return database_path('seeders/Page_V3/json/NounsArticlesQuantity/page_v3_plural_nouns_seed_pack.json');
    }

    protected function sourceSlug(): ?string
    {
        return 'imennyky-artykli-ta-kilkist';
    }

    protected function localizationDefinitionPath(): ?string
    {
        return database_path('seeders/Page_V3/definitions/nouns_articles_quantity_category.json');
    }
}
