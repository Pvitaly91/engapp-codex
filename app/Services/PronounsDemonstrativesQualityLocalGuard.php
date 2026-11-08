<?php

namespace App\Services;

/** Ten exact current UK owners, and no unrelated local/remote target. */
final class PronounsDemonstrativesQualityLocalGuard extends TheoryLocalReleaseGuard
{
    protected const LABEL = 'Pronouns and Demonstratives quality';
    protected const PRIVATE_DIRECTORY = 'pronouns-demonstratives-quality';
    protected const ENDPOINT = 'pronouns-demonstratives-quality-';

    public const BINDINGS = [
        'pronouns-overview' => ['page_id' => 126, 'category_id' => 20, 'identity' => 'Database\\Seeders\\Page_V3\\PronounsDemonstratives\\PronounsDemonstrativesPronounsTheorySeeder', 'path' => '/theory/zaimennyky-ta-vkazivni-slova/pronouns-theory'],
        'personal-object-pronouns' => ['page_id' => 124, 'category_id' => 20, 'identity' => 'Database\\Seeders\\Page_V3\\PronounsDemonstratives\\PronounsDemonstrativesPersonalObjectPronounsTheorySeeder', 'path' => '/theory/zaimennyky-ta-vkazivni-slova/personal-object-pronouns'],
        'possessive-forms' => ['page_id' => 125, 'category_id' => 20, 'identity' => 'Database\\Seeders\\Page_V3\\PronounsDemonstratives\\PronounsDemonstrativesPossessiveAdjectivesVsPronounsTheorySeeder', 'path' => '/theory/zaimennyky-ta-vkazivni-slova/possessive-adjectives-vs-pronouns-my-mine-your-yours'],
        'reflexive-pronouns' => ['page_id' => 128, 'category_id' => 20, 'identity' => 'Database\\Seeders\\Page_V3\\PronounsDemonstratives\\PronounsDemonstrativesReflexivePronounsTheorySeeder', 'path' => '/theory/zaimennyky-ta-vkazivni-slova/reflexive-pronouns-myself-yourself-themselves'],
        'demonstratives' => ['page_id' => 130, 'category_id' => 20, 'identity' => 'Database\\Seeders\\Page_V3\\PronounsDemonstratives\\PronounsDemonstrativesThisThatTheseThoseTheorySeeder', 'path' => '/theory/zaimennyky-ta-vkazivni-slova/demonstratives-this-that-these-those'],
        'indefinite-pronouns' => ['page_id' => 122, 'category_id' => 20, 'identity' => 'Database\\Seeders\\Page_V3\\PronounsDemonstratives\\PronounsDemonstrativesIndefinitePronounsTheorySeeder', 'path' => '/theory/zaimennyky-ta-vkazivni-slova/indefinite-pronouns-someone-anyone-nobody-nothing'],
        'relative-pronouns' => ['page_id' => 129, 'category_id' => 20, 'identity' => 'Database\\Seeders\\Page_V3\\PronounsDemonstratives\\PronounsDemonstrativesRelativePronounsTheorySeeder', 'path' => '/theory/zaimennyky-ta-vkazivni-slova/relative-pronouns-who-which-that-whose'],
        'each-every-all' => ['page_id' => 121, 'category_id' => 20, 'identity' => 'Database\\Seeders\\Page_V3\\PronounsDemonstratives\\PronounsDemonstrativesEachEveryAllTheorySeeder', 'path' => '/theory/zaimennyky-ta-vkazivni-slova/each-every-all'],
        'reciprocal-pronouns' => ['page_id' => 127, 'category_id' => 20, 'identity' => 'Database\\Seeders\\Page_V3\\PronounsDemonstratives\\PronounsDemonstrativesReciprocalPronounsTheorySeeder', 'path' => '/theory/zaimennyky-ta-vkazivni-slova/reciprocal-pronouns-each-other-one-another'],
        'one-ones' => ['page_id' => 123, 'category_id' => 20, 'identity' => 'Database\\Seeders\\Page_V3\\PronounsDemonstratives\\PronounsDemonstrativesOneOnesTheorySeeder', 'path' => '/theory/zaimennyky-ta-vkazivni-slova/one-ones'],
    ];

    public static function scope(): array
    {
        return array_values(array_map(static fn (array $binding): array => [
            'identity' => $binding['identity'], 'locale' => 'uk', 'path' => $binding['path'],
        ], self::BINDINGS));
    }
}
