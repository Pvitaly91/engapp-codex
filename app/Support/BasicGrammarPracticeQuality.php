<?php

namespace App\Support;

use InvalidArgumentException;
use RuntimeException;
use App\Models\Page;
use Illuminate\Support\Arr;

/** Remaining six UK Basic Grammar lessons; completed child catalogs stay unchanged. */
final class BasicGrammarPracticeQuality extends FinitePracticeQualityCatalog
{
    public const REVISION = 'basic-grammar-v1';
    public const LEVELS = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'];
    public const TOPICS = [
        'parts-of-speech' => ['PartsOfSpeech', 'PartsOfSpeechCastiniMovi'],
        'sentence-structure' => ['SentenceStructureSvo', 'SentenceStructureBudovaRecenniaSvo'],
        'sentence-types' => ['SentenceTypes', 'SentenceTypesVidiRecen'],
        'imperatives' => ['Imperatives', 'ImperativesNakazoviRecennia'],
        'conjunctions' => ['Conjunctions', 'ConjunctionsAndButOrBecauseSo'],
        'have-got' => ['HaveGotHasGot', null],
    ];
    protected const UUID_PREFIX = 'bgq-';
    protected const SOURCE_PREFIX = 'basic_grammar_quality_';
    protected const NAMESPACE_PREFIX = 'basic-grammar-quality-';
    protected const LABEL = 'Basic Grammar Quality';
    protected const META_PREFIX = 'basic_grammar_quality';
    public const CATEGORY_ID = 2;
    public const CATEGORY_SLUG = 'basic-grammar';
    public const CATEGORY_TOPICS = ['parts-of-speech', 'sentence-structure', 'sentence-types', 'imperatives', 'conjunctions', 'have-got'];

    private const ORIGINALS = [
        'parts-of-speech' => [
            'standard' => ['pos-castmovi-opus46-saved', '348123d71f10361e9c8700ffb716f101d7e0ebbaa8620b2569f4739afe375d1a'],
            'builder' => ['poly-pos-all-levels', 'f75646602949be9fcd2a2959bf101dbe663ef249a25f2534b59c1ce032f494d7'],
        ],
        'sentence-structure' => [
            'standard' => ['svo-structure-opus46-saved', '1af3e924b2a0f3c42c70fc807928f791cf3e746f9b6ae8a1b749293d1af12e61'],
            'builder' => ['bg-svo-poly-all', 'c1c73429cb5f98ccf573e344272ac0c66c41ad66e0b2cd78e948e08b4deecbe8'],
        ],
        'sentence-types' => [
            'standard' => ['sent-types-opus46-v3-test', '499fb472aec71da18135da046aaa687101c5982999cd0b38e53168f316a4ffb0'],
            'builder' => ['bg-st-poly-all', '4fff3696a19b7a3b2fe9e20464c9ffd5de339eacbe86c51449fe3dc78f5ceb71'],
        ],
        'imperatives' => [
            'standard' => ['imperatives-opus46-v3-saved', '59b93ba87bdc2e065f47b4bde7d9ace5e54c24017fa03b687a144049b2f931da'],
            'builder' => ['bg-imp-poly-all', '9e500724f703ede9f4cee07dfdd6f86d8aa24c214288784d8e59fcd333ab050c'],
        ],
        'conjunctions' => [
            'standard' => ['conj-and-but-or-bc-so-opus46', '64c65f025ce9ce57f500082d957a4a0e8885e67868c8eab545ea2595dff6858f'],
            'builder' => ['poly-conj-all-levels', '5333e4fcc3557e42a4b4fdc16047a16c3497f20bda518be57c573b97f2073c1d'],
        ],
        'have-got' => [
            'standard' => ['bg-hg-v3-all', '797e613b573f0a9c7434b913afcc192201e9c8036cc131e24b221cf9c0d75b63'],
            'builder' => ['bg-hg-poly-all', '9a9f880fb74b4c6f70531f68027aa2aac00874c6313594fee9e1d83672ec949f'],
        ],
    ];
    private const SONATE_PINS = [
        'parts-of-speech' => '5c7c9633e19b02a11930d542bb02e9bfe1c3ee0352701b501f002259ca223767',
        'sentence-structure' => 'ec73f5d7edca3fee407a8013bb199e9ec7c12e77aacd88c911290bc48bf05a5f',
        'sentence-types' => 'b785a4c6f8769cd6d7a0c392373adc7a2802c39dbe6dcf1ea084b0e845869f41',
        'imperatives' => 'f8c8ed58858cd22ea54565bc7ffaa7e39cab1d3a8ada0139b61d7bb139bb25e8',
        'conjunctions' => 'f3f6afc55ca9ed157cc403e6bfeebe293cdd0c23a1a2fa7435136dc2f9a3f2a1',
    ];
    private const GENERIC_PINS = [
        'sentence-structure' => 'fa60763f1c1b13d93013e5b090e5c21beecb00ae8e88f80fca80db190d2c541c',
        'sentence-types' => '3009503593596c0d16a73b7795fd6e2a95ba07f7d4668de85ea348e8c06d96bc',
        'imperatives' => 'a410d4d07964ddffab4089b5996ef92ca25b12e6108d1614f9a84f5bde239c74',
    ];

    protected static function composeSlots(array $author): array
    {
        if (!array_key_exists('compose_slots', $author)) { return parent::composeSlots($author); }
        $slots = $author['compose_slots'];
        if (($author['kind'] ?? null) !== 'compose' || !is_array($slots) || count($slots) < 2) {
            throw new RuntimeException('Basic Grammar reviewed compose slots differ: '.$author['id']);
        }
        foreach ($slots as $slot) {
            if (!is_string($slot) || trim($slot) !== $slot || $slot === '' || count(preg_split('/\s+/u', $slot)) > 3) {
                throw new RuntimeException('Basic Grammar compose slots require1–3 words: '.$author['id']);
            }
        }
        if (implode(' ', $slots) !== $author['target']) { throw new RuntimeException('Basic Grammar compose slots do not reconstruct target: '.$author['id']); }
        return $slots;
    }

    public static function mapping(string $topic): array
    {
        if (!isset(self::TOPICS[$topic])) { throw new InvalidArgumentException('Unknown Basic Grammar topic.'); }
        [$stem, $standardStem] = self::TOPICS[$topic];
        $map = ['topic' => $topic, 'stem' => $stem, 'author' => 'docs/content/basic-grammar-quality/'.$topic.'.v1.json'];
        foreach (['standard', 'builder'] as $kind) {
            $namespace = $kind === 'standard' ? 'BasicGrammar' : 'Polyglot';
            $name = 'UkrainianMixed'.$stem.'Quality'.($kind === 'standard' ? 'Standard' : 'Builder').'Seeder';
            $directory = 'database/seeders/V3/'.$namespace.'/'.$name;
            $oldName = $kind === 'builder' ? 'Polyglot/Polyglot'.$stem.'AllLevelsLessonSeeder'
                : ($standardStem === null ? 'BasicGrammar/'.$stem.'AllLevelsV3Seeder' : 'AI/Copilot/Opus46/'.$standardStem.'V3QuestionsOnlySeeder');
            $map[$kind] = ['class' => 'Database\\Seeders\\V3\\'.$namespace.'\\'.$name,
                'definition' => $directory.'/definition.json', 'loader' => $directory.'.php',
                'implementation' => $directory.'/'.$name.'.php',
                'old_definition' => 'database/seeders/V3/'.$oldName.'/definition.json',
                'old_class' => 'Database\\Seeders\\V3\\'.str_replace('/', '\\', $oldName),
                'old_saved_test_uuid' => self::ORIGINALS[$topic][$kind][0], 'old_sha256' => self::ORIGINALS[$topic][$kind][1]];
        }
        $additional = [];
        if (isset(self::SONATE_PINS[$topic])) {
            $additional[] = ['name' => 'AI/Copilot/Sonate/'.$standardStem.'V3QuestionsOnlySeeder', 'sha256' => self::SONATE_PINS[$topic]];
        }
        if (isset(self::GENERIC_PINS[$topic])) {
            $additional[] = ['name' => 'BasicGrammar/'.$stem.'AllLevelsV3Seeder', 'sha256' => self::GENERIC_PINS[$topic]];
        }
        foreach ($additional as $source) {
            $class = 'Database\\Seeders\\V3\\'.str_replace('/', '\\', $source['name']);
            $map['additional_originals'][] = ['path' => 'database/seeders/V3/'.$source['name'].'/definition.json',
                'sha256' => $source['sha256'], 'class' => $class];
            $map['additional_locale_overrides'][$class] = $map['standard']['class'];
        }
        return $map;
    }

    /** Exact current recursive ROOT scope; no completed child source is rewritten or reseeded. */
    public static function compositeMembers(): array
    {
        static $members;
        if ($members !== null) { return $members; }
        $definitions = [
            [self::class, ['parts-of-speech' => 22, 'sentence-structure' => 23, 'sentence-types' => 2,
                'imperatives' => 21, 'conjunctions' => 20, 'have-got' => 201]],
            [VerbToBePracticeQuality::class, ['present' => 28, 'negatives' => 26, 'questions' => 29, 'past' => 27, 'future' => 25, 'there' => 24]],
            [WordOrderPracticeQuality::class, ['basic' => 256, 'questions-negatives' => 258, 'adverbs-adverbials' => 257,
                'verbs-objects' => 259, 'advanced-emphasis' => 255, 'inversion-basics' => 288,
                'negative-adverbial-inversion' => 295, 'fronting-emphasis' => 313]],
        ];
        $members = [];
        foreach ($definitions as [$catalog, $topics]) {
            foreach ($topics as $topic => $id) {
                $map = $catalog::mapping($topic);
                $prompt = $catalog::originalDefinition($topic, 'standard')['saved_test']['filters']['prompt_generator']['theory_page'];
                $members[$id] = ['page_id' => $id, 'slug' => $prompt['slug'], 'owner' => $prompt['page_seeder_class'],
                    'catalog' => $catalog, 'topic' => $topic, 'profile' => $catalog::REVISION,
                    'seeder_classes' => [$map['standard']['class'], $map['builder']['class']]];
            }
        }
        return $members;
    }

    private static function classes(array $classes): array
    {
        if (array_filter($classes, static fn ($class) => !is_string($class)) !== []) { return []; }
        $classes = array_values(array_unique(array_map(static fn ($class) => strtolower(trim($class)), $classes)));
        sort($classes); return $classes;
    }

    /** Used only by the ROOT category; child category contracts and filter hashes remain unchanged. */
    public static function compositeCategoryProfile(array $groups): ?string
    {
        if (count($groups) !== 20) { return null; }
        $members = self::compositeMembers(); $seen = [];
        foreach ($groups as $group) {
            $page = $group['page'] ?? null;
            if (!$page instanceof Page || !isset($members[(int) $page->getKey()])) { return null; }
            $entry = $members[(int) $page->getKey()];
            if (isset($seen[$entry['page_id']]) || (string) $page->slug !== $entry['slug'] || (string) $page->seeder !== $entry['owner']
                || ($group['base_filters']['theory_page_mixed_type_profile'] ?? null) !== $entry['profile']
                || !is_array($group['seeder_classes'] ?? null)
                || self::classes($group['seeder_classes']) !== self::classes($entry['seeder_classes'])) { return null; }
            $seen[$entry['page_id']] = true;
        }
        return count($seen) === 20 ? self::REVISION : null;
    }

    private static function isParentCategoryContext(array $filters): bool
    {
        return (int) Arr::get($filters, '__meta.theory_category_id') === self::CATEGORY_ID
            && Arr::get($filters, '__meta.theory_category_slug') === self::CATEGORY_SLUG;
    }

    private static function compositeContextClasses(array $filters, string $locale): array
    {
        if (!in_array(strtolower(trim($locale)), ['uk', 'ua'], true) || !self::isParentCategoryContext($filters)
            || ($filters['theory_page_mixed_type_profile'] ?? null) !== self::REVISION
            || ($filters['theory_category_page_test'] ?? null) !== true
            || Arr::get($filters, '__meta.mode') !== 'filters'
            || Arr::get($filters, '__meta.aggregated_theory_category_test') !== true
            || Arr::get($filters, '__meta.theory_page_mixed_polyglot_test') !== true
            || filled($filters['course_slug'] ?? null) || !is_array($filters['seeder_classes'] ?? null)
            || !is_array($filters['theory_category_page_groups'] ?? null) || count($filters['theory_category_page_groups']) !== 20) { return []; }
        $members = self::compositeMembers(); $classes = []; $seen = [];
        foreach ($filters['theory_category_page_groups'] as $group) {
            $id = (int) ($group['page_id'] ?? 0);
            if (!isset($members[$id]) || isset($seen[$id])) { return []; }
            $entry = $members[$id];
            if (($group['page_slug'] ?? null) !== $entry['slug'] || ($group['quality_profile'] ?? null) !== $entry['profile']
                || ($group['page_seeder_class'] ?? null) !== $entry['owner'] || !is_array($group['seeder_classes'] ?? null)
                || self::classes($group['seeder_classes']) !== self::classes($entry['seeder_classes'])) { return []; }
            $seen[$id] = true; $classes = array_merge($classes, $entry['seeder_classes']);
        }
        return self::classes($classes) === self::classes($filters['seeder_classes']) ? self::classes($classes) : [];
    }

    public static function stateNamespace(array $filters, string $locale, string $testUuid = ''): ?string
    {
        if (!self::isParentCategoryContext($filters)) { return parent::stateNamespace($filters, $locale, $testUuid); }
        $classes = self::compositeContextClasses($filters, $locale);
        return $classes === [] ? null : 'bgq-parent-'.substr(hash('sha256', self::REVISION."\0uk\0".implode("\0", $classes)), 0, 20);
    }

    public static function testContextNeedsRefresh(array $cached, array $filters, string $locale, string $testUuid = ''): bool
    {
        if (!self::isParentCategoryContext($filters)) { return parent::testContextNeedsRefresh($cached, $filters, $locale, $testUuid); }
        $classes = self::compositeContextClasses($filters, $locale);
        return $classes !== [] && self::cachedBankMembershipNeedsRefresh($cached, $classes);
    }
}
