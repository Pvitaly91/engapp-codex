<?php

namespace App\Support;

use App\Models\Page;
use RuntimeException;

/** Six finite teaching-data owners; no presentation, formatter or grading implementation. */
final class M83B2RevisionBasicGrammar
{
    public const BASE_SHA = '6d1681c9e5b7ffc06d47b00537c87a5588b05df8';
    public const VERSION = '1.0.0';
    public const BEFORE = 'docs/content/m83-before-source.v1.json';
    public const SOURCE = 'database/content-patches/m83-b2-revision-basic-grammar.v1.0.1.json';
    public const MASTER = 'docs/content/m83-b2-revision-basic-grammar.v1.0.1.json';
    public const CONTRACT = 'docs/content/theory-sentence-builders/m83-b2-revision-basic-grammar.v1.json';
    public const CHECKSUMS = 'docs/content/m83-source-checksums.v1.0.1.json';
    public const BINDINGS = 'docs/content/m83-native-bindings.v1.json';
    public const NAVIGATION = 'docs/content/m83-navigation-review.v1.json';
    // Reviewed finite author/bank/metadata freeze; not a claim of live acceptance.
    public const BEFORE_SHA = '91e7813255549ff4245f47e69fce72dc8589074af9ece8dcda9f96c38fc2ac54';
    public const SOURCE_SHA = 'd0ca64f24cd6c1767e331aaeda258ba778e9eac49c6accc55a23237b89478e7c';
    public const MASTER_SHA = '58674cc3e1a4619028c5d7e0826f70450a974cde07002edf6fcfa744546c0db4';
    public const CONTRACT_SHA = '66917ee713f20762e59442c0b099974b7c56fa9420e786b8130618e1f8b3c6b1';
    public const CHECKSUMS_SHA = 'd1e2b1b8c5c55c68ce2a35a2d842693d046e4d33871783dab61f29e836174ef0';
    public const BINDINGS_SHA = '7832a66ceb9ae24961f0ae29b6b3968f737ce47c1ec271b141f90f5f1e8126c3';
    public const NAVIGATION_SHA = '10ed7050ced1e55a7c14359bcdd7c735e342e9732cb66919fcfbcc4ec67ab49d';
    public const OWNERS = [
        'Database\\Seeders\\Page_V3\\BasicGrammar\\B2MixedRevisionTheorySeeder',
        'Database\\Seeders\\Page_V3\\BasicGrammar\\BasicGrammarPartsOfSpeechTheorySeeder',
        'Database\\Seeders\\Page_V3\\BasicGrammar\\BasicGrammarSentenceStructureSvoTheorySeeder',
        'Database\\Seeders\\Page_V3\\BasicGrammar\\BasicGrammarSentenceTypesTheorySeeder',
        'Database\\Seeders\\Page_V3\\BasicGrammar\\BasicGrammarImperativesTheorySeeder',
        'Database\\Seeders\\Page_V3\\BasicGrammar\\BasicGrammarConjunctionsTheorySeeder',
    ];
    public const PAGE_IDS = [328, 22, 23, 2, 21, 20];
    public const CATEGORY_IDS = [59, 2, 2, 2, 2, 2];
    public const BANKS = [
        'Database\\Seeders\\V3\\Polyglot\\PolyglotB2MixedRevisionAllLevelsLessonSeeder',
        'Database\\Seeders\\V3\\Polyglot\\PolyglotPartsOfSpeechAllLevelsLessonSeeder',
        'Database\\Seeders\\V3\\Polyglot\\PolyglotSentenceStructureSvoAllLevelsLessonSeeder',
        'Database\\Seeders\\V3\\Polyglot\\PolyglotSentenceTypesAllLevelsLessonSeeder',
        'Database\\Seeders\\V3\\Polyglot\\PolyglotImperativesAllLevelsLessonSeeder',
        'Database\\Seeders\\V3\\Polyglot\\PolyglotConjunctionsAllLevelsLessonSeeder',
    ];
    public const SLUGS = ['b2-mixed-revision', 'parts-of-speech', 'sentence-structure-svo', 'sentence-types',
        'imperatives-sit-down-dont-open-it', 'basic-conjunctions-and-but-or-because-so'];
    public const ANCESTRIES = [['mixed-revision'], ['basic-grammar'], ['basic-grammar'], ['basic-grammar'],
        ['basic-grammar'], ['basic-grammar']];
    public const LEVELS = ['B2', null, null, null, null, null];
    public const FOCUS_LEVELS = ['B2', 'A1', 'A1', 'A1', 'A1', 'A1'];
    public const DISPLAY_LEVELS = ['B2', 'A1–A2', 'A1–A2', 'A1–A2', 'A1–A2', 'A1–A2'];
    public const CONTINUING_COUNTS = [5, 15, 13, 12, 13, 13];
    public const PRACTICE_SOURCE_INDICES = [5, 13, 11, 10, 11, 11];
    public const NAVIGATION_SOURCE_INDICES = [4, 14, 12, 11, 12, 12];
    public const RENDERED_TITLES = ['B2 Mixed Revision', 'Parts of speech', 'Sentence structure', 'Sentence types',
        'Imperative sentences', 'Conjunctions'];
    public const GRADED_COUNTS = [6, 12, 10, 11, 10, 11];
    public const SELECT_COUNTS = [2, 3, 3, 4, 3, 4];
    public const CHOICE_COUNTS = [2, 2, 2, 2, 2, 3];
    public const INPUT_COUNTS = [2, 5, 3, 3, 3, 3];
    public const REPHRASE_COUNTS = [0, 2, 2, 2, 2, 1];
    public const SENTENCE_INPUT_INDICES = [[0, 1], [3, 4], [0, 1, 2], [0, 1, 2], [0, 1, 2], [0, 1, 2]];
    public const EXISTING_PRACTICE_UUIDS = [null, '2950dfc0-453b-5998-a7bb-874df985e406',
        'bd533496-3830-5362-8875-4961528a5a05', '934b155f-2b78-5810-824a-ae68b49cfb6a',
        '9f1d2863-d323-50f3-8081-5e2018987726', 'c0c1a7ab-b0e7-58d1-85dc-66f3cc713996'];

    public static function draftProfile(array $bindings): array
    {
        return ['label' => 'M83', 'base_sha' => self::BASE_SHA, 'version' => self::VERSION,
            'marker' => 'm83_native_v1', 'detail_marker' => 'm83_detail_key', 'uuid_prefix' => 'm83:',
            'design' => 'm83-native-v1', 'bindings' => $bindings];
    }

    private static function readFrozen(string $path, string $sha, ?string $root = null): array
    {
        $root ??= dirname(__DIR__, 2);
        if (preg_match('/^[a-f0-9]{64}$/D', $sha) !== 1 || !is_file($root.'/'.$path) || is_link($root.'/'.$path)) {
            throw new RuntimeException('M83 artifact is not reviewed/frozen: '.$path);
        }
        $bytes = file_get_contents($root.'/'.$path);
        if (!is_string($bytes) || !hash_equals($sha, hash('sha256', $bytes))) {
            throw new RuntimeException('M83 immutable artifact differs: '.$path);
        }
        return json_decode($bytes, true, flags: JSON_THROW_ON_ERROR);
    }

    public static function profile(): array
    {
        $data = self::readFrozen(self::BINDINGS, self::BINDINGS_SHA);
        if (($data['base_sha'] ?? null) !== self::BASE_SHA
            || array_column($data['bindings'] ?? [], 'identity') !== self::OWNERS) {
            throw new RuntimeException('M83 exact six-owner bindings differ.');
        }
        return self::draftProfile($data['bindings']) + ['artifacts' => [
            self::BEFORE => self::BEFORE_SHA, self::SOURCE => self::SOURCE_SHA,
            self::MASTER => self::MASTER_SHA, self::CONTRACT => self::CONTRACT_SHA,
            self::CHECKSUMS => self::CHECKSUMS_SHA, self::BINDINGS => self::BINDINGS_SHA,
            self::NAVIGATION => self::NAVIGATION_SHA,
        ]];
    }

    private static function release(): TheoryNativeContentRelease { return new TheoryNativeContentRelease(self::profile()); }
    public static function load(?string $root = null): array { return self::release()->load($root); }
    public static function definition(array $lesson, array $before): array { return self::release()->definition($lesson, $before); }
    public static function validate(array $before, array $patch, array $master, ?array $contract = null): void
    {
        $bytes = json_encode($before, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
        if (preg_match('/^[a-f0-9]{64}$/D', self::BEFORE_SHA) !== 1 || !hash_equals(self::BEFORE_SHA, hash('sha256', $bytes))) {
            throw new RuntimeException('M83 independently frozen source BEFORE differs.');
        }
        self::release()->validate($before, $patch, $master, $contract);
        self::assertEditorial($before, $patch, $master);
    }

    /**
     * Draft callers provide the independently reviewed navigation object.
     * Active validation always loads its immutable public hash-bound counterpart.
     */
    public static function assertEditorial(array $before, array $patch, array $master, ?array $navigation = null): void
    {
        $navigation ??= self::readFrozen(self::NAVIGATION, self::NAVIGATION_SHA);
        if (($before['content_type'] ?? null) !== 'M83_source_before'
            || ($patch['content_type'] ?? null) !== 'M83_B2_Revision_Basic_Grammar'
            || array_column($before['targets'] ?? [], 'identity') !== self::OWNERS
            || array_column($patch['targets'] ?? [], 'identity') !== self::OWNERS
            || array_column($master['lessons'] ?? [], 'identity') !== self::OWNERS
            || ($master['editorial_review']['navigation_review'] ?? null) !== $navigation) {
            throw new RuntimeException('M83 exact source/editorial/navigation provenance differs.');
        }
        self::assertNavigation($before, $master['lessons'], $navigation);
        $ids = []; $gradedTotal = 0; $demoTotal = 0; $fullTotal = 0;
        foreach ($master['lessons'] as $i => $lesson) {
            $old = $before['targets'][$i]['before'];
            $blocks = $lesson['blocks'] ?? []; $count = self::CONTINUING_COUNTS[$i];
            if (($lesson['preserved_h1'] ?? null) !== self::RENDERED_TITLES[$i]
                || ($lesson['raw_title'] ?? null) !== $old['page']['title']
                || ($lesson['intended_own_practice_level'] ?? null) !== self::FOCUS_LEVELS[$i]
                || !array_key_exists('technical_block_level', $lesson)
                || $lesson['technical_block_level'] !== self::LEVELS[$i]
                || ($lesson['display_level'] ?? null) !== self::DISPLAY_LEVELS[$i]
                || empty($lesson['point_detail_decisions'])
                || count($old['page']['blocks']) !== $count || count($blocks) !== $count + ($i === 0 ? 1 : 0)) {
                throw new RuntimeException('M83 retained title/level/native block vector differs.');
            }
            foreach ($old['page']['blocks'] as $slot => $source) {
                $after = $blocks[$slot] ?? [];
                if (($after['existing_source_index'] ?? null) !== $slot || ($after['type'] ?? null) !== $source['type']
                    || ($after['column'] ?? null) !== $source['column'] || !array_key_exists('level', $after)
                    || $after['level'] !== ($source['level'] ?? null)) {
                    throw new RuntimeException('M83 continuing native metadata/column/slot changed.');
                }
            }
            $practice = $blocks[self::PRACTICE_SOURCE_INDICES[$i]];
            if (($practice['type'] ?? null) !== 'practice-set' || ($practice['column'] ?? null) !== 'left'
                || !array_key_exists('level', $practice) || $practice['level'] !== self::LEVELS[$i]) {
                throw new RuntimeException('M83 exact appended/continuing practice differs.');
            }
            if ($i > 0 && M26DetailPackage::uuid(self::OWNERS[$i],
                $old['page']['blocks'][self::PRACTICE_SOURCE_INDICES[$i]], self::PRACTICE_SOURCE_INDICES[$i] + 1)
                    !== self::EXISTING_PRACTICE_UUIDS[$i]) {
                throw new RuntimeException('M83 independently captured practice UUID differs.');
            }
            $data = $practice['data'];
            if (($data['linked_practice']['source'] ?? null) !== 'theory_links'
                || ($data['linked_practice']['question_types'] ?? null) !== ['4']
                || ($data['linked_practice']['seeder_classes'] ?? null) !== [self::BANKS[$i]]) {
                throw new RuntimeException('M83 exact thematic type-4 bank declaration differs.');
            }
            if (array_key_exists('intro', $data)) {
                throw new RuntimeException('M83 generic practice intro cannot carry an invisible criterion.');
            }
            $counts = ['selects' => self::SELECT_COUNTS[$i], 'choices' => self::CHOICE_COUNTS[$i],
                'inputs' => self::INPUT_COUNTS[$i], 'rephrase' => self::REPHRASE_COUNTS[$i] + ($i === 0 ? 0 : 1)];
            $graded = 0; $demos = 0;
            foreach ($counts as $group => $expected) {
                if (!is_array($data[$group] ?? null) || !array_is_list($data[$group]) || count($data[$group]) !== $expected) {
                    throw new RuntimeException('M83 finite retained/new practice group counts differ.');
                }
                foreach ($data[$group] as $index => $task) {
                    if ($group === 'rephrase' && $index === 0 && $i > 0) {
                        if (empty($task['example_original']) || empty($task['example_target'])
                            || array_key_exists('answer', $task) || array_key_exists('accepted', $task)) {
                            throw new RuntimeException('M83 retained demo must remain ungraded.');
                        }
                        $demos++; continue;
                    }
                    self::assertTask($task, $group, $data, $ids);
                    if ($group === 'inputs' && in_array($index, self::SENTENCE_INPUT_INDICES[$i], true)) {
                        self::assertSentenceBank($task); $fullTotal++;
                    }
                    $graded++;
                }
            }
            if ($graded !== self::GRADED_COUNTS[$i] || $demos !== ($i === 0 ? 0 : 1)) {
                throw new RuntimeException('M83 graded tasks and demos were conflated.');
            }
            self::assertTaskMap($i, $old, $data, $master['editorial_review']['task_map'][self::OWNERS[$i]] ?? null);
            if ($i === 1) {
                // Retain both full grammar analyses, not two replacement noun-only questions.
                $expected = ['pronoun conjunction pronoun verb preposition noun adverb',
                    'interjection article adjective noun verb verb preposition article adjective noun'];
                foreach ($expected as $offset => $answer) {
                    if (($data['rephrase'][$offset + 1]['answer'] ?? null) !== $answer) {
                        throw new RuntimeException('M83 complete word-by-word grammar analysis was lost.');
                    }
                }
            }
            $gradedTotal += $graded; $demoTotal += $demos;
        }
        if ($gradedTotal !== 60 || $demoTotal !== 5 || $fullTotal !== 16) {
            throw new RuntimeException('M83 exact graded/demo/full-sentence totals differ.');
        }
    }

    private static function assertTask(array $task, string $group, array $data, array &$ids): void
    {
        if (array_key_exists('strict', $task) || array_key_exists('punctuation_sensitive', $task)
            || ($group === 'selects' && array_key_exists('prompt', $task))
            || !is_array($task['accepted'] ?? null) || $task['accepted'] === []
            || !is_string($task['answer'] ?? null) || !in_array($task['answer'], $task['accepted'], true)) {
            throw new RuntimeException('M83 explicit accepted/visible native task contract differs.');
        }
        if (array_key_exists('id', $task)) {
            if (!is_string($task['id']) || !str_starts_with($task['id'], 'm83-') || isset($ids[$task['id']])) {
                throw new RuntimeException('M83 stable task ID differs or repeats.');
            }
            $ids[$task['id']] = true;
        }
        foreach ($task['feedback'] ?? [] as $key => $text) {
            if (!is_string($key) || $key !== mb_strtolower(trim($key)) || !self::plainText($text)) {
                throw new RuntimeException('M83 feedback must be plain text with literal trim/lowercase keys.');
            }
        }
        foreach ($task['accepted'] as $answer) {
            if (!is_string($answer) || self::comparisonKey($answer) === ''
                || !self::plainText($task['feedback'][mb_strtolower(trim($answer))] ?? null)) {
                throw new RuntimeException('M83 accepted alias lacks a nonempty plain explanation.');
            }
        }
        if (in_array($group, ['selects', 'choices'], true)) {
            $options = $task['options'] ?? ($group === 'selects' ? ($data['options'] ?? []) : ($data['choice_options'] ?? ['a', 'b']));
            if (!is_array($options) || count($options) < 2 || trim(strip_tags($task['label'] ?? '')) === '') {
                throw new RuntimeException('M83 options or visible classification criterion missing.');
            }
            $keys = array_map(self::comparisonKey(...), $options);
            if (in_array('', $keys, true) || count(array_unique($keys)) !== count($keys)
                || array_diff($task['accepted'], $options) !== []) {
                throw new RuntimeException('M83 option/key normalization collision.');
            }
            $correct = 0;
            foreach ($options as $option) {
                if (!self::plainText($task['feedback'][mb_strtolower(trim($option))] ?? null)) {
                    throw new RuntimeException('M83 option explanation missing.');
                }
                foreach ($task['accepted'] as $answer) {
                    $variantKeys = array_map(self::comparisonKey(...), AcceptedAnswerVariants::for($answer));
                    if (in_array(self::comparisonKey($option), $variantKeys, true)) { $correct++; break; }
                }
            }
            if ($correct !== 1) { throw new RuntimeException('M83 displayed options do not have one native-correct choice.'); }
        } elseif ($group === 'inputs') {
            if (isset($task['hint']) || isset($task['placeholder']) || trim(strip_tags($task['after'] ?? '')) === '') {
                throw new RuntimeException('M83 manual input requires a visible after condition, not a hidden/leaking field.');
            }
        } elseif ($group === 'rephrase') {
            if (trim($task['original'] ?? '') === '' || ($task['placeholder'] ?? '') === $task['answer']) {
                throw new RuntimeException('M83 rephrase criterion missing or target leaked.');
            }
        }
    }

    /** Data-only author review; the existing browser engine still performs all actual grading. */
    private static function assertSentenceBank(array $task): void
    {
        $tokens = array_map('trim', explode('/', $task['before'] ?? ''));
        if (count($tokens) < 2 || count(array_filter($tokens, static fn ($t) => $t === '' || count(preg_split('/\s+/u', $t)) > 3)) > 0
            || implode(' ', $tokens) === $task['answer'] || preg_match('/[\x{0400}-\x{04FF}]/u', $task['after'] ?? '') !== 1
            || isset($task['hint']) || isset($task['placeholder'])) {
            throw new RuntimeException('M83 full sentence needs a clean shuffled 1–3-word bank and visible Ukrainian condition.');
        }
        // Match literal groups at word boundaries; punctuation/case must already be canonical in author data.
        $remaining = $tokens; $tail = $task['answer'];
        while ($tail !== '' && $remaining !== []) {
            $found = false;
            foreach ($remaining as $index => $token) {
                if ($tail === $token || str_starts_with($tail, $token.' ')) {
                    $tail = $tail === $token ? '' : substr($tail, strlen($token) + 1);
                    unset($remaining[$index]); $found = true; break;
                }
            }
            if (!$found) { throw new RuntimeException('M83 token data cannot assemble the exact visible canonical sentence.'); }
        }
        if ($tail !== '' || $remaining !== []) {
            throw new RuntimeException('M83 full sentence loses or duplicates a literal token group.');
        }
    }

    private static function assertTaskMap(int $i, array $old, array $data, mixed $ledger): void
    {
        if (!is_array($ledger) || !array_is_list($ledger)) { throw new RuntimeException('M83 exact OLD→AFTER task ledger missing.'); }
        $source = $i === 0 ? [] : json_decode($old['page']['blocks'][self::PRACTICE_SOURCE_INDICES[$i]]['body'], true, flags: JSON_THROW_ON_ERROR);
        $oldSeen = []; $afterSeen = [];
        foreach ($ledger as $row) {
            $group = $row['after_group'] ?? null; $index = $row['after_group_index'] ?? null;
            $task = is_string($group) && is_int($index) ? ($data[$group][$index] ?? null) : null;
            if (!is_array($task) || ($row['after_task'] ?? null) !== $task
                || ($row['after_block_source_index'] ?? null) !== self::PRACTICE_SOURCE_INDICES[$i]
                || isset($afterSeen[$group.':'.$index]) || trim($row['learning_function'] ?? '') === ''
                || trim($row['change_reason'] ?? '') === '') {
                throw new RuntimeException('M83 duplicate, missing or stale AFTER task ledger.');
            }
            $afterSeen[$group.':'.$index] = true;
            $graded = !($group === 'rephrase' && $index === 0 && $i > 0);
            if (($row['graded'] ?? null) !== $graded) { throw new RuntimeException('M83 task ledger conflates demo and graded task.'); }
            $oldGroup = $row['old_group'] ?? null; $oldIndex = $row['old_group_index'] ?? null;
            if (($row['old_task'] ?? null) === null) {
                if ($oldGroup !== null || $oldIndex !== null || ($row['old_block_source_index'] ?? null) !== null
                    || !is_string($task['id'] ?? null) || !str_starts_with($task['id'], 'm83-')) {
                    throw new RuntimeException('M83 new task falsely claims a source position or lacks stable ID.');
                }
                continue;
            }
            $captured = is_string($oldGroup) && is_int($oldIndex) ? ($source[$oldGroup][$oldIndex] ?? null) : null;
            if (!is_array($captured) || $row['old_task'] !== $captured
                || ($row['old_block_source_index'] ?? null) !== self::PRACTICE_SOURCE_INDICES[$i]
                || isset($oldSeen[$oldGroup.':'.$oldIndex])) {
                throw new RuntimeException('M83 old task was duplicated, invented or detached from independent source.');
            }
            $oldSeen[$oldGroup.':'.$oldIndex] = true;
            $transfer = $i === 5 && $oldGroup === 'rephrase' && $oldIndex === 2 && $group === 'choices' && $index === 0;
            if (!$transfer && ($group !== $oldGroup || $index !== $oldIndex)) {
                throw new RuntimeException('M83 unreviewed task transfer.');
            }
            foreach (['id', 'source_index'] as $field) {
                if ($transfer && $field === 'id') {
                    if (($task['id'] ?? null) !== 'm83-conjunctions-retained-comma' || array_key_exists('id', $captured)) {
                        throw new RuntimeException('M83 transferred control ID differs.');
                    }
                } elseif (array_key_exists($field, $task) !== array_key_exists($field, $captured)
                    || (array_key_exists($field, $captured) && $task[$field] !== $captured[$field])) {
                    throw new RuntimeException('M83 retained task identity/absence changed.');
                }
            }
            // The two grammar analyses change answer format; one comma-only correction changes control type.
            if ($graded && !$transfer && !($i === 1 && $oldGroup === 'rephrase')
                && self::comparisonKey($task['answer']) !== self::comparisonKey($captured['answer'])) {
                throw new RuntimeException('M83 retained participant/object/time or canonical target changed.');
            }
        }
        foreach (['selects', 'choices', 'inputs', 'rephrase'] as $group) {
            foreach ($data[$group] ?? [] as $index => $_) {
                if (!isset($afterSeen[$group.':'.$index])) { throw new RuntimeException('M83 AFTER control is not mapped.'); }
            }
            foreach ($source[$group] ?? [] as $index => $_) {
                if (!isset($oldSeen[$group.':'.$index])) { throw new RuntimeException('M83 retained source task/demo was lost.'); }
            }
        }
    }

    private static function assertNavigation(array $before, array $lessons, array $navigation): void
    {
        if (($navigation['schema_version'] ?? null) !== 1
            || array_column($navigation['targets'] ?? [], 'identity') !== self::OWNERS) {
            throw new RuntimeException('M83 verified navigation owner vector differs.');
        }
        foreach (self::OWNERS as $i => $owner) {
            $slot = self::NAVIGATION_SOURCE_INDICES[$i];
            $old = json_decode($before['targets'][$i]['before']['page']['blocks'][$slot]['body'], true, flags: JSON_THROW_ON_ERROR)['items'];
            $items = $lessons[$i]['blocks'][$slot]['data']['items'] ?? [];
            $review = $navigation['targets'][$i];
            $labels = static fn (array $list) => array_map(
                static fn (array $item) => array_intersect_key($item, array_flip(['label', 'current'])), $list);
            if (($review['page_id'] ?? null) !== self::PAGE_IDS[$i] || ($review['items'] ?? null) !== $items
                || $labels($items) !== $labels($old)) {
                throw new RuntimeException('M83 navigation label/current/destination differs from exact reviewed data.');
            }
            foreach ($items as $item) {
                if (empty($item['current']) && preg_match('~^/theory/[a-z0-9-]+(?:/[a-z0-9-]+)*$~D', $item['url'] ?? '') !== 1) {
                    throw new RuntimeException('M83 verified local navigation destination missing.');
                }
                if (!empty($item['current']) && array_key_exists('url', $item)
                    && $item['url'] !== '/theory/'.implode('/', self::ANCESTRIES[$i]).'/'.self::SLUGS[$i]) {
                    throw new RuntimeException('M83 current navigation destination is foreign.');
                }
            }
        }
    }

    private static function plainText(mixed $value): bool
    {
        return is_string($value) && trim($value) !== ''
            && preg_match('/<(?:\\/?[a-z]|[!?])|&(?:#[0-9]+|#x[0-9a-f]+|[a-z][a-z0-9]+);/iu', $value) !== 1;
    }

    public static function comparisonKey(string $value): string { return M76PronounRelations::comparisonKey($value); }
    public static function presentation(object $block, array $data): ?array { return self::release()->presentation($block, $data); }
    public static function validDesign(?array $design): bool { return self::release()->validDesign($design); }
    public static function pointPanels(?array $design): ?array { return self::release()->pointPanels($design); }
    public static function correction(array $item, ?array $design): ?array { return self::release()->correction($item, $design); }
    public static function preserveExcludedTextBlock(object $block, string $caller): ?object { return self::release()->preserveExcludedTextBlock($block, $caller); }
    public static function preserveCoursePage(?Page $page): ?Page { return self::release()->preserveCoursePage($page); }
}
