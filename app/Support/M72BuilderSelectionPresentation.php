<?php

namespace App\Support;

/** Exact-bound condition-only clones for the existing thematic banks. */
final class M72BuilderSelectionPresentation extends TheoryBuilderSelectionPresentation
{
    public const AUDIT = 'docs/content/m72-partitives-negatives-geography-bank-audit.v1.0.1.json';
    public const AUDIT_SHA = '803855b45aa9f01d4ae7a9c6e01b8fa108f975104624ff3825bea21015999529';
    public const REVISION = 'docs/content/m72-bank-selection-revision.v1.0.1.json';
    public const REVISION_SHA = '09d3e65edf74dc1e2e05e1bc0edd11a8b63e4b4062f502d375605ea9434d00b6';
    private const TARGETS_SHA = '8165a14510e0d8ed9b5e17cc1dac3929b5e28de394c8700e3e0cfa018bcf74a6';
    private const SCOPE = ['selected_questions'=>15,'reused_questions'=>15,'new_questions'=>0,'question_writes'=>0,
        'option_writes'=>0,'answer_writes'=>0,'option_link_writes'=>0,'link_writes'=>0,'old_hint_writes'=>0,
        'old_question_updates'=>0,'old_option_updates'=>0,'old_answer_updates'=>0,'grader_changes'=>0,'progress_writes'=>0];

    protected static function packageClass(): string { return M72PartitivesNegativesGeography::class; }
    protected static function policy(): array
    {
        return ['label'=>'M72','package'=>'m72-partitives-negatives-geography-bank-audit','version'=>'1.0.1',
            'audit'=>self::AUDIT,'audit_sha256'=>self::AUDIT_SHA,'targets_sha256'=>self::TARGETS_SHA,
            'before_sha256'=>'33cd475c775288da255c3584aa3b9ef665255a1f31abc14f4df294ef749a690d','scope'=>self::SCOPE];
    }
}
