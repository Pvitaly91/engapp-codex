<?php

namespace App\Services;

use RuntimeException;

/** Fixed M15 package; retains the accepted M11/M12 transaction and restore contract. */
class ConditionalsContentPatch extends LinkingWordsContentPatch
{
    public const NAMES = [
        'Database\\Seeders\\Page_V3\\Conditionals\\ConditionalsWithUnlessProvidedAsLongAsTheorySeeder',
        'Database\\Seeders\\Page_V3\\Conditionals\\AdvancedConditionalsTheorySeeder',
        'Database\\Seeders\\Page_V3\\Conditionals\\ConditionalAlternativesAndNuanceTheorySeeder',
    ];

    public const DEFINITIONS = [
        'seeders/Page_V3/Conditionals/ConditionalsWithUnlessProvidedAsLongAsTheorySeeder/definition.json',
        'seeders/Page_V3/Conditionals/AdvancedConditionalsTheorySeeder/definition.json',
        'seeders/Page_V3/Conditionals/ConditionalAlternativesAndNuanceTheorySeeder/definition.json',
    ];

    protected const ID = 'm15-conditionals-v1';

    protected const MANIFEST = 'content-patches/m15-conditionals-before.json';

    protected const LABEL = 'M15';

    protected const LOCAL_GUARD = M15LocalTargetGuard::class;

    protected function identity(string $name): array
    {
        $index = array_search($name, self::NAMES, true);
        if ($index === false) {
            throw new RuntimeException('M15 requires an exact full Page.seeder identity.');
        }

        return ['seeder' => $name, 'relative' => self::DEFINITIONS[$index]];
    }

    protected function categorySnapshot(array $category, string $name, bool $lock): array
    {
        if ($category['slug'] !== 'conditionals' || $category['parent_id'] !== null
            || $category['language'] !== 'uk' || $category['type'] !== 'theory') {
            throw new RuntimeException('M15 category ancestry differs from the verified local route: '.$name);
        }

        return ['category_ancestry' => [$category]];
    }
}
