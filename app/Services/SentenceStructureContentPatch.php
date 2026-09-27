<?php

namespace App\Services;

use RuntimeException;

/** Fixed M13 package; retains the accepted M11/M12 transaction and restore contract. */
class SentenceStructureContentPatch extends LinkingWordsContentPatch
{
    public const NAMES = [
        'Database\\Seeders\\Page_V3\\SentenceStructure\\CleftSentencesEmphasisTheorySeeder',
        'Database\\Seeders\\Page_V3\\SentenceStructure\\ComplexNounPhrasesTheorySeeder',
        'Database\\Seeders\\Page_V3\\SentenceStructure\\EllipsisSubstitutionAndReferenceTheorySeeder',
    ];
    public const DEFINITIONS = [
        'seeders/Page_V3/SentenceStructure/CleftSentencesEmphasisTheorySeeder/definition.json',
        'seeders/Page_V3/SentenceStructure/ComplexNounPhrasesTheorySeeder/definition.json',
        'seeders/Page_V3/SentenceStructure/EllipsisSubstitutionAndReferenceTheorySeeder/definition.json',
    ];
    protected const ID = 'm13-sentence-structure-v1';
    protected const MANIFEST = 'content-patches/m13-sentence-structure-before.json';
    protected const LABEL = 'M13';
    protected const LOCAL_GUARD = M13LocalTargetGuard::class;

    protected function identity(string $name): array
    {
        $index = array_search($name, self::NAMES, true);
        if ($index === false) { throw new RuntimeException('M13 requires an exact full Page.seeder identity.'); }
        return ['seeder' => $name, 'relative' => self::DEFINITIONS[$index]];
    }

    protected function categorySnapshot(array $category, string $name, bool $lock): array
    {
        if ($category['slug'] !== 'sentence-structure' || $category['parent_id'] !== null
            || $category['language'] !== 'uk' || $category['type'] !== 'theory') {
            throw new RuntimeException('M13 category ancestry differs from the verified local route: '.$name);
        }
        return ['category_ancestry' => [$category]];
    }
}
