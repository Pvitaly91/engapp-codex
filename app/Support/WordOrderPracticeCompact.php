<?php

namespace App\Support;

/** Only the eight Basic Grammar children; unrelated transformation/mistake lessons stay unchanged. */
final class WordOrderPracticeCompact extends TheoryPracticeCompactProjection
{
    public const SOURCE = 'docs/content/word-order-practice-compact.v1.json';
    public const SOURCE_SHA = '08d091867afc15802e56459a76dfca8f84d23df146bbab8a5b7396504ec4a5b3';
    public const SUPPLEMENT = WordOrderPracticeSupplement::class;
    public const TOPICS = ['basic', 'questions-negatives', 'adverbs-adverbials', 'verbs-objects',
        'advanced-emphasis', 'inversion-basics', 'negative-adverbial-inversion', 'fronting-emphasis'];
}
