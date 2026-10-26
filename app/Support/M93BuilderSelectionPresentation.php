<?php

namespace App\Support;

/** Exact canonical-theory clones; original questions, hints and foreign callers remain unchanged. */
final class M93BuilderSelectionPresentation extends TheoryBuilderSelectionPresentation
{
    public const AUDIT = 'docs/content/m93-sentence-builder-bank-audit.v1.json';
    public const AUDIT_SHA = 'b7c79624cfa6eaaafc4c74cfd79fb7a2d182a0f830472d4ed0a1d3c6c07281b4';
    public const TARGETS_SHA = 'a6280f7edb5d0a21772870bdd0c3eba4d4a8b56860333095afaaf5c2d6a35598';
    public const BEFORE_SHA = '6729742666cb49dfbe3edbd16d93878a495a01c6c46a37a5419279a643772d92';

    protected static function packageClass(): string { return M93FutureCompletionDemonstrativesSomeAny::class; }

    protected static function policy(): array
    {
        return ['label' => 'M93', 'package' => 'm93-future-completion-demonstratives-some-any-bank-audit', 'version' => '1.0.0',
            'scope' => '30 selected existing questions; theory-only condition clones; no question/answer/option/grader/progress writes',
            'audit' => self::AUDIT, 'audit_sha256' => self::AUDIT_SHA, 'targets_sha256' => self::TARGETS_SHA,
            'verb_hint_snapshot_schema' => 'complete-loaded-verb-hints-v1',
            'before_sha256' => self::BEFORE_SHA];
    }
}
