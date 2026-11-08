<?php

namespace App\Services;

/** Exact ten-owner local-only boundary for the finite UK Word Order quality revision. */
final class WordOrderQualityLocalGuard extends TheoryLocalReleaseGuard
{
    protected const LABEL = 'Word Order quality';
    protected const PRIVATE_DIRECTORY = 'word-order-quality';
    protected const ENDPOINT = 'word-order-quality-';

    public const BINDINGS = [
        'basic' => ['page_id' => 256, 'category_id' => 48, 'identity' => 'Database\\Seeders\\Page_V3\\BasicGrammar\\WordOrder\\BasicWordOrderTheorySeeder', 'path' => '/theory/basic-grammar/word-order/theory-basic-word-order'],
        'questions-negatives' => ['page_id' => 258, 'category_id' => 48, 'identity' => 'Database\\Seeders\\Page_V3\\BasicGrammar\\WordOrder\\WordOrderQuestionsNegativesTheorySeeder', 'path' => '/theory/basic-grammar/word-order/theory-word-order-questions-negatives'],
        'adverbs-adverbials' => ['page_id' => 257, 'category_id' => 48, 'identity' => 'Database\\Seeders\\Page_V3\\BasicGrammar\\WordOrder\\WordOrderAdverbsAdverbialsTheorySeeder', 'path' => '/theory/basic-grammar/word-order/theory-word-order-adverbs-adverbials'],
        'verbs-objects' => ['page_id' => 259, 'category_id' => 48, 'identity' => 'Database\\Seeders\\Page_V3\\BasicGrammar\\WordOrder\\WordOrderVerbsObjectsTheorySeeder', 'path' => '/theory/basic-grammar/word-order/theory-word-order-verbs-objects'],
        'advanced-emphasis' => ['page_id' => 255, 'category_id' => 48, 'identity' => 'Database\\Seeders\\Page_V3\\BasicGrammar\\WordOrder\\AdvancedWordOrderEmphasisTheorySeeder', 'path' => '/theory/basic-grammar/word-order/theory-advanced-word-order-emphasis'],
        'inversion-basics' => ['page_id' => 288, 'category_id' => 48, 'identity' => 'Database\\Seeders\\Page_V3\\BasicGrammar\\WordOrder\\InversionBasicsTheorySeeder', 'path' => '/theory/basic-grammar/word-order/inversion-basics'],
        'negative-adverbial-inversion' => ['page_id' => 295, 'category_id' => 48, 'identity' => 'Database\\Seeders\\Page_V3\\BasicGrammar\\WordOrder\\InversionAfterNegativeAdverbialsTheorySeeder', 'path' => '/theory/basic-grammar/word-order/inversion-after-negative-adverbials'],
        'fronting-emphasis' => ['page_id' => 313, 'category_id' => 48, 'identity' => 'Database\\Seeders\\Page_V3\\BasicGrammar\\WordOrder\\AdvancedFrontingAndEmphasisTheorySeeder', 'path' => '/theory/basic-grammar/word-order/advanced-fronting-and-emphasis'],
        'transformations' => ['page_id' => 153, 'category_id' => 23, 'identity' => 'Database\\Seeders\\Page_V3\\SentenceTransformations\\SentenceTransformationsWordOrderEmphasisTheorySeeder', 'path' => '/theory/sentence-transformations/word-order-emphasis'],
        'common-mistakes' => ['page_id' => 43, 'category_id' => 9, 'identity' => 'Database\\Seeders\\Page_V3\\CommonMistakes\\CommonMistakesWordOrderTheorySeeder', 'path' => '/theory/common-mistakes/word-order-common-mistakes'],
    ];

    public static function scope(): array
    {
        return array_values(array_map(static fn (array $binding): array => [
            'identity' => $binding['identity'], 'locale' => 'uk', 'path' => $binding['path'],
        ], self::BINDINGS));
    }
}
