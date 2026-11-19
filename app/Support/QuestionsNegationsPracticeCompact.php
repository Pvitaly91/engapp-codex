<?php

namespace App\Support;

/** Finite teaching display data; all rendering and grading stay in the shared native components. */
final class QuestionsNegationsPracticeCompact extends TheoryPracticeCompactProjection
{
    public const SOURCE = 'docs/content/questions-negations-practice-compact.v1.json';
    public const SOURCE_SHA = 'f4f1b6f1b3576cb0a544f1c5fb3dfc87a1b9e1079d6cdce6682de3bac6315250';
    public const SUPPLEMENT = QuestionsNegationsPracticeSupplement::class;
}
