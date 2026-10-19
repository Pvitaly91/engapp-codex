<?php

namespace App\Services;

use RuntimeException;

/** Fixed M18 package; retains the accepted M11/M12 transaction and restore contract. */
class ArgumentationCohesionContentPatch extends LinkingWordsContentPatch
{
    public const NAMES = [
        'Database\\Seeders\\Page_V3\\AcademicEnglish\\ArgumentationAndAcademicToneTheorySeeder',
        'Database\\Seeders\\Page_V3\\ClausesAndLinkingWords\\DiscourseMarkersAndCohesionTheorySeeder',
        'Database\\Seeders\\Page_V3\\FormalEnglish\\ParaphraseAndReformulationTheorySeeder',
    ];

    public const DEFINITIONS = [
        'seeders/Page_V3/AcademicEnglish/ArgumentationAndAcademicToneTheorySeeder/definition.json',
        'seeders/Page_V3/ClausesAndLinkingWords/DiscourseMarkersAndCohesionTheorySeeder/definition.json',
        'seeders/Page_V3/FormalEnglish/ParaphraseAndReformulationTheorySeeder/definition.json',
    ];

    public const CATEGORIES = ['academic-english', 'clauses-and-linking-words', 'formal-english'];

    protected const ID = 'm18-argumentation-cohesion-v1';

    protected const MANIFEST = 'content-patches/m18-argumentation-cohesion-before.json';

    protected const LABEL = 'M18';

    protected const LOCAL_GUARD = M18LocalTargetGuard::class;

    protected function identity(string $name): array
    {
        $index = array_search($name, self::NAMES, true);
        if ($index === false) {
            throw new RuntimeException('M18 requires an exact full Page.seeder identity.');
        }

        return ['seeder' => $name, 'relative' => self::DEFINITIONS[$index]];
    }

    protected function categorySnapshot(array $category, string $name, bool $lock): array
    {
        $index = array_search($name, self::NAMES, true);
        if ($index === false || $category['slug'] !== self::CATEGORIES[$index] || $category['parent_id'] !== null
            || $category['language'] !== 'uk' || $category['type'] !== 'theory') {
            throw new RuntimeException('M18 category ancestry differs from the verified local route: '.$name);
        }

        return ['category_ancestry' => [$category]];
    }
}
