<?php

namespace App\Support;

use App\Models\Page;
use RuntimeException;

/** Finite teaching-data contract. Native presentation, formatting and grading remain shared. */
final class M90PastPerfectFutureSimpleBasics
{
    public const BASE_SHA = '9607ebefbbf870f4b303bbc05a0c6c0ed0341a17';
    public const VERSION = '1.0.0';
    public const BEFORE = 'docs/content/m90-before-source.v1.json';
    public const SOURCE = 'database/content-patches/m90-past-perfect-future-simple-basics.v1.json';
    public const MASTER = 'docs/content/m90-past-perfect-future-simple-basics.v1.json';
    public const CONTRACT = 'docs/content/theory-sentence-builders/m90-past-perfect-future-simple-basics.v1.json';
    public const CHECKSUMS = 'docs/content/m90-source-checksums.v1.json';
    public const BINDINGS = 'docs/content/m90-native-bindings.v1.json';
    public const NAVIGATION = 'docs/content/m90-navigation-review.v1.json';
    // Reviewed immutable source-only package; apply and browser acceptance remain explicit separate gates.
    public const BEFORE_SHA = 'ee80297ca2211651db0c0e61237208bb50668a9694e1cab4bf75bdbb9745116e';
    public const SOURCE_SHA = '742f0c798e667e7edbf2a52289d5a6729f9b91d06adc4861d81a96c1bf9eca64';
    public const MASTER_SHA = '7623501215030f54de39dc69fd67627f3190c1511fe9c9dc1ee97ae92efe3876';
    public const CONTRACT_SHA = '3c556bd9f70497a8736633f4cd068895ef8fdb7a8b92ee038e3bde271028184c';
    public const CHECKSUMS_SHA = '9e83de7eb76bcfc25ff8d0ceef5d9db5f37ea9524c2b1f597216de557890d3c2';
    public const BINDINGS_SHA = '26dcaebef0147bbf987fa86cb2f60eee5f4cf8b6db99780b7ec93aa5cb12da22';
    public const NAVIGATION_SHA = 'fd0b35208351b21e4158ab84c4e0db4e2b788e67b3f503d0aec68affc3b6b984';
    public const OWNERS = ['Database\\Seeders\\Page_V3\\Tenses\\PastPerfect\\PastPerfectFormsTheorySeeder', 'Database\\Seeders\\Page_V3\\Tenses\\PastPerfect\\PastPerfectNegativesTheorySeeder', 'Database\\Seeders\\Page_V3\\Tenses\\PastPerfect\\PastPerfectQuestionsTheorySeeder', 'Database\\Seeders\\Page_V3\\Tenses\\PastPerfect\\PastPerfectTimeExpressionsTheorySeeder', 'Database\\Seeders\\Page_V3\\FutureForms\\FutureSimple\\FutureSimpleFormsTheorySeeder', 'Database\\Seeders\\Page_V3\\FutureForms\\FutureSimple\\FutureSimpleNegativesTheorySeeder'];
    public const PAGE_IDS = [158, 159, 160, 161, 342, 343];
    public const CATEGORY_IDS = [25, 25, 25, 25, 62, 62];
    public const BANKS = ['Database\\Seeders\\V3\\Polyglot\\PolyglotPastPerfectFormsAllLevelsLessonSeeder', 'Database\\Seeders\\V3\\Polyglot\\PolyglotPastPerfectNegativesAllLevelsLessonSeeder', 'Database\\Seeders\\V3\\Polyglot\\PolyglotPastPerfectQuestionsAllLevelsLessonSeeder', 'Database\\Seeders\\V3\\Polyglot\\PolyglotPastPerfectTimeExpressionsAllLevelsLessonSeeder', 'Database\\Seeders\\V3\\Polyglot\\PolyglotFutureSimpleFormsAllLevelsLessonSeeder', 'Database\\Seeders\\V3\\Polyglot\\PolyglotFutureSimpleNegativesAllLevelsLessonSeeder'];
    public const SLUGS = ['past-perfect-forms', 'past-perfect-negatives', 'past-perfect-questions', 'past-perfect-time-expressions', 'future-simple-forms', 'future-simple-negatives'];
    public const ANCESTRIES = [['tenses', 'past-perfect'], ['tenses', 'past-perfect'], ['tenses', 'past-perfect'], ['tenses', 'past-perfect'], ['maibutni-formy', 'future-simple'], ['maibutni-formy', 'future-simple']];
    public const LEVELS = [null, null, null, null, null, null];
    public const FOCUS_LEVELS = ['A2', 'A2', 'A2', 'A2', 'A1', 'A1'];
    public const DISPLAY_LEVELS = ['A2–B1', 'A2–B1', 'A2–B1', 'A2–B1', 'A1–A2', 'A1–A2'];
    public const CONTINUING_COUNTS = [7, 7, 7, 7, 7, 7];
    public const PRACTICE_SOURCE_INDICES = [5, 5, 5, 5, 5, 5];
    public const NAVIGATION_SOURCE_INDICES = [6, 6, 6, 6, 6, 6];
    public const EXISTING_PRACTICE_UUIDS = ['1d3f76f9-51b0-58fb-94ea-ee34fe20e320', 'cb34f0f6-5995-5e27-8b2b-4d41323c445a', 'd21a1e3d-2a43-566a-99ef-9685e2f74afe', '8602672d-8ade-5551-adc8-c40bd8fa22b4', 'd98377db-0351-538b-b152-fec103a88fe3', 'f2317f57-bd1d-5c99-87a0-4563d0b13fa5'];
    public const RENDERED_TITLES = ['Past Perfect: Forms and Use', 'Past Perfect: Negatives', 'Past Perfect: Questions and Short Answers', 'Past Perfect: Time Expressions', 'Future Simple: Forms and Use', 'Future Simple: Negatives'];

    private static function fail(string $message): never { throw new RuntimeException('M90 '.$message); }

    public static function draftProfile(array $bindings): array
    {
        self::assertBindings($bindings);
        return ['label' => 'M90', 'base_sha' => self::BASE_SHA, 'version' => self::VERSION,
            'marker' => 'm90_native_v1', 'detail_marker' => 'm90_detail_key', 'uuid_prefix' => 'm90:',
            'design' => 'm90-native-v1', 'bindings' => $bindings];
    }

    /** Validate identities independently of an owner-shared legacy uuid_key. */
    public static function assertBindings(array $bindings): void
    {
        if (!array_is_list($bindings) || array_column($bindings, 'identity') !== self::OWNERS) {
            self::fail('exact six-owner binding vector differs.');
        }
        foreach ($bindings as $i => $binding) {
            $url = 'http://gramlyze.loc/theory/'.implode('/', self::ANCESTRIES[$i]).'/'.self::SLUGS[$i];
            $counts = ['selects' => 2, 'choices' => 2, 'inputs' => 2];
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
                || ($binding['existing_practice'] ?? null) !== ['source_index' => 5,
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
        if (($before['content_type'] ?? null) !== 'M90_source_before'
            || ($patch['content_type'] ?? null) !== 'M90_Past_Perfect_Future_Simple_Basics'
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
            $expected = ['selects' => 2, 'choices' => 2, 'inputs' => 2, 'rephrase' => 0];
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
                    if (!$graded || !is_string($task['id'] ?? null) || !str_starts_with($task['id'], 'm90-')) {
                        self::fail('new graded control lacks a stable M90 ID.');
                    }
                    $totals['new']++;
                }
                if (isset($task['id'])) {
                    if (isset($ids[$task['id']])) { self::fail('task ID repeats across owners.'); }
                    $ids[$task['id']] = true;
                }
                $expectedRole = $group === 'inputs' ? 'full_sentence'
                    : ($group === 'choices' ? 'meaning' : 'grammar_form');
                if (($row['assessed_role'] ?? null) !== $expectedRole
                    || ($captured === null && $expectedRole !== 'meaning')) {
                    self::fail('retained form choices and genuinely new meaning controls must remain distinct.');
                }
                self::assertTask($task, $group, $data); $totals['graded']++;
                if ($group === 'inputs') { self::assertSentence($task); $totals['full']++; }
            }
            if (count($oldData['selects'] ?? []) !== 2 || count($oldData['inputs'] ?? []) !== 2
                || count($oldData['choices'] ?? []) !== 0
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
        if (array_values($totals) !== [24, 12, 0, 36, 12, 6]) {
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
