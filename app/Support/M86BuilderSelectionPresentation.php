<?php

namespace App\Support;

/** Exact canonical-theory conditions only; original questions and every foreign caller remain unchanged. */
final class M86BuilderSelectionPresentation extends TheoryBuilderSelectionPresentation
{
    public const AUDIT = 'docs/content/m86-sentence-builder-bank-audit.v1.json';
    // Frozen raw-byte audit and independently reviewed target projection pins.
    public const AUDIT_SHA = '150d4245e064c01e0c656b0d5ecec172daf9abd5b483640b6fb381a30a0866cd';
    public const TARGETS_SHA = '921c30eed8afca73c44bd8862a35d3fbabe08ab1b4421a1a7df2e77f02c621dc';
    public const BEFORE_SHA = '42f8a613fcb1721b2badcfabb9c67116f2c79fa9c14b7dc4df5e19dd71892208';

    protected static function packageClass(): string { return M86PresentSimpleContinuousBasics::class; }

    protected static function policy(): array
    {
        return ['label' => 'M86', 'package' => 'm86-present-simple-continuous-basics-bank-audit', 'version' => '1.0.0',
            'scope' => '30 selected existing questions; theory-only condition clones; no question/answer/option/grader/progress writes',
            'audit' => self::AUDIT, 'audit_sha256' => self::AUDIT_SHA, 'targets_sha256' => self::TARGETS_SHA,
            'before_sha256' => self::BEFORE_SHA];
    }
}
