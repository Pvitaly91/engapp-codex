<?php

namespace App\Support;

use App\Models\Page;
use RuntimeException;

/** Finite teaching-data contract. Native presentation, formatting and grading remain shared. */
final class M91FutureSimpleCompletionContinuous
{
    public const BASE_SHA = '03a72ee95374385995b29e00d7961ee396632fde';
    public const VERSION = '1.0.0';
    public const BEFORE = 'docs/content/m91-before-source.v1.json';
    public const SOURCE = 'database/content-patches/m91-future-simple-completion-continuous.v1.json';
    public const MASTER = 'docs/content/m91-future-simple-completion-continuous.v1.json';
    public const CONTRACT = 'docs/content/theory-sentence-builders/m91-future-simple-completion-continuous.v1.json';
    public const CHECKSUMS = 'docs/content/m91-source-checksums.v1.json';
    public const BINDINGS = 'docs/content/m91-native-bindings.v1.json';
    public const NAVIGATION = 'docs/content/m91-navigation-review.v1.json';
    // Reviewed immutable source-only package; apply and browser acceptance remain explicit separate gates.
    public const BEFORE_SHA = 'b6d90295d41683607f704547e11734159189127f4eaf2d14b42d3f94b0e8df98';
    public const SOURCE_SHA = '91ee72758534bd804874402a20aa0d722071f9450ed9a64a31af744b9760f2c7';
    public const MASTER_SHA = 'd9bc3dfcb596932140436e3213ebc838b97743e7c6cce2c9930025267ab93041';
    public const CONTRACT_SHA = 'cb6263f208c6778b3584f5be5bc730e7a5d5be3f5ae00e626ac934331ff69167';
    public const CHECKSUMS_SHA = '8634fc945055e4e97d5bc97206fb76e18dc5e49f8ce5657776871b917f3d2bbf';
    public const BINDINGS_SHA = 'c97abd3b2c4b6bef07a4549f11ef8b43a9143157f059bb80c03ce223ed43da7f';
    public const NAVIGATION_SHA = 'b59f88d686a96f9dd513d01c1da4cbdda890a8025299ed45c14e6b3db1b19c5d';
    public const OWNERS = ['Database\\Seeders\\Page_V3\\FutureForms\\FutureSimple\\FutureSimpleQuestionsTheorySeeder', 'Database\\Seeders\\Page_V3\\FutureForms\\FutureSimple\\FutureSimpleTimeExpressionsTheorySeeder', 'Database\\Seeders\\Page_V3\\FutureForms\\FutureContinuous\\FutureContinuousFormsTheorySeeder', 'Database\\Seeders\\Page_V3\\FutureForms\\FutureContinuous\\FutureContinuousNegativesTheorySeeder', 'Database\\Seeders\\Page_V3\\FutureForms\\FutureContinuous\\FutureContinuousQuestionsTheorySeeder', 'Database\\Seeders\\Page_V3\\FutureForms\\FutureContinuous\\FutureContinuousTimeExpressionsTheorySeeder'];
    public const PAGE_IDS = [344, 345, 54, 55, 56, 57];
    public const CATEGORY_IDS = [62, 62, 11, 11, 11, 11];
    public const BANKS = ['Database\\Seeders\\V3\\Polyglot\\PolyglotFutureSimpleQuestionsAllLevelsLessonSeeder', 'Database\\Seeders\\V3\\Polyglot\\PolyglotFutureSimpleTimeExpressionsAllLevelsLessonSeeder', 'Database\\Seeders\\V3\\Polyglot\\PolyglotFutureContinuousFormsAllLevelsLessonSeeder', 'Database\\Seeders\\V3\\Polyglot\\PolyglotFutureContinuousNegativesAllLevelsLessonSeeder', 'Database\\Seeders\\V3\\Polyglot\\PolyglotFutureContinuousQuestionsAllLevelsLessonSeeder', 'Database\\Seeders\\V3\\Polyglot\\PolyglotFutureContinuousTimeExpressionsAllLevelsLessonSeeder'];
    public const SLUGS = ['future-simple-questions', 'future-simple-time-expressions', 'future-continuous-forms', 'future-continuous-negatives', 'future-continuous-questions', 'future-continuous-time-expressions'];
    public const ANCESTRIES = [['maibutni-formy', 'future-simple'], ['maibutni-formy', 'future-simple'], ['maibutni-formy', 'future-continuous'], ['maibutni-formy', 'future-continuous'], ['maibutni-formy', 'future-continuous'], ['maibutni-formy', 'future-continuous']];
    public const LEVELS = [null, null, null, null, null, null];
    public const FOCUS_LEVELS = ['A1', 'A1', 'B1', 'B1', 'B1', 'B1'];
    public const DISPLAY_LEVELS = ['A1–A2', 'A1–A2', 'B1', 'B1', 'B1', 'B1'];
    public const CONTINUING_COUNTS = [7, 7, 7, 7, 7, 7];
    public const PRACTICE_SOURCE_INDICES = [5, 5, 7, 7, 7, 7];
    public const NAVIGATION_SOURCE_INDICES = [6, 6, 6, 6, 6, 6];
    public const EXISTING_PRACTICE_UUIDS = ['ec3bed0d-bb72-5f4e-89b4-6303cfe166a5', '412badfc-e254-5b74-b2ef-5cdb4a41819d'];
    public const RENDERED_TITLES = ['Future Simple: Questions and Short Answers', 'Future Simple: Time Expressions', 'Future Continuous: Forms and Use', 'Future Continuous: Negatives', 'Future Continuous: Questions and Short Answers', 'Future Continuous: Time Expressions'];

    public const SUBTITLE_TEXT_AFTER = [null, null, 'Future Continuous: Forms and Use — will be + verb-ing для дій у процесі в майбутньому.', 'Future Continuous: Negatives — will not be + verb-ing для дій, що не будуть у процесі в майбутньому.', 'Future Continuous: Questions and Short Answers — Запитуй про майбутній процес, очікувану діяльність і плани за моделлю Will + підмет + be + V-ing? Відповідай за контекстом, а не за самою формою питання.', 'Future Continuous: Time Expressions — Розрізняй часову точку, період і тло іншої події: this time tomorrow, at 8 p.m. tomorrow, next week at this time, all day tomorrow, later tonight, when і while.'];

    private static function fail(string $message): never { throw new RuntimeException('M91 '.$message); }

    public static function draftProfile(array $bindings): array
    {
        self::assertBindings($bindings);
        return ['label' => 'M91', 'base_sha' => self::BASE_SHA, 'version' => self::VERSION,
            'marker' => 'm91_native_v1', 'detail_marker' => 'm91_detail_key', 'uuid_prefix' => 'm91:',
            'design' => 'm91-native-v1', 'bindings' => $bindings];
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
            if ($i < 2) {
                if (($binding['appended_blocks'] ?? null) !== []
                    || ($binding['existing_practice'] ?? null) !== ['source_index' => 5,
                        'uuid' => self::EXISTING_PRACTICE_UUIDS[$i], 'level' => null]) {
                    self::fail('A/B continuing practices must retain exact owner, UUID and slot without duplicate append.');
                }
            } elseif (array_key_exists('existing_practice', $binding)
                || ($binding['appended_blocks'] ?? null) !== [['key' => self::SLUGS[$i].'-practice',
                    'type' => 'practice-set', 'column' => 'left', 'level' => null]]) {
                self::fail('C–F require exactly one owner-keyed left/NULL practice append, not an invented existing UUID.');
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
        if (($before['content_type'] ?? null) !== 'M91_source_before'
            || ($patch['content_type'] ?? null) !== 'M91_Future_Simple_Completion_Continuous'
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
            if (count($old['page']['blocks'] ?? []) !== $count || count($blocks) !== $count + ($i < 2 ? 0 : 1)
                || ($lesson['preserved_h1'] ?? null) !== self::RENDERED_TITLES[$i]
                || ($lesson['raw_title'] ?? null) !== ($old['page']['title'] ?? null)
                || ($lesson['intended_own_practice_level'] ?? null) !== self::FOCUS_LEVELS[$i]
                || !array_key_exists('technical_block_level', $lesson) || $lesson['technical_block_level'] !== null
                || ($lesson['display_level'] ?? null) !== self::DISPLAY_LEVELS[$i]
                || !array_key_exists('subtitle_text', $lesson) || $lesson['subtitle_text'] !== self::SUBTITLE_TEXT_AFTER[$i]
                || ($i < 4 && $lesson['subtitle_text'] !== ($old['page']['subtitle_text'] ?? null))
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
            if ($i >= 2) {
                $appended = $blocks[$count] ?? [];
                if (($appended['key'] ?? null) !== self::SLUGS[$i].'-practice'
                    || !array_key_exists('existing_source_index', $appended) || $appended['existing_source_index'] !== null
                    || ($appended['type'] ?? null) !== 'practice-set' || ($appended['column'] ?? null) !== 'left'
                    || !array_key_exists('level', $appended) || $appended['level'] !== null
                    || in_array('practice-set', array_column($old['page']['blocks'], 'type'), true)) {
                    self::fail('new practice must be the single exact source7 append after the unchanged seven components.');
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
            $oldData = $i < 2 ? json_decode($old['page']['blocks'][$slot]['body'], true, flags: JSON_THROW_ON_ERROR) : [];
            if ($i < 2 && M26DetailPackage::uuid(self::OWNERS[$i], $old['page']['blocks'][$slot], $slot + 1)
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
                    if (!$graded || !is_string($task['id'] ?? null) || !str_starts_with($task['id'], 'm91-')) {
                        self::fail('new graded control lacks a stable M91 ID.');
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
                    || ($i < 2 && $captured === null && $expectedRole !== 'meaning')) {
                    self::fail('retained form choices and genuinely new meaning controls must remain distinct.');
                }
                self::assertTask($task, $group, $data); $totals['graded']++;
                if ($group === 'inputs') { self::assertSentence($task); $totals['full']++; }
            }
            if ($i < 2 && (count($oldData['selects'] ?? []) !== 2 || count($oldData['inputs'] ?? []) !== 2
                || count($oldData['choices'] ?? []) !== 0
                || ($data['options'] ?? null) !== ($oldData['options'] ?? null)
                || (array_key_exists('choice_options', $oldData) && ($data['choice_options'] ?? null) !== $oldData['choice_options'])
                || (array_key_exists('linked_practice', $oldData) && ($data['linked_practice'] ?? null) !== $oldData['linked_practice']))) {
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
        if (array_values($totals) !== [8, 28, 0, 36, 12, 15]) {
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
