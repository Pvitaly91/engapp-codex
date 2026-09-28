<?php

namespace App\Support;

/**
 * Preserve authored section numbers on only the three M24 native lessons.
 * Every existing page keeps the original icon badge.
 */
final class M24NativeTitleNumber
{
    private const SEEDERS = [
        'Database\\Seeders\\Page_V3\\Tenses\\TensesPresentPerfectVsPresentPerfectContinuousTheorySeeder',
        'Database\\Seeders\\Page_V3\\Tenses\\TensesNarrativeTensesTheorySeeder',
        'Database\\Seeders\\Page_V3\\BasicGrammar\\BasicGrammarB1MixedRevisionTheorySeeder',
    ];

    public static function forBlock(object $block, ?string $title): ?string
    {
        if (!in_array($block->seeder ?? null, self::SEEDERS, true) || $title === null) {
            return null;
        }

        return preg_match('/^(\d+)\.\s*/u', $title, $matches) ? $matches[1] : null;
    }
}
