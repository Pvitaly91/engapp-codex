<?php

namespace App\Support;

use App\Models\Page;
use RuntimeException;

/** Finite teaching-data contract. Native presentation, formatting and grading remain shared. */
final class M89PresentPerfectCompletionContinuous
{
    public const BASE_SHA = 'f0181dc0c85444900a8696c7f0c6edbe07b2a5d6';
    public const VERSION = '1.0.0';
    public const BEFORE = 'docs/content/m89-before-source.v1.json';
    public const SOURCE = 'database/content-patches/m89-present-perfect-completion-continuous.v1.json';
    public const MASTER = 'docs/content/m89-present-perfect-completion-continuous.v1.json';
    public const CONTRACT = 'docs/content/theory-sentence-builders/m89-present-perfect-completion-continuous.v1.json';
    public const CHECKSUMS = 'docs/content/m89-source-checksums.v1.json';
    public const BINDINGS = 'docs/content/m89-native-bindings.v1.json';
    public const NAVIGATION = 'docs/content/m89-navigation-review.v1.json';
    // PENDING raw-byte pins; private scaffold must not be registered or used for apply before reviewed freeze.
    public const BEFORE_SHA = 'dbfa9599872b2aa8d508bffeb033a8360ca420f9dfb3c525f5987917d01762c3';
    public const SOURCE_SHA = '9385d853e27fc1b149f910af249f6f6fdd429fb03f346db8dd76b72f18c57ab9';
    public const MASTER_SHA = 'ef03835371e773d84a57e68959af34fa18b4367129a27c650c1ab1bc419b4edc';
    public const CONTRACT_SHA = 'b62821b26f6058667d30d3e935424dd4801bf7058249d8cf8fba3dcd7c900440';
    public const CHECKSUMS_SHA = 'd113d65c60f20e73599acee0ab09a719995059e52b4f2a1e998c48a8c4c742e0';
    public const BINDINGS_SHA = '5d4d9891fe28da5932436d63758cf5d44a50885df239c51c3af7ca278bf6d254';
    public const NAVIGATION_SHA = '690b05c6ed74260621db2985bf1e1a51f74bd8c7e6848e6ca132922f6f3b8ccb';
    public const OWNERS = ['Database\\Seeders\\Page_V3\\Tenses\\PresentPerfect\\PresentPerfectQuestionsTheorySeeder', 'Database\\Seeders\\Page_V3\\Tenses\\PresentPerfect\\PresentPerfectTimeExpressionsTheorySeeder', 'Database\\Seeders\\Page_V3\\Tenses\\PresentPerfectContinuous\\PresentPerfectContinuousFormsTheorySeeder', 'Database\\Seeders\\Page_V3\\Tenses\\PresentPerfectContinuous\\PresentPerfectContinuousNegativesTheorySeeder', 'Database\\Seeders\\Page_V3\\Tenses\\PresentPerfectContinuous\\PresentPerfectContinuousQuestionsTheorySeeder', 'Database\\Seeders\\Page_V3\\Tenses\\PresentPerfectContinuous\\PresentPerfectContinuousTimeExpressionsTheorySeeder'];
    public const PAGE_IDS = [176, 177, 375, 376, 377, 378];
    public const CATEGORY_IDS = [29, 29, 30, 30, 30, 30];
    public const BANKS = ['Database\\Seeders\\V3\\Polyglot\\PolyglotPresentPerfectQuestionsAllLevelsLessonSeeder', 'Database\\Seeders\\V3\\Polyglot\\PolyglotPresentPerfectTimeExpressionsAllLevelsLessonSeeder', 'Database\\Seeders\\V3\\Polyglot\\PolyglotPresentPerfectContinuousFormsAllLevelsLessonSeeder', 'Database\\Seeders\\V3\\Polyglot\\PolyglotPresentPerfectContinuousNegativesAllLevelsLessonSeeder', 'Database\\Seeders\\V3\\Polyglot\\PolyglotPresentPerfectContinuousQuestionsAllLevelsLessonSeeder', 'Database\\Seeders\\V3\\Polyglot\\PolyglotPresentPerfectContinuousTimeExpressionsAllLevelsLessonSeeder'];
    public const SLUGS = ['present-perfect-questions', 'present-perfect-time-expressions', 'present-perfect-continuous-forms', 'present-perfect-continuous-negatives', 'present-perfect-continuous-questions', 'present-perfect-continuous-time-expressions'];
    public const ANCESTRIES = [['tenses', 'present-perfect'], ['tenses', 'present-perfect'], ['tenses', 'present-perfect-continuous'], ['tenses', 'present-perfect-continuous'], ['tenses', 'present-perfect-continuous'], ['tenses', 'present-perfect-continuous']];
    public const LEVELS = [null, null, null, null, null, null];
    public const FOCUS_LEVELS = ['A2', 'A2', 'A2', 'A2', 'A2', 'A2'];
    public const DISPLAY_LEVELS = ['A2–B1', 'A1–C2', 'A2–B1', 'A2–B1', 'A2–B1', 'A2–B1'];
    public const CONTINUING_COUNTS = [8, 8, 8, 8, 8, 8];
    public const PRACTICE_SOURCE_INDICES = [6, 6, 6, 6, 6, 6];
    public const NAVIGATION_SOURCE_INDICES = [7, 7, 7, 7, 7, 7];
    public const EXISTING_PRACTICE_UUIDS = ['e3436e74-b260-5999-8704-7a3bba1b2ae4', '14c98a38-c4fa-5c01-8266-060775eff84d', 'd173c80b-8a13-5645-a6ae-25011d180621', 'a2a2d093-e2b0-5cc8-a0cc-ef53a3f41caa', '7a5d7fe9-cfff-5d34-a294-d3cf61529717', '4b9808fa-816f-55ae-9001-d3f64c7016e6'];
    public const RENDERED_TITLES = ['Present Perfect: Questions and Short Answers', 'Present Perfect: часові маркери', 'Present Perfect Continuous: Forms and Use', 'Present Perfect Continuous: Negatives', 'Present Perfect Continuous: Questions and Short Answers', 'Present Perfect Continuous: Time Expressions'];

    private static function fail(string $message): never { throw new RuntimeException('M89 '.$message); }

    public static function draftProfile(array $bindings): array
    {
        self::assertBindings($bindings);
        return ['label' => 'M89', 'base_sha' => self::BASE_SHA, 'version' => self::VERSION,
            'marker' => 'm89_native_v1', 'detail_marker' => 'm89_detail_key', 'uuid_prefix' => 'm89:',
            'design' => 'm89-native-v1', 'bindings' => $bindings];
    }

    /** Validate identities independently of an owner-shared legacy uuid_key. */
    public static function assertBindings(array $bindings): void
    {
        if (!array_is_list($bindings) || array_column($bindings, 'identity') !== self::OWNERS) {
            self::fail('exact six-owner binding vector differs.');
        }
        foreach ($bindings as $i => $binding) {
            $url = 'http://gramlyze.loc/theory/'.implode('/', self::ANCESTRIES[$i]).'/'.self::SLUGS[$i];
            $counts = ['selects' => 2, 'choices' => $i < 2 ? 2 : 4, 'inputs' => 2];
            if (($binding['page_id'] ?? null) !== self::PAGE_IDS[$i]
                || ($binding['category_id'] ?? null) !== self::CATEGORY_IDS[$i]
                || ($binding['slug'] ?? null) !== self::SLUGS[$i]
                || ($binding['ancestry'] ?? null) !== self::ANCESTRIES[$i]
                || ($binding['local_url'] ?? null) !== $url
                || !array_key_exists('practice_level', $binding) || $binding['practice_level'] !== null
                || ($binding['display_level'] ?? null) !== self::DISPLAY_LEVELS[$i]
                || ($binding['rendered_title'] ?? null) !== self::RENDERED_TITLES[$i]
                || ($binding['continuing_levels'] ?? null) !== array_fill(0, self::CONTINUING_COUNTS[$i], null)
                || ($binding['practice_contract'] ?? null) !== ['counts' => $counts,
                    'sentence_input_indices' => [0, 1]]) {
                self::fail('actual metadata, range or practice contract binding differs.');
            }
            if (($binding['appended_blocks'] ?? null) !== []
                || ($binding['existing_practice'] ?? null) !== ['source_index' => 6,
                    'uuid' => self::EXISTING_PRACTICE_UUIDS[$i], 'level' => null]) {
                self::fail('all six continuing practices must retain exact owner, UUID and slot; no append is authorised.');
            }
        }
    }

    private static function readFrozen(string $path, string $sha, ?string $root = null): array
    {
        $root ??= dirname(__DIR__, 2); $file = $root.'/'.$path;
        if (preg_match('/^[a-f0-9]{64}$/D', $sha) !== 1 || !is_file($file) || is_link($file)
            || !hash_equals($sha, hash_file('sha256', $file))) {
            self::fail('immutable artifact pending or changed: '.$path);
        }
        return json_decode(file_get_contents($file), true, flags: JSON_THROW_ON_ERROR);
    }

    public static function profile(): array
    {
        $bindings = self::readFrozen(self::BINDINGS, self::BINDINGS_SHA);
        if (($bindings['base_sha'] ?? null) !== self::BASE_SHA) { self::fail('binding base differs.'); }
        return self::draftProfile($bindings['bindings'] ?? []) + ['artifacts' => [
            self::BEFORE => self::BEFORE_SHA, self::SOURCE => self::SOURCE_SHA,
            self::MASTER => self::MASTER_SHA, self::CONTRACT => self::CONTRACT_SHA,
            self::CHECKSUMS => self::CHECKSUMS_SHA, self::BINDINGS => self::BINDINGS_SHA,
            self::NAVIGATION => self::NAVIGATION_SHA]];
    }

    private static function release(): TheoryNativeContentRelease { return new TheoryNativeContentRelease(self::profile()); }
    public static function load(?string $root = null): array { return self::release()->load($root); }
    public static function definition(array $lesson, array $before): array { return self::release()->definition($lesson, $before); }
    public static function validate(array $before, array $patch, array $master, ?array $contract = null): void
    {
        $bytes = json_encode($before, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
        if (!hash_equals(self::BEFORE_SHA, hash('sha256', $bytes))) { self::fail('independent source BEFORE differs.'); }
        self::release()->validate($before, $patch, $master, $contract);
        self::assertEditorial($before, $patch, $master);
    }

    public static function assertEditorial(array $before, array $patch, array $master, ?array $navigation = null): void
    {
        $navigation ??= self::readFrozen(self::NAVIGATION, self::NAVIGATION_SHA);
        if (($before['content_type'] ?? null) !== 'M89_source_before'
            || ($patch['content_type'] ?? null) !== 'M89_Present_Perfect_Completion_Continuous'
            || array_column($before['targets'] ?? [], 'identity') !== self::OWNERS
            || array_column($patch['targets'] ?? [], 'identity') !== self::OWNERS
            || array_column($master['lessons'] ?? [], 'identity') !== self::OWNERS
            || array_column($navigation['targets'] ?? [], 'identity') !== self::OWNERS) {
            self::fail('finite author/source/navigation owner vector differs.');
        }
        $ids = []; $totals = ['retained' => 0, 'new' => 0, 'demos' => 0, 'graded' => 0, 'full' => 0, 'mistakes' => 0];
        foreach ($master['lessons'] as $i => $lesson) {
            $old = $before['targets'][$i]['before']; $blocks = $lesson['blocks'] ?? [];
            $count = self::CONTINUING_COUNTS[$i]; $slot = self::PRACTICE_SOURCE_INDICES[$i];
            if (count($old['page']['blocks'] ?? []) !== $count || count($blocks) !== $count
                || ($lesson['preserved_h1'] ?? null) !== self::RENDERED_TITLES[$i]
                || ($lesson['raw_title'] ?? null) !== ($old['page']['title'] ?? null)
                || ($lesson['intended_own_practice_level'] ?? null) !== self::FOCUS_LEVELS[$i]
                || !array_key_exists('technical_block_level', $lesson) || $lesson['technical_block_level'] !== null
                || ($lesson['display_level'] ?? null) !== self::DISPLAY_LEVELS[$i]
                || !array_key_exists('subtitle_text', $lesson) || $lesson['subtitle_text'] !== null
                || empty($lesson['point_detail_decisions'])) {
                self::fail('actual metadata or finite author block vector differs.');
            }
            foreach ($old['page']['blocks'] as $n => $source) {
                $after = $blocks[$n] ?? [];
                if (($after['existing_source_index'] ?? null) !== $n || ($after['type'] ?? null) !== $source['type']
                    || ($after['column'] ?? null) !== $source['column']
                    || !array_key_exists('level', $after) || $after['level'] !== null) {
                    self::fail('continuing native metadata/order changed.');
                }
            }
            foreach ($blocks as $block) {
                if ($block['type'] === 'mistakes-grid') {
                    foreach ($block['data']['items'] ?? [] as $item) {
                        foreach (['wrong_en', 'wrong_ua', 'right_en', 'right_ua', 'note'] as $field) {
                            if (!self::plain($item[$field] ?? null)) { self::fail('paired mistake meaning or supported note is missing.'); }
                        }
                        foreach (['wrong_ua', 'right_ua', 'note'] as $field) {
                            if (!self::ukrainian($item[$field])) { self::fail('mistake meaning or note lacks Ukrainian.'); }
                        }
                        $totals['mistakes']++;
                    }
                }
                if ($block['type'] === 'comparison-table') {
                    foreach ($block['data']['rows'] ?? [] as $row) {
                        if (!is_string($row['en'] ?? null) || trim(strip_tags($row['en'])) === ''
                            || !self::ukrainian($row['ua'] ?? null)) {
                            self::fail('table model and actual Ukrainian translation are required.');
                        }
                    }
                }
            }
            $practices = array_values(array_filter($blocks, static fn ($b) => $b['type'] === 'practice-set'));
            $practice = $blocks[$slot] ?? []; $data = $practice['data'] ?? [];
            if (count($practices) !== 1 || ($practice['type'] ?? null) !== 'practice-set'
                || ($practice['column'] ?? null) !== 'left' || !array_key_exists('level', $practice) || $practice['level'] !== null
                || ($data['linked_practice']['source'] ?? null) !== 'theory_links'
                || ($data['linked_practice']['question_types'] ?? null) !== ['4']
                || ($data['linked_practice']['seeder_classes'] ?? null) !== [self::BANKS[$i]]) {
                self::fail('one exact owner-bound practice/thematic bank is required.');
            }
            $oldData = json_decode($old['page']['blocks'][$slot]['body'], true, flags: JSON_THROW_ON_ERROR);
            if (M26DetailPackage::uuid(self::OWNERS[$i], $old['page']['blocks'][$slot], $slot + 1)
                !== self::EXISTING_PRACTICE_UUIDS[$i]) { self::fail('legacy shared uuid_key was mistaken for an owner identity.'); }
            $expected = ['selects' => 2, 'choices' => $i < 2 ? 2 : 4, 'inputs' => 2, 'rephrase' => 0];
            foreach ($expected as $group => $size) {
                if (count($data[$group] ?? []) !== $size) { self::fail('retained/new practice group count differs.'); }
            }
            $ledger = $master['editorial_review']['task_map'][self::OWNERS[$i]] ?? []; $seen = [];
            foreach ($ledger as $row) {
                $group = $row['after_group'] ?? ''; $n = $row['after_group_index'] ?? null;
                $task = is_int($n) && $n >= 0 ? ($data[$group][$n] ?? null) : null;
                if (!array_key_exists($group, $expected) || !is_array($task) || isset($seen[$group.':'.$n])
                    || ($row['after_block_source_index'] ?? null) !== $slot || ($row['after_task'] ?? null) !== $task
                    || empty($row['classification']) || empty($row['learning_function']) || empty($row['change_reason'])) {
                    self::fail('literal task ledger is stale, repeated or incomplete.');
                }
                $seen[$group.':'.$n] = true; $captured = $oldData[$group][$n] ?? null;
                $graded = array_key_exists('answer', $task);
                if (($row['graded'] ?? null) !== $graded) { self::fail('demo and graded ledger states differ.'); }
                if ($captured !== null) {
                    if (($row['old_task'] ?? null) !== $captured || ($row['old_group'] ?? null) !== $group
                        || ($row['old_group_index'] ?? null) !== $n || ($row['old_block_source_index'] ?? null) !== $slot) {
                        self::fail('exact captured OLD-to-NEW task mapping differs.');
                    }
                    foreach (['id', 'source_index'] as $field) {
                        if (array_key_exists($field, $captured) !== array_key_exists($field, $task)
                            || (array_key_exists($field, $captured) && $captured[$field] !== $task[$field])) {
                            self::fail('retained task identity or absence changed.');
                        }
                    }
                    if (array_key_exists('answer', $captured) || !empty($captured['accepted'])) {
                        $canonical = $captured['answer'] ?? $captured['accepted'][0];
                        if (!$graded || $task['answer'] !== $canonical) { self::fail('retained canonical answer or accepted-only canonical changed.'); }
                        if (array_key_exists('accepted', $captured) && $task['accepted'] !== $captured['accepted']) {
                            self::fail('captured accepted-only/full-form alias array or order changed.');
                        }
                        foreach ($captured['accepted'] ?? [$captured['answer']] as $alias) {
                            if (!in_array($alias, $task['accepted'] ?? [], true)) { self::fail('retained accepted alias was lost.'); }
                        }
                        $totals['retained']++;
                    } else {
                        if ($group !== 'rephrase' || $n !== 0 || $graded || isset($task['accepted'])
                            || !str_contains($task['example_original'] ?? '', $captured['example_original'] ?? "\0")
                            || !str_contains($task['example_target'] ?? '', $captured['example_target'] ?? "\0")) {
                            self::fail('ungraded source demonstration was lost, replaced or scored.');
                        }
                        $totals['demos']++; continue;
                    }
                } else {
                    foreach (['old_task', 'old_group', 'old_group_index', 'old_block_source_index'] as $field) {
                        if (!array_key_exists($field, $row) || $row[$field] !== null) { self::fail('new control invents a source identity.'); }
                    }
                    if (!$graded || !is_string($task['id'] ?? null) || !str_starts_with($task['id'], 'm89-')) {
                        self::fail('new graded control lacks a stable M89 ID.');
                    }
                    $totals['new']++;
                }
                if (isset($task['id'])) {
                    if (isset($ids[$task['id']])) { self::fail('task ID repeats across owners.'); }
                    $ids[$task['id']] = true;
                }
                $expectedRole = $group === 'inputs' ? 'full_sentence'
                    : ($group === 'choices' && ($i < 2 || $n >= 2) ? 'meaning' : 'grammar_form');
                if (($row['assessed_role'] ?? null) !== $expectedRole
                    || ($captured === null && $expectedRole !== 'meaning')) {
                    self::fail('retained form choices and genuinely new meaning controls must remain distinct.');
                }
                self::assertTask($task, $group, $data); $totals['graded']++;
                if ($group === 'inputs') { self::assertSentence($task); $totals['full']++; }
            }
            if (count($oldData['selects'] ?? []) !== 2 || count($oldData['inputs'] ?? []) !== 2
                || count($oldData['choices'] ?? []) !== ($i < 2 ? 0 : 2)
                || ($data['options'] ?? null) !== ($oldData['options'] ?? null)
                || (array_key_exists('choice_options', $oldData) && ($data['choice_options'] ?? null) !== $oldData['choice_options'])
                || (array_key_exists('linked_practice', $oldData) && ($data['linked_practice'] ?? null) !== $oldData['linked_practice'])) {
                self::fail('captured source groups, global options or existing thematic link changed.');
            }
            if (count($seen) !== array_sum($expected)) { self::fail('practice controls and task ledger do not cover the same exact positions.'); }
            $navSlot = self::NAVIGATION_SOURCE_INDICES[$i]; $nav = $navigation['targets'][$i];
            $oldNav = json_decode($old['page']['blocks'][$navSlot]['body'], true, flags: JSON_THROW_ON_ERROR);
            $items = $blocks[$navSlot]['data']['items'];
            $labels = static fn ($xs) => array_map(static fn ($x) => array_intersect_key($x, array_flip(['label', 'current'])), $xs);
            if (($nav['items'] ?? null) !== $items || $labels($items) !== $labels($oldNav['items'])) {
                self::fail('footer labels, current flags or order changed.');
            }
            foreach ($items as $item) {
                if (empty($item['current']) && preg_match('~^/theory/[a-z0-9-]+(?:/[a-z0-9-]+)*$~D', $item['url'] ?? '') !== 1) {
                    self::fail('canonical footer destination is missing.');
                }
            }
        }
        if (array_values($totals) !== [32, 12, 0, 44, 12, 22]) {
            self::fail('retained controls, new controls, demos, full inputs or paired mistakes differ.');
        }
    }

    private static function assertTask(array $task, string $group, array $data): void
    {
        if (isset($task['strict']) || isset($task['punctuation_sensitive'])
            || !is_array($task['accepted'] ?? null) || !in_array($task['answer'] ?? null, $task['accepted'], true)) {
            self::fail('native accepted-answer contract differs.');
        }
        $visible = $group === 'inputs' ? ($task['after'] ?? null)
            : ($group === 'rephrase' ? ($task['original'] ?? null) : ($task['label'] ?? null));
        if (!self::ukrainian($visible) || preg_match('/\{a[0-9]+\}/u', $visible) === 1) {
            self::fail('task criterion must be in its actually rendered field.');
        }
        foreach ($task['feedback'] ?? [] as $key => $value) {
            if ($key !== mb_strtolower(trim($key)) || !self::plain($value) || !self::ukrainian($value)) {
                self::fail('feedback must be plain Ukrainian explanatory text with native keys.');
            }
        }
        foreach ($task['accepted'] as $answer) {
            if (!self::plain($answer) || !self::plain($task['feedback'][mb_strtolower(trim($answer))] ?? null)) {
                self::fail('accepted answer lacks plain per-alias feedback.');
            }
        }
        if (in_array($group, ['selects', 'choices'], true)) {
            $options = $task['options'] ?? ($group === 'selects' ? ($data['options'] ?? []) : ($data['choice_options'] ?? ['a', 'b']));
            $keys = array_map(self::comparisonKey(...), $options); $correct = 0;
            if (count($options) < 2 || count(array_unique($keys)) !== count($keys) || in_array('', $keys, true)) {
                self::fail('displayed options are empty or collide under native normalization.');
            }
            if (count(array_filter($options, static fn ($o) => preg_match('/^[a-zа-яіїєґ]$/iu', $o) === 1)) === count($options)
                && $task['accepted'] !== [$task['answer']]) {
                self::fail('semantic candidate identifiers cannot acquire English contraction aliases.');
            }
            foreach ($options as $option) {
                if (!self::plain($option) || !self::plain($task['feedback'][mb_strtolower(trim($option))] ?? null)) {
                    self::fail('displayed option lacks plain feedback.');
                }
                foreach ($task['accepted'] as $answer) {
                    if (in_array(self::comparisonKey($option), array_map(self::comparisonKey(...), AcceptedAnswerVariants::for($answer)), true)) {
                        $correct++; break;
                    }
                }
            }
            if ($correct !== 1) { self::fail('displayed options require exactly one native-correct candidate.'); }
        }
    }

    private static function assertSentence(array $task): void
    {
        $tokens = array_map('trim', explode('/', $task['before'] ?? ''));
        $remaining = $tokens; $tail = $task['answer'];
        if (count($tokens) < 2 || self::comparisonKey(implode(' ', $tokens)) === self::comparisonKey($tail)
            || !self::ukrainian($task['after'] ?? null)) {
            self::fail('full sentence needs shuffled groups and a visible Ukrainian condition.');
        }
        foreach ($tokens as $token) {
            if (!self::plain($token) || count(preg_split('/\s+/u', $token)) > 3) {
                self::fail('native token groups must contain one to three words.');
            }
        }
        while ($tail !== '' && $remaining !== []) {
            $found = false;
            foreach ($remaining as $n => $token) {
                if ($tail === $token || str_starts_with($tail, $token.' ')) {
                    $tail = $tail === $token ? '' : substr($tail, strlen($token) + 1);
                    unset($remaining[$n]); $found = true; break;
                }
            }
            if (!$found) { self::fail('literal token bank cannot compose the complete canonical sentence.'); }
        }
        if ($tail !== '' || $remaining !== []) { self::fail('token multiset loses or repeats a word group.'); }
    }

    private static function ukrainian(mixed $value): bool
    {
        return is_string($value) && preg_match('/[\x{0400}-\x{04FF}]/u', $value) === 1;
    }
    private static function plain(mixed $value): bool
    {
        return is_string($value) && trim($value) !== ''
            && preg_match('/<[^>]+>|\{a[0-9]+\}|&(?:#[0-9]+|#x[0-9a-f]+|[a-z][a-z0-9]+);/iu', $value) !== 1;
    }
    public static function comparisonKey(string $value): string { return M76PronounRelations::comparisonKey($value); }
    public static function presentation(object $block, array $data): ?array { return self::release()->presentation($block, $data); }
    public static function validDesign(?array $design): bool { return self::release()->validDesign($design); }
    public static function pointPanels(?array $design): ?array { return self::release()->pointPanels($design); }
    public static function correction(array $item, ?array $design): ?array { return self::release()->correction($item, $design); }
    public static function preserveExcludedTextBlock(object $block, string $caller): ?object { return self::release()->preserveExcludedTextBlock($block, $caller); }
    public static function preserveCoursePage(?Page $page): ?Page { return self::release()->preserveCoursePage($page); }
}
