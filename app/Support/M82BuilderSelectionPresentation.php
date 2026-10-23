<?php

namespace App\Support;

/** Exact existing-question conditions on canonical-theory clones only. */
final class M82BuilderSelectionPresentation extends TheoryBuilderSelectionPresentation
{
    public const AUDIT='docs/content/m82-sentence-builder-bank-audit.v1.json';
    public const AUDIT_SHA='c99047c5719e2f7662cb8dd356f90d9aba68a18d2edfa1934024f5ac9f1e1dc2';
    public const TARGETS_SHA='8b438ff8574abf3ad42bf25db7a75a33559fd935b9f239d3264e63713fe6432e';
    public const BEFORE_SHA='94312691251f96433ee72c3fe544db71767c795495874af3ba1c80c2cbb49c62';
    protected static function packageClass():string { return M82CommonMistakesLevelRevision::class; }
    protected static function policy():array
    {
        return ['label'=>'M82','package'=>'m82-common-mistakes-level-revision-bank-audit','version'=>'1.0.0',
            'scope'=>'30 selected / 30 reused / 0 new; no question/answer/option/grader/progress writes',
            'audit'=>self::AUDIT,'audit_sha256'=>self::AUDIT_SHA,'targets_sha256'=>self::TARGETS_SHA,
            'before_sha256'=>self::BEFORE_SHA];
    }
}
