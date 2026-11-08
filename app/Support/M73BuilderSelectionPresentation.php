<?php

namespace App\Support;

/** Exact-bound condition-only clones for the existing thematic banks. */
final class M73BuilderSelectionPresentation extends TheoryBuilderSelectionPresentation
{
    public const AUDIT = 'docs/content/m73-generic-irregular-collective-bank-audit.v1.json';
    public const AUDIT_SHA = 'a17f7f03501efe1db4c232736f04fd2b811d4913fcf8aef2102227e9a6581042';
    private const TARGETS_SHA = '2fbf634b76cb25b6185303b899d177f6dba37d484ae997743070d93671901d43';
    private const SCOPE = ['selected_questions'=>15,'reused_questions'=>15,'new_questions'=>0,'question_writes'=>0,
        'option_writes'=>0,'answer_writes'=>0,'option_link_writes'=>0,'link_writes'=>0,'old_hint_writes'=>0,
        'old_question_updates'=>0,'old_option_updates'=>0,'old_answer_updates'=>0,'grader_changes'=>0,'progress_writes'=>0];

    protected static function packageClass(): string { return M73GenericIrregularCollective::class; }
    protected static function policy(): array
    {
        return ['label'=>'M73','package'=>'m73-generic-irregular-collective-bank-audit','version'=>'1.0.0',
            'audit'=>self::AUDIT,'audit_sha256'=>self::AUDIT_SHA,'targets_sha256'=>self::TARGETS_SHA,
            'before_sha256'=>'41cb3ac163738e4d2662b4c53a3b2288de3303161d2da6ef5a1f42a9632d0ac9','scope'=>self::SCOPE];
    }
}
