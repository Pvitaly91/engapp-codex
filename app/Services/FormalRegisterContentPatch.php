<?php

namespace App\Services;

use RuntimeException;

/** Fixed M16 package; retains the accepted M11/M12 transaction and restore contract. */
class FormalRegisterContentPatch extends LinkingWordsContentPatch
{
    public const NAMES = [
        'Database\\Seeders\\Page_V3\\FormalEnglish\\FormalRegisterAndNominalisationBasicsTheorySeeder',
        'Database\\Seeders\\Page_V3\\FormalEnglish\\NominalisationFormalRegisterTheorySeeder',
        'Database\\Seeders\\Page_V3\\FormalEnglish\\RegisterToneAndParaphraseTheorySeeder',
    ];

    public const DEFINITIONS = [
        'seeders/Page_V3/FormalEnglish/FormalRegisterAndNominalisationBasicsTheorySeeder/definition.json',
        'seeders/Page_V3/FormalEnglish/NominalisationFormalRegisterTheorySeeder/definition.json',
        'seeders/Page_V3/FormalEnglish/RegisterToneAndParaphraseTheorySeeder/definition.json',
    ];

    protected const ID = 'm16-formal-register-v1';

    protected const MANIFEST = 'content-patches/m16-formal-register-before.json';

    protected const LABEL = 'M16';

    protected const LOCAL_GUARD = M16LocalTargetGuard::class;

    protected function identity(string $name): array
    {
        $index = array_search($name, self::NAMES, true);
        if ($index === false) {
            throw new RuntimeException('M16 requires an exact full Page.seeder identity.');
        }

        return ['seeder' => $name, 'relative' => self::DEFINITIONS[$index]];
    }

    protected function categorySnapshot(array $category, string $name, bool $lock): array
    {
        if ($category['slug'] !== 'formal-english' || $category['parent_id'] !== null
            || $category['language'] !== 'uk' || $category['type'] !== 'theory') {
            throw new RuntimeException('M16 category ancestry differs from the verified local route: '.$name);
        }

        return ['category_ancestry' => [$category]];
    }
}
