<?php

namespace App\Services;

/** Exact six-owner local-only boundary for the finite UK Verb to Be revision. */
final class VerbToBeQualityLocalGuard extends TheoryLocalReleaseGuard
{
    protected const LABEL = 'Verb to Be quality';
    protected const PRIVATE_DIRECTORY = 'verb-to-be-quality';
    protected const ENDPOINT = 'verb-to-be-quality-';

    public const BINDINGS = [
        'present' => ['page_id' => 28, 'category_id' => 8, 'identity' => 'Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\VerbToBePresentTheorySeeder', 'path' => '/theory/basic-grammar/verb-to-be/verb-to-be-present'],
        'negatives' => ['page_id' => 26, 'category_id' => 8, 'identity' => 'Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\VerbToBeNegativesTheorySeeder', 'path' => '/theory/basic-grammar/verb-to-be/verb-to-be-negatives'],
        'questions' => ['page_id' => 29, 'category_id' => 8, 'identity' => 'Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\VerbToBeQuestionsTheorySeeder', 'path' => '/theory/basic-grammar/verb-to-be/verb-to-be-questions'],
        'past' => ['page_id' => 27, 'category_id' => 8, 'identity' => 'Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\VerbToBePastTheorySeeder', 'path' => '/theory/basic-grammar/verb-to-be/verb-to-be-past'],
        'future' => ['page_id' => 25, 'category_id' => 8, 'identity' => 'Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\VerbToBeFutureTheorySeeder', 'path' => '/theory/basic-grammar/verb-to-be/verb-to-be-future'],
        'there' => ['page_id' => 24, 'category_id' => 8, 'identity' => 'Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\ThereIsThereAreTheorySeeder', 'path' => '/theory/basic-grammar/verb-to-be/there-is-there-are'],
    ];

    public static function scope(): array
    {
        return array_values(array_map(static fn (array $binding): array => [
            'identity' => $binding['identity'], 'locale' => 'uk', 'path' => $binding['path'],
        ], self::BINDINGS));
    }
}
