<?php

namespace App\Services;

/** Exact twelve existing UK owners; no historical question bank or unrelated page is changed. */
final class NounsArticlesQuantityQualityLocalGuard extends TheoryLocalReleaseGuard
{
    protected const LABEL = 'Nouns Articles Quantity quality';
    protected const PRIVATE_DIRECTORY = 'nouns-articles-quantity-quality';
    protected const ENDPOINT = 'nouns-articles-quantity-quality-';

    public const BINDINGS = [
        'plural-nouns' => ['page_id' => 89, 'category_id' => 16, 'identity' => 'Database\\Seeders\\Page_V3\\NounsArticlesQuantity\\NounsArticlesQuantityPluralNounsTheorySeeder', 'path' => '/theory/imennyky-artykli-ta-kilkist/plural-nouns-s-es-ies'],
        'countable-uncountable' => ['page_id' => 85, 'category_id' => 16, 'identity' => 'Database\\Seeders\\Page_V3\\NounsArticlesQuantity\\NounsArticlesQuantityCountableUncountableNounsTheorySeeder', 'path' => '/theory/imennyky-artykli-ta-kilkist/countable-vs-uncountable-nouns'],
        'articles' => ['page_id' => 82, 'category_id' => 16, 'identity' => 'Database\\Seeders\\Page_V3\\NounsArticlesQuantity\\NounsArticlesQuantityArticlesTheorySeeder', 'path' => '/theory/imennyky-artykli-ta-kilkist/articles-a-an-the'],
        'zero-article' => ['page_id' => 91, 'category_id' => 16, 'identity' => 'Database\\Seeders\\Page_V3\\NounsArticlesQuantity\\NounsArticlesQuantityZeroArticleTheorySeeder', 'path' => '/theory/imennyky-artykli-ta-kilkist/zero-article'],
        'quantifiers' => ['page_id' => 90, 'category_id' => 16, 'identity' => 'Database\\Seeders\\Page_V3\\NounsArticlesQuantity\\NounsArticlesQuantityQuantifiersTheorySeeder', 'path' => '/theory/imennyky-artykli-ta-kilkist/quantifiers-much-many-a-lot-few-little'],
        'partitives' => ['page_id' => 88, 'category_id' => 16, 'identity' => 'Database\\Seeders\\Page_V3\\NounsArticlesQuantity\\NounsArticlesQuantityPartitivesTheorySeeder', 'path' => '/theory/imennyky-artykli-ta-kilkist/partitives-with-uncountable-nouns-a-piece-of-a-cup-of'],
        'no-none-neither-either' => ['page_id' => 87, 'category_id' => 16, 'identity' => 'Database\\Seeders\\Page_V3\\NounsArticlesQuantity\\NounsArticlesQuantityNoNoneNeitherEitherTheorySeeder', 'path' => '/theory/imennyky-artykli-ta-kilkist/no-none-neither-either'],
        'geographical-articles' => ['page_id' => 83, 'category_id' => 16, 'identity' => 'Database\\Seeders\\Page_V3\\NounsArticlesQuantity\\NounsArticlesQuantityArticlesWithGeographicalNamesTheorySeeder', 'path' => '/theory/imennyky-artykli-ta-kilkist/articles-with-geographical-names'],
        'generic-reference' => ['page_id' => 80, 'category_id' => 16, 'identity' => 'Database\\Seeders\\Page_V3\\NounsArticlesQuantity\\NounsArticlesQuantityAdvancedArticlesGenericReferenceTheorySeeder', 'path' => '/theory/imennyky-artykli-ta-kilkist/advanced-articles-generic-reference-the-rich-a-tiger-people'],
        'irregular-plurals' => ['page_id' => 86, 'category_id' => 16, 'identity' => 'Database\\Seeders\\Page_V3\\NounsArticlesQuantity\\NounsArticlesQuantityIrregularPluralsTheorySeeder', 'path' => '/theory/imennyky-artykli-ta-kilkist/irregular-plurals'],
        'collective-nouns' => ['page_id' => 84, 'category_id' => 16, 'identity' => 'Database\\Seeders\\Page_V3\\NounsArticlesQuantity\\NounsArticlesQuantityCollectiveNounsTheorySeeder', 'path' => '/theory/imennyky-artykli-ta-kilkist/collective-nouns'],
        'another-other-the-other' => ['page_id' => 81, 'category_id' => 16, 'identity' => 'Database\\Seeders\\Page_V3\\NounsArticlesQuantity\\NounsArticlesQuantityAnotherOtherTheOtherTheorySeeder', 'path' => '/theory/imennyky-artykli-ta-kilkist/another-other-the-other'],
    ];

    public static function scope(): array
    {
        return array_values(array_map(static fn (array $binding): array => [
            'identity' => $binding['identity'], 'locale' => 'uk', 'path' => $binding['path'],
        ], self::BINDINGS));
    }
}
