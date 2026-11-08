<?php

namespace App\Support;

/** Exact canonical-theory clones; original questions, hints and foreign callers remain unchanged. */
final class M91BuilderSelectionPresentation extends TheoryBuilderSelectionPresentation
{
    public const AUDIT = 'docs/content/m91-sentence-builder-bank-audit.v1.json';
    public const AUDIT_SHA = 'a7c8539643c6d7a8be149a523697e61d58f21b8a5b551f76908ab6d7969c5d19';
    public const TARGETS_SHA = 'bdcaa6eea3b7883e4c18fb3884c0f7740c8f7595c5cea50a390550751b28b071';
    public const BEFORE_SHA = '182192d1e92382649dc4179df5ca88f43a858c2f4cd8a0f527d87dc3222ee18f';

    protected static function packageClass(): string { return M91FutureSimpleCompletionContinuous::class; }

    protected static function policy(): array
    {
        return ['label' => 'M91', 'package' => 'm91-future-simple-completion-continuous-bank-audit', 'version' => '1.0.0',
            'scope' => '30 selected existing questions; theory-only condition clones; no question/answer/option/grader/progress writes',
            'audit' => self::AUDIT, 'audit_sha256' => self::AUDIT_SHA, 'targets_sha256' => self::TARGETS_SHA,
            'verb_hint_snapshot_schema' => 'complete-loaded-verb-hints-v1',
            'before_sha256' => self::BEFORE_SHA];
    }
}
