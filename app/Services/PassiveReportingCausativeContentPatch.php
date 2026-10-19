<?php

namespace App\Services;

use RuntimeException;

/** Fixed M19 package; retains the accepted M11/M12 transaction and restore contract. */
class PassiveReportingCausativeContentPatch extends LinkingWordsContentPatch
{
    public const NAMES = [
        'Database\\Seeders\\Page_V3\\PassiveVoice\\PassiveReportingStructuresTheorySeeder',
        'Database\\Seeders\\Page_V3\\PassiveVoice\\ComplexPassiveAndCausativeTheorySeeder',
        'Database\\Seeders\\Page_V3\\PassiveVoice\\ComplexPassiveImpersonalStyleTheorySeeder',
    ];

    public const DEFINITIONS = [
        'seeders/Page_V3/PassiveVoice/PassiveReportingStructuresTheorySeeder/definition.json',
        'seeders/Page_V3/PassiveVoice/ComplexPassiveAndCausativeTheorySeeder/definition.json',
        'seeders/Page_V3/PassiveVoice/ComplexPassiveImpersonalStyleTheorySeeder/definition.json',
    ];

    public const CATEGORY = 'passive-voice';

    protected const ID = 'm19-passive-reporting-causative-v1';

    protected const MANIFEST = 'content-patches/m19-passive-reporting-causative-before.json';

    protected const LABEL = 'M19';

    protected const LOCAL_GUARD = M19LocalTargetGuard::class;

    protected function identity(string $name): array
    {
        $index = array_search($name, self::NAMES, true);
        if ($index === false) {
            throw new RuntimeException('M19 requires an exact full Page.seeder identity.');
        }

        return ['seeder' => $name, 'relative' => self::DEFINITIONS[$index]];
    }

    protected function categorySnapshot(array $category, string $name, bool $lock): array
    {
        $index = array_search($name, self::NAMES, true);
        if ($index === false || $category['slug'] !== self::CATEGORY || $category['parent_id'] !== null
            || $category['language'] !== 'uk' || $category['type'] !== 'theory') {
            throw new RuntimeException('M19 category ancestry differs from the verified local route: '.$name);
        }

        return ['category_ancestry' => [$category]];
    }
}
