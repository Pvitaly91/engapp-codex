<?php

namespace App\Support;

use InvalidArgumentException;
use RuntimeException;
use Illuminate\Support\Arr;
use Throwable;

/** Finite UK pronoun practice. Historical banks, courses and progress stay untouched. */
final class PronounsDemonstrativesPracticeQuality extends FinitePracticeQualityCatalog
{
    public const REVISION = 'pronouns-demonstratives-v1';
    public const LEVELS = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'];
    public const TOPICS = [
        'pronouns-overview' => 'PronounsTheory',
        'personal-object-pronouns' => 'PersonalObjectPronouns',
        'possessive-forms' => 'PossessiveAdjectivesVsPronouns',
        'reflexive-pronouns' => 'ReflexivePronouns',
        'demonstratives' => 'DemonstrativesThisThatTheseThose',
        'indefinite-pronouns' => 'IndefinitePronouns',
        'relative-pronouns' => 'RelativePronouns',
        'each-every-all' => 'EachEveryAll',
        'reciprocal-pronouns' => 'ReciprocalPronouns',
        'one-ones' => 'OneOnes',
    ];
    protected const UUID_PREFIX = 'pdqq-';
    protected const SOURCE_PREFIX = 'pronouns_demonstratives_quality_';
    protected const NAMESPACE_PREFIX = 'pronouns-demonstratives-quality-';
    protected const LABEL = 'Pronouns and Demonstratives Quality';
    protected const META_PREFIX = 'pronouns_demonstratives_quality';
    public const CATEGORY_ID = 20;
    public const CATEGORY_SLUG = 'zaimennyky-ta-vkazivni-slova';
    public const SAVED_TEST_COUNT = 21;
    public const CATEGORY_TOPICS = [
        'pronouns-overview', 'personal-object-pronouns', 'possessive-forms', 'reflexive-pronouns', 'demonstratives',
        'indefinite-pronouns', 'relative-pronouns', 'each-every-all', 'reciprocal-pronouns', 'one-ones',
    ];

    private const ORIGINALS = [
        'pronouns-overview' => [
            'standard' => ['pd-prt-v3-all', '932b14fe1af9b89e15d4fce3dc35d63cfb94eac9ea13406b060393de3f4a969f'],
            'builder' => ['pd-prt-poly-all', 'a5292fde8fa57c6c56ac9b1288e4d6a0e89d0266dfbc610e530f76069059b028'],
        ],
        'personal-object-pronouns' => [
            'standard' => ['pd-pop-v3-all', 'c8438693bffee860cff968bcd9b593ea9f9c12582a1583c8af895a440df36e23'],
            'builder' => ['pd-pop-poly-all', 'ec53f982d67942fa14ffd0e5322318c919c0f0e3cd8e59f239d173204c22e8a9'],
        ],
        'possessive-forms' => [
            'standard' => ['pd-pavp-v3-all', 'acf91c9aa483cb3d6600eb6138b0a392a432a9862f666c129fc71f2e46927151'],
            'builder' => ['pd-pavp-poly-all', '8c4b39c3d24f7e9a1b36eda4d78ad7bc6a93f1da84ac770590403847af81bcfd'],
        ],
        'reflexive-pronouns' => [
            'standard' => ['pd-rfx-v3-all', 'c63ce3f8f83638b8c8836ba2d67c57d754b3a5940df8a97b15ffb3f94cd7478b'],
            'builder' => ['pd-rfx-poly-all', '1c5582636948b10094a11897f145a8ca62ea48976386ba5ebd392ace82c43c13'],
        ],
        'demonstratives' => [
            'standard' => ['pd-dtt-v3-all', '2236f5b41b313b05ae4422e67b73614e47e20fd583869b715068b00377a54e92'],
            'builder' => ['pd-dtt-poly-all', 'a407893591d8418e33a3120fa3e7786cc3b1d85741f4a28fbbd0009e588e9b65'],
        ],
        'indefinite-pronouns' => [
            'standard' => ['pd-ipn-v3-all', '67860aaf4fb0d25a85cd0424c6d49de25bde6383d04fc56a7953401757482ec2'],
            'builder' => ['pd-ipn-poly-all', 'a7e9963795f0ccc8cb6fa1527462f62940c26d0c6b82b6ec7b745ffd0f74f82e'],
        ],
        'relative-pronouns' => [
            'standard' => ['pd-rlp-v3-all', 'a588add66764935c86439eadd765c83b079396a07893889c2308de5648cb7f40'],
            'builder' => ['pd-rlp-poly-all', '1d36c8dacee6826d4bc692d87e06f4efdce9a4098b494057d276196a8ed79392'],
        ],
        'each-every-all' => [
            'standard' => ['pd-eea-v3-all', '264188d682dcc28adb63b901ad96509e88a01c1c6be700cc9a81dcd4236254ae'],
            'builder' => ['pd-eea-poly-all', '0a530c15b96aab22200e08858c565381fd2b03777c3ddf616e15b593f9dfdec4'],
        ],
        'reciprocal-pronouns' => [
            'standard' => ['pd-rcp-v3-all', 'e4f322ee51da93633a3e70ecc4136029a9be4970bc238c034eaf93a0c606b4d1'],
            'builder' => ['pd-rcp-poly-all', '20dc136181e8e64321d5b2f08b87552d46d3f732fdda7244e726d194f9bb8689'],
        ],
        'one-ones' => [
            'standard' => ['pd-oo-v3-all', 'd0b890b23f027741b2bbeab9b27f7ea03a9e42f1efd8faf25ce89b22d4161ee9'],
            'builder' => ['pd-oo-poly-all', 'fe13e84e75af81eafcd8d5e0185f4eab9f51f0e962cdd21c67859cbbc4d89fac'],
        ],
    ];

    private const EXTRA_OVERVIEW = [
        ['PronounsDemonstrativesAllLevelsV3Seeder', 'd2c44513785556931c3b3b296d8abd90f97f8a40767bed3fc360e01314a78e4c', 'pronouns-demonstratives-v3-test'],
        ['V2/IndefinitePronounsPracticeV3Seeder', '4c4d343d978f2812f1287183babad5f56388fe4d1ff6727fd2a4b4a422a19d2e', null],
    ];

    public static function mapping(string $topic): array
    {
        if (!isset(self::TOPICS[$topic])) { throw new InvalidArgumentException('Unknown pronoun practice topic.'); }
        $stem = self::TOPICS[$topic];
        $map = ['topic' => $topic, 'stem' => $stem, 'author' => 'docs/content/pronouns-demonstratives-quality/'.$topic.'.v1.json'];
        foreach (['standard', 'builder'] as $kind) {
            $namespace = $kind === 'standard' ? 'PronounsDemonstratives' : 'Polyglot';
            $name = 'UkrainianMixed'.$stem.'Quality'.($kind === 'standard' ? 'Standard' : 'Builder').'Seeder';
            $directory = 'database/seeders/V3/'.$namespace.'/'.$name;
            $oldName = $kind === 'standard' ? 'PronounsDemonstratives/'.$stem.'AllLevelsV3Seeder'
                : 'Polyglot/Polyglot'.$stem.'AllLevelsLessonSeeder';
            $map[$kind] = ['class' => 'Database\\Seeders\\V3\\'.$namespace.'\\'.$name,
                'definition' => $directory.'/definition.json', 'loader' => $directory.'.php',
                'implementation' => $directory.'/'.$name.'.php',
                'old_definition' => 'database/seeders/V3/'.$oldName.'/definition.json',
                'old_class' => 'Database\\Seeders\\V3\\'.str_replace('/', '\\', $oldName),
                'old_saved_test_uuid' => self::ORIGINALS[$topic][$kind][0],
                'old_sha256' => self::ORIGINALS[$topic][$kind][1]];
        }
        if ($topic === 'pronouns-overview') {
            foreach (self::EXTRA_OVERVIEW as [$name, $hash, $uuid]) {
                $class = 'Database\\Seeders\\V3\\'.str_replace('/', '\\', $name);
                $map['additional_originals'][] = ['path' => 'database/seeders/V3/'.$name.'/definition.json',
                    'sha256' => $hash, 'class' => $class, 'kind' => 'standard', 'saved_test_uuid' => $uuid];
                $map['additional_locale_overrides'][$class] = $map['standard']['class'];
            }
        }
        return $map;
    }

    /** Slots are explicit reviewed groups, not a runtime guess at grammatical phrases. */
    protected static function composeSlots(array $author): array
    {
        if (!array_key_exists('compose_slots', $author)) { return parent::composeSlots($author); }
        $slots = $author['compose_slots'];
        if (($author['kind'] ?? '') !== 'compose' || !is_array($slots) || count($slots) < 2) {
            throw new RuntimeException('Reviewed pronoun compose slots missing: '.$author['id']);
        }
        foreach ($slots as $slot) {
            if (!is_string($slot) || trim($slot) !== $slot || $slot === '' || count(preg_split('/\s+/u', $slot)) > 3) {
                throw new RuntimeException('Pronoun compose groups require1–3 words: '.$author['id']);
            }
        }
        if (implode(' ', $slots) !== $author['target']) { throw new RuntimeException('Pronoun slots do not reconstruct target.'); }
        return $slots;
    }

    /** Canonical grammatical alternatives are compiled before any DB apply, not patched afterwards. */
    public static function compile(string $topic, string $kind): array
    {
        static $compiled = [];
        $source = self::source($topic); self::originalDefinition($topic, $kind);
        $cacheKey = $topic.'|'.$kind.'|'.hash('sha256', json_encode($source, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
        if (isset($compiled[$cacheKey])) { return $compiled[$cacheKey]; }
        $definition = parent::compileAuthored($topic, $kind);
        if ($kind !== 'builder') { return $compiled[$cacheKey] = $definition; }
        return $compiled[$cacheKey] = FinitePracticeChoiceOptions::compile($definition, $source, $topic, self::composeSlots(...));
    }

    public static function decorate(array $payload): array
    {
        $payload = parent::decorate($payload);
        $source = self::questions()[$payload['uuid'] ?? ''] ?? null;
        if ($source && (string) $source['type'] === '0' && ($payload['question'] ?? null) === $source['question']
            && ($payload['psp_content_revision'] ?? null) === hash('sha256', json_encode($source, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
            && ($payload['answer_map'] ?? null) === array_map(static fn ($marker) => $marker['answer'], $source['markers'])) {
            $payload['passive_phrase_word_inputs'] = true;
        }
        return $payload;
    }

    public static function cachedNeedsRefresh(array $payloads): bool
    {
        if (parent::cachedNeedsRefresh($payloads)) { return true; }
        $sources = self::questions();
        foreach ($payloads as $payload) {
            $source = $sources[$payload['uuid'] ?? ''] ?? null;
            if ($source && (string) $source['type'] === '0' && ($payload['passive_phrase_word_inputs'] ?? null) !== true) { return true; }
        }
        return false;
    }

    /** The historical overview alias is page-owned, not an independently sequenced course. */
    private static function additionalMetadataPatches(): array
    {
        $topic = 'pronouns-overview'; $map = self::mapping($topic); self::originalDefinition($topic, 'standard');
        foreach (['standard', 'builder'] as $kind) {
            $file = dirname(__DIR__, 2).'/'.$map[$kind]['definition'];
            if (!is_file($file) || is_link($file) || json_decode(file_get_contents($file), true, flags: JSON_THROW_ON_ERROR) !== self::compile($topic, $kind)) {
                throw new RuntimeException('Overview alias requires current canonical banks.');
            }
        }
        $entry = $map['additional_originals'][0];
        $original = json_decode(file_get_contents(dirname(__DIR__, 2).'/'.$entry['path']), true, flags: JSON_THROW_ON_ERROR);
        $before = $original['saved_test'] ?? [];
        if (($before['uuid'] ?? '') !== 'pronouns-demonstratives-v3-test' || ($original['defaults']['default_locale'] ?? '') !== 'uk'
            || ($original['seeder']['class'] ?? '') !== $entry['class']) { throw new RuntimeException('Exact overview alias source differs.'); }
        foreach (['course_slug', 'lesson_order', 'previous_lesson_slug', 'next_lesson_slug', 'completion'] as $key) {
            if (array_key_exists($key, $before['filters'] ?? [])) { throw new RuntimeException('Course cannot enter overview alias migration.'); }
        }
        $after = parent::updatedSavedTest($topic, 'standard', $original);
        return [$before['uuid'] => ['topic' => $topic, 'kind' => 'standard', 'identity' => $entry['class'],
            'source_path' => $entry['path'], 'source_sha256' => $entry['sha256'],
            'new_seeder_class' => $map['standard']['class'], 'before' => $before, 'after' => $after]];
    }

    public static function metadataPatches(): array { return parent::metadataPatches() + self::additionalMetadataPatches(); }

    public static function definitionMetadata(array $definition, string $locale = 'uk'): array
    {
        $projected = parent::definitionMetadata($definition, $locale);
        if ($projected !== $definition || $locale !== 'uk' || ($definition['defaults']['default_locale'] ?? '') !== 'uk'
            || ($definition['saved_test']['uuid'] ?? '') !== 'pronouns-demonstratives-v3-test') { return $projected; }
        try {
            $patch = self::additionalMetadataPatches()['pronouns-demonstratives-v3-test'];
            $original = json_decode(file_get_contents(dirname(__DIR__, 2).'/'.$patch['source_path']), true, flags: JSON_THROW_ON_ERROR);
            $strip = static function (array $data): array {
                foreach ($data['questions'] ?? [] as $i => $row) { unset($data['questions'][$i]['localizations']); } return $data;
            };
            if ($strip($definition) === $strip($original)) { $definition['saved_test'] = $patch['after']; }
        } catch (Throwable) { /* Changed or foreign data retains the entire historical definition. */ }
        return $definition;
    }

    public static function savedTestFilters(array $payload, string $seederClass, array $filters, string $locale = 'uk'): array
    {
        $projected = parent::savedTestFilters($payload, $seederClass, $filters, $locale);
        if ($projected !== $filters || $locale !== 'uk' || ($payload['uuid'] ?? '') !== 'pronouns-demonstratives-v3-test') { return $projected; }
        try {
            $patch = self::additionalMetadataPatches()['pronouns-demonstratives-v3-test'];
            if ($seederClass === $patch['identity'] && $payload === $patch['after']
                && ($filters['seeder_classes'] ?? []) === [$patch['new_seeder_class'], $patch['identity']]) {
                $filters['seeder_classes'] = [$patch['new_seeder_class']];
            }
        } catch (Throwable) { /* Exact historical alias binding is mandatory. */ }
        return $filters;
    }

    public static function filtersForLocale(string $uuid, array $filters, string $locale): array
    {
        if ($uuid !== 'pronouns-demonstratives-v3-test' || in_array(strtolower(trim($locale)), ['uk', 'ua'], true)) {
            return parent::filtersForLocale($uuid, $filters, $locale);
        }
        try {
            $patch = self::additionalMetadataPatches()[$uuid]; $before = $patch['before']['filters']; $after = $patch['after']['filters'];
            foreach (['course_slug', 'lesson_type', 'lesson_order', 'previous_lesson_slug', 'next_lesson_slug', 'completion'] as $key) {
                if (Arr::get($filters, $key) !== Arr::get($before, $key)) { return $filters; }
            }
            $paths = ['seeder_classes', 'num_questions', 'include_ai_v2', 'theory_page_mixed_type_profile',
                'theory_page_mixed_interleave_question_types', '__meta.mode',
                '__meta.pronouns_demonstratives_quality_revision', '__meta.pronouns_demonstratives_quality_topic'];
            $map = self::mapping('pronouns-overview');
            foreach (array_keys(array_replace([$map['standard']['old_class'] => '', $map['builder']['old_class'] => ''], $map['additional_locale_overrides'])) as $class) {
                $paths[] = '__meta.theory_page_mixed_locale_seeder_overrides.uk.'.$class;
            }
            foreach ($paths as $path) { if (!Arr::has($filters, $path) || Arr::get($filters, $path) !== Arr::get($after, $path)) { return $filters; } }
            foreach ($paths as $path) {
                if (Arr::has($before, $path)) { Arr::set($filters, $path, Arr::get($before, $path)); } else { Arr::forget($filters, $path); }
            }
            foreach (['__meta.theory_page_mixed_locale_seeder_overrides.uk', '__meta.theory_page_mixed_locale_seeder_overrides', '__meta'] as $path) {
                if (Arr::get($filters, $path) === [] && !Arr::has($before, $path)) { Arr::forget($filters, $path); }
            }
        } catch (Throwable) { /* Never rewrite partial or foreign selectors. */ }
        return $filters;
    }

    private static function contextUuid(array $filters, string $locale, string $uuid): string
    {
        if ($uuid !== 'pronouns-demonstratives-v3-test' || !in_array(strtolower(trim($locale)), ['uk', 'ua'], true)) { return $uuid; }
        try {
            if (self::filtersForLocale($uuid, $filters, 'en') !== $filters) { return self::mapping('pronouns-overview')['standard']['old_saved_test_uuid']; }
        } catch (Throwable) { /* No opt-in without exact alias filters. */ }
        return $uuid;
    }

    public static function stateNamespace(array $filters, string $locale, string $uuid = ''): ?string
    {
        return parent::stateNamespace($filters, $locale, self::contextUuid($filters, $locale, $uuid));
    }

    public static function testContextNeedsRefresh(array $cached, array $filters, string $locale, string $uuid = ''): bool
    {
        return parent::testContextNeedsRefresh($cached, $filters, $locale, self::contextUuid($filters, $locale, $uuid));
    }
}
