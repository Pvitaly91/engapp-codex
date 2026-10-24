<?php

namespace App\Support;

/** Exact theory-only condition clones and finite exclusions; original banks remain unchanged. */
final class M85BuilderSelectionPresentation extends TheoryBuilderSelectionPresentation
{
    public const AUDIT='docs/content/m85-sentence-builder-bank-audit.v1.json';
    public const AUDIT_SHA='7968e7ed3e524e2029a2190b3f9362c27ef8b4a2bea2b35aa5e745822dbafbb4';
    public const TARGETS_SHA='24e70b966ad069ab523b628aadff9ebb895c5203f629fddbe94b10922af905f3';
    public const BEFORE_SHA='fe639c06446c6fb46b5b4ab25ae654e3910b6733cc124a9e4bee262e41b411b7';
    protected static function packageClass():string{return M85VerbToBe::class;}
    protected static function policy():array
    {
        return ['label'=>'M85','package'=>'m85-verb-to-be-bank-audit','version'=>'1.0.0',
            'scope'=>'30 selected existing questions; theory-only condition clones; no question/answer/option/grader/progress writes',
            'audit'=>self::AUDIT,'audit_sha256'=>self::AUDIT_SHA,'targets_sha256'=>self::TARGETS_SHA,'before_sha256'=>self::BEFORE_SHA];
    }
}
