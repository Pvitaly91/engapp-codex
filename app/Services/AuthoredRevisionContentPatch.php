<?php

namespace App\Services;

use RuntimeException;

/** Fixed M23 package; reuses the established guarded transaction and backup. */
class AuthoredRevisionContentPatch extends LinkingWordsContentPatch
{
    public const NAMES = [
        'Database\\Seeders\\Page_V3\\FormalEnglish\\NominalStyleAndInformationDensityTheorySeeder',
        'Database\\Seeders\\Page_V3\\BasicGrammar\\C1MixedRevisionTheorySeeder',
        'Database\\Seeders\\Page_V3\\BasicGrammar\\C2MixedRevisionTheorySeeder',
    ];

    public const DEFINITIONS = [
        'seeders/Page_V3/FormalEnglish/NominalStyleAndInformationDensityTheorySeeder/definition.json',
        'seeders/Page_V3/BasicGrammar/C1MixedRevisionTheorySeeder/definition.json',
        'seeders/Page_V3/BasicGrammar/C2MixedRevisionTheorySeeder/definition.json',
    ];

    private const ANCESTRY = [
        ['formal-english'],
        ['mixed-revision'],
        ['mixed-revision'],
    ];

    protected const ID = 'm23-authored-revision-v1';

    protected const MANIFEST = 'content-patches/m23-authored-revision-before.json';

    protected const LABEL = 'M23';

    protected const LOCAL_GUARD = M23LocalTargetGuard::class;

    protected function identity(string $name): array
    {
        $index = array_search($name, self::NAMES, true);
        if ($index === false) {
            throw new RuntimeException('M23 requires an exact full Page.seeder identity.');
        }

        return ['seeder' => $name, 'relative' => self::DEFINITIONS[$index]];
    }

    protected function categorySnapshot(array $category, string $name, bool $lock): array
    {
        $chain = [];
        $seen = [];
        while (true) {
            if (isset($seen[$category['id']]) || count($chain) >= 8 || $category['language'] !== 'uk' || $category['type'] !== 'theory') {
                throw new RuntimeException('M23 category ancestry is cyclic, too deep or has a foreign identity: '.$name);
            }
            $seen[$category['id']] = true;
            array_unshift($chain, $category);
            if ($category['parent_id'] === null) {
                break;
            }
            $parent = $this->rows('page_categories', fn ($q) => $q->where('id', $category['parent_id']), $lock);
            if (count($parent) !== 1) {
                throw new RuntimeException('M23 category ancestor is missing or ambiguous: '.$name);
            }
            $category = $parent[0];
        }
        if (array_column($chain, 'slug') !== self::ANCESTRY[array_search($name, self::NAMES, true)]) {
            throw new RuntimeException('M23 category ancestry differs from the verified local route: '.$name);
        }

        return ['category_ancestry' => $chain];
    }
}
