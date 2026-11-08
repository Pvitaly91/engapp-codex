<?php

namespace App\Support;

/** Exact-bound condition-only clones for the existing thematic banks. */
final class M71BuilderSelectionPresentation extends TheoryBuilderSelectionPresentation
{
    public const AUDIT = 'docs/content/m71-articles-quantifiers-bank-audit.v1.json';
    public const AUDIT_SHA = '5bb43042588c644fd3f88e1dbf2a6fc36e803a5d453a4c64895d347c58b81c26';
    private const TARGETS_SHA = 'ea156c4bc4615ca7207c9545fb741fa2dd4744dbea127ce2859d223e622e43cb';
    private const SCOPE = ['selected_questions'=>15,'reused_questions'=>15,'new_questions'=>0,'question_writes'=>0,
        'option_writes'=>0,'answer_writes'=>0,'option_link_writes'=>0,'link_writes'=>0,'old_hint_writes'=>0,
        'old_question_updates'=>0,'old_option_updates'=>0,'old_answer_updates'=>0,'grader_changes'=>0,'progress_writes'=>0];

    protected static function packageClass(): string { return M71ArticlesQuantifiers::class; }
    protected static function policy(): array
    {
        return ['label'=>'M71','package'=>'m71-articles-quantifiers-bank-audit','version'=>'1.0.0',
            'audit'=>self::AUDIT,'audit_sha256'=>self::AUDIT_SHA,'targets_sha256'=>self::TARGETS_SHA,
            'before_sha256'=>'8644354b102cbe4c8756e13c6af76472f753c10185d122849a0bf95e0279bd35','scope'=>self::SCOPE];
    }
}
