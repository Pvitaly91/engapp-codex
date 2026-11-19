<?php

namespace App\Support;

/** Exact canonical-theory clones; original questions, hints and foreign callers remain unchanged. */
final class M92BuilderSelectionPresentation extends TheoryBuilderSelectionPresentation
{
    public const AUDIT = 'docs/content/m92-sentence-builder-bank-audit.v1.json';
    public const AUDIT_SHA = '54cb7bdc401d721a4f6f1804133ddfc5cc396a80c4a421fa26b2ab21654a96a9';
    public const TARGETS_SHA = 'ffa5f2c153cdfdfeff082e8d4cd528e5cf51f50e4adac975ab77d0e6f6d48043';
    public const BEFORE_SHA = '86f2f244d5cbb3854ff452fb427e59cd88d64754730d0d228910de80a38bf65c';

    protected static function packageClass(): string { return M92FuturePerfectContinuousBasics::class; }

    protected static function policy(): array
    {
        return ['label' => 'M92', 'package' => 'm92-future-perfect-continuous-basics-bank-audit', 'version' => '1.0.0',
            'scope' => '30 selected existing questions; theory-only condition clones; no question/answer/option/grader/progress writes',
            'audit' => self::AUDIT, 'audit_sha256' => self::AUDIT_SHA, 'targets_sha256' => self::TARGETS_SHA,
            'verb_hint_snapshot_schema' => 'complete-loaded-verb-hints-v1',
            'before_sha256' => self::BEFORE_SHA];
    }
}
