<?php

namespace App\Services;

use RuntimeException;

/** Fixed M12 package; shares transaction/backup machinery without widening M11. */
class InversionCleftsContentPatch extends LinkingWordsContentPatch
{
    public const NAMES = [
        'Database\\Seeders\\Page_V3\\BasicGrammar\\WordOrder\\InversionBasicsTheorySeeder',
        'Database\\Seeders\\Page_V3\\SentenceStructure\\CleftSentencesBasicsTheorySeeder',
        'Database\\Seeders\\Page_V3\\BasicGrammar\\WordOrder\\AdvancedFrontingAndEmphasisTheorySeeder',
    ];
    public const DEFINITIONS = [
        'seeders/Page_V3/BasicGrammar/WordOrder/InversionBasicsTheorySeeder/definition.json',
        'seeders/Page_V3/SentenceStructure/CleftSentencesBasicsTheorySeeder/definition.json',
        'seeders/Page_V3/BasicGrammar/WordOrder/AdvancedFrontingAndEmphasisTheorySeeder/definition.json',
    ];
    private const ANCESTRY = [['basic-grammar', 'word-order'], ['sentence-structure'], ['basic-grammar', 'word-order']];
    protected const ID = 'm12-inversion-clefts-v1';
    protected const MANIFEST = 'content-patches/m12-inversion-clefts-before.json';
    protected const LABEL = 'M12';
    protected const LOCAL_GUARD = M12LocalTargetGuard::class;

    protected function identity(string $name): array
    {
        $index = array_search($name, self::NAMES, true);
        if ($index === false) { throw new RuntimeException('M12 requires an exact full Page.seeder identity.'); }
        return ['seeder' => $name, 'relative' => self::DEFINITIONS[$index]];
    }

    protected function categorySnapshot(array $category, string $name, bool $lock): array
    {
        $chain = []; $seen = [];
        while (true) {
            if (isset($seen[$category['id']]) || count($chain) >= 8 || $category['language'] !== 'uk' || $category['type'] !== 'theory') {
                throw new RuntimeException('M12 category ancestry is cyclic, too deep or has a foreign identity: '.$name);
            }
            $seen[$category['id']] = true;
            array_unshift($chain, $category);
            if ($category['parent_id'] === null) { break; }
            $parent = $this->rows('page_categories', fn ($q) => $q->where('id', $category['parent_id']), $lock);
            if (count($parent) !== 1) { throw new RuntimeException('M12 category ancestor is missing/ambiguous: '.$name); }
            $category = $parent[0];
        }
        if (array_column($chain, 'slug') !== self::ANCESTRY[array_search($name, self::NAMES, true)]) {
            throw new RuntimeException('M12 category ancestry differs from the verified local route: '.$name);
        }
        return ['category_ancestry' => $chain];
    }
}
