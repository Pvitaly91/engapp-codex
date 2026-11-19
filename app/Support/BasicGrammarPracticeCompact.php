<?php

namespace App\Support;

/** Finite short instructions; native controls and grading remain source-owned. */
final class BasicGrammarPracticeCompact extends TheoryPracticeCompactProjection
{
    public const SOURCE = 'docs/content/basic-grammar-practice-compact.v1.json';
    public const SOURCE_SHA = 'e82182064669e362f6f2fc7d97f5e9b8c6be9f6f281ce2c49f86d0c5e642e16b';
    public const SUPPLEMENT = BasicGrammarPracticeSupplement::class;
}
