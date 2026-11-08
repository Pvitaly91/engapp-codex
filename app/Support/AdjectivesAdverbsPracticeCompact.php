<?php

namespace App\Support;

/** Finite teaching display data; existing native components own every visual property. */
final class AdjectivesAdverbsPracticeCompact extends TheoryPracticeCompactProjection
{
    public const SOURCE = 'docs/content/adjectives-adverbs-practice-compact.v1.json';
    public const SOURCE_SHA = '4334668c0117ee93491a8f4651f701864bb70f2608d0561a818d2eb1d590f496';
    public const SUPPLEMENT = AdjectivesAdverbsPracticeSupplement::class;
}
