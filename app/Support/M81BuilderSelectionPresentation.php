<?php

namespace App\Support;

/** Exact existing-question conditions on canonical-theory clones only. */
final class M81BuilderSelectionPresentation extends TheoryBuilderSelectionPresentation
{
    public const AUDIT='docs/content/m81-sentence-builder-bank-audit.v1.json';
    public const AUDIT_SHA='2f69a7466a0512d06eef3c0ab2387dd7a5ef8c24dc6fe6793c67185014e8df70';
    public const TARGETS_SHA='cad55bd8efde774fdaa5850771713560adea9e22f3633d3e86ff5fa7e28a544b';
    public const BEFORE_SHA='facabc5369230dac0958f2e2277c7cd0e4b0523fb794c041afd46116e095c023';
    protected static function packageClass():string { return M81TransformationsCommonMistakes::class; }
    protected static function policy():array
    {
        return ['label'=>'M81','package'=>'m81-transformations-common-mistakes-bank-audit','version'=>'1.0.0',
            'scope'=>'30 selected / 30 reused / 0 new; no question/answer/option/grader/progress writes',
            'audit'=>self::AUDIT,'audit_sha256'=>self::AUDIT_SHA,'targets_sha256'=>self::TARGETS_SHA,
            'before_sha256'=>self::BEFORE_SHA];
    }
}
