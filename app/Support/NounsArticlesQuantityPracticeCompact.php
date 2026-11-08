<?php

namespace App\Support;

/** Finite teaching display data, rendered by the shared native practice view. */
final class NounsArticlesQuantityPracticeCompact extends TheoryPracticeCompactProjection
{
    public const SOURCE = 'docs/content/nouns-articles-quantity-practice-compact.v1.json';
    public const SOURCE_SHA = '0df0bdb83f561a4d52bdfef32d006c1cc65938ada2a16e11d6a58155655acafb';
    public const SUPPLEMENT = NounsArticlesQuantityPracticeSupplement::class;
}
