<?php

namespace App\Support;

/** Exact-bound display-condition clones; original questions and tokens are unchanged. */
final class M74BuilderSelectionPresentation extends TheoryBuilderSelectionPresentation
{
    public const AUDIT = 'docs/content/m74-other-pronouns-basics-bank-audit.v1.0.1.json';
    public const AUDIT_SHA = '0937345b2d3c2b8f7842a0e47d75cd8da268a9ae4e43734aaa8447ce49c78e26';
    private const TARGETS_SHA = 'efe8bea1beb47c2bf7e53436bd52d4bfb6d9679a3e47424f5940333a5d173d2b';
    private const SCOPE = ['selected_questions'=>15,'reused_questions'=>14,'new_questions'=>1,'question_writes'=>0,
        'option_writes'=>0,'answer_writes'=>0,'option_link_writes'=>0,'link_writes'=>0,'old_hint_writes'=>0,
        'old_question_updates'=>0,'old_option_updates'=>0,'old_answer_updates'=>0,'grader_changes'=>0,'progress_writes'=>0];
    protected static function packageClass():string { return M74OtherPronounsBasics::class; }
    protected static function policy():array
    {
        return ['label'=>'M74','package'=>'m74-other-pronouns-bank-audit','version'=>'1.0.1',
            'audit'=>self::AUDIT,'audit_sha256'=>self::AUDIT_SHA,'targets_sha256'=>self::TARGETS_SHA,
            'before_sha256'=>'9e7abb860af2a61bfb10f61047f7ffaad055e20368edfc24c9ed095301516fdc','scope'=>self::SCOPE];
    }
}
