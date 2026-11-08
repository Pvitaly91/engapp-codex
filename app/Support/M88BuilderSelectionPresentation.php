<?php

namespace App\Support;

/** Exact canonical-theory conditions only; original questions and every foreign caller remain unchanged. */
final class M88BuilderSelectionPresentation extends TheoryBuilderSelectionPresentation
{
    public const AUDIT = 'docs/content/m88-sentence-builder-bank-audit.v1.json';
    public const AUDIT_SHA = '4d0115170624021585d3099a3af1b0a0e2f01ed53ff56738dd0011941bd1eedd';
    public const TARGETS_SHA = '21e8cebaf5b6ff7db7443d0557e5a6e1a2134c66a28118cd064c5ce7d4615f9a';
    public const BEFORE_SHA = 'ea09671a15af34ba0045b195b13e97a9581a38a308f6e136dd0c02d457b23abc';

    protected static function packageClass(): string { return M88PastContinuousPresentPerfectBasics::class; }

    protected static function policy(): array
    {
        return ['label' => 'M88', 'package' => 'm88-past-continuous-present-perfect-basics-bank-audit', 'version' => '1.0.0',
            'scope' => '30 selected existing questions; theory-only condition clones; no question/answer/option/grader/progress writes',
            'audit' => self::AUDIT, 'audit_sha256' => self::AUDIT_SHA, 'targets_sha256' => self::TARGETS_SHA,
            'before_sha256' => self::BEFORE_SHA];
    }
}
