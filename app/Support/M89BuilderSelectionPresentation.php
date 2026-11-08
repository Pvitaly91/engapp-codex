<?php

namespace App\Support;

/** Exact canonical-theory clones; original questions, hints and foreign callers remain unchanged. */
final class M89BuilderSelectionPresentation extends TheoryBuilderSelectionPresentation
{
    public const AUDIT = 'docs/content/m89-sentence-builder-bank-audit.v1.json';
    public const AUDIT_SHA = '9a542e7a2bb19db2e11256a42639c0edf80dfc297c0419c01b11132839df999f';
    public const TARGETS_SHA = 'ecab740dd39c4653d56c26fdb9e1fe673a64e9b1b58f2d5a38aa5586b261055c';
    public const BEFORE_SHA = '6c443660442bc7f683264c381b559973e19ea2fce9e58fa8410e6b21ff0416ae';

    protected static function packageClass(): string { return M89PresentPerfectCompletionContinuous::class; }

    protected static function policy(): array
    {
        return ['label' => 'M89', 'package' => 'm89-present-perfect-completion-continuous-bank-audit', 'version' => '1.0.0',
            'scope' => '30 selected existing questions; theory-only condition clones; no question/answer/option/grader/progress writes',
            'audit' => self::AUDIT, 'audit_sha256' => self::AUDIT_SHA, 'targets_sha256' => self::TARGETS_SHA,
            'verb_hint_snapshot_schema' => 'complete-loaded-verb-hints-v1',
            'before_sha256' => self::BEFORE_SHA];
    }
}
