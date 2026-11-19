<?php

namespace App\Support;

use InvalidArgumentException;
use RuntimeException;
use Illuminate\Support\Arr;
use Throwable;

/** Twelve finite UK Nouns, Articles and Quantity banks; historical sources remain unchanged. */
final class NounsArticlesQuantityPracticeQuality extends FinitePracticeQualityCatalog
{
    public const REVISION = 'nouns-articles-quantity-v1';
    public const LEVELS = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'];
    public const TOPICS = [
        'plural-nouns' => 'PluralNounsSEsIes',
        'countable-uncountable' => 'CountableVsUncountableNouns',
        'articles' => 'ArticlesAAnThe',
        'zero-article' => 'ZeroArticle',
        'quantifiers' => 'QuantifiersMuchManyALotFewLittle',
        'partitives' => 'PartitivesWithUncountableNouns',
        'no-none-neither-either' => 'NoNoneNeitherEither',
        'geographical-articles' => 'ArticlesWithGeographicalNames',
        'generic-reference' => 'AdvancedArticlesGenericReference',
        'irregular-plurals' => 'IrregularPlurals',
        'collective-nouns' => 'CollectiveNouns',
        'another-other-the-other' => 'AnotherOtherTheOther',
    ];
    protected const UUID_PREFIX = 'naqq-';
    protected const SOURCE_PREFIX = 'nouns_articles_quantity_quality_';
    protected const NAMESPACE_PREFIX = 'nouns-articles-quantity-quality-';
    protected const LABEL = 'Nouns Articles Quantity Quality';
    protected const META_PREFIX = 'nouns_articles_quantity_quality';
    public const CATEGORY_ID = 16;
    public const CATEGORY_SLUG = 'imennyky-artykli-ta-kilkist';
    public const SAVED_TEST_COUNT = 34;
    public const CATEGORY_TOPICS = [
        'plural-nouns',
        'countable-uncountable',
        'articles',
        'zero-article',
        'quantifiers',
        'partitives',
        'no-none-neither-either',
        'geographical-articles',
        'generic-reference',
        'irregular-plurals',
        'collective-nouns',
        'another-other-the-other',
    ];

    private const ORIGINALS = [
        'plural-nouns' => [
            'standard' => ['naq-pns-v3-all', 'f4ca858b156891a76f422688466d952acb59c34e04337b303b42f7dc29dd7a99'],
            'builder' => ['naq-pns-poly-all', 'abad81597500f44f5a6f8dfd7273606c104fef077992025390f54d1e43a550a5'],
        ],
        'countable-uncountable' => [
            'standard' => ['naq-cvu-v3-all', '0bb277ebba47fb416fe58e47896607ee06fcaf8498ddc8b7b0960988ec358a92'],
            'builder' => ['naq-cvu-poly-all', 'c9df33c4de15615850e6eb7b318cb2f4c05999702b5ad9d0a5d4dcb0dd4f8469'],
        ],
        'articles' => [
            'standard' => ['naq-aat-v3-all', '2d96e3a5fafac1c6657a4f12c768792243ddbe8bbfca2f8fe02156348d84078c'],
            'builder' => ['naq-aat-poly-all', 'e112ceb16933a9759404eca5549292830751c3f162c04002f709ee0793f03b59'],
        ],
        'zero-article' => [
            'standard' => ['naq-za-v3-all', 'de66edad6e5d4c1d22a471172c7249073e22c664156cab3a763408161a8507e4'],
            'builder' => ['naq-za-poly-all', '086e22996f95b37c24593ce9e05e8c32cea65fadb5adb801130c1bb0f2794637'],
        ],
        'quantifiers' => [
            'standard' => ['naq-qmm-v3-all', '175d71539ff73f3c63c890556c7e6d6503a83adf2e77c366ae1699bbd828bb48'],
            'builder' => ['naq-qmm-poly-all', '565765b3d0e47bc449a25ed87cc86a5f9a27cc4cb9b41cc5752a244d740421d0'],
        ],
        'partitives' => [
            'standard' => ['naq-pwu-v3-all', '6dc64ee0b7195116432fbb7377ed31e4fc7c2dd0baf243c0a12ec7b4d7513f5c'],
            'builder' => ['naq-pwu-poly-all', '5debb6f9f3f9e45d9893c5e950f582a6c82b83900bccebc074b92672ca2af969'],
        ],
        'no-none-neither-either' => [
            'standard' => ['naq-nne-v3-all', '0116ebbfcb5538a711f0c258f87be63ee795d48f26d36446ea492561803f5145'],
            'builder' => ['naq-nne-poly-all', 'cfc324fa19f43a71e1a8142631429986da1df2480aa87176c0530f293e0d7c2d'],
        ],
        'geographical-articles' => [
            'standard' => ['naq-geo-v3-all', '348a2580d017d1a2cf1ef36d52a51a7699cea6e713e3d217c373e3a8776ecdc3'],
            'builder' => ['naq-geo-poly-all', 'f16e333390424782d8978487799c0f152a1c0005f64ebcf62128d3607c685161'],
        ],
        'generic-reference' => [
            'standard' => ['naq-agr-v3-all', 'ff12bc4c31c9a2a06697aa0d28b38ef4f7075a66c908e19b5ad205e7aefa0432'],
            'builder' => ['naq-agr-poly-all', '830cb9df70228d79f96ddb2e01342c6f059048cd5b7b30d39191cd29838a36b8'],
        ],
        'irregular-plurals' => [
            'standard' => ['naq-ipl-v3-all', 'e3bd1bfc3e506249924d36138411c80f0fe8d3fd5002c726922bce2377a33a9a'],
            'builder' => ['naq-ipl-poly-all', 'a7473e15c071112b7fe6873834ffb11129f2bee094e2864e3f187b065d43fce2'],
        ],
        'collective-nouns' => [
            'standard' => ['naq-cn-v3-all', 'e103c6822ed4ea7a4f68fd22128e1b64034bd5194984f1628028110cdde25611'],
            'builder' => ['naq-cn-poly-all', '77a327b4abeaceda6256d4d47f8629d3e7276675cdb4469f6acbb134751ec512'],
        ],
        'another-other-the-other' => [
            'standard' => ['naq-aot-v3-all', '6c72ec03dd5b9a1419107d9b01e4c43a8eb16dc91e6f7a35207a166c5fb9d533'],
            'builder' => ['naq-aot-poly-all', 'ab498d92f04da33e362123b35abe522903a3ce4f92a253c106c100e04dc96614'],
        ],
    ];

    /** Additional historical banks are exact-pinned request aliases, never reseeded. */
    private const ADDITIONAL_ORIGINALS = [
        'plural-nouns' => [
            ['AI/ChatGpt/Codex/PluralNounsMnozinaImennikivPravilaViniatkiPrikladiV3QuestionsOnlySeeder', '15607640c7990ea487b4d4e0fba23dd3b9d2fd0f0fbd375a3a540258d517d486', 'standard', 'pn-cg-cx-saved-test'],
            ['AI/ChatGpt/CodexWithTags/PluralNounsMnozinaImennikivPravilaViniatkiPrikladiV3QuestionsOnlySeeder', '82a5490f981d385fdb7593a6862f03fb82de5e2255fbe40e5027cbb07182d8ab', 'standard', 'pn-cg-cwt-saved-test'],
            ['AI/ChatGpt/PluralNounsMnozinaImennikivPravilaViniatkiPrikladiV3QuestionsOnlySeeder', '9ee193cb8f449d96e5dbc3c2a862a83b8ac4b0b2cfe57c7fb72b1a8d1c938709', 'standard', 'plural-nouns-v3-saved-test'],
            ['AI/ChatGptPro/PluralNounsMnozinaImennikivPravilaViniatkiPrikladiV3QuestionsOnlySeeder', '923e5513b5e404dc781f141fbecb6366ac14835d22c5b4eda9f679d5530bb0a6', 'standard', 'plural-nouns-s-es-ies-saved-test'],
            ['AI/Copilot/ClaudeOpus45/PluralNounsMnozinaImennikivPravilaViniatkiPrikladiV3QuestionsOnlySeeder', '176c67fe25c9339337b81a365f0496540268ee20b55b80ab4aea87863fe8de07', 'standard', 'plural-nouns-copilot-v3-test'],
            ['AI/Copilot/NewV3Opus46/PluralNounsMnozinaImennikivPravilaViniatkiPrikladiV3QuestionsOnlySeeder', '766c9f71b97aca651706bbaee9f489ff4c91bd3f815ec3b4e319e169f4411920', 'standard', 'plnouns-nv3o46-saved-test'],
            ['AI/Copilot/Opus46/PluralNounsMnozinaImennikivPravilaViniatkiPrikladiV3QuestionsOnlySeeder', '79d0336c61c1d7cf856c16224671684071dbe784d5a9aed6cb14ca76293195df', 'standard', 'plnouns-opus46-v3-saved-test'],
            ['AI/Copilot/Sonate/PluralNounsMnozinaImennikivPravilaViniatkiPrikladiV3QuestionsOnlySeeder', 'd94b815bffbf9f7ecdeaa77fea12f3c569b71fe1d7aab077a4cfefcefdf58881', 'standard', 'pn-cp-sn-saved-test'],
            ['AI/Gemini/PluralNounsMnozinaImennikivPravilaViniatkiPrikladiV3QuestionsOnlySeeder', 'e2de14c7c2c075dc3699845647b83345c8b865d94668190a5ffcc868f7e5c4ea', 'standard', 'plural-nouns-v3-full-test'],
            ['AI/NewV3/Codex/PluralNounsMnozinaImennikivPravilaViniatkiPrikladiV3QuestionsOnlySeeder', 'cc73a266861063876bd68084c96a9647724431eb91552059cae7c95c4c14686c', 'standard', 'pn-nv3-cx-saved-test'],
        ],
        'quantifiers' => [
            ['Polyglot/PolyglotMuchManyALotOfLessonSeeder', '2905e530cd0596884c63c0032f78b5d214dc933c7ee0094412ee5d6d682ba199', 'builder', 'polyglot-much-many-a-lot-of-a-1e9df0'],
        ],
    ];

    public static function mapping(string $topic): array
    {
        if (!isset(self::TOPICS[$topic])) { throw new InvalidArgumentException('Unknown Nouns Articles Quantity topic.'); }
        $stem = self::TOPICS[$topic];
        $map = ['topic' => $topic, 'stem' => $stem, 'author' => 'docs/content/nouns-articles-quantity-quality/'.$topic.'.v1.json'];
        foreach (['standard', 'builder'] as $kind) {
            $namespace = $kind === 'standard' ? 'NounsArticlesQuantity' : 'Polyglot';
            $name = 'UkrainianMixed'.$stem.'Quality'.($kind === 'standard' ? 'Standard' : 'Builder').'Seeder';
            $directory = 'database/seeders/V3/'.$namespace.'/'.$name;
            $oldName = $kind === 'standard' ? 'NounsArticlesQuantity/'.$stem.'AllLevelsV3Seeder'
                : 'Polyglot/Polyglot'.$stem.'AllLevelsLessonSeeder';
            $map[$kind] = ['class' => 'Database\\Seeders\\V3\\'.$namespace.'\\'.$name,
                'definition' => $directory.'/definition.json', 'loader' => $directory.'.php',
                'implementation' => $directory.'/'.$name.'.php',
                'old_definition' => 'database/seeders/V3/'.$oldName.'/definition.json',
                'old_class' => 'Database\\Seeders\\V3\\'.str_replace('/', '\\', $oldName),
                'old_saved_test_uuid' => self::ORIGINALS[$topic][$kind][0],
                'old_sha256' => self::ORIGINALS[$topic][$kind][1]];
        }
        foreach (self::ADDITIONAL_ORIGINALS[$topic] ?? [] as [$name, $hash, $kind, $uuid]) {
            $class = 'Database\\Seeders\\V3\\'.str_replace('/', '\\', $name);
            $map['additional_originals'][] = ['path' => 'database/seeders/V3/'.$name.'/definition.json',
                'sha256' => $hash, 'class' => $class, 'kind' => $kind, 'saved_test_uuid' => $uuid];
            // The older A1 quantifier bank belongs to an independent course.
            // Pin it for protection, but never migrate its filters or runtime bank.
            if ($topic !== 'quantifiers') { $map['additional_locale_overrides'][$class] = $map[$kind]['class']; }
        }
        return $map;
    }

    /** Diverse finite choices from other reviewed sentences, never the same first words per slot. */
    public static function compile(string $topic, string $kind): array
    {
        static $compiled = [];
        $source = self::source($topic);
        self::originalDefinition($topic, $kind);
        $cacheKey = $topic.'|'.$kind.'|'.hash('sha256', json_encode($source, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
        if (isset($compiled[$cacheKey])) { return $compiled[$cacheKey]; }
        $definition = parent::compileAuthored($topic, $kind);
        if ($kind !== 'builder') { return $compiled[$cacheKey] = $definition; }
        $pool = [];
        foreach ($source['questions'] as $author) {
            foreach (array_merge($author['groups'], preg_split('/\s+/u', $author['target']), $author['options'] ?? []) as $value) {
                $value = trim(preg_replace('/^[.,!?;:]+|[.,!?;:]+$/u', '', $value));
                if ($value !== '' && $value !== '-' && preg_match('/[\pL\pN]/u', $value) === 1) {
                    $pool[self::normalized($value)] = $value;
                }
            }
        }
        $poolWidths = array_map(static fn ($value) => count(preg_split('/\s+/u', $value)), $pool);
        foreach ($definition['questions'] as &$row) {
            $banned = []; $used = [];
            foreach (array_merge([$row['target_text']], $row['accepted_target_texts']) as $sentence) {
                foreach (preg_split('/\s+/u', $sentence) as $word) { $banned[self::normalized($word)] = true; }
            }
            foreach ($row['markers'] as $marker) { $banned[self::normalized($marker['answer'])] = true; }
            foreach ($row['answer_synonyms'] as $aliases) { foreach ($aliases as $alias) { $banned[self::normalized($alias)] = true; } }
            $choices = [];
            foreach ($row['markers'] as $key => $marker) {
                $width = count(preg_split('/\s+/u', $marker['answer']));
                $candidates = [];
                foreach ($pool as $candidateKey => $candidate) {
                    if (isset($banned[$candidateKey]) || isset($used[$candidateKey])) { continue; }
                    $candidates[$candidateKey] = ['value' => $candidate, 'distance' => abs($poolWidths[$candidateKey] - $width),
                        'rank' => hash('sha256', $row['uuid'].'|'.$key.'|'.$candidateKey)];
                }
                // Preserve the exact width/hash ordering, computing each key
                // once rather than again for every sort comparison.
                uasort($candidates, static function (array $a, array $b): int {
                    return ($a['distance'] <=> $b['distance']) ?: strcmp($a['rank'], $b['rank']);
                });
                $wrong = [];
                foreach ($candidates as $candidateKey => $candidate) {
                    $wrong[] = $candidate['value']; $used[$candidateKey] = true;
                    if (count($wrong) === 3) { break; }
                }
                if (count($wrong) !== 3) { throw new RuntimeException('Reviewed compose distractor pool exhausted: '.$topic.'/'.$row['author_id'].'/'.$key); }
                $choices[$key] = array_merge([$marker['answer']], $wrong);
                $row['markers'][$key]['options'] = $choices[$key];
            }
            $row['options_by_marker'] = $choices;
            $row['options'] = array_values(array_unique(array_merge(...array_values($choices))));
        }
        unset($row);
        return $compiled[$cacheKey] = FiniteBuilderWordSlots::finalize($definition, $source);
    }

    /** An explicit no-article marker is graded as '-', but omitted in the full sentence. */
    protected static function gapTarget(array $author): string
    {
        if (!array_key_exists('gap_semantics', $author)) {
            if (($author['answer'] ?? null) === '-') { throw new RuntimeException('No-article answers require explicit omission semantics.'); }
            return parent::gapTarget($author);
        }
        if ($author['gap_semantics'] !== 'no_article' || ($author['answer'] ?? null) !== '-'
            || ($author['kind'] ?? null) !== 'gap' || ($author['answer_synonyms'] ?? []) !== []) {
            throw new RuntimeException('Nouns Articles Quantity omission semantics differ: '.($author['id'] ?? ''));
        }
        return trim(preg_replace('/\s+/u', ' ', str_replace('{a1}', '', $author['question'])));
    }

    /** Historical catalogs keep word slots; this catalog permits finite reviewed 1–3 word slots. */
    protected static function composeSlots(array $author): array
    {
        if (!array_key_exists('compose_slots', $author)) { return parent::composeSlots($author); }
        $slots = $author['compose_slots'];
        if (($author['kind'] ?? null) !== 'compose' || !is_array($slots) || count($slots) < 2) {
            throw new RuntimeException('Nouns Articles Quantity reviewed compose slots differ: '.$author['id']);
        }
        foreach ($slots as $slot) {
            if (!is_string($slot) || trim($slot) !== $slot || $slot === '' || count(preg_split('/\s+/u', $slot)) > 3) {
                throw new RuntimeException('Nouns Articles Quantity compose slots require1–3 words: '.$author['id']);
            }
        }
        if (implode(' ', $slots) !== $author['target']) {
            throw new RuntimeException('Nouns Articles Quantity compose slots do not reconstruct target: '.$author['id']);
        }
        return $slots;
    }

    /** Exact compiled omission rows alone may replace literal raw reconstruction for ordering. */
    public static function reorderSentence(array $payload): ?string
    {
        $source = self::questions()[$payload['uuid'] ?? ''] ?? null;
        if (!$source || (string) ($source['type'] ?? '') !== '0'
            || ($source['markers']['a1']['answer'] ?? null) !== '-'
            || ($payload['question'] ?? null) !== $source['question']
            || (string) ($payload['type'] ?? '') !== '0'
            || ($payload['answer_map'] ?? null) !== ['a1' => '-']
            || ($payload['psp_content_revision'] ?? null) !== hash('sha256', json_encode($source, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))) {
            return null;
        }
        return $source['target_text'];
    }

    public static function decorate(array $payload): array
    {
        $payload = parent::decorate($payload);
        $source = self::questions()[$payload['uuid'] ?? ''] ?? null;
        if ($source && (string) ($source['type'] ?? '') === '0' && ($payload['question'] ?? null) === $source['question']
            && ($payload['psp_content_revision'] ?? null) === hash('sha256', json_encode($source, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
            && ($payload['answer_map'] ?? null) === array_map(static fn ($marker) => $marker['answer'], $source['markers'])) {
            $payload['passive_phrase_word_inputs'] = true;
            if (self::isGenericReferenceGap($source)) {
                $payload['naqq_generic_reference_caption_revision'] = $payload['psp_content_revision'];
                $payload = self::restoreGenericReferenceHint($payload, 'uk');
            }
        }
        if (self::reorderSentence($payload) !== null) {
            // Never accept a dash inside an English reordered sentence.
            $source = self::questions()[$payload['uuid']];
            $payload['reorder_accepted_answers'] = $source['accepted_target_texts'];
        }
        return NounsArticlesQuantityComposeOptions::decorate($payload, $source);
    }

    private static function isGenericReferenceGap(array $source): bool
    {
        return (string) ($source['type'] ?? '') === '0'
            && preg_match('/^naqq-generic-reference-(?:a1|a2|b1|b2|c1|c2)-g[0-9]{2}$/D', $source['uuid'] ?? '') === 1;
    }

    /** Keep canonical dash answers and saved attempts, but explain the learner-facing option. */
    public static function restoreGenericReferenceHint(array $payload, string $locale): array
    {
        if ($locale !== 'uk' || !self::isGenericReferenceGap($payload)
            || ($payload['naqq_generic_reference_caption_revision'] ?? null) !== ($payload['psp_content_revision'] ?? null)
            || self::reorderSentence($payload) === null) { return $payload; }

        $source = self::questions()[$payload['uuid']];
        $hint = preg_replace('/[,;]\s*(?:введи дефіс(?: замість артикля)?|познач його дефісом|нульовий артикль познач дефісом)\.$/u', '.', $source['verb_hint']);
        if ($hint === null || $hint === $source['verb_hint']) { return $payload; }
        $hint .= ' Обери або введи «Без артикля».';
        $payload['verb_hint'] = $hint;
        $payload['verb_hints']['a1'] = $hint;
        return $payload;
    }

    public static function cachedNeedsRefresh(array $payloads): bool
    {
        if (parent::cachedNeedsRefresh($payloads)) { return true; }
        $sources = self::questions();
        foreach ($payloads as $payload) {
            $source = $sources[$payload['uuid'] ?? ''] ?? null;
            if ($source && (string) $source['type'] === '0' && ($payload['passive_phrase_word_inputs'] ?? null) !== true) { return true; }
            if ($source && self::isGenericReferenceGap($source)
                && ($payload['naqq_generic_reference_caption_revision'] ?? null) !== ($payload['psp_content_revision'] ?? null)) { return true; }
        }
        return NounsArticlesQuantityComposeOptions::cachedNeedsRefresh($payloads, $sources);
    }

    /** Ten additional plural page tests, not the two independently sequenced courses. */
    private static function additionalMetadataPatches(): array
    {
        $topic = 'plural-nouns'; $map = self::mapping($topic); $patches = [];
        // Revalidates every source pin before reading any extra descriptor.
        self::originalDefinition($topic, 'standard');
        foreach (['standard', 'builder'] as $kind) {
            $file = dirname(__DIR__, 2).'/'.$map[$kind]['definition'];
            if (!is_file($file) || is_link($file)
                || json_decode(file_get_contents($file), true, flags: JSON_THROW_ON_ERROR) !== self::compile($topic, $kind)) {
                throw new RuntimeException('Additional plural projection requires current generated banks.');
            }
        }
        foreach ($map['additional_originals'] as $entry) {
            $original = json_decode(file_get_contents(dirname(__DIR__, 2).'/'.$entry['path']), true, flags: JSON_THROW_ON_ERROR);
            $before = $original['saved_test'] ?? [];
            if (($before['uuid'] ?? null) !== $entry['saved_test_uuid'] || ($original['defaults']['default_locale'] ?? null) !== 'uk'
                || ($original['seeder']['class'] ?? null) !== $entry['class']) {
                throw new RuntimeException('Additional plural test identity differs.');
            }
            foreach (['course_slug', 'lesson_order', 'previous_lesson_slug', 'next_lesson_slug', 'completion'] as $field) {
                if (array_key_exists($field, $before['filters'] ?? [])) {
                    throw new RuntimeException('An independent course cannot enter plural page migration.');
                }
            }
            // This protected pure projection keeps the extra UUID, slug, title,
            // original prompt binding and every unrelated selector intact.
            $after = parent::updatedSavedTest($topic, 'standard', $original);
            if (($before['uuid'] ?? null) === 'plural-nouns-s-es-ies-saved-test') {
                if (($before['filters']['tags'] ?? null) !== ['Plural Nouns']) {
                    throw new RuntimeException('Exact legacy plural tag selector differs.');
                }
                // Its source-specific legacy tag is not attached to the new bank.
                $after['filters']['tags'] = [];
            }
            $patches[$before['uuid']] = ['topic' => $topic, 'kind' => 'standard', 'identity' => $entry['class'],
                'source_path' => $entry['path'], 'source_sha256' => $entry['sha256'],
                'new_seeder_class' => $map['standard']['class'], 'before' => $before, 'after' => $after];
        }
        if (count($patches) !== 10) { throw new RuntimeException('Exactly ten additional plural page tests required.'); }
        return $patches;
    }

    public static function metadataPatches(): array
    {
        return parent::metadataPatches() + self::additionalMetadataPatches();
    }

    private static function isAdditionalTestUuid(string $uuid): bool
    {
        foreach (self::ADDITIONAL_ORIGINALS['plural-nouns'] as $entry) { if ($entry[3] === $uuid) { return true; } }
        return false;
    }

    public static function definitionMetadata(array $definition, string $locale = 'uk'): array
    {
        $projected = parent::definitionMetadata($definition, $locale);
        if ($projected !== $definition || $locale !== 'uk' || ($definition['defaults']['default_locale'] ?? null) !== 'uk') {
            return $projected;
        }
        if (!self::isAdditionalTestUuid((string) ($definition['saved_test']['uuid'] ?? ''))) { return $definition; }
        try {
            foreach (self::additionalMetadataPatches() as $patch) {
                if (($definition['seeder']['class'] ?? null) !== $patch['identity']) { continue; }
                $original = json_decode(file_get_contents(dirname(__DIR__, 2).'/'.$patch['source_path']), true, flags: JSON_THROW_ON_ERROR);
                $strip = static function (array $value): array {
                    foreach ($value['questions'] ?? [] as $index => $row) { unset($value['questions'][$index]['localizations']); }
                    return $value;
                };
                if ($strip($definition) !== $strip($original)) { return $definition; }
                self::compile('plural-nouns', 'standard');
                $definition['saved_test'] = $patch['after']; return $definition;
            }
        } catch (Throwable) { /* Incomplete or changed source retains historical behavior. */ }
        return $definition;
    }

    public static function savedTestFilters(array $payload, string $seederClass, array $filters, string $locale = 'uk'): array
    {
        $projected = parent::savedTestFilters($payload, $seederClass, $filters, $locale);
        if ($projected !== $filters || $locale !== 'uk' || !self::isAdditionalTestUuid((string) ($payload['uuid'] ?? ''))) { return $projected; }
        try {
            $patch = self::additionalMetadataPatches()[$payload['uuid'] ?? ''] ?? null;
            if ($patch && $seederClass === $patch['identity'] && $payload === $patch['after']
                && ($filters['seeder_classes'] ?? null) === [$patch['new_seeder_class'], $patch['identity']]) {
                $filters['seeder_classes'] = [$patch['new_seeder_class']];
            }
        } catch (Throwable) { /* Exact source binding is mandatory. */ }
        return $filters;
    }

    private static function extraProjectionPaths(array $patch): array
    {
        $paths = ['seeder_classes', 'num_questions', 'include_ai_v2', 'theory_page_mixed_type_profile',
            'theory_page_mixed_interleave_question_types', '__meta.mode',
            '__meta.nouns_articles_quantity_quality_revision', '__meta.nouns_articles_quantity_quality_topic'];
        $map = self::mapping('plural-nouns');
        foreach (array_keys(array_replace([$map['standard']['old_class'] => $map['standard']['class'],
            $map['builder']['old_class'] => $map['builder']['class']], $map['additional_locale_overrides'])) as $class) {
            $paths[] = '__meta.theory_page_mixed_locale_seeder_overrides.uk.'.$class;
        }
        if ($patch['before']['uuid'] === 'plural-nouns-s-es-ies-saved-test') { $paths[] = 'tags'; }
        return $paths;
    }

    /** Additional UK page tests retain their own exact original EN/PL selectors. */
    public static function filtersForLocale(string $testUuid, array $filters, string $locale): array
    {
        if (in_array(strtolower(trim($locale)), ['uk', 'ua'], true)) { return parent::filtersForLocale($testUuid, $filters, $locale); }
        if (!self::isAdditionalTestUuid($testUuid)) { return parent::filtersForLocale($testUuid, $filters, $locale); }
        try {
            $patch = self::additionalMetadataPatches()[$testUuid] ?? null;
            if (!$patch) { return parent::filtersForLocale($testUuid, $filters, $locale); }
            $before = $patch['before']['filters']; $after = $patch['after']['filters'];
            foreach (['course_slug', 'lesson_type', 'lesson_order', 'previous_lesson_slug', 'next_lesson_slug', 'completion'] as $field) {
                if (Arr::get($filters, $field) !== Arr::get($before, $field)) { return $filters; }
            }
            $paths = self::extraProjectionPaths($patch);
            foreach ($paths as $path) {
                if (!Arr::has($filters, $path) || Arr::get($filters, $path) !== Arr::get($after, $path)) { return $filters; }
            }
            foreach ($paths as $path) {
                if (Arr::has($before, $path)) { Arr::set($filters, $path, Arr::get($before, $path)); }
                else { Arr::forget($filters, $path); }
            }
            foreach (['__meta.theory_page_mixed_locale_seeder_overrides.uk',
                '__meta.theory_page_mixed_locale_seeder_overrides', '__meta'] as $path) {
                if (Arr::get($filters, $path) === [] && !Arr::has($before, $path)) { Arr::forget($filters, $path); }
            }
        } catch (Throwable) { /* No foreign or incomplete projection is applied. */ }
        return $filters;
    }

    private static function additionalContextUuid(array $filters, string $locale, string $testUuid): ?string
    {
        if (!in_array(strtolower(trim($locale)), ['uk', 'ua'], true) || !self::isAdditionalTestUuid($testUuid)) { return null; }
        try {
            if (!isset(self::additionalMetadataPatches()[$testUuid]) || self::filtersForLocale($testUuid, $filters, 'en') === $filters) { return null; }
            return self::mapping('plural-nouns')['standard']['old_saved_test_uuid'];
        } catch (Throwable) { return null; }
    }

    public static function stateNamespace(array $filters, string $locale, string $testUuid = ''): ?string
    {
        return parent::stateNamespace($filters, $locale, self::additionalContextUuid($filters, $locale, $testUuid) ?? $testUuid);
    }

    public static function testContextNeedsRefresh(array $cached, array $filters, string $locale, string $testUuid = ''): bool
    {
        return parent::testContextNeedsRefresh($cached, $filters, $locale, self::additionalContextUuid($filters, $locale, $testUuid) ?? $testUuid);
    }
}

