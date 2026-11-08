<?php

namespace App\Support;

/** Exact canonical-theory conditions only; original questions and every foreign caller remain unchanged. */
final class M87BuilderSelectionPresentation extends TheoryBuilderSelectionPresentation
{
    public const AUDIT = 'docs/content/m87-sentence-builder-bank-audit.v1.json';
    // Frozen raw-byte audit and independently reviewed target projection pins.
    public const AUDIT_SHA = '858c9809863288177471c1044ba583e5cc50c3022df1033786a7210f09364878';
    public const TARGETS_SHA = 'de1b6cf96b577f7bfeb18f91cfddc856f48f3b6f91ab78f4b223095077efefb4';
    public const BEFORE_SHA = 'c9b7cffb07a0440e5051a0b7c522eb016ce4cdcc80107edf6573e8780eb151c5';

    protected static function packageClass(): string { return M87ContinuousCompletionPastSimple::class; }

    protected static function policy(): array
    {
        return ['label' => 'M87', 'package' => 'm87-continuous-completion-past-simple-bank-audit', 'version' => '1.0.0',
            'scope' => '30 selected existing questions; theory-only condition clones; no question/answer/option/grader/progress writes',
            'audit' => self::AUDIT, 'audit_sha256' => self::AUDIT_SHA, 'targets_sha256' => self::TARGETS_SHA,
            'before_sha256' => self::BEFORE_SHA];
    }
}
