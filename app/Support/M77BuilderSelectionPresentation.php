<?php

namespace App\Support;

/** Exact six-owner condition clones; original answers, options and models are never changed. */
final class M77BuilderSelectionPresentation extends TheoryBuilderSelectionPresentation
{
    public const AUDIT='docs/content/m77-sentence-builder-bank-audit.v1.json';
    public const AUDIT_SHA='806db0adf2f70e64d0021c42170f6f856f796af3b2b19e87895fedb29df90cc1';
    public const TARGETS_SHA='8f3af0a2b3a6ff53e6481f30674a15852f521f8d0bd2026b8aa0ef4da52a84f4';
    public const BEFORE_SHA='3ee7c26081727dd746dfe1e94a03dcc4273c48bcb40be5091b84d27bc52cab31';
    protected static function packageClass():string { return M77OnePrepositionsPhrasal::class; }
    protected static function policy():array
    {
        return ['label'=>'M77','package'=>'m77-one-prepositions-phrasal-bank-audit','version'=>'1.0.0',
            'scope'=>'30 selected / 30 reused / 0 new; no question/answer/option/grader/progress writes',
            'audit'=>self::AUDIT,'audit_sha256'=>self::AUDIT_SHA,'targets_sha256'=>self::TARGETS_SHA,
            'before_sha256'=>self::BEFORE_SHA];
    }
}
