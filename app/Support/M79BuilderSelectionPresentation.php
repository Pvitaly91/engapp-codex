<?php

namespace App\Support;

/** Exact six-owner condition clones; original answers, options and models are never changed. */
final class M79BuilderSelectionPresentation extends TheoryBuilderSelectionPresentation
{
    public const AUDIT='docs/content/m79-sentence-builder-bank-audit.v1.0.1.json';
    public const AUDIT_SHA='d3acbfc669a8842f11dcbf040493541a66c0e07305c626ce62bba4aee071ebd7';
    public const TARGETS_SHA='95d50bab4ce06b7acee3a85679b5a976484ecace54ee6ed967697f179a5eacb2';
    public const BEFORE_SHA='8c261b17b3f7e332fc5fc86eef8b555af83de19938aee7ae83d2a23e7dbb3add';
    protected static function packageClass():string { return M79QuestionTypesCompletion::class; }
    protected static function policy():array
    {
        return ['label'=>'M79','package'=>'m79-question-types-completion-bank-audit','version'=>'1.0.1',
            'scope'=>'30 selected / 22 reused / 8 new; original rows unchanged; no grader/progress writes',
            'audit'=>self::AUDIT,'audit_sha256'=>self::AUDIT_SHA,'targets_sha256'=>self::TARGETS_SHA,
            'before_sha256'=>self::BEFORE_SHA];
    }
}
