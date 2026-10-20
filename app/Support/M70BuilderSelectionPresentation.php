<?php

namespace App\Support;

/** Exact-bound condition-only clones for the existing thematic banks. */
final class M70BuilderSelectionPresentation extends TheoryBuilderSelectionPresentation
{
    public const AUDIT = 'docs/content/m70-intensifiers-noun-basics-bank-audit.v1.json';
    public const AUDIT_SHA = '0ba0c655beca179cc1a03396327f87e2e51470f371ca5af142d354616fa2d6f0';
    private const TARGETS_SHA = '4b7410a12ca54a1a4a9c7ca2522e63bcb62c273e2183ac2d084621d7856109b7';
    private const SCOPE = ['selected_questions'=>15,'reused_questions'=>15,'new_questions'=>0,'question_writes'=>0,
        'option_writes'=>0,'answer_writes'=>0,'option_link_writes'=>0,'link_writes'=>0,'old_hint_writes'=>0,
        'old_question_updates'=>0,'old_option_updates'=>0,'old_answer_updates'=>0,'grader_changes'=>0,'progress_writes'=>0];

    protected static function packageClass(): string { return M70IntensifiersNounBasics::class; }
    protected static function policy(): array
    {
        return ['label'=>'M70','package'=>'m70-intensifiers-noun-basics-bank-audit','version'=>'1.0.0',
            'audit'=>self::AUDIT,'audit_sha256'=>self::AUDIT_SHA,'targets_sha256'=>self::TARGETS_SHA,
            'before_sha256'=>'5c2a7785156041dc8cc6e4dc26f8776b939ec59226119898fb9f0d1376d2f3b8','scope'=>self::SCOPE];
    }
}
