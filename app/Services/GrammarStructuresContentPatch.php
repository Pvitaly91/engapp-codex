<?php

namespace App\Services;

use RuntimeException;

/** Fixed M21 package; shares transaction/backup machinery without widening M11. */
class GrammarStructuresContentPatch extends LinkingWordsContentPatch
{
    public const NAMES = [
        'Database\\Seeders\\Page_V3\\VerbPatterns\\AdvancedGerundInfinitivePatternsTheorySeeder',
        'Database\\Seeders\\Page_V3\\RelativeClauses\\ComplexRelativeClausesTheorySeeder',
        'Database\\Seeders\\Page_V3\\BasicGrammar\\WordOrder\\InversionAfterNegativeAdverbialsTheorySeeder',
    ];

    public const DEFINITIONS = [
        'seeders/Page_V3/VerbPatterns/AdvancedGerundInfinitivePatternsTheorySeeder/definition.json',
        'seeders/Page_V3/RelativeClauses/ComplexRelativeClausesTheorySeeder/definition.json',
        'seeders/Page_V3/BasicGrammar/WordOrder/InversionAfterNegativeAdverbialsTheorySeeder/definition.json',
    ];

    private const ANCESTRY = [['verb-patterns'], ['relative-clauses'], ['basic-grammar', 'word-order']];

    protected const ID = 'm21-grammar-structures-v1';

    protected const MANIFEST = 'content-patches/m21-grammar-structures-before.json';

    protected const LABEL = 'M21';

    protected const LOCAL_GUARD = M21LocalTargetGuard::class;

    protected function identity(string $name): array
    {
        $index = array_search($name, self::NAMES, true);
        if ($index === false) {
            throw new RuntimeException('M21 requires an exact full Page.seeder identity.');
        }

        return ['seeder' => $name, 'relative' => self::DEFINITIONS[$index]];
    }

    protected function categorySnapshot(array $category, string $name, bool $lock): array
    {
        $chain = [];
        $seen = [];
        while (true) {
            if (isset($seen[$category['id']]) || count($chain) >= 8 || $category['language'] !== 'uk' || $category['type'] !== 'theory') {
                throw new RuntimeException('M21 category ancestry is cyclic, too deep or has a foreign identity: '.$name);
            }
            $seen[$category['id']] = true;
            array_unshift($chain, $category);
            if ($category['parent_id'] === null) {
                break;
            }
            $parent = $this->rows('page_categories', fn ($q) => $q->where('id', $category['parent_id']), $lock);
            if (count($parent) !== 1) {
                throw new RuntimeException('M21 category ancestor is missing/ambiguous: '.$name);
            }
            $category = $parent[0];
        }
        if (array_column($chain, 'slug') !== self::ANCESTRY[array_search($name, self::NAMES, true)]) {
            throw new RuntimeException('M21 category ancestry differs from the verified local route: '.$name);
        }

        return ['category_ancestry' => $chain];
    }
}
