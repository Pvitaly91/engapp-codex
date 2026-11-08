<?php

namespace App\Support;

use App\Models\Page;
use RuntimeException;

/** Finite teaching-data contract. Native presentation, formatting and grading remain shared. */
final class M93FutureCompletionDemonstrativesSomeAny
{
    public const BASE_SHA = '6d3003884fb8d15e870285c1ac0875995ff84fc8';
    public const VERSION = '1.0.0';
    public const BEFORE = 'docs/content/m93-before-source.v1.json';
    public const SOURCE = 'database/content-patches/m93-future-completion-demonstratives-some-any.v1.json';
    public const MASTER = 'docs/content/m93-future-completion-demonstratives-some-any.v1.json';
    public const CONTRACT = 'docs/content/theory-sentence-builders/m93-future-completion-demonstratives-some-any.v1.json';
    public const CHECKSUMS = 'docs/content/m93-source-checksums.v1.json';
    public const BINDINGS = 'docs/content/m93-native-bindings.v1.json';
    public const NAVIGATION = 'docs/content/m93-navigation-review.v1.json';
    // PRIVATE DRAFT: immutable publication hashes remain pending; no activation/apply authority.
    public const BEFORE_SHA = 'e0752e23219777b107b18e337f0fe9fbf6965f2f96e792c07bfc1ea71af63812';
    public const SOURCE_SHA = '58beaf325626784858765db31920c5dd660a49e233f6acb2d4c2d48c72d877a5';
    public const MASTER_SHA = 'dc735717145312d77d90a71ac9552b2773d3cf63cb3fd2958cdfec8bb42a4f80';
    public const CONTRACT_SHA = '8a03e3dd1de3a1bed77681ff094251e2991e351988d3decdead2769885890acf';
    public const CHECKSUMS_SHA = '21f8df95ff83e11b16e0757896a7f7d637dac3213fc66137246d8dafdf54d0c6';
    public const BINDINGS_SHA = '245aa6b8fa2a60b8a3a27ab80ad118306234f047b0091b012d9b335b1b51c801';
    public const NAVIGATION_SHA = '8963eca7ec218e8423333ed19afcd3295fd60341cc8395ddd75511490a45e2fb';
    public const OWNERS = ['Database\\Seeders\\Page_V3\\FutureForms\\FuturePerfectContinuous\\FuturePerfectContinuousQuestionsTheorySeeder', 'Database\\Seeders\\Page_V3\\FutureForms\\FuturePerfectContinuous\\FuturePerfectContinuousTimeExpressionsTheorySeeder', 'Database\\Seeders\\Page_V3\\PronounsDemonstratives\\PronounsDemonstrativesThisThatTheseThoseTheorySeeder', 'Database\\Seeders\\Page_V3\\Articles\\SomeAny\\SomeAnyPlacesTheorySeeder', 'Database\\Seeders\\Page_V3\\Articles\\SomeAny\\SomeAnyPeopleTheorySeeder', 'Database\\Seeders\\Page_V3\\Articles\\SomeAny\\SomeAnyThingsTheorySeeder'];
    public const PAGE_IDS = [70, 71, 130, 264, 263, 265];
    public const CATEGORY_IDS = [14, 14, 20, 50, 50, 50];
    public const BANKS = ['Database\\Seeders\\V3\\Polyglot\\PolyglotFuturePerfectContinuousQuestionsAllLevelsLessonSeeder', 'Database\\Seeders\\V3\\Polyglot\\PolyglotFuturePerfectContinuousTimeExpressionsAllLevelsLessonSeeder', 'Database\\Seeders\\V3\\Polyglot\\PolyglotDemonstrativesThisThatTheseThoseAllLevelsLessonSeeder', 'Database\\Seeders\\V3\\Polyglot\\PolyglotSomeAnyPlacesAllLevelsLessonSeeder', 'Database\\Seeders\\V3\\Polyglot\\PolyglotSomeAnyPeopleAllLevelsLessonSeeder', 'Database\\Seeders\\V3\\Polyglot\\PolyglotSomeAnyThingsAllLevelsLessonSeeder'];
    public const SLUGS = ['future-perfect-continuous-questions', 'future-perfect-continuous-time-expressions', 'demonstratives-this-that-these-those', 'theory-some-any-places', 'theory-some-any-people', 'theory-some-any-things'];
    public const ANCESTRIES = [['maibutni-formy', 'future-perfect-continuous'], ['maibutni-formy', 'future-perfect-continuous'], ['zaimennyky-ta-vkazivni-slova'], ['some-any'], ['some-any'], ['some-any']];
    public const LEVELS = [null, null, 'A1', null, 'A2', 'A2'];
    public const FOCUS_LEVELS = ['B2', 'B2', 'A1', 'A2', 'A2', 'A2'];
    public const DISPLAY_LEVELS = ['B2', 'B2', 'A1', 'A2', 'A2', 'A2'];
    public const CONTINUING_COUNTS = [7, 7, 6, 8, 6, 6];
    public const PRACTICE_SOURCE_INDICES = [7, 7, 6, 6, 6, 6];
    public const PRACTICE_KEYS = ['future-perfect-continuous-questions-practice', 'future-perfect-continuous-time-expressions-practice', 'demonstratives-practice', 'some-any-places-6', 'theory-some-any-people-practice', 'theory-some-any-things-practice'];
    public const NAVIGATION_SOURCE_INDICES = [6, 6, 5, 7, 5, 5];
    public const CONTINUING_LEVELS = [[null, null, null, null, null, null, null], [null, null, null, null, null, null, null], ['A1', 'A1', 'A1', 'A1', 'A1', null], [null, null, null, null, null, null, null, null], ['A2', 'A2', 'A2', 'A2', 'A2', null], ['A2', 'A2', 'A2', 'A2', 'A2', null]];
    public const SENTENCE_INPUT_INDICES = [[0, 1], [0, 1], [0, 1], [3, 4], [0, 1], [0, 1]];
    public const EXISTING_PRACTICE_UUIDS = [null, null, null, '9fcd6c4f-a0e3-5e1b-95a3-64ce4ede412c', null, null];
    public const RENDERED_TITLES = ['Future Perfect Continuous: Questions and Short Answers', 'Future Perfect Continuous: Time Expressions', 'Demonstratives: this / that / these / those', 'Some / Any — Places', 'Some / Any — People', 'Some / Any — Things'];

    public const SUBTITLE_TEXT_AFTER = ['Future Perfect Continuous: Questions and Short Answers — Will + subject + have been + V-ing?', 'Future Perfect Continuous: Time Expressions — for, by, by the time, by then, how long, all day, all morning, since.', 'Demonstratives: this / that / these / those допомагає швидко вибрати правильне слово за відстанню та числом.', 'Some / Any — Places показує, як уживати somewhere, anywhere, nowhere та everywhere, коли ми говоримо про місце.', 'Some / Any — People допомагає природно говорити про людей у ствердженнях, питаннях і запереченнях.', 'Some / Any — Things показує, як говорити про речі, предмети й ідеї в різних типах речень.'];

    private static function fail(string $message): never { throw new RuntimeException('M93 '.$message); }

    public static function draftProfile(array $bindings): array
    {
        self::assertBindings($bindings);
        return ['label' => 'M93', 'base_sha' => self::BASE_SHA, 'version' => self::VERSION,
            'marker' => 'm93_native_v1', 'detail_marker' => 'm93_detail_key', 'uuid_prefix' => 'm93:',
            'design' => 'm93-native-v1', 'bindings' => $bindings];
    }

    /** Validate actual identities and unequal source-owned practice contracts before publication. */
    public static function assertBindings(array $bindings): void
    {
        if (!array_is_list($bindings) || array_column($bindings, 'identity') !== self::OWNERS) {
            self::fail('exact six-owner binding vector differs.');
        }
        foreach ($bindings as $i => $binding) {
            $url = 'http://gramlyze.loc/theory/'.implode('/', self::ANCESTRIES[$i]).'/'.self::SLUGS[$i];
            $counts = $i === 3 ? ['selects' => 3, 'choices' => 2, 'inputs' => 5]
                : ['selects' => 2, 'choices' => 2, 'inputs' => 2];
            if (($binding['page_id'] ?? null) !== self::PAGE_IDS[$i]
                || ($binding['category_id'] ?? null) !== self::CATEGORY_IDS[$i]
                || ($binding['slug'] ?? null) !== self::SLUGS[$i]
                || ($binding['ancestry'] ?? null) !== self::ANCESTRIES[$i]
                || ($binding['local_url'] ?? null) !== $url
                || !array_key_exists('practice_level', $binding) || $binding['practice_level'] !== self::LEVELS[$i]
                || ($binding['display_level'] ?? null) !== self::DISPLAY_LEVELS[$i]
                || ($binding['rendered_title'] ?? null) !== self::RENDERED_TITLES[$i]
                || ($binding['continuing_levels'] ?? null) !== self::CONTINUING_LEVELS[$i]
                || ($binding['practice_contract'] ?? null) !== ['counts' => $counts,
                    'sentence_input_indices' => self::SENTENCE_INPUT_INDICES[$i]]) {
                self::fail('actual metadata, mixed technical levels or unequal practice contract differs.');
            }
            if ($i === 3) {
                if (($binding['appended_blocks'] ?? null) !== []
                    || ($binding['existing_practice'] ?? null) !== ['source_index' => 6,
                        'uuid' => self::EXISTING_PRACTICE_UUIDS[3], 'level' => null]) {
                    self::fail('Places must retain its sole captured practice UUID/source6/NULL without duplicate append.');
                }
            } elseif (array_key_exists('existing_practice', $binding)
                || ($binding['appended_blocks'] ?? null) !== [['key' => self::PRACTICE_KEYS[$i],
                    'type' => 'practice-set', 'column' => 'left', 'level' => self::LEVELS[$i]]]) {
                self::fail('A/B/C/E/F require exactly one owner-keyed practice append at its explicit technical level.');
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
        if (($before['content_type'] ?? null) !== 'M93_source_before'
            || ($patch['content_type'] ?? null) !== 'M93_Future_Completion_Demonstratives_Some_Any'
            || array_column($before['targets'] ?? [], 'identity') !== self::OWNERS
            || array_column($patch['targets'] ?? [], 'identity') !== self::OWNERS
            || array_column($master['lessons'] ?? [], 'identity') !== self::OWNERS
            || array_column($navigation['targets'] ?? [], 'identity') !== self::OWNERS) {
            self::fail('finite author/source/navigation owner vector differs.');
        }
        $ids = []; $totals = ['retained' => 0, 'new' => 0, 'demos' => 0, 'graded' => 0, 'full' => 0, 'mistakes' => 0];
        foreach ($master['lessons'] as $i => $lesson) {
            $old = $before['targets'][$i]['before']; $blocks = $lesson['blocks'] ?? [];
            $count = self::CONTINUING_COUNTS[$i]; $slot = self::PRACTICE_SOURCE_INDICES[$i]; $existing = $i === 3;
            if (count($old['page']['blocks'] ?? []) !== $count || count($blocks) !== $count + ($existing ? 0 : 1)
                || ($lesson['preserved_h1'] ?? null) !== self::RENDERED_TITLES[$i]
                || ($lesson['raw_title'] ?? null) !== ($old['page']['title'] ?? null)
                || ($lesson['intended_own_practice_level'] ?? null) !== self::FOCUS_LEVELS[$i]
                || !array_key_exists('technical_block_level', $lesson) || $lesson['technical_block_level'] !== self::LEVELS[$i]
                || ($lesson['display_level'] ?? null) !== self::DISPLAY_LEVELS[$i]
                || !array_key_exists('subtitle_text', $lesson) || $lesson['subtitle_text'] !== self::SUBTITLE_TEXT_AFTER[$i]
                || $lesson['subtitle_text'] !== ($old['page']['subtitle_text'] ?? null)
                || empty($lesson['point_detail_decisions'])) {
                self::fail('actual metadata, unchanged Page text or finite author block vector differs.');
            }
            foreach ($old['page']['blocks'] as $n => $source) {
                $after = $blocks[$n] ?? [];
                if (($after['existing_source_index'] ?? null) !== $n || ($after['type'] ?? null) !== $source['type']
                    || ($after['column'] ?? null) !== $source['column']
                    || !array_key_exists('level', $after) || $after['level'] !== self::CONTINUING_LEVELS[$i][$n]) {
                    self::fail('continuing native metadata/order or explicit A1/A2 versus NULL level changed.');
                }
            }
            if (!$existing) {
                $appended = $blocks[$count] ?? [];
                if (($appended['key'] ?? null) !== self::PRACTICE_KEYS[$i]
                    || !array_key_exists('existing_source_index', $appended) || $appended['existing_source_index'] !== null
                    || ($appended['type'] ?? null) !== 'practice-set' || ($appended['column'] ?? null) !== 'left'
                    || !array_key_exists('level', $appended) || $appended['level'] !== self::LEVELS[$i]
                    || in_array('practice-set', array_column($old['page']['blocks'], 'type'), true)) {
                    self::fail('new practice must be a single exact append after all unchanged source slots.');
                }
            }
            foreach ($blocks as $block) {
                if ($block['type'] === 'mistakes-grid') {
                    foreach ($block['data']['items'] ?? [] as $item) {
                        foreach (['wrong_en', 'wrong_ua', 'right_en', 'right_ua', 'note'] as $field) {
                            if (!self::plain($item[$field] ?? null)) { self::fail('paired mistake meaning or supported visible note is missing.'); }
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
            if (count($practices) !== 1 || ($practice['key'] ?? null) !== self::PRACTICE_KEYS[$i] || ($practice['type'] ?? null) !== 'practice-set'
                || ($practice['column'] ?? null) !== 'left' || !array_key_exists('level', $practice) || $practice['level'] !== self::LEVELS[$i]
                || ($data['linked_practice']['source'] ?? null) !== 'theory_links'
                || ($data['linked_practice']['question_types'] ?? null) !== ['4']
                || ($data['linked_practice']['seeder_classes'] ?? null) !== [self::BANKS[$i]]) {
                self::fail('one exact owner-bound practice/thematic bank at its captured technical level is required.');
            }
            $oldData = $existing ? json_decode($old['page']['blocks'][$slot]['body'], true, flags: JSON_THROW_ON_ERROR) : [];
            if ($existing && M26DetailPackage::uuid(self::OWNERS[$i], $old['page']['blocks'][$slot], $slot + 1)
                !== self::EXISTING_PRACTICE_UUIDS[3]) { self::fail('sole continuing Places practice UUID or absent uuid_key was replaced.'); }
            $expected = $existing ? ['selects' => 3, 'choices' => 2, 'inputs' => 5, 'rephrase' => 3]
                : ['selects' => 2, 'choices' => 2, 'inputs' => 2, 'rephrase' => 0];
            foreach ($expected as $group => $size) {
                if (count($data[$group] ?? []) !== $size) { self::fail('unequal retained/new practice group count differs.'); }
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
                    if (!$existing || ($row['old_task'] ?? null) !== $captured || ($row['old_group'] ?? null) !== $group
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
                        if (!$graded || $task['answer'] !== $canonical) { self::fail('retained canonical answer changed.'); }
                        if (array_key_exists('accepted', $captured) && $task['accepted'] !== $captured['accepted']) {
                            self::fail('captured accepted alias array or order changed.');
                        }
                        foreach ($captured['accepted'] ?? [$captured['answer']] as $alias) {
                            if (!in_array($alias, $task['accepted'] ?? [], true)) { self::fail('retained accepted alias was lost.'); }
                        }
                        $totals['retained']++;
                    } else {
                        if ($group !== 'rephrase' || $n !== 0 || $graded || array_key_exists('accepted', $task)
                            || ($row['assessed_role'] ?? null) !== 'demonstration'
                            || !str_contains($task['example_original'] ?? '', $captured['example_original'] ?? "\0")
                            || !str_contains($task['example_target'] ?? '', $captured['example_target'] ?? "\0")
                            || !self::ukrainian($task['example_original'] ?? null) || !self::ukrainian($task['example_target'] ?? null)) {
                            self::fail('ungraded bilingual source demonstration was lost, replaced or scored.');
                        }
                        $totals['demos']++; continue;
                    }
                } else {
                    foreach (['old_task', 'old_group', 'old_group_index', 'old_block_source_index'] as $field) {
                        if (!array_key_exists($field, $row) || $row[$field] !== null) { self::fail('new control invents a source identity.'); }
                    }
                    if (!$graded || !is_string($task['id'] ?? null) || !str_starts_with($task['id'], 'm93-')) {
                        self::fail('new graded control lacks a stable M93 ID.');
                    }
                    $totals['new']++;
                }
                if (isset($task['id'])) {
                    if (isset($ids[$task['id']])) { self::fail('task ID repeats across owners.'); }
                    $ids[$task['id']] = true;
                }
                $fullInput = $group === 'inputs' && in_array($n, self::SENTENCE_INPUT_INDICES[$i], true);
                $expectedRole = $fullInput ? 'full_sentence' : ($group === 'choices' ? 'meaning'
                    : ($group === 'rephrase' ? 'rephrasing' : 'grammar_form'));
                if (($row['assessed_role'] ?? null) !== $expectedRole
                    || ($existing && $captured === null && !in_array($expectedRole, ['meaning', 'full_sentence'], true))) {
                    self::fail('full inputs, retained partial gaps/rephrases and genuinely new meaning controls must remain distinct.');
                }
                self::assertTask($task, $group, $data, $fullInput); $totals['graded']++;
                if ($fullInput) { self::assertSentence($task); $totals['full']++; }
                if ($existing && $group === 'inputs' && !$fullInput
                    && (($task['before'] ?? null) !== ($captured['before'] ?? null) || str_contains($task['before'] ?? '', '/'))) {
                    self::fail('retained one-word gap was silently converted to a full sentence/token task.');
                }
            }
            if ($existing && (count($oldData['selects'] ?? []) !== 3 || count($oldData['inputs'] ?? []) !== 3
                || count($oldData['rephrase'] ?? []) !== 3 || count($oldData['choices'] ?? []) !== 0
                || ($data['options'] ?? null) !== ($oldData['options'] ?? null)
                || (array_key_exists('choice_options', $oldData) && ($data['choice_options'] ?? null) !== $oldData['choice_options'])
                || array_key_exists('uuid_key', $old['page']['blocks'][$slot]))) {
                self::fail('captured Places source groups/global options or absent uuid_key changed.');
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
        if (array_values($totals) !== [8, 34, 1, 42, 12, 18]) {
            self::fail('retained/new controls, ungraded demo, full inputs or paired mistakes differ.');
        }
    }

    private static function assertTask(array $task, string $group, array $data, bool $fullInput): void
    {
        if (isset($task['strict']) || isset($task['punctuation_sensitive'])
            || !is_array($task['accepted'] ?? null) || !in_array($task['answer'] ?? null, $task['accepted'], true)) {
            self::fail('native accepted-answer contract differs.');
        }
        $visible = $group === 'inputs' ? ($fullInput ? ($task['after'] ?? null) : ($task['prompt'] ?? null))
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
