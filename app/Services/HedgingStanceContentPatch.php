<?php

namespace App\Services;

use RuntimeException;

/** Fixed M17 package; retains the accepted M11/M12 transaction and restore contract. */
class HedgingStanceContentPatch extends LinkingWordsContentPatch
{
    public const NAMES = [
        'Database\\Seeders\\Page_V3\\AcademicEnglish\\HedgingAndCautiousLanguageBasicsTheorySeeder',
        'Database\\Seeders\\Page_V3\\AcademicEnglish\\HedgingAndCautiousLanguageTheorySeeder',
        'Database\\Seeders\\Page_V3\\AcademicEnglish\\StanceRegisterAndEvaluationTheorySeeder',
    ];

    public const DEFINITIONS = [
        'seeders/Page_V3/AcademicEnglish/HedgingAndCautiousLanguageBasicsTheorySeeder/definition.json',
        'seeders/Page_V3/AcademicEnglish/HedgingAndCautiousLanguageTheorySeeder/definition.json',
        'seeders/Page_V3/AcademicEnglish/StanceRegisterAndEvaluationTheorySeeder/definition.json',
    ];

    protected const ID = 'm17-hedging-stance-v1';

    protected const MANIFEST = 'content-patches/m17-hedging-stance-before.json';

    protected const LABEL = 'M17';

    protected const LOCAL_GUARD = M17LocalTargetGuard::class;

    protected function identity(string $name): array
    {
        $index = array_search($name, self::NAMES, true);
        if ($index === false) {
            throw new RuntimeException('M17 requires an exact full Page.seeder identity.');
        }

        return ['seeder' => $name, 'relative' => self::DEFINITIONS[$index]];
    }

    protected function categorySnapshot(array $category, string $name, bool $lock): array
    {
        if ($category['slug'] !== 'academic-english' || $category['parent_id'] !== null
            || $category['language'] !== 'uk' || $category['type'] !== 'theory') {
            throw new RuntimeException('M17 category ancestry differs from the verified local route: '.$name);
        }

        return ['category_ancestry' => [$category]];
    }
}
