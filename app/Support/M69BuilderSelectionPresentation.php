<?php

namespace App\Support;

/** Finite M69 teaching profile; inherited selection never grades, queries or writes models. */
final class M69BuilderSelectionPresentation extends TheoryBuilderSelectionPresentation
{
    public const AUDIT = 'docs/content/m69-adjectives-adverbs-bank-audit.v1.json';
    public const AUDIT_SHA = 'bd402d3ef93cd2ef758c9d096fc424e319e26c57991af9e80f34cd2f0867b512';
    private const TARGETS_SHA = '64e24c9a6905a3d5f4a96a34c3678f3f259313e399bad2c97f9c8c9cb2d664b3';
    private const SCOPE = ['selected_questions'=>15,'reused_questions'=>15,'new_questions'=>0,'question_writes'=>0,'option_writes'=>0,'answer_writes'=>0,'option_link_writes'=>0,'link_writes'=>0,'old_hint_writes'=>0,'old_question_updates'=>0,'old_option_updates'=>0,'old_answer_updates'=>0,'grader_changes'=>0,'progress_writes'=>0];

    protected static function packageClass(): string
    {
        return M69AdjectivesAdverbs::class;
    }

    protected static function policy(): array
    {
        return [
            'label' => 'M69',
            'package' => 'm69-adjectives-adverbs-bank-audit',
            'version' => '1.0.0',
            'audit' => self::AUDIT,
            'audit_sha256' => self::AUDIT_SHA,
            'targets_sha256' => self::TARGETS_SHA,
            'before_sha256' => '3fbcc654435119c5607d0ede041bead091bce4527efadde6a2b1bef661de4d62',
            'scope' => self::SCOPE,
        ];
    }
}
