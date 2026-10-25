<?php

namespace App\Support;

/** Exact canonical-theory clones; original questions, hints and foreign callers remain unchanged. */
final class M90BuilderSelectionPresentation extends TheoryBuilderSelectionPresentation
{
    public const AUDIT = 'docs/content/m90-sentence-builder-bank-audit.v1.json';
    public const AUDIT_SHA = '397ff1459a4c6ba1d40d543b84ece6e018e914493deda35f42a93484ea146a39';
    public const TARGETS_SHA = 'ade0c05319c2ce135cb9e8e19c5f5fc4fc2220d70551fd9ef470eacaf567f909';
    public const BEFORE_SHA = 'a18d8a109c08a914907811dbf98ab865406ffa96a97bb3a04eac6ccd10cdc518';

    protected static function packageClass(): string { return M90PastPerfectFutureSimpleBasics::class; }

    protected static function policy(): array
    {
        return ['label' => 'M90', 'package' => 'm90-past-perfect-future-simple-basics-bank-audit', 'version' => '1.0.0',
            'scope' => '30 selected existing questions; theory-only condition clones; no question/answer/option/grader/progress writes',
            'audit' => self::AUDIT, 'audit_sha256' => self::AUDIT_SHA, 'targets_sha256' => self::TARGETS_SHA,
            'verb_hint_snapshot_schema' => 'complete-loaded-verb-hints-v1',
            'before_sha256' => self::BEFORE_SHA];
    }
}
