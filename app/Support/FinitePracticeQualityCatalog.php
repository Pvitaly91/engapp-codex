<?php

namespace App\Support;

use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use App\Support\Database\QuestionUuidResolver;
use App\Models\Question;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

/** Shared source-bound practice catalog mechanics; subclasses supply finite identities only. */
abstract class FinitePracticeQualityCatalog
{
    private static function root(): string { return dirname(__DIR__, 2); }

    abstract public static function mapping(string $topic): array;

    private static function readJson(string $path): array
    {
        $file = static::root().'/'.$path;
        if (!is_file($file) || is_link($file)) { throw new RuntimeException('Word Order source missing or linked: '.$path); }
        return json_decode(file_get_contents($file), true, flags: JSON_THROW_ON_ERROR);
    }

    public static function originalDefinition(string $topic, string $kind): array
    {
        $entry = static::bank($topic, $kind);
        foreach (static::mapping($topic)['additional_originals'] ?? [] as $additional) {
            $additionalFile = static::root().'/'.$additional['path'];
            if (!is_file($additionalFile) || is_link($additionalFile)
                || !hash_equals($additional['sha256'], hash_file('sha256', $additionalFile))
                || (static::readJson($additional['path'])['seeder']['class'] ?? null) !== $additional['class']) {
                throw new RuntimeException('Additional canonical practice source differs: '.$topic);
            }
        }
        $file = static::root().'/'.$entry['old_definition'];
        if (!is_file($file) || is_link($file) || !hash_equals($entry['old_sha256'], hash_file('sha256', $file))) {
            throw new RuntimeException('Word Order original canonical source differs: '.$topic.'/'.$kind);
        }
        $definition = static::readJson($entry['old_definition']);
        if (($definition['seeder']['class'] ?? null) !== $entry['old_class']
            || ($definition['saved_test']['uuid'] ?? null) !== $entry['old_saved_test_uuid']
            || static::definitionLocale($definition) !== 'uk') {
            throw new RuntimeException('Word Order original metadata binding differs: '.$topic.'/'.$kind);
        }
        return $definition;
    }

    private static function bank(string $topic, string $kind): array
    {
        if (!in_array($kind, ['standard', 'builder'], true)) { throw new InvalidArgumentException('Unknown Word Order bank kind.'); }
        return static::mapping($topic)[$kind];
    }

    public static function source(string $topic): array
    {
        $source = static::readJson(static::mapping($topic)['author']);
        if (($source['schema_version'] ?? null) !== 1 || ($source['topic_key'] ?? null) !== $topic
            || !is_string($source['title'] ?? null) || trim($source['title']) === '' || !is_array($source['questions'] ?? null)) {
            throw new RuntimeException('Invalid Word Order author source: '.$topic);
        }
        $ids = []; $targets = []; $counts = [];
        foreach ($source['questions'] as $row) {
            $id = $row['id'] ?? ''; $level = $row['level'] ?? ''; $kind = $row['kind'] ?? '';
            if (!in_array($level, static::LEVELS, true) || !in_array($kind, ['gap', 'compose'], true)
                || !is_string($id) || preg_match('/^'.preg_quote($level, '/').'-'.($kind === 'gap' ? 'g' : 'c').'[0-9]{2}$/iD', $id) !== 1
                || isset($ids[strtolower($id)])) { throw new RuntimeException('Word Order row identity differs: '.$topic.'/'.$id); }
            $ids[strtolower($id)] = true; $counts[$level][$kind] = ($counts[$level][$kind] ?? 0) + 1;
            foreach (['question', 'target', 'hint_uk'] as $key) {
                if (!is_string($row[$key] ?? null) || trim($row[$key]) !== $row[$key] || $row[$key] === '') {
                    throw new RuntimeException('Word Order row field missing: '.$topic.'/'.$id.'/'.$key);
                }
                static::assertClean($row[$key], $topic.'/'.$id.'/'.$key, $kind === 'gap' && $key === 'question');
            }
            if (preg_match('/[A-Za-z]/u', $row['hint_uk']) === 1 || preg_match('/[А-Яа-яІіЇїЄєҐґ]/u', $row['hint_uk']) !== 1
                || mb_strlen($row['hint_uk']) < 12 || preg_match('/[А-Яа-яІіЇїЄєҐґ]/u', $row['target']) === 1) {
                throw new RuntimeException('Word Order Ukrainian hint/English target differs: '.$topic.'/'.$id);
            }
            $targetKey = static::normalized($row['target']);
            if (isset($targets[$targetKey])) { throw new RuntimeException('Word Order duplicate target: '.$topic.'/'.$id); }
            $targets[$targetKey] = true;
            if (!is_array($row['groups'] ?? null) || count($row['groups']) < 2 || implode(' ', $row['groups']) !== $row['target']) {
                throw new RuntimeException('Word Order ordered groups do not reconstruct target: '.$topic.'/'.$id);
            }
            foreach ($row['groups'] as $group) {
                if (!is_string($group) || trim($group) !== $group || count(preg_split('/\s+/u', $group)) < 1
                    || count(preg_split('/\s+/u', $group)) > 3) {
                    throw new RuntimeException('Word Order groups must have1–3 words: '.$topic.'/'.$id);
                }
            }
            if ($kind === 'gap') {
                if (substr_count($row['question'], '{a1}') !== 1 || !is_string($row['answer'] ?? null)
                    || static::gapTarget($row) !== $row['target']
                    || !is_array($row['options'] ?? null) || count($row['options']) !== 3
                    || count(array_unique($row['options'])) !== 3 || !in_array($row['answer'], $row['options'], true)) {
                    throw new RuntimeException('Word Order gap/answer/options differ: '.$topic.'/'.$id);
                }
                foreach (array_merge($row['options'], $row['answer_synonyms'] ?? []) as $text) {
                    static::assertClean($text, $topic.'/'.$id.'/option');
                }
            } elseif (preg_match('/[А-Яа-яІіЇїЄєҐґ]/u', $row['question']) !== 1) {
                throw new RuntimeException('Word Order compose condition must be Ukrainian: '.$topic.'/'.$id);
            }
            foreach ($row['accepted_targets'] ?? [] as $text) { static::assertClean($text, $topic.'/'.$id.'/alias'); }
            static::composeSlots($row);
        }
        $requiredRows = static::requiredRowsPerLevel();
        foreach (static::LEVELS as $level) {
            if (($counts[$level]['gap'] ?? 0) !== $requiredRows['gap']
                || !in_array($counts[$level]['compose'] ?? 0, $requiredRows['compose'], true)) {
                throw new RuntimeException('Finite author source differs from its exact per-level row contract: '.$topic.'/'.$level);
            }
        }
        return $source;
    }

    /** Historical catalogs keep their original counts; finite topic catalogs may opt into an exact smaller contract. */
    protected static function requiredRowsPerLevel(): array
    {
        return ['gap' => 6, 'compose' => [3, 5]];
    }

    /** Historical catalogs require an explicit locale; exact-bound subclasses may mirror their seeder's default. */
    protected static function definitionLocale(array $definition): mixed
    {
        return $definition['defaults']['default_locale'] ?? null;
    }

    public static function normalized(string $text): string
    {
        return trim(preg_replace('/[^\pL\pN]+/u', ' ', mb_strtolower($text)));
    }

    /** Historical catalogs retain exact word slots; reviewed subclasses may opt into authored groups. */
    protected static function composeSlots(array $author): array
    {
        return preg_split('/\s+/u', $author['target']);
    }

    /** Historical gap reconstruction is unchanged unless a finite catalog opts in. */
    protected static function gapTarget(array $author): string
    {
        return str_replace('{a1}', $author['answer'], $author['question']);
    }

    private static function assertClean(string $text, string $label, bool $allowGap = false): void
    {
        if ($allowGap) { $text = str_replace('{a1}', '', $text); }
        if (preg_match('/<[^>]*>|&(?:#[0-9]+|#x[0-9a-f]+|[a-z][a-z0-9]+);|\{[^}]*\}/iu', $text) === 1) {
            throw new RuntimeException('Word Order technical display text: '.$label);
        }
    }

    /** Match the seeder's existing36-byte persistent UUID policy before catalog lookup. */
    public static function questionUuid(string $topic, string $authorId): string
    {
        static::mapping($topic);
        if (preg_match('/^(?:A1|A2|B1|B2|C1|C2)-(?:g|c)[0-9]{2}$/iD', $authorId) !== 1) {
            throw new InvalidArgumentException('Invalid Word Order author identity.');
        }
        return (new QuestionUuidResolver)->toPersistent(static::UUID_PREFIX.$topic.'-'.strtolower($authorId));
    }

    /** Routing prefilter only; matching IDs still require the full source guards. */
    public static function mayOwnQuestionUuid(mixed $uuid): bool
    {
        // Preserve the original lookup/error semantics for malformed callers.
        return !is_string($uuid) || str_starts_with($uuid, static::UUID_PREFIX);
    }

    /** Pure compilation of canonical author data; never boots Laravel or writes files/DB. */
    public static function compile(string $topic, string $kind): array
    {
        return FiniteBuilderWordSlots::finalize(static::compileAuthored($topic, $kind), static::source($topic));
    }

    /** Authored groups are retained until finite choice roles have been validated. */
    protected static function compileAuthored(string $topic, string $kind): array
    {
        $map = static::mapping($topic); $bank = static::bank($topic, $kind);
        $source = static::source($topic); $old = static::originalDefinition($topic, $kind);
        $rows = []; $sourceKey = static::SOURCE_PREFIX.str_replace('-', '_', $topic);
        foreach ($source['questions'] as $author) {
            if (($author['kind'] === 'gap') !== ($kind === 'standard')) { continue; }
            $words = $kind === 'builder' ? static::composeSlots($author) : preg_split('/\s+/u', $author['target']);
            $markers = [];
            if ($kind === 'standard') {
                $markers['a1'] = ['answer' => $author['answer'], 'options' => $author['options'], 'verb_hint' => $author['hint_uk']];
            } else {
                foreach ($words as $index => $word) { $markers['a'.($index + 1)] = ['answer' => $word, 'verb_hint' => $author['hint_uk']]; }
            }
            $display = [];
            if ($kind === 'standard' && ($author['answer_synonyms'] ?? []) !== []) { $display['a1'] = $author['answer_synonyms']; }
            if ($kind === 'builder') {
                foreach ($words as $index => $word) {
                    $aliases = static::displayAliasesForToken($word, $author['display_synonyms'] ?? []);
                    if ($aliases !== []) { $display['a'.($index + 1)] = $aliases; }
                }
            }
            $acceptedTargets = array_merge([$author['target']], $author['accepted_targets'] ?? []);
            if ($kind === 'standard') {
                // A reviewed whole-gap phrase can replace this single marker;
                // never manufacture word-by-word synonym combinations.
                foreach ($author['answer_synonyms'] ?? [] as $alias) { $acceptedTargets[] = str_replace('{a1}', $alias, $author['question']); }
            }
            $rows[] = ['id' => count($rows) + 1, 'uuid' => static::questionUuid($topic, $author['id']), 'author_id' => $author['id'],
                'question' => $author['question'], 'level' => $author['level'], 'type' => $kind === 'standard' ? 0 : 4,
                'source' => $sourceKey, 'translation_uk' => $author['uk'] ?? $author['question'],
                'target_text' => $author['target'], 'markers' => $markers, 'verb_hint' => $author['hint_uk'],
                'accepted_target_texts' => array_values(array_unique($acceptedTargets)),
                // Explicit reviewed lexical alternatives can span several words in one marker.
                // The existing compose whole-sentence prefix/final whitelist still forbids hybrids.
                'answer_synonyms' => $kind === 'standard' ? ['a1' => $author['answer_synonyms'] ?? []] : $display,
                'display_answer_synonyms' => $display, 'reorder_groups' => $author['groups'],
                'token_groups' => $author['groups'], 'quality_revision' => static::REVISION]
                + ($kind === 'builder' ? ['tokens_correct' => $words, 'options' => $words, 'source_text_uk' => $author['question']] : []);
        }
        return ['schema_version' => 1, 'quality_revision' => static::REVISION,
            'compiled_from' => ['path' => $map['author'], 'sha256' => hash_file('sha256', static::root().'/'.$map['author'])],
            'seeder' => ['class' => $bank['class'], 'uuid_namespace' => static::NAMESPACE_PREFIX.$topic.'-'.$kind],
            'defaults' => ['default_locale' => 'uk', 'flag' => $kind === 'standard' ? 2 : 0, 'type' => $kind === 'standard' ? 0 : 4,
                'level_difficulty' => ['A1' => 1, 'A2' => 2, 'B1' => 3, 'B2' => 4, 'C1' => 5, 'C2' => 5]],
            'category' => $old['category'], 'sources' => [$sourceKey => ['name' => static::LABEL.': '.$source['title']]],
            'tags' => $old['tags'] ?? [], 'default_tag_keys' => $old['default_tag_keys'] ?? [],
            'questions' => $rows];
    }

    /** Display-only lookup in this row's explicit map; canonical token/grading bytes never change. */
    public static function displayAliasesForToken(string $token, array $reviewedMap): array
    {
        if (isset($reviewedMap[$token]) && is_array($reviewedMap[$token])) { return $reviewedMap[$token]; }
        if (preg_match('/^(.+?)([.,!?;:]+)$/u', $token, $match) !== 1
            || !isset($reviewedMap[$match[1]]) || !is_array($reviewedMap[$match[1]])) { return []; }
        return array_values(array_unique(array_map(static function (string $alias) use ($match): string {
            return preg_match('/[.,!?;:]$/u', $alias) === 1 ? $alias : $alias.$match[2];
        }, $reviewedMap[$match[1]])));
    }

    private static function compiledDefinition(string $topic, string $kind): array
    {
        $actual = static::readJson(static::bank($topic, $kind)['definition']);
        if ($actual !== static::compile($topic, $kind)) { throw new RuntimeException('Word Order generated bank is stale: '.$topic.'/'.$kind); }
        return $actual;
    }

    public static function questions(): array
    {
        static $catalogs = [];
        if (isset($catalogs[static::class])) { return $catalogs[static::class]; }
        $questions = [];
        foreach (array_keys(static::TOPICS) as $topic) {
            foreach (['standard', 'builder'] as $kind) {
                try {
                    foreach (static::compiledDefinition($topic, $kind)['questions'] as $row) { $questions[$row['uuid']] = $row; }
                } catch (Throwable) { /* Incomplete/stale topic retains the ordinary original presentation. */ }
            }
        }
        return $catalogs[static::class] = $questions;
    }

    private static function payloadMatches(array $payload, array $source): bool
    {
        $key = static fn ($value) => mb_strtolower(Str::ascii((string) $value));
        return ($payload['uuid'] ?? null) === $source['uuid'] && ($payload['question'] ?? null) === $source['question']
            && (string) ($payload['type'] ?? '') === (string) $source['type']
            && array_map($key, array_values($payload['answer_map'] ?? [])) === array_map($key, array_column($source['markers'], 'answer'));
    }

    public static function decorate(array $payload): array
    {
        $source = static::questions()[$payload['uuid'] ?? ''] ?? null;
        if (!$source || !static::payloadMatches($payload, $source)) { return $payload; }
        $payload = PassivePracticeQuality::decorate($payload, $source);
        if ((string) $source['type'] === '0') {
            $payload['passive_gap_phrase_input'] = true;
            $payload['reorder_accepted_answers'] = array_values(array_unique(array_merge(
                $payload['reorder_accepted_answers'] ?? [], $source['accepted_target_texts'])));
        }
        return $payload;
    }

    public static function cachedNeedsRefresh(array $payloads): bool
    {
        $questions = static::questions();
        if (PassivePracticeQuality::cachedNeedsRefresh($payloads, $questions)) { return true; }
        foreach ($payloads as $payload) {
            $source = $questions[$payload['uuid'] ?? ''] ?? null;
            if ($source && (!static::payloadMatches($payload, $source)
                || ((string) $source['type'] === '0' && ($payload['passive_gap_phrase_input'] ?? null) !== true))) { return true; }
        }
        return false;
    }

    protected static function updatedSavedTest(string $topic, string $kind, array $original): array
    {
        $map = static::mapping($topic); $saved = $original['saved_test']; $filters = $saved['filters'] ?? [];
        $filters['seeder_classes'] = [$map[$kind]['class']];
        $filters['num_questions'] = count(static::compile($topic, $kind)['questions']);
        // V3 standard rows retain flag2. Explicit new-only standalone filters
        // must opt into that flag, just as page aggregates already do.
        if ($kind === 'standard') { $filters['include_ai_v2'] = true; }
        $filters['theory_page_mixed_type_profile'] = static::REVISION;
        $filters['theory_page_mixed_interleave_question_types'] = true;
        $meta = $filters['__meta'] ?? [];
        $meta['mode'] = 'filters';
        $meta[static::META_PREFIX.'_revision'] = static::REVISION;
        $meta[static::META_PREFIX.'_topic'] = $topic;
        $overrides = $meta['theory_page_mixed_locale_seeder_overrides'] ?? [];
        $overrides['uk'] = array_replace($overrides['uk'] ?? [], [
            $map['standard']['old_class'] => $map['standard']['class'], $map['builder']['old_class'] => $map['builder']['class'],
        ], $map['additional_locale_overrides'] ?? []);
        $meta['theory_page_mixed_locale_seeder_overrides'] = $overrides;
        $filters['__meta'] = $meta; $saved['filters'] = $filters;
        return $saved;
    }

    /** Explicit metadata-only plan, keyed by the existing saved-test UUID. */
    public static function metadataPatches(): array
    {
        $patches = [];
        foreach (array_keys(static::TOPICS) as $topic) {
            foreach (['standard', 'builder'] as $kind) {
                static::compiledDefinition($topic, $kind);
                $original = static::originalDefinition($topic, $kind); $bank = static::bank($topic, $kind);
                $patches[$bank['old_saved_test_uuid']] = ['topic' => $topic, 'kind' => $kind, 'identity' => $bank['old_class'],
                    'source_path' => $bank['old_definition'], 'source_sha256' => $bank['old_sha256'], 'new_seeder_class' => $bank['class'],
                    'before' => $original['saved_test'], 'after' => static::updatedSavedTest($topic, $kind, $original)];
            }
        }
        return $patches;
    }

    /** Localizations can be merged, but every original non-localization field remains exact-bound. */
    public static function definitionMetadata(array $definition, string $locale = 'uk'): array
    {
        if ($locale !== 'uk' || static::definitionLocale($definition) !== 'uk') { return $definition; }
        foreach (array_keys(static::TOPICS) as $topic) {
            foreach (['standard', 'builder'] as $kind) {
                $bank = static::bank($topic, $kind);
                if (($definition['seeder']['class'] ?? null) !== $bank['old_class']) { continue; }
                try {
                    $original = static::originalDefinition($topic, $kind);
                    $strip = static function (array $value): array {
                        if (isset($value['questions']) && is_array($value['questions'])) {
                            foreach ($value['questions'] as &$row) { unset($row['localizations']); }
                            unset($row);
                        }
                        return $value;
                    };
                    if ($strip($definition) !== $strip($original)) { return $definition; }
                    static::compiledDefinition($topic, 'standard'); static::compiledDefinition($topic, 'builder');
                    $definition['saved_test'] = static::updatedSavedTest($topic, $kind, $original);
                    return $definition;
                } catch (Throwable) { return $definition; }
            }
        }
        return $definition;
    }

    /** Remove only JsonTestSeeder's automatic old-class append after an exact metadata overlay. */
    public static function savedTestFilters(array $payload, string $seederClass, array $filters, string $locale = 'uk'): array
    {
        if ($locale !== 'uk') { return $filters; }
        foreach (array_keys(static::TOPICS) as $topic) {
            foreach (['standard', 'builder'] as $kind) {
                $bank = static::bank($topic, $kind);
                if ($seederClass !== $bank['old_class'] || ($payload['uuid'] ?? null) !== $bank['old_saved_test_uuid']) { continue; }
                try {
                    $original = static::originalDefinition($topic, $kind);
                    static::compiledDefinition($topic, $kind);
                    if ($payload !== static::updatedSavedTest($topic, $kind, $original)
                        || ($filters['seeder_classes'] ?? null) !== [$bank['class'], $bank['old_class']]) { return $filters; }
                    $filters['seeder_classes'] = [$bank['class']]; return $filters;
                } catch (Throwable) { return $filters; }
            }
        }
        return $filters;
    }

    /** Request-only projection: globally stored UK bank switches must not replace other locales' banks. */
    public static function filtersForLocale(string $testUuid, array $filters, string $locale): array
    {
        if (in_array(strtolower(trim($locale)), ['uk', 'ua'], true)) { return $filters; }
        foreach (array_keys(static::TOPICS) as $topic) {
            foreach (['standard', 'builder'] as $kind) {
                $bank = static::bank($topic, $kind);
                if ($testUuid !== $bank['old_saved_test_uuid']) { continue; }
                try {
                    $original = static::originalDefinition($topic, $kind);
                    static::compiledDefinition($topic, $kind);
                    $before = $original['saved_test']['filters'] ?? [];
                    $after = static::updatedSavedTest($topic, $kind, $original)['filters'];
                    // These are page-local saved tests, not course tests borrowing their UUID.
                    foreach (['course_slug', 'lesson_type', 'lesson_order'] as $field) {
                        if (Arr::get($filters, $field) !== Arr::get($before, $field)) { return $filters; }
                    }
                    $map = static::mapping($topic);
                    $paths = ['seeder_classes', 'num_questions', 'theory_page_mixed_type_profile',
                        'theory_page_mixed_interleave_question_types', '__meta.mode',
                        '__meta.'.static::META_PREFIX.'_revision', '__meta.'.static::META_PREFIX.'_topic',
                        '__meta.theory_page_mixed_locale_seeder_overrides.uk.'.$map['standard']['old_class'],
                        '__meta.theory_page_mixed_locale_seeder_overrides.uk.'.$map['builder']['old_class']];
                    foreach (array_keys($map['additional_locale_overrides'] ?? []) as $class) {
                        $paths[] = '__meta.theory_page_mixed_locale_seeder_overrides.uk.'.$class;
                    }
                    if ($kind === 'standard') { $paths[] = 'include_ai_v2'; }
                    foreach ($paths as $path) {
                        if (!Arr::has($filters, $path) || Arr::get($filters, $path) !== Arr::get($after, $path)) { return $filters; }
                    }
                    $projected = $filters;
                    foreach ($paths as $path) {
                        if (Arr::has($before, $path)) { Arr::set($projected, $path, Arr::get($before, $path)); }
                        else { Arr::forget($projected, $path); }
                    }
                    // Remove only newly empty containers; retain unrelated locale/UK override entries.
                    foreach (['__meta.theory_page_mixed_locale_seeder_overrides.uk',
                        '__meta.theory_page_mixed_locale_seeder_overrides', '__meta'] as $path) {
                        if (Arr::get($projected, $path) === [] && !Arr::has($before, $path)) { Arr::forget($projected, $path); }
                    }
                    return $projected;
                } catch (Throwable) { return $filters; }
            }
        }
        return $filters;
    }

    /** Context-bound bank migration guard; UUID-only revisions cannot recognize a replaced bank. */
    public static function testContextNeedsRefresh(array $cached, array $filters, string $locale, string $testUuid = ''): bool
    {
        $allowed = static::testContextSeederClasses($filters, $locale, $testUuid);
        if ($allowed === []) { return false; }
        return static::cachedBankMembershipNeedsRefresh($cached, $allowed);
    }

    protected static function cachedBankMembershipNeedsRefresh(array $cached, array $allowed): bool
    {
        if ($cached === [] || count($cached) > 2000) { return true; }
        $uuids = [];
        foreach ($cached as $row) {
            if (!is_array($row) || !is_string($row['uuid'] ?? null) || trim($row['uuid']) === '') { return true; }
            $uuids[] = $row['uuid'];
        }
        try {
            // Check bank membership, not the resolver's randomized subset. A
            // valid saved selection/progress must not change on every reload.
            $actual = Question::query()->whereIn('uuid', $uuids)->pluck('seeder', 'uuid')->all();
            foreach ($uuids as $uuid) {
                if (!isset($actual[$uuid]) || !in_array(strtolower((string) $actual[$uuid]), $allowed, true)) { return true; }
            }
        } catch (Throwable) { return false; }
        return false;
    }

    /** Versioned bank runs retain legacy progress untouched; author-copy edits do not reset a run. */
    public static function stateNamespace(array $filters, string $locale, string $testUuid = ''): ?string
    {
        $classes = static::testContextSeederClasses($filters, $locale, $testUuid);
        if ($classes === []) { return null; }
        $locale = strtolower(trim($locale));
        $locale = $locale === 'ua' ? 'uk' : $locale;
        return static::UUID_PREFIX.substr(hash('sha256', static::REVISION."\0".$locale."\0".implode("\0", $classes)), 0, 20);
    }

    private static function normalizedBankClasses(array $classes): array
    {
        if (array_filter($classes, static fn ($class) => !is_string($class)) !== []) { return []; }
        $classes = array_values(array_unique(array_map(static fn (string $class) => static::classIdentity($class), $classes)));
        sort($classes);
        return $classes;
    }

    /** Only finite saved tests, exact page-pairs, and the declared category opt in. */
    private static function testContextSeederClasses(array $filters, string $locale, string $testUuid): array
    {
        if (!is_array($filters['seeder_classes'] ?? null)) { return []; }
        $classes = static::normalizedBankClasses($filters['seeder_classes']);
        if ($classes === []) { return []; }
        $uk = in_array(strtolower(trim($locale)), ['uk', 'ua'], true);
        $profile = Arr::get($filters, 'theory_page_mixed_type_profile', Arr::get($filters, '__meta.theory_page_mixed_type_profile', ''));
        if (($uk && $profile !== static::REVISION) || (!$uk && !in_array($profile, ['', null], true))) { return []; }
        try {
            $pairs = [];
            foreach (array_keys(static::TOPICS) as $topic) {
                $map = static::mapping($topic);
                foreach (['standard', 'builder'] as $kind) {
                    $bank = $map[$kind];
                    if ($testUuid !== $bank['old_saved_test_uuid']) { continue; }
                    if ($uk) {
                        return static::filtersForLocale($testUuid, $filters, 'en') !== $filters ? $classes : [];
                    }
                    $before = static::originalDefinition($topic, $kind)['saved_test']['filters'];
                    foreach (['seeder_classes', 'num_questions', 'course_slug', 'lesson_type', 'lesson_order', '__meta.mode',
                        '__meta.'.static::META_PREFIX.'_revision', '__meta.'.static::META_PREFIX.'_topic'] as $path) {
                        if (Arr::get($filters, $path) !== Arr::get($before, $path)) { return []; }
                    }
                    return $classes;
                }
                $pair = static::normalizedBankClasses([$map['standard'][$uk ? 'class' : 'old_class'], $map['builder'][$uk ? 'class' : 'old_class']]);
                $pairs[$topic] = $pair;
                if ($classes === $pair && Arr::get($filters, 'aggregated_theory_page_test',
                    Arr::get($filters, '__meta.aggregated_theory_page_test', false)) === true
                    && Arr::get($filters, '__meta.mode') === 'filters'
                    && Arr::get($filters, '__meta.theory_page_mixed_polyglot_test') === true
                    && !filled($filters['course_slug'] ?? null)) {
                    $before = static::originalDefinition($topic, 'standard')['saved_test']['filters'];
                    if (static::classIdentity((string) Arr::get($filters, 'prompt_generator.theory_page.page_seeder_class'))
                        === static::classIdentity((string) Arr::get($before, 'prompt_generator.theory_page.page_seeder_class'))) { return $classes; }
                }
            }
            if (Arr::get($filters, '__meta.mode') !== 'filters'
                || Arr::get($filters, '__meta.theory_page_mixed_polyglot_test') !== true
                || Arr::get($filters, '__meta.aggregated_theory_category_test') !== true
                || (int) Arr::get($filters, '__meta.theory_category_id') !== static::CATEGORY_ID
                || Arr::get($filters, '__meta.theory_category_slug') !== static::CATEGORY_SLUG
                || !($filters['theory_category_page_test'] ?? false) || filled($filters['course_slug'] ?? null)) { return []; }
            $groups = $filters['theory_category_page_groups'] ?? [];
            if (!is_array($groups) || $groups === []) { return []; }
            $categoryPairs = array_intersect_key($pairs, array_flip(static::CATEGORY_TOPICS)); $groupClasses = [];
            foreach ($groups as $group) {
                if (!is_array($group) || !is_array($group['seeder_classes'] ?? null)) { return []; }
                $pair = static::normalizedBankClasses($group['seeder_classes']);
                if (!in_array($pair, $categoryPairs, true)) { return []; }
                $groupClasses = array_merge($groupClasses, $pair);
            }
            return static::normalizedBankClasses($groupClasses) === $classes ? $classes : [];
        } catch (Throwable) { return []; }
    }

    private static function classIdentity(string $class): string
    {
        return strtolower(preg_replace('/\\\\+/u', '\\\\', trim($class)));
    }
}


