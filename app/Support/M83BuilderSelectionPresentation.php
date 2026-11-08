<?php

namespace App\Support;

/** Finite reviewed conditions on exact canonical-theory clones; no shared bank mutation. */
final class M83BuilderSelectionPresentation extends TheoryBuilderSelectionPresentation
{
    public const AUDIT='docs/content/m83-sentence-builder-bank-audit.v1.json';
    public const AUDIT_SHA='b6b60ca21dc0f9793d4e18d581b7f6775ae5279da381496a73d9bf8f0a4c5b16';
    public const TARGETS_SHA='4a3344fc7ad21f3081b86f75a68ef2f8575c6ea2a5b44facc23b7fa71d1d0daf';
    public const BEFORE_SHA='d522a715b123748b84f52f076b156ccf5224892f138d5429062d9527a843e33e';
    protected static function packageClass():string { return M83B2RevisionBasicGrammar::class; }
    protected static function policy():array
    {
        return ['label'=>'M83','package'=>'m83-b2-revision-basic-grammar-bank-audit','version'=>'1.0.0',
            'scope'=>'30 selected / 30 reused / 0 new; no question/answer/option/grader/progress writes',
            'audit'=>self::AUDIT,'audit_sha256'=>self::AUDIT_SHA,'targets_sha256'=>self::TARGETS_SHA,
            'before_sha256'=>self::BEFORE_SHA];
    }
}
