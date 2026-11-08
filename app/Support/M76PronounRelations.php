<?php

namespace App\Support;

use App\Models\Page;
use RuntimeException;

/** Finite M76 teaching-data policy. The existing native renderer/grader owns presentation. */
final class M76PronounRelations
{
    public const BASE_SHA = 'e0720938bad114c7e43e8e6efafa8355c1993fc1';
    public const BEFORE = 'docs/content/m76-before-source.v1.json';
    public const SOURCE = 'database/content-patches/m76-relative-each-reciprocal.v1.0.1.json';
    public const MASTER = 'docs/content/m76-relative-each-reciprocal.v1.0.1.json';
    public const CONTRACT = 'docs/content/theory-sentence-builders/m76-relative-each-reciprocal.v1.json';
    public const CHECKSUMS = 'docs/content/m76-source-checksums.v1.0.1.json';
    // Raw UTF-8/LF teaching artifact pins, fixed after independent editorial review.
    public const BEFORE_SHA = '08184ba09866bbf50d7f26a497ffa509c53da2f125f65c33ab7da4cd4854caad';
    public const SOURCE_SHA = 'e5320ab32be7f15684797173a46f68892fc492e2821b2c41f6306cd730c8e5fc';
    public const MASTER_SHA = '33fa160344fcc980d8d980aadf19e0db03212b1a54a443100e2f24c3bc033738';
    public const CONTRACT_SHA = '77abdac91ab3fa6eedba9b19ae4ccf241f73c8ac004e874b1255fd6e48419789';
    public const CHECKSUMS_SHA = '3a8d147f12436087489fcba478f9fcda6442fdf28c3c4c5b550909b0f64f2e6c';
    public const OWNERS = [
        'Database\\Seeders\\Page_V3\\PronounsDemonstratives\\PronounsDemonstrativesRelativePronounsTheorySeeder',
        'Database\\Seeders\\Page_V3\\PronounsDemonstratives\\PronounsDemonstrativesEachEveryAllTheorySeeder',
        'Database\\Seeders\\Page_V3\\PronounsDemonstratives\\PronounsDemonstrativesReciprocalPronounsTheorySeeder',
    ];
    public const SLUGS = ['relative-pronouns-who-which-that-whose', 'each-every-all', 'reciprocal-pronouns-each-other-one-another'];
    public const PAGE_IDS = [129, 121, 127];
    public const CATEGORY_IDS = [20, 20, 20];
    public const ANCESTRIES = [['zaimennyky-ta-vkazivni-slova'], ['zaimennyky-ta-vkazivni-slova'], ['zaimennyky-ta-vkazivni-slova']];
    public const LEVELS = [null, null, null];
    public const DISPLAY_LEVELS = ['B1–B2', 'A2–B1', 'A2–B1'];
    public const CONTINUING_COUNTS = [12, 12, 12];
    public const PRACTICE_SOURCE_INDICES = [10, 10, 9];
    public const PRACTICE_UUIDS = ['9391a281-14c4-5d53-aea8-d14f4a73b3d5', 'c49b6f66-a028-59a7-819b-e73cc565709c', '44d5c5f4-df97-5ac1-aa6d-ccc490091d7a'];
    public const RENDERED_TITLES = ['Відносні займенники', 'Each / Every / All', 'Reciprocal pronouns'];
    public const GRADED_COUNTS = [16, 16, 14];
    public const DEMO_COUNTS = [1, 1, 0];
    public const SENTENCE_INPUT_INDICES = [[4, 5], [4, 5], [0, 1]];
    public const AFTER_TYPES = [
        ['hero', 'forms-grid', 'usage-panels', 'usage-panels', 'usage-panels', 'usage-panels', 'usage-panels', 'usage-panels', 'mistakes-grid', 'summary-list', 'practice-set', 'navigation-chips'],
        ['hero', 'comparison-table', 'usage-panels', 'usage-panels', 'usage-panels', 'usage-panels', 'usage-panels', 'usage-panels', 'mistakes-grid', 'summary-list', 'practice-set', 'navigation-chips'],
        ['hero', 'forms-grid', 'usage-panels', 'usage-panels', 'usage-panels', 'comparison-table', 'usage-panels', 'mistakes-grid', 'summary-list', 'practice-set', 'forms-grid', 'navigation-chips'],
    ];
    private const NEW_IDS = [
        ['choices' => ['m76-relative-meaning-call-roles', 'm76-relative-meaning-which-reference'], 'inputs' => ['m76-relative-sentence-object-he', 'm76-relative-sentence-whose-modifier']],
        ['choices' => ['m76-each-meaning-book', 'm76-each-meaning-not-all'], 'inputs' => ['m76-each-sentence-ticket', 'm76-each-sentence-books-table']],
        ['choices' => ['m76-reciprocal-meaning-negative', 'm76-reciprocal-meaning-request'], 'inputs' => ['m76-reciprocal-sentence-listen', 'm76-reciprocal-sentence-names']],
    ];
    private const RECIPROCAL_SELECTS = [
        'They love (each other / themselves).', 'We helped (each other / ourselves) with homework.',
        "I know (each other's / her) address.", 'The team supported (one another / oneself).', 'She talks to (herself / each other) when nervous.',
    ];
    private const RECIPROCAL_REPHRASES = [
        'They met themselves at the party.', 'We respect each others opinions.',
        'She and I understand each other very good.', 'The students helped themself.', 'He talked to each other.',
    ];
    /** Reviewed raw-block digest, exact secondary keys, original heading. No live-source inference. */
    private const RECIPROCAL_BOXES = [
        0 => ['7b1c4cb000f792de810f4ffba973be1a18b794571731ec0bf76911c8749b4819', ['layout', 'hero_image', 'content'], 'Reciprocal Pronouns — Взаємні займенники'],
        1 => ['af5115b026f5b9437cc51746924e73686c8fadc5f8518100083893e12134704a', ['layout', 'columns'], null],
        2 => ['184e267a04de5df33bf23caccafacebfed77f3825d5fa7bb26c47449a5516f76', ['layout', 'content'], 'Сучасне використання'],
        3 => ['f66d691e3182e72ea3302431b56e00c013e12b7575dd81d949d33c92564c67b6', ['layout', 'columns'], null],
        4 => ['1d7fdd8b6bb3c4187f0911a0864438ec8295b0d5541f64cfa92bc426fb30f015', ['layout', 'content'], 'Присвійні форми'],
        6 => ['cce0936ebe3376a0f8e1c7e19eaacf9d24c49e27363468d99cbb1b436aa8039e', ['layout', 'content'], 'Типові вирази з Each Other'],
        7 => ['b931a2d08ee1296d6ea2a9e4b03d366f07d1dcf38d67f9cc8f32e82d0e961a8e', ['layout', 'mistakes'], null],
        8 => ['e6508d65fd7de7f5f6bbe84c394c51287b106b23582d59851f8f484fb7162d95', ['layout', 'summary_points'], null],
        9 => ['4fac8c2169637cd2e45081e68593eb50f8e8f19959c943b49b0c422886dbb7c0', ['layout', 'exercises'], null],
        10 => ['2f0024dfd07595fb7a6fd2b10fff4945e8511f37c2c684f89b71984c36e68a6e', ['layout', 'links'], null],
    ];

    public static function profile(): array
    {
        $bindings = [];
        foreach (self::OWNERS as $i => $owner) {
            $binding = ['identity' => $owner, 'slug' => self::SLUGS[$i], 'page_id' => self::PAGE_IDS[$i],
                'category_id' => self::CATEGORY_IDS[$i], 'ancestry' => self::ANCESTRIES[$i],
                'local_url' => 'http://gramlyze.loc/theory/zaimennyky-ta-vkazivni-slova/'.self::SLUGS[$i],
                'practice_level' => null, 'display_level' => self::DISPLAY_LEVELS[$i], 'rendered_title' => self::RENDERED_TITLES[$i],
                'continuing_levels' => array_fill(0, 12, null), 'appended_blocks' => [],
                'existing_practice' => ['source_index' => self::PRACTICE_SOURCE_INDICES[$i], 'uuid' => self::PRACTICE_UUIDS[$i], 'level' => null],
                'practice_contract' => ['counts' => ['selects' => 5, 'choices' => 2, 'inputs' => $i === 2 ? 2 : 6],
                    'sentence_input_indices' => self::SENTENCE_INPUT_INDICES[$i]]];
            if ($i === 1) {
                $binding['before_subtitle'] = ['mode' => 'captured-plain', 'sha256' => '7947674a32a984145c6f232a9d338f0bc9332937ff4bf08aa00e9af4b3a752ad',
                    'raw_page_title' => 'Each / Every / All', 'extracted_title' => 'Each / Every / All', 'h1' => 'Each / Every / All'];
                $binding['preserved_plain_subtitle'] = ['sha256' => '7b2fb90415ce01d790bf5802fc3c67add56d863ea88e4d06708f71a29aa16843',
                    'extracted_title' => 'Each / Every / All', 'h1' => 'Each / Every / All'];
            }
            if ($i === 2) {
                foreach (self::RECIPROCAL_BOXES as $slot => [$sha, $remove, $heading]) {
                    $column = $slot === 0 ? 'header' : ($slot === 10 ? 'footer' : 'left');
                    $binding['continuing_metadata'][$slot] = ['column' => $column,
                        'before' => ['type' => 'box', 'heading' => $heading, 'level' => null],
                        'after' => ['type' => self::AFTER_TYPES[$i][$slot], 'heading' => null, 'level' => null]];
                    $binding['preserved_native_metadata'][$slot] = ['column' => $column, 'level' => null];
                    $binding['legacy_box_projection'][$slot] = ['before_sha256' => $sha, 'remove_fields' => $remove];
                }
            }
            $bindings[] = $binding;
        }
        return ['label' => 'M76', 'base_sha' => self::BASE_SHA, 'version' => '1.0.1', 'marker' => 'm76_native_v1',
            'detail_marker' => 'm76_detail_key', 'uuid_prefix' => 'm76:', 'design' => 'm76-native-v1',
            'artifacts' => [self::BEFORE => self::BEFORE_SHA, self::SOURCE => self::SOURCE_SHA, self::MASTER => self::MASTER_SHA,
                self::CONTRACT => self::CONTRACT_SHA, self::CHECKSUMS => self::CHECKSUMS_SHA], 'bindings' => $bindings];
    }

    private static function release(): TheoryNativeContentRelease { return new TheoryNativeContentRelease(self::profile()); }
    public static function load(?string $root = null): array { return self::release()->load($root); }
    public static function definition(array $lesson, array $before): array { return self::release()->definition($lesson, $before); }

    public static function validate(array $before, array $patch, array $master, ?array $contract = null): void
    {
        if (($before['content_type'] ?? null) !== 'M76_source_before' || ($patch['content_type'] ?? null) !== 'M76_Pronoun_Relations') {
            throw new RuntimeException('M76 exact provenance differs.');
        }
        self::release()->validate($before, $patch, $master, $contract);
        foreach ($master['lessons'] as $i => $lesson) {
            $old = $before['targets'][$i]['before']['page']['blocks'];
            if (array_column($lesson['blocks'], 'type') !== self::AFTER_TYPES[$i]
                || array_column($old, 'column') !== array_column($lesson['blocks'], 'column')
                || ($i < 2 && array_column($old, 'type') !== self::AFTER_TYPES[$i])
                || ($lesson['preserved_h1'] ?? null) !== self::RENDERED_TITLES[$i]
                || ($lesson['raw_title'] ?? null) !== $before['targets'][$i]['before']['page']['title']
                || ($lesson['intended_own_practice_level'] ?? null) !== ($i === 0 ? 'B1' : 'A2')) {
                throw new RuntimeException('M76 continuing native components, title or pedagogical level differs.');
            }
            $data = $lesson['blocks'][self::PRACTICE_SOURCE_INDICES[$i]]['data'];
            self::assertPractice($data, $i);
            self::assertOldTasks($old[self::PRACTICE_SOURCE_INDICES[$i]], $data, $i);
            self::assertNavigation($old, $lesson['blocks'], $i);
        }
    }

    private static function assertPractice(array $data, int $owner): void
    {
        $graded = $demos = 0;
        foreach (['selects', 'choices', 'inputs', 'rephrase'] as $group) {
            foreach ($data[$group] ?? [] as $n => $task) {
                if (!empty($task['example_original'])) {
                    if ($owner === 2 || $group !== 'rephrase' || $n !== 0 || isset($task['answer']) || isset($task['accepted']) || empty($task['example_target'])) {
                        throw new RuntimeException('M76 old demo identity or ungraded state differs.');
                    }
                    ++$demos;
                    continue;
                }
                ++$graded;
                if (!is_string($task['answer'] ?? null) || !is_array($task['accepted'] ?? null) || $task['accepted'] === []
                    || !in_array($task['answer'], $task['accepted'], true) || isset($task['punctuation_sensitive']) || isset($task['strict'])) {
                    throw new RuntimeException('M76 canonical/accepted answers must use the unchanged native comparison.');
                }
                foreach ($task['accepted'] as $answer) {
                    if (!is_string($answer) || self::comparisonKey($answer) === ''
                        || empty($task['feedback'][mb_strtolower(trim($answer))])) {
                        throw new RuntimeException('M76 nonempty accepted-answer literal feedback is required.');
                    }
                }
                if (in_array($group, ['selects', 'choices'], true)) {
                    $options = $task['options'] ?? []; $keys = array_map(self::comparisonKey(...), $options);
                    if (count($options) < 2 || !in_array($task['answer'], $options, true)
                        || count(array_unique($keys)) !== count($keys) || in_array('', $keys, true)
                        || array_diff($task['accepted'], $options) !== []) {
                        throw new RuntimeException('M76 finite displayed options collide or omit a correct form.');
                    }
                    foreach ($options as $option) {
                        if (empty($task['feedback'][mb_strtolower(trim($option))])) { throw new RuntimeException('M76 displayed-option feedback is missing.'); }
                    }
                }
                foreach ($task['feedback'] ?? [] as $key => $text) {
                    if (!is_string($key) || $key === '' || $key !== mb_strtolower(trim($key)) || !is_string($text) || trim($text) === ''
                        || preg_match('/<(?:\/?[a-z]|[!?])|&(?:#[0-9]+|#x[0-9a-f]+|[a-z][a-z0-9]+);/iu', $text)) {
                        throw new RuntimeException('M76 feedback must be plain text with literal lower-case keys.');
                    }
                }
            }
        }
        if ($graded !== self::GRADED_COUNTS[$owner] || $demos !== self::DEMO_COUNTS[$owner]
            || count($data['rephrase'] ?? []) !== ($owner === 2 ? 5 : 4)
            || ($data['linked_practice']['title'] ?? null) !== 'Вправа 5. Побудуй речення') {
            throw new RuntimeException('M76 exact retained/transferred graded and demo counts differ.');
        }
        foreach (self::NEW_IDS[$owner] as $group => $ids) {
            $indices = $group === 'inputs' ? self::SENTENCE_INPUT_INDICES[$owner] : [0, 1];
            foreach ($indices as $offset => $index) {
                if (($data[$group][$index]['id'] ?? null) !== $ids[$offset] || array_key_exists('source_index', $data[$group][$index])) {
                    throw new RuntimeException('M76 four new task identities differ.');
                }
            }
        }
        if ($owner === 0 && (($data['selects'][0]['accepted'] ?? []) !== ['who', 'that']
            || ($data['selects'][1]['accepted'] ?? []) !== ['which', 'that']
            || ($data['selects'][3]['accepted'] ?? []) !== ['that', 'which']
            || ($data['inputs'][3]['accepted'] ?? []) !== ['that', 'Займенник не потрібен'])) {
            throw new RuntimeException('M76 Relative alternatives or nonempty omission changed.');
        }
        if ($owner === 1 && (($data['selects'][0]['accepted'] ?? []) !== ['each', 'every']
            || ($data['selects'][2]['accepted'] ?? []) !== ['every', 'each']
            || ($data['selects'][4]['accepted'] ?? []) !== ['all', 'each']
            || ($data['inputs'][1]['accepted'] ?? []) !== ['All', 'All of'])) {
            throw new RuntimeException('M76 Each natural alternatives changed.');
        }
        if ($owner === 2 && (($data['rephrase'][1]['answer'] ?? null) !== 'речення Б'
            || ($data['rephrase'][1]['accepted'] ?? []) !== ['речення Б', 'Б'])) {
            throw new RuntimeException('M76 reciprocal apostrophe function must remain a nonempty semantic transfer.');
        }
    }

    private static function assertOldTasks(array $oldBlock, array $data, int $owner): void
    {
        if ($owner < 2) {
            $old = json_decode($oldBlock['body'], true, flags: JSON_THROW_ON_ERROR);
            foreach (['selects' => 5, 'inputs' => 4, 'rephrase' => 4] as $group => $count) {
                if (count($old[$group] ?? []) !== $count) { throw new RuntimeException('M76 captured old task coordinates differ.'); }
                foreach ($old[$group] as $n => $task) { self::assertTaskIdentity($task, $data[$group][$n]); }
            }
            return;
        }
        if (($oldBlock['exercises'][0]['items'] ?? null) !== self::RECIPROCAL_SELECTS
            || ($oldBlock['exercises'][1]['items'] ?? null) !== self::RECIPROCAL_REPHRASES
            || count($oldBlock['exercises']) !== 2) { throw new RuntimeException('M76 exact ten static reciprocal source functions differ.'); }
        foreach (['selects' => self::RECIPROCAL_SELECTS, 'rephrase' => self::RECIPROCAL_REPHRASES] as $group => $stimuli) {
            foreach ($stimuli as $n => $stimulus) {
                // These reviewed old items are strings, not invented old graded tasks or IDs.
                self::assertTaskIdentity([], $data[$group][$n]);
                if ($group === 'rephrase' && !str_contains($data[$group][$n]['original'] ?? '', $stimulus)) {
                    throw new RuntimeException('M76 reciprocal static correction moved or lost its original stimulus.');
                }
            }
        }
    }

    private static function assertTaskIdentity(array $old, array $new): void
    {
        foreach (['id', 'source_index'] as $field) {
            if (array_key_exists($field, $old) !== array_key_exists($field, $new)
                || (array_key_exists($field, $old) && $old[$field] !== $new[$field])) {
                throw new RuntimeException('M76 retained/transferred task identity changed.');
            }
        }
    }

    private static function assertNavigation(array $old, array $blocks, int $owner): void
    {
        $oldNav = json_decode($old[11]['body'], true, flags: JSON_THROW_ON_ERROR)['items'];
        $newNav = $blocks[11]['data']['items'];
        $labels = static fn (array $items): array => array_map(static fn ($item) => array_intersect_key($item, array_flip(['label', 'current'])), $items);
        if (count($newNav) !== 9 || $labels($oldNav) !== $labels($newNav)) { throw new RuntimeException('M76 nine navigation labels/current flags differ.'); }
        if ($owner === 2) {
            $links = $blocks[10]['data']['items'];
            if ($blocks[10]['type'] !== 'forms-grid' || $blocks[10]['column'] !== 'footer' || count($links) !== 2
                || array_column($old[10]['links'], 'title') !== array_column($links, 'label')
                || array_column($links, 'url') !== ['/theory/zaimennyky-ta-vkazivni-slova/reflexive-pronouns-myself-yourself-themselves', '/theory/zaimennyky-ta-vkazivni-slova/each-every-all']
                || array_filter($links, static fn ($link) => ($link['current'] ?? null) !== false
                    || !is_string($link['title'] ?? null) || trim($link['title']) === ''
                    || !is_string($link['subtitle'] ?? null) || trim($link['subtitle']) === '')
                || count(array_filter($blocks, static fn ($block) => $block['type'] === 'navigation-chips')) !== 1) {
                throw new RuntimeException('M76 two continuing legacy navigation destinations differ.');
            }
        }
    }

    /** Test-only alphabet mirrors the current native comparison; it never replaces grading. */
    public static function comparisonKey(string $value): string
    {
        $value = \Normalizer::normalize($value, \Normalizer::FORM_KD);
        if (!is_string($value)) { throw new RuntimeException('M76 comparison alphabet unavailable.'); }
        $value = preg_replace('/[\x{0300}-\x{036F}]/u', '', mb_strtolower($value));
        $value = preg_replace('/[\x{2018}\x{2019}\x{201B}\x{2032}\x{0060}´ʼ\']/u', '', $value);
        return trim(preg_replace('/\s+/u', ' ', preg_replace('/[^\p{L}\p{N}]+/u', ' ', $value)));
    }

    public static function presentation(object $block, array $data): ?array { return self::release()->presentation($block, $data); }
    public static function validDesign(?array $design): bool { return self::release()->validDesign($design); }
    public static function pointPanels(?array $design): ?array { return self::release()->pointPanels($design); }
    public static function correction(array $item, ?array $design): ?array { return self::release()->correction($item, $design); }
    public static function preserveExcludedTextBlock(object $block, string $caller): ?object { return self::release()->preserveExcludedTextBlock($block, $caller); }
    public static function preserveCoursePage(?Page $page): ?Page { return self::release()->preserveCoursePage($page); }
}
