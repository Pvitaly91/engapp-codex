<?php

/** Pure finite M83 draft projection. No application bootstrap, DB/HTTP calls or active-source writes. */
if (PHP_SAPI !== 'cli' || count($argv) !== 6
    || preg_match('/^author-projection-v[1-9][0-9]*\.json$/D', $argv[1]) !== 1
    || preg_match('/^navigation-review-v[1-9][0-9]*\.json$/D', $argv[2]) !== 1
    || preg_match('/^[a-f0-9]{64}$/D', $argv[3]) !== 1
    || $argv[4] !== 'm83-widget-selection.v1.json'
    || preg_match('/^[a-f0-9]{64}$/D', $argv[5]) !== 1) {
    throw new RuntimeException('M83 requires exclusive output, private navigation basename/SHA and exact public selection basename/SHA.');
}
$root = 'D:/DEV/htdocs/gramlyze.loc';
$wt = $root.'/storage/app/m75-worktree';
$dir = $root.'/storage/app/seo-m83-local';
require $root.'/vendor/autoload.php';
require_once $wt.'/app/Support/TheoryNativeContentRelease.php';
require_once $wt.'/app/Support/M83B2RevisionBasicGrammar.php';

use App\Support\M83B2RevisionBasicGrammar as Package;
use App\Support\M26DetailPackage as Digest;

$beforeSha = 'd522a715b123748b84f52f076b156ccf5224892f138d5429062d9527a843e33e';
$read = static function (string $path, string $sha): array {
    if (!is_file($path) || is_link($path) || !hash_equals($sha, hash_file('sha256', $path))) {
        throw new RuntimeException('M83 independently pinned input differs: '.basename($path));
    }
    return json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
};
$before = $read($dir.'/before-v1.json', $beforeSha);
$navReceipt = $read($dir.'/'.$argv[2], $argv[3]);
$selection = $read($wt.'/docs/content/'.$argv[4], $argv[5]);
if (($navReceipt['independent_before_sha256'] ?? null) !== $beforeSha
    || ($navReceipt['db_writes'] ?? null) !== 0 || ($navReceipt['source_writes'] ?? null) !== 0
    || ($navReceipt['tables_unchanged'] ?? null) !== true
    || ($selection['independent_before_sha256'] ?? null) !== $beforeSha
    || array_column($selection['targets'] ?? [], 'identity') !== Package::OWNERS) {
    throw new RuntimeException('M83 navigation/selection independent source or read-only receipt differs.');
}
$navigation = ['schema_version' => 1, 'receipt_sha256' => $argv[3], 'targets' => []];
$parts = []; $drafts = []; $bindings = []; $edits = [];
foreach (Package::SLUGS as $i => $name) {
    $file = $wt.'/docs/content/m83-author-parts/'.$name.'.v1.json';
    if (!is_file($file) || is_link($file)) { throw new RuntimeException('M83 author part missing: '.$name); }
    $parts[$i] = json_decode(file_get_contents($file), true, flags: JSON_THROW_ON_ERROR);
    $drafts[$name] = hash_file('sha256', $file);
    $lesson = &$parts[$i]['lesson']; $owner = Package::OWNERS[$i];
    $state = $before['pages'][$owner] ?? null; $captured = $before['definitions'][$owner] ?? null;
    $old = $captured['definition'] ?? null;
    if (!is_array($state) || !is_array($old)
        || !hash_equals($captured['sha256'], hash('sha256', $captured['bytes']))
        || json_decode($captured['bytes'], true, flags: JSON_THROW_ON_ERROR) !== $old
        || ($lesson['identity'] ?? null) !== $owner || $state['page']['id'] !== Package::PAGE_IDS[$i]
        || $state['page']['page_category_id'] !== Package::CATEGORY_IDS[$i]
        || ($lesson['local_url'] ?? null) !== $state['target_route']['url']
        || array_column($state['category_ancestry'], 'slug') !== Package::ANCESTRIES[$i]
        || count($old['page']['blocks']) !== Package::CONTINUING_COUNTS[$i]
        || count($lesson['blocks']) !== Package::CONTINUING_COUNTS[$i] + ($i === 0 ? 1 : 0)
        || $state['continuing_levels'] !== array_map(static fn ($b) => $b['level'] ?? null, $old['page']['blocks'])) {
        throw new RuntimeException('M83 exact source/SQL identity, ancestry, levels or continuing vector differs.');
    }
    if (in_array($i, [4, 5], true)) {
        if (($lesson['intended_own_practice_level'] ?? null) !== 'A1–A2') {
            throw new RuntimeException('M83 exact EF editorial-focus draft differs.');
        }
        $edits[$owner]['editorial_focus'] = ['before' => 'A1–A2', 'after' => 'A1',
            'reason' => 'The task explicitly sets A1 editorial practice focus; actual SQL NULL and display A1–A2 are unchanged. A range is not used as an exact bank filter.'];
        $lesson['intended_own_practice_level'] = 'A1';
    }
    if (($lesson['technical_block_level'] ?? null) !== Package::LEVELS[$i]
        || ($lesson['intended_own_practice_level'] ?? null) !== Package::FOCUS_LEVELS[$i]
        || ($lesson['display_level'] ?? null) !== Package::DISPLAY_LEVELS[$i]) {
        throw new RuntimeException('M83 author technical/focus/display levels differ; no implicit CEFR rewrite.');
    }
    foreach ($old['page']['blocks'] as $slot => $raw) {
        $block = $lesson['blocks'][$slot];
        if (($block['existing_source_index'] ?? null) !== $slot || $block['type'] !== $raw['type']
            || $block['column'] !== $raw['column'] || ($block['level'] ?? null) !== ($raw['level'] ?? null)) {
            throw new RuntimeException('M83 author changed continuing native metadata.');
        }
    }
    if ($i === 0) {
        $courseScope = [
            '/blocks/0/data/rules/2/text' => [
                'before' => 'Повні форми у відповідях фінального курсу — його навчальна вимога, не загальна граматична заборона скорочень на B2.',
                'after' => 'Повні форми зберігай там, де їх прямо вимагає конкретне завдання курсу; це не загальна граматична заборона скорочень на B2.',
            ],
            '/blocks/1/data/items/5' => [
                'before' => '<strong>Formal register</strong>: nominalisation називає процес іменником; точні зв’язки допомагають читачеві. У фінальному курсі збережи повні форми його ключів; це не загальна заборона contractions.',
                'after' => '<strong>Formal register</strong>: nominalisation називає процес іменником; точні зв’язки допомагають читачеві. У конкретному завданні збережи прямо задану вимогу повної форми; це не загальна заборона contractions.',
            ],
            '/blocks/3/data/sections/6/note' => [
                'before' => 'Скорочення доречні в багатьох розмовних і неформальних текстах B2. Повні форми фінального курсу збережено в його власному scope; урок не змінює course questions, membership, оцінювання чи прогрес.',
                'after' => 'Скорочення доречні в багатьох розмовних і неформальних текстах B2. Якщо конкретне завдання курсу прямо просить повну форму, виконай цю умову.',
            ],
        ];
        foreach ($courseScope as $pointer => $change) {
            $value = &$lesson;
            foreach (explode('/', ltrim($pointer, '/')) as $key) {
                if (!is_array($value) || !array_key_exists($key, $value)) {
                    throw new RuntimeException('M83 exact course-scope editorial pointer is missing.');
                }
                $value = &$value[$key];
            }
            if ($value !== $change['before']) { throw new RuntimeException('M83 course-scope draft prose changed before review.'); }
            $value = $change['after']; unset($value);
            $edits[$owner]['course_scope_editorial'][] = ['pointer' => $pointer] + $change + [
                'reason' => 'Independent course audit found criterion-specific full-form tasks, not a blanket B2/course contraction ban; no course source or membership changes.',
            ];
        }
    }
    $navSlot = Package::NAVIGATION_SOURCE_INDICES[$i];
    $review = $navReceipt['owner_navigations'][$owner] ?? null;
    $oldNav = json_decode($old['page']['blocks'][$navSlot]['body'], true, flags: JSON_THROW_ON_ERROR);
    $labels = static fn ($list) => array_map(static fn ($item) => array_intersect_key($item, array_flip(['label', 'current'])), $list);
    if (!is_array($review) || ($review['source_index'] ?? null) !== $navSlot
        || ($review['data']['title'] ?? null) !== ($oldNav['title'] ?? null)
        || $labels($review['data']['items'] ?? []) !== $labels($oldNav['items'])
        || $labels($lesson['blocks'][$navSlot]['data']['items'] ?? []) !== $labels($oldNav['items'])) {
        throw new RuntimeException('M83 exact independent navigation labels/current/title differ.');
    }
    foreach ($review['data']['items'] as $itemIndex => $item) {
        if (!empty($item['current'])) { continue; }
        $matches = array_values(array_filter($navReceipt['targets'] ?? [], static fn ($row) =>
            ($row['owner'] ?? null) === $owner && ($row['source_index'] ?? null) === $navSlot
            && ($row['item_index'] ?? null) === $itemIndex));
        if (count($matches) !== 1 || $matches[0]['current'] !== false
            || $matches[0]['label'] !== $item['label'] || $matches[0]['url'] !== ($item['url'] ?? null)
            || $matches[0]['http_status'] !== 200 || !is_int($matches[0]['target_page_id'])
            || $matches[0]['target_page_id'] < 1
            || preg_match('/^[a-f0-9]{64}$/D', $matches[0]['html_sha256'] ?? '') !== 1) {
            throw new RuntimeException('M83 destination has no exact successful independent navigation proof.');
        }
    }
    $edits[$owner]['navigation'] = ['source_index' => $navSlot,
        'before' => $lesson['blocks'][$navSlot]['data'], 'after' => $review['data'],
        'reason' => 'Verified finite URL data only; original title/labels/current and shared resolver remain unchanged.'];
    $lesson['blocks'][$navSlot]['data'] = $review['data'];
    $navigation['targets'][] = ['identity' => $owner, 'page_id' => Package::PAGE_IDS[$i],
        'source_index' => $navSlot, 'items' => $review['data']['items']];

    if ($i === 5) {
        if (($lesson['blocks'][6]['data']['sections'][0]['color'] ?? null) !== 'purple') {
            throw new RuntimeException('M83 exact unsupported conjunction usage accent changed before review.');
        }
        $lesson['blocks'][6]['data']['sections'][0]['color'] = 'slate';
        $edits[$owner]['supported_usage_accent'] = ['pointer' => '/blocks/6/data/sections/0/color',
            'before' => 'purple', 'after' => 'slate',
            'reason' => 'The unchanged native release permits slate but not purple for usage sections. Hero and supported mistake accents are untouched; no palette/view/style change.'];
    }
    if (in_array($i, [4, 5], true)) {
        $translations = $i === 4 ? [
            ['А ти відчини двері!', 'Відчини двері!'],
            ['Задум: не чіпай це!', 'Не чіпай це!'],
            ['Задум: сідай!', 'Сідай!'],
            ['Задум: не запізнюйся!', 'Не запізнюйся!'],
        ] : [
            ['Задум: я залишився вдома, тому що йшов дощ.', 'Я залишився вдома, тому що йшов дощ.'],
            ['Задум: я не люблю ні чаю, ні кави.', 'Я не люблю ні чаю, ні кави.'],
            ['Оскільки йшов дощ, я залишився вдома.', 'Оскільки йшов дощ, я залишився вдома.'],
            ['Задум: я був утомлений, тому ліг спати.', 'Я був утомлений, тому ліг спати.'],
        ];
        $mistakeSlot = 9;
        if ($lesson['blocks'][$mistakeSlot]['type'] !== 'mistakes-grid'
            || count($lesson['blocks'][$mistakeSlot]['data']['items']) !== 4) {
            throw new RuntimeException('M83 exact EF four-correction native function differs.');
        }
        foreach ($translations as $itemIndex => [$wrongUk, $rightUk]) {
            $item = &$lesson['blocks'][$mistakeSlot]['data']['items'][$itemIndex]; $beforeItem = $item;
            if (!is_string($item['wrong'] ?? null) || trim($item['wrong']) === ''
                || !is_string($item['hint'] ?? null) || trim($item['hint']) === ''
                || preg_match('~\A✅ <span class="font-mono">([^<>]+)</span>\z~uD', $item['right'] ?? '', $right) !== 1
                || isset($item['wrong_en']) || isset($item['right_en'])) {
                throw new RuntimeException('M83 exact EF legacy correction fields/wrapper changed before review.');
            }
            unset($item['wrong'], $item['right'], $item['hint']);
            $item += ['wrong_en' => $beforeItem['wrong'], 'wrong_ua' => $wrongUk,
                'right_en' => $right[1], 'right_ua' => $rightUk, 'note' => $beforeItem['hint']];
            $edits[$owner]['structured_correction_pairs'][] = ['pointer' => '/blocks/9/data/items/'.$itemIndex,
                'before' => $beforeItem, 'after' => $item,
                'field_mapping' => ['wrong' => 'wrong_en', 'right' => 'right_en (only exact checkmark/span wrapper removed)',
                    'hint' => 'note', 'new_fields' => ['wrong_ua', 'right_ua']],
                'reason' => 'Existing canonical correction component requires separate translated pairs; native type, order, colors, English content and visible criterion stay unchanged.'];
            unset($item);
        }
    }
    $practiceSlot = Package::PRACTICE_SOURCE_INDICES[$i]; $practice = &$lesson['blocks'][$practiceSlot];
    if (in_array($i, [1, 4, 5], true)) {
        $sourcePractice = json_decode($old['page']['blocks'][$practiceSlot]['body'], true, flags: JSON_THROW_ON_ERROR);
        foreach ($practice['data']['selects'] as $selectIndex => &$task) {
            $oldPrompt = $sourcePractice['selects'][$selectIndex]['prompt'] ?? null;
            if (!is_string($oldPrompt) || ($task['prompt'] ?? null) !== $oldPrompt
                || preg_match('/[\x{0400}-\x{04FF}]/u', strip_tags($task['label'] ?? '')) !== 1) {
                throw new RuntimeException('M83 exact legacy select prompt or visible reviewed criterion differs.');
            }
            unset($task['prompt']);
            $edits[$owner]['unused_select_prompts'][] = ['group' => 'selects', 'index' => $selectIndex,
                'before_prompt' => $oldPrompt, 'after_prompt_present' => false,
                'reason' => 'The native select ignores prompt; the full substantive criterion is already in its reviewed visible label. Remove only the old unused duplicate field, without rendered/grading changes.'];
        }
        unset($task);
    }
    $selected = $selection['targets'][$i];
    if (($selected['bank'] ?? null) !== Package::BANKS[$i]
        || !array_key_exists('actual_practice_level', $selected) || $selected['actual_practice_level'] !== Package::LEVELS[$i]
        || count($selected['selected'] ?? []) !== 5
        || array_column($selected['selected'], 'level') !== ($selected['selected_levels'] ?? null)
        || count(array_unique(array_column($selected['selected'], 'id'))) !== 5
        || count(array_unique(array_column($selected['selected'], 'uuid'))) !== 5) {
        throw new RuntimeException('M83 exact reviewed five-question bank/level declaration differs.');
    }
    foreach ($selected['selected'] as $question) {
        if (!is_int($question['id'] ?? null) || $question['id'] < 1
            || !in_array($question['level'] ?? null, ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'], true)
            || ($i === 0 && $question['level'] !== 'B2')) {
            throw new RuntimeException('M83 selected question identity or actual CEFR is invalid.');
        }
    }
    $linked = $practice['data']['linked_practice'] ?? [];
    if (isset($linked['seeder_classes']) && $linked['seeder_classes'] !== [Package::BANKS[$i]]) {
        throw new RuntimeException('M83 existing author bank declaration is foreign.');
    }
    $widgetNumber = $i === 0 ? 4 : 5;
    $practice['data']['linked_practice'] = array_replace([
        'source' => 'theory_links', 'question_types' => ['4'], 'seeder_classes' => [Package::BANKS[$i]],
        'title' => 'Вправа '.$widgetNumber.'. Побудуй речення',
        'intro' => 'Склади англійське речення за українською умовою, клікаючи на слова.',
        'footer' => 'Завдання підібрані лише для цієї теми.',
    ], $linked);
    $edits[$owner]['widget'] = ['bank' => Package::BANKS[$i], 'selection_sha256' => $argv[5],
        'reason' => 'Native type-4 bank declaration; five actual selected questions are reviewed separately, never counted as own tasks.'];

    if ($i === 4) {
        foreach ([0 => ['Let’s sit here.</span> ', 'Let’s sit here.</span> — Сідаймо тут. '],
            1 => ['Please wait here.</span> ', 'Please wait here.</span> — Будь ласка, зачекай тут. ']] as $choiceIndex => [$from, $to]) {
            $label = &$practice['data']['choices'][$choiceIndex]['label'];
            if (!is_string($label) || substr_count($label, $from) !== 1) {
                throw new RuntimeException('M83 exact new imperative meaning translation pointer differs.');
            }
            $beforeLabel = $label; $label = str_replace($from, $to, $label);
            $edits[$owner]['meaning_translations'][] = ['group' => 'choices', 'index' => $choiceIndex,
                'before' => $beforeLabel, 'after' => $label,
                'reason' => 'The visible English stimulus receives a complete Ukrainian translation; semantic alternatives and grading are unchanged.'];
            unset($label);
        }
    }
    if (in_array($i, [4, 5], true)) {
        foreach ([0, 1, 2] as $inputIndex) {
            $task = &$practice['data']['inputs'][$inputIndex];
            if (!is_string($task['prompt'] ?? null) || trim($task['prompt']) === '' || ($task['after'] ?? null) !== '') {
                throw new RuntimeException('M83 exact six prompt→after projections do not match the reviewed draft.');
            }
            $edits[$owner]['visible_input_conditions'][] = ['group' => 'inputs', 'index' => $inputIndex,
                'before_prompt' => $task['prompt'], 'before_after' => '',
                'reason' => 'Same complete visible criterion moved between supported native fields to satisfy the unchanged full-input contract; no view/grader change.'];
            $task['after'] = '<p>'.htmlspecialchars($task['prompt'], ENT_NOQUOTES | ENT_SUBSTITUTE, 'UTF-8').'</p>';
            unset($task['prompt']); unset($task);
        }
    }
    if ($i === 5) {
        $task = &$practice['data']['choices'][0];
        if (array_key_exists('id', $task) || array_key_exists('source_index', $task)) {
            throw new RuntimeException('M83 retained comma-control draft identity absence differs.');
        }
        $task['id'] = 'm83-conjunctions-retained-comma';
        $edits[$owner]['retained_transfer_control_id'] = ['old_group' => 'rephrase', 'old_index' => 2,
            'after_group' => 'choices', 'after_index' => 0, 'id' => $task['id'],
            'reason' => 'One retained learning position moves to a new supported semantic choice control; stable control ID only, no invented source_index.'];
        unset($task);
    }
    foreach ($parts[$i]['task_map'] as &$taskMap) {
        $afterIndex = $taskMap['after_block_source_index']; $group = $taskMap['after_group']; $index = $taskMap['after_group_index'];
        if ($afterIndex !== $practiceSlot || !isset($lesson['blocks'][$afterIndex]['data'][$group][$index])) {
            throw new RuntimeException('M83 task map references a foreign/missing control.');
        }
        $taskMap['after_task'] = $lesson['blocks'][$afterIndex]['data'][$group][$index];
    }
    unset($taskMap);

    $binding = ['identity' => $owner, 'slug' => $name, 'page_id' => Package::PAGE_IDS[$i],
        'category_id' => Package::CATEGORY_IDS[$i], 'ancestry' => Package::ANCESTRIES[$i], 'local_url' => $lesson['local_url'],
        'practice_level' => Package::LEVELS[$i], 'display_level' => Package::DISPLAY_LEVELS[$i],
        'rendered_title' => Package::RENDERED_TITLES[$i], 'continuing_levels' => $state['continuing_levels'],
        'appended_blocks' => $i === 0 ? [['key' => $practice['key'], 'type' => 'practice-set', 'column' => 'left', 'level' => 'B2']] : [],
        'practice_contract' => ['counts' => ['selects' => Package::SELECT_COUNTS[$i],
            'choices' => Package::CHOICE_COUNTS[$i], 'inputs' => Package::INPUT_COUNTS[$i]],
            'sentence_input_indices' => Package::SENTENCE_INPUT_INDICES[$i]]];
    if ($i === 0) {
        if ($state['existing_practices'] !== []) { throw new RuntimeException('M83 B2 already has an own practice; append would duplicate it.'); }
    } else {
        $existing = $state['existing_practices'];
        if (count($existing) !== 1 || $existing[0]['uuid'] !== Package::EXISTING_PRACTICE_UUIDS[$i]
            || $existing[0]['level'] !== null || $existing[0]['sort_order'] !== $practiceSlot + 1
            || $existing[0]['body'] !== $old['page']['blocks'][$practiceSlot]['body']) {
            throw new RuntimeException('M83 independent continuing practice binding differs.');
        }
        $binding['existing_practice'] = ['source_index' => $practiceSlot, 'uuid' => $existing[0]['uuid'], 'level' => null];
    }
    $bindings[] = $binding;
    unset($practice, $lesson);
}
$release = new App\Support\TheoryNativeContentRelease(Package::draftProfile($bindings));
$base = ['schema_version' => 1, 'base_sha' => Package::BASE_SHA];
$sourceBefore = $base + ['content_type' => 'M83_source_before', 'targets' => []];
$patch = $base + ['content_type' => 'M83_B2_Revision_Basic_Grammar', 'status' => 'editorial-review', 'targets' => []];
$master = $base + ['version' => Package::VERSION, 'status' => 'editorial-review', 'policy' => [
    'visual_changes' => 0, 'grader_changes' => 0, 'formatter_changes' => 0, 'continuing_metadata_changes' => 0,
    'old_task_identity' => '42 retained/corrected graded source positions plus 5 ungraded demos; one finite rephrase→choice semantic transfer.',
    'practice' => '60 own graded tasks, including 16 full manual/token inputs; 30 selected widget questions are counted separately.',
    'metadata' => 'One appended B2 practice only; five continuing practice UUIDs and all native types/columns/actual SQL levels preserved.',
    'detail' => 'Finite semantic author decisions; essential explanations, translations and short boundaries remain visible.',
], 'editorial_review' => ['pre_apply_metadata' => [], 'post_apply_metadata' => [], 'lesson_reviews' => [],
    'projection_edits' => $edits, 'task_map' => [], 'navigation_review' => $navigation,
    'widget_selection_sha256' => $argv[5]], 'lessons' => [], 'sources' => []];
$contract = ['schema_version' => 1, 'targets' => []]; $maps = []; $tasks = [];
foreach ($parts as $i => $part) {
    $lesson = $part['lesson']; $owner = Package::OWNERS[$i];
    $old = $before['definitions'][$owner]; $state = $before['pages'][$owner];
    $after = $release->definition($lesson, $old['definition']);
    $sourceBefore['targets'][] = ['identity' => $owner, 'path' => $old['path'], 'before' => $old['definition']];
    $patch['targets'][] = ['identity' => $owner, 'slug' => $lesson['slug'], 'path' => $old['path'],
        'ancestry' => Package::ANCESTRIES[$i], 'local_url' => $lesson['local_url'],
        'page_binding' => ['id' => $state['page']['id'], 'page_category_id' => $state['page']['page_category_id']],
        'block_keys' => array_column($lesson['blocks'], 'key'), 'after' => $after];
    $practice = $lesson['blocks'][Package::PRACTICE_SOURCE_INDICES[$i]]['data'];
    $contract['targets'][] = ['identity' => $owner, 'definition' => $old['path'], 'local_url' => $lesson['local_url'],
        'level' => Package::LEVELS[$i], 'bank_seeders' => $practice['linked_practice']['seeder_classes']];
    $master['lessons'][] = $lesson; $master['editorial_review']['lesson_reviews'][$owner] = $part['editorial_review'];
    $master['editorial_review']['task_map'][$owner] = $part['task_map']; $tasks[$owner] = $part['task_map'];
    $http = array_values(array_filter($before['http'], static fn ($row) => $row['identity'] === $owner && $row['kind'] === 'theory'));
    if (count($http) !== 1 || $http[0]['h1'] !== Package::RENDERED_TITLES[$i]) {
        throw new RuntimeException('M83 independent actual HTTP H1 differs.');
    }
    $master['editorial_review']['pre_apply_metadata'][] = ['identity' => $owner, 'mode' => 'leading-strong',
        'sha256' => hash('sha256', $old['definition']['page']['subtitle_html']),
        'raw_page_title' => $state['page']['title'], 'extracted_title' => $state['subtitle_extracted_title'], 'h1' => $http[0]['h1']];
    $master['editorial_review']['post_apply_metadata'][] = ['identity' => $owner,
        'sha256' => hash('sha256', $lesson['subtitle_html']), 'extracted_title' => Package::RENDERED_TITLES[$i], 'h1' => Package::RENDERED_TITLES[$i]];
    foreach ($part['editorial_review']['sources'] ?? [] as $source) { $master['sources'][] = ['lesson' => $owner] + $source; }
    foreach ($after['page']['blocks'] as $slot => $block) {
        if (strlen($block['body']) > (int) $before['body_capacity']['max_bytes']) {
            throw new RuntimeException('M83 generated body exceeds independently inspected SQL TEXT capacity.');
        }
        $raw = $old['definition']['page']['blocks'][$slot] ?? null;
        $maps[] = ['identity' => $owner, 'source_index' => $slot, 'key' => $lesson['blocks'][$slot]['key'],
            'before_type' => $raw['type'] ?? null, 'type' => $block['type'], 'column' => $block['column'], 'level' => $block['level'] ?? null,
            'uuid' => Digest::uuid($owner, $block, $slot + 1), 'before_block_sha256' => $raw === null ? null : Digest::digest($raw),
            'removed_secondary_fields' => $raw === null ? [] : array_values(array_diff(array_keys($raw), array_keys($block))),
            'body_sha256' => hash('sha256', $block['body']), 'detail_count' => count(array_filter($lesson['details'],
                static fn ($detail) => $detail['owner_block_key'] === $lesson['blocks'][$slot]['key'])), 'new_block' => $raw === null];
    }
}
$release->validate($sourceBefore, $patch, $master, $contract);
Package::assertEditorial($sourceBefore, $patch, $master, $navigation);
$out = ['source_before' => $sourceBefore, 'master' => $master, 'patch' => $patch, 'contract' => $contract,
    'native_bindings' => $base + ['bindings' => $bindings], 'component_map' => $maps, 'task_map' => $tasks,
    'navigation_review' => $navigation, 'draft_sha256' => $drafts,
    'navigation_receipt_sha256' => $argv[3], 'widget_selection_sha256' => $argv[5],
    'independent_before_sha256' => $beforeSha, 'db_connections' => 0, 'db_writes' => 0, 'http_requests' => 0];
$json = static fn ($value) => json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
$bindingName = str_replace('author-projection-', 'native-bindings-', $argv[1]);
$files = [$argv[1] => $out, $bindingName => $out['native_bindings']];
foreach ($files as $name => $_) {
    if (file_exists($dir.'/'.$name) || is_link($dir.'/'.$name)) {
        throw new RuntimeException('M83 exclusive projection output already exists: '.$name);
    }
}
foreach ($files as $name => $value) {
    $bytes = $json($value); $handle = fopen($dir.'/'.$name, 'x');
    if (!$handle) { throw new RuntimeException('M83 exclusive private projection unavailable.'); }
    try {
        if (fwrite($handle, $bytes) !== strlen($bytes) || !fflush($handle)) {
            throw new RuntimeException('M83 private projection write incomplete.');
        }
    } finally { fclose($handle); }
}
echo $json(['file' => $argv[1], 'sha256' => hash_file('sha256', $dir.'/'.$argv[1]),
    'bindings_sha256' => hash_file('sha256', $dir.'/'.$bindingName), 'lessons' => 6, 'components' => count($maps),
    'own_graded' => 60, 'ungraded_demos' => 5, 'full_sentence_inputs' => 16,
    'db_connections' => 0, 'db_writes' => 0, 'http_requests' => 0, 'active_source_writes' => 0]);
