<?php

namespace App\Support;

/** Exact six-owner condition clones; original answers, options and models are never changed. */
final class M78BuilderSelectionPresentation extends TheoryBuilderSelectionPresentation
{
    public const AUDIT='docs/content/m78-sentence-builder-bank-audit.v1.json';
    public const AUDIT_SHA='5561564f886faa59435bb9ec59d980e3079b6b9716d050b5623736bcd928aee7';
    public const TARGETS_SHA='b2b3aa954e0c5ea839392488030b6dffc0090bf417de5804c80c1d08da733eed';
    public const BEFORE_SHA='327424de32f1cebaad86adef078d25aa1d4747b257894be3fdadc6706c630422';
    protected static function packageClass():string { return M78PhrasalQuestionBasics::class; }
    protected static function policy():array
    {
        return ['label'=>'M78','package'=>'m78-phrasal-question-basics-bank-audit','version'=>'1.0.0',
            'scope'=>'30 selected / 30 reused / 0 new; no question/answer/option/grader/progress writes',
            'audit'=>self::AUDIT,'audit_sha256'=>self::AUDIT_SHA,'targets_sha256'=>self::TARGETS_SHA,
            'before_sha256'=>self::BEFORE_SHA];
    }
}

