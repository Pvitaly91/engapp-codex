<?php

namespace App\Support;

/** Exact existing-question conditions on canonical-theory clones only. */
final class M80BuilderSelectionPresentation extends TheoryBuilderSelectionPresentation
{
    public const AUDIT='docs/content/m80-sentence-builder-bank-audit.v1.json';
    public const AUDIT_SHA='421a835e9483138e7a6cf7abd14ecdcc938f8551ac0b04efc59b63d530d8435b';
    public const TARGETS_SHA='9a21333a08b1cddec38d92c665191f3b48aaf2c8e582a23c1345de9e8f9a3d19';
    public const BEFORE_SHA='75a9e58003f9364c45e4e3c2696aab7c0b8e5d5073a2bb2acefe54fb198dbefb';
    protected static function packageClass():string { return M80NegativesTransformations::class; }
    protected static function policy():array
    {
        return ['label'=>'M80','package'=>'m80-negatives-transformations-bank-audit','version'=>'1.0.0',
            'scope'=>'30 selected / 30 reused / 0 new; no question/answer/option/grader/progress writes',
            'audit'=>self::AUDIT,'audit_sha256'=>self::AUDIT_SHA,'targets_sha256'=>self::TARGETS_SHA,
            'before_sha256'=>self::BEFORE_SHA];
    }
}
