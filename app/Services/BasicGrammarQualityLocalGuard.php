<?php

namespace App\Services;

/** Exact remaining six owners; completed Verb to Be and Word Order are protected. */
final class BasicGrammarQualityLocalGuard extends TheoryLocalReleaseGuard
{
    protected const LABEL = 'Basic Grammar quality';
    protected const PRIVATE_DIRECTORY = 'basic-grammar-quality';
    protected const ENDPOINT = 'basic-grammar-quality-';

    public const BINDINGS = [
        'parts-of-speech' => ['page_id' => 22, 'category_id' => 2, 'identity' => 'Database\\Seeders\\Page_V3\\BasicGrammar\\BasicGrammarPartsOfSpeechTheorySeeder', 'path' => '/theory/basic-grammar/parts-of-speech'],
        'sentence-structure' => ['page_id' => 23, 'category_id' => 2, 'identity' => 'Database\\Seeders\\Page_V3\\BasicGrammar\\BasicGrammarSentenceStructureSvoTheorySeeder', 'path' => '/theory/basic-grammar/sentence-structure-svo'],
        'sentence-types' => ['page_id' => 2, 'category_id' => 2, 'identity' => 'Database\\Seeders\\Page_V3\\BasicGrammar\\BasicGrammarSentenceTypesTheorySeeder', 'path' => '/theory/basic-grammar/sentence-types'],
        'imperatives' => ['page_id' => 21, 'category_id' => 2, 'identity' => 'Database\\Seeders\\Page_V3\\BasicGrammar\\BasicGrammarImperativesTheorySeeder', 'path' => '/theory/basic-grammar/imperatives-sit-down-dont-open-it'],
        'conjunctions' => ['page_id' => 20, 'category_id' => 2, 'identity' => 'Database\\Seeders\\Page_V3\\BasicGrammar\\BasicGrammarConjunctionsTheorySeeder', 'path' => '/theory/basic-grammar/basic-conjunctions-and-but-or-because-so'],
        'have-got' => ['page_id' => 201, 'category_id' => 2, 'identity' => 'Database\\Seeders\\Page_V3\\BasicGrammar\\BasicGrammarHaveGotHasGotTheorySeeder', 'path' => '/theory/basic-grammar/have-got-has-got'],
    ];

    public static function scope(): array
    {
        return array_values(array_map(static fn (array $binding): array => [
            'identity' => $binding['identity'], 'locale' => 'uk', 'path' => $binding['path'],
        ], self::BINDINGS));
    }
}
