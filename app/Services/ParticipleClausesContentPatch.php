<?php

namespace App\Services;

use RuntimeException;

/** Fixed M14 package; retains the accepted M11/M12 transaction and restore contract. */
class ParticipleClausesContentPatch extends LinkingWordsContentPatch
{
    public const NAMES = [
        'Database\\Seeders\\Page_V3\\ClausesAndLinkingWords\\ParticipleClausesBasicsTheorySeeder',
        'Database\\Seeders\\Page_V3\\ClausesAndLinkingWords\\ParticipleClausesTheorySeeder',
        'Database\\Seeders\\Page_V3\\ClausesAndLinkingWords\\AdvancedParticipleAndAbsoluteClausesTheorySeeder',
    ];
    public const DEFINITIONS = [
        'seeders/Page_V3/ClausesAndLinkingWords/ParticipleClausesBasicsTheorySeeder/definition.json',
        'seeders/Page_V3/ClausesAndLinkingWords/ParticipleClausesTheorySeeder/definition.json',
        'seeders/Page_V3/ClausesAndLinkingWords/AdvancedParticipleAndAbsoluteClausesTheorySeeder/definition.json',
    ];
    protected const ID = 'm14-participle-clauses-v1';
    protected const MANIFEST = 'content-patches/m14-participle-clauses-before.json';
    protected const LABEL = 'M14';
    protected const LOCAL_GUARD = M14LocalTargetGuard::class;

    protected function identity(string $name): array
    {
        $index = array_search($name, self::NAMES, true);
        if ($index === false) { throw new RuntimeException('M14 requires an exact full Page.seeder identity.'); }
        return ['seeder' => $name, 'relative' => self::DEFINITIONS[$index]];
    }

    protected function categorySnapshot(array $category, string $name, bool $lock): array
    {
        if ($category['slug'] !== 'clauses-and-linking-words' || $category['parent_id'] !== null
            || $category['language'] !== 'uk' || $category['type'] !== 'theory') {
            throw new RuntimeException('M14 category ancestry differs from the verified local route: '.$name);
        }
        return ['category_ancestry' => [$category]];
    }
}
