<?php

namespace App\Services;

use RuntimeException;

/** Fixed M20 package; retains the accepted M11/M12 transaction and restore contract. */
class ModalsSubjunctiveContentPatch extends LinkingWordsContentPatch
{
    public const NAMES = [
        'Database\\Seeders\\Page_V3\\ModalVerbs\\ModalPerfectAndDeductionTheorySeeder',
        'Database\\Seeders\\Page_V3\\FormalEnglish\\SubjunctiveAndFormalStructuresTheorySeeder',
        'Database\\Seeders\\Page_V3\\ModalVerbs\\SubtleModalMeaningsTheorySeeder',
    ];

    public const DEFINITIONS = [
        'seeders/Page_V3/ModalVerbs/ModalPerfectAndDeductionTheorySeeder/definition.json',
        'seeders/Page_V3/FormalEnglish/SubjunctiveAndFormalStructuresTheorySeeder/definition.json',
        'seeders/Page_V3/ModalVerbs/SubtleModalMeaningsTheorySeeder/definition.json',
    ];

    public const CATEGORIES = ['modal-verbs', 'formal-english', 'modal-verbs'];

    protected const ID = 'm20-modals-subjunctive-v1';

    protected const MANIFEST = 'content-patches/m20-modals-subjunctive-before.json';

    protected const LABEL = 'M20';

    protected const LOCAL_GUARD = M20LocalTargetGuard::class;

    protected function identity(string $name): array
    {
        $index = array_search($name, self::NAMES, true);
        if ($index === false) {
            throw new RuntimeException('M20 requires an exact full Page.seeder identity.');
        }

        return ['seeder' => $name, 'relative' => self::DEFINITIONS[$index]];
    }

    protected function categorySnapshot(array $category, string $name, bool $lock): array
    {
        $index = array_search($name, self::NAMES, true);
        if ($index === false || $category['slug'] !== self::CATEGORIES[$index] || $category['parent_id'] !== null
            || $category['language'] !== 'uk' || $category['type'] !== 'theory') {
            throw new RuntimeException('M20 category ancestry differs from the verified local route: '.$name);
        }

        return ['category_ancestry' => [$category]];
    }
}
