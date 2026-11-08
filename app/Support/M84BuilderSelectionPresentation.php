<?php

namespace App\Support;

/** Exact theory-only copies, using the unchanged finite bank-presentation primitive. */
final class M84BuilderSelectionPresentation extends TheoryBuilderSelectionPresentation
{
    public const AUDIT='docs/content/m84-sentence-builder-bank-audit.v1.json';
    public const AUDIT_SHA='8b363a911ac4df61702686e4bda9260ba056ec654faaf152dcbf241b14b0117e';
    public const TARGETS_SHA='5364c4be978371bc8e4bf6adac8425a29ab5f29373cd556d17f9055ef25e9c18';
    public const BEFORE_SHA='babac2b3efa88b87fc8ea50b1ad45fb9feb8cadfdf766c33e76ca7b24aa19916';
    protected static function packageClass():string { return M84WordOrderHaveGot::class; }
    protected static function policy():array
    {
        return ['label'=>'M84','package'=>'m84-word-order-have-got-bank-audit','version'=>'1.0.0',
            'scope'=>'30 selected / 5 reused / 25 new; insert-only question supplement; no old question/answer/option/grader/progress writes',
            'audit'=>self::AUDIT,'audit_sha256'=>self::AUDIT_SHA,'targets_sha256'=>self::TARGETS_SHA,
            'before_sha256'=>self::BEFORE_SHA];
    }
}
