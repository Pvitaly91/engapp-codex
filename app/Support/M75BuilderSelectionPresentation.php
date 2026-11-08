<?php

namespace App\Support;

/** Closed three-owner selection and condition clones; no key/case/option changes. */
final class M75BuilderSelectionPresentation extends TheoryBuilderSelectionPresentation
{
    public const AUDIT = 'docs/content/m75-sentence-builder-bank-audit.v1.json';
    public const AUDIT_SHA = '78872f9dfb4594e1c3caf58ee899c910ec7638d4bfbe867221e04e9f6dd6d0e6';
    public const TARGETS_SHA = 'dde4ad3d7eb907129d838490c0e73b24cb66cc51e4dab5aceb1dbfd4e86f8618';
    public const BEFORE_SHA = '5e238ff98020caf4ce97a3057c7a624bd48898d64791e83a720e3fe792b09c4a';
    protected static function packageClass():string { return M75PronounForms::class; }
    protected static function policy():array
    {
        return ['label'=>'M75','package'=>'m75-pronoun-forms-bank-audit','version'=>'1.0.0',
            'scope'=>'15 selected / 13 reused / 2 new; no existing question/answer/option/grader/progress writes',
            'audit'=>self::AUDIT,'audit_sha256'=>self::AUDIT_SHA,'targets_sha256'=>self::TARGETS_SHA,
            'before_sha256'=>self::BEFORE_SHA];
    }
}
