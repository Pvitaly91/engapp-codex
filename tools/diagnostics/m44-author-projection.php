<?php

// Mechanical transfer of the frozen author master; no Laravel, HTTP or DB access.
const M44_AUTHOR_PATH = 'docs/content/m44-authored-future-forms.v1.0.0.json';
const M44_AUTHOR_SHA = '541db4dec57ed372d23832e849f84c0554fb40faf73693c5b7ab2d306fa7b888';
const M44_MAPPING_PATH = 'docs/content/m44-native-mapping.v1.0.0.json';
const M44_MAPPING_SHA = '9e9e3897d1858d5d305182cacd52e9da7761ab87c91c08876fed69fb46c8dcbd';
const M44_BASE_SHA = 'd3c5fa811e3e7eb5de2e348a6cd2d0bed3a76ba0';
const M44_BEFORE_PATH = 'database/content-patches/m44-authored-future-forms-before.json';
const M44_SOURCE_PATH = 'database/content-patches/m44-authored-future-forms.v1.0.0.json';
const M44_OWN_BANKS = [
    'Database\\Seeders\\V3\\Polyglot\\PolyglotWillVsBeGoingToAllLevelsLessonSeeder',
    'Database\\Seeders\\V3\\Polyglot\\PolyglotPresentContinuousForFutureAllLevelsLessonSeeder',
    'Database\\Seeders\\V3\\Polyglot\\PolyglotChoosingTheRightFutureFormAllLevelsLessonSeeder',
];
function m44Json(array $value, bool $pretty = false): string
{
    return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR | ($pretty ? JSON_PRETTY_PRINT : 0)).($pretty ? "\n" : '');
}
function m44Escape(string $value): string { return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8'); }
function m44Paragraphs(array $values): string { return implode('', array_map(fn (string $text) => '<p lang="uk">'.m44Escape($text).'</p>', $values)); }
function m44Examples(array $examples): string
{
    $out = '';
    foreach ($examples as $example) {
        if (!is_string($example['en'] ?? null) || !is_string($example['uk'] ?? null)) { throw new RuntimeException('M44 example translation incomplete.'); }
        $out .= '<div class="theory-example"><p lang="en">'.m44Escape($example['en']).'</p><p lang="uk" class="theory-translation">'.m44Escape($example['uk']).'</p>';
        if (isset($example['note_uk'])) { $out .= '<p lang="uk" class="theory-example-note">'.m44Escape($example['note_uk']).'</p>'; }
        $out .= '</div>';
    }
    return $out;
}
function m44ReadExact(string $root, string $path, string $sha): array
{
    $bytes = file_get_contents($root.'/'.$path);
    if (!is_string($bytes) || !hash_equals($sha, hash('sha256', $bytes)) || str_contains($bytes, "\r")
        || str_starts_with($bytes, "\xef\xbb\xbf") || !str_ends_with($bytes, "\n") || str_ends_with($bytes, "\n\n")) { throw new RuntimeException('M44 frozen UTF-8/LF source differs: '.$path); }
    return json_decode($bytes, true, flags: JSON_THROW_ON_ERROR);
}
function m44AuthorMaster(string $root): array
{
    $master = m44ReadExact($root, M44_AUTHOR_PATH, M44_AUTHOR_SHA);
    if ($master['schema'] !== 'gramlyze.authored-layer-master.v1' || $master['package'] !== 'M44' || $master['version'] !== '1.0.0'
        || $master['status'] !== 'authored_editorially_reviewed_ready_for_local_implementation' || $master['base_commit'] !== M44_BASE_SHA
        || $master['locale'] !== 'uk' || $master['policy']['personally_sentence_approved_by_user'] !== false
        || $master['policy']['implementation_authorized_by_user'] !== true || $master['policy']['frozen_master_edit'] !== false
        || array_keys($master['policy']) !== ['personally_sentence_approved_by_user', 'implementation_authorized_by_user', 'frozen_master_edit']
        || count($master['lessons']) !== 3) { throw new RuntimeException('M44 author identity/scope differs.'); }
    $ids = [];
    foreach ($master['lessons'] as $lesson) {
        if (count($lesson['practice']) !== 6) { throw new RuntimeException('M44 requires six original tasks per owner.'); }
        $points = array_merge(...array_column($lesson['sections'], 'points'));
        $controls = array_merge(...array_column($lesson['practice'], 'controls'));
        $details = array_column(array_filter($points, fn ($p) => isset($p['detail'])), 'detail');
        foreach (array_merge($lesson['sections'], $points, $details, $lesson['practice'], $controls) as $node) {
            if (!is_string($node['id'] ?? null) || !preg_match('/^m44-[a-z0-9-]+$/D', $node['id']) || isset($ids[$node['id']])) { throw new RuntimeException('M44 source ID absent/duplicated.'); }
            $ids[$node['id']] = true;
        }
    }
    return $master;
}
function m44NativeMapping(string $root): array
{
    $mapping = m44ReadExact($root, M44_MAPPING_PATH, M44_MAPPING_SHA);
    if ($mapping['schema'] !== 'gramlyze.m44-native-map.v1' || $mapping['base'] !== M44_BASE_SHA || count($mapping['targets']) !== 3) { throw new RuntimeException('M44 finite native mapping differs.'); }
    return $mapping;
}
function m44Marker(array $lesson, string $role, string $key): array
{
    return ['revision' => '1.0.0', 'master_sha256' => M44_AUTHOR_SHA, 'mapping_sha256' => M44_MAPPING_SHA,
        'lesson_key' => $lesson['key'], 'role' => $role, 'key' => $key];
}
function m44Section(array $lesson, array $section, array $mapping): array
{
    if ($mapping['section_id'] !== $section['id'] || $mapping['component'] !== $section['native_kind'] || count($mapping['points']) !== count($section['points'])) { throw new RuntimeException('M44 section mapping differs.'); }
    $plans = [];
    foreach ($section['points'] as $i => $point) {
        $map = $mapping['points'][$i]; $decision = isset($point['detail']) ? 'point_detail' : 'visible_basic';
        if ($map['id'] !== $point['id'] || $map['point_index'] !== $i || $map['decision'] !== $decision || $map['detail_id'] !== ($point['detail']['id'] ?? null)) { throw new RuntimeException('M44 detail point owner differs.'); }
        $plan = ['point_index' => $i, 'id' => $point['id'], 'title' => $point['title'], 'decision' => $decision,
            'reason' => $decision === 'point_detail' ? 'Explicit meaningful point-level author deepening; full basic remains visible.' : 'Complete visible basic; no runtime word threshold.'];
        if (isset($point['detail'])) { $detail = $point['detail']; $plan['detail'] = $detail;
            $plan['detail_html'] = '<h4 lang="uk">'.m44Escape($detail['title']).'</h4>'.m44Paragraphs($detail['paragraphs_uk']).m44Examples($detail['examples']); }
        $plans[] = $plan;
    }
    return ['title' => $section['title'], 'author_section' => $section, 'm44_native_design' => $mapping,
        'm44_v1' => m44Marker($lesson, 'section', $section['id']), 'point_plan' => $plans];
}
function m44Practice(array $practice, array $bank): array
{
    if (count($practice) !== 6 || !in_array($bank['seeder_class'] ?? null, M44_OWN_BANKS, true)
        || (string) ($bank['question_type'] ?? '') !== '4' || ($bank['question_count'] ?? 0) < 1
        || !preg_match('/^[a-f0-9]{64}$/D', $bank['question_ids_sha256'] ?? '')) { throw new RuntimeException('M44 actual own bank is not verified.'); }
    $cases = []; $prompts = []; $answers = []; $ids = [];
    foreach ($practice as $i => $task) {
        if ($task['source_index'] !== $i + 1 || $task['scoring'] !== 'all_required_controls' || !$task['prompt_uk'] || !$task['context_uk']) { throw new RuntimeException('M44 original task order/context differs.'); }
        $prompts[] = '<h4>'.m44Escape($task['title']).'</h4><p>'.m44Escape($task['prompt_uk']).'</p><p>'.m44Escape($task['context_uk']).'</p>';
        $answers[] = m44Examples($task['feedback']['answer_examples']).m44Paragraphs($task['feedback']['paragraphs_uk']);
        $controls = [];
        foreach ($task['controls'] as $control) {
            if (isset($ids[$control['id']]) || $control['required'] !== true || !in_array($control['kind'], ['select', 'choice', 'manual', 'tokens'], true)) { throw new RuntimeException('M44 unknown/optional/duplicate control.'); }
            $ids[$control['id']] = true;
            $kind = $control['kind'] === 'tokens' ? 'manual' : $control['kind'];
            $mapped = ['id' => $control['id'], 'kind' => $kind, 'source_kind' => $control['kind'], 'label' => $control['label_uk'], 'required' => true];
            if (isset($control['stimulus_en'])) { $mapped['stimulus_en'] = $control['stimulus_en']; }
            if ($kind === 'manual') {
                if (!in_array($control['canonical_answer'], $control['accepted_answers'], true) || empty($control['tokens'])
                    || ($control['kind'] === 'manual' && implode(' ', $control['tokens']) !== $control['canonical_answer'])) { throw new RuntimeException('M44 explicit answer/token contract differs.'); }
                if ($control['kind'] === 'tokens') {
                    $words = preg_split('/\s+/u', implode(' ', $control['tokens'])); $canonicalWords = preg_split('/\s+/u', $control['canonical_answer']);
                    sort($words); sort($canonicalWords); if ($words !== $canonicalWords) { throw new RuntimeException('M44 shuffled token bank changes lexical content or multiplicity.'); }
                }
                $mapped += ['options' => [], 'answer' => $control['canonical_answer'], 'accepted' => $control['accepted_answers'], 'tokens' => $control['tokens']];
            } else {
                $values = array_column($control['options'], 'value');
                if (count(array_filter($values, fn ($v) => $v === $control['correct_value'])) !== 1 || count(array_unique($values)) !== count($values)) { throw new RuntimeException('M44 correct option is ambiguous.'); }
                $mapped += ['options' => $control['options'], 'answer' => $control['correct_value']];
            }
            $controls[] = $mapped;
        }
        $cases[] = ['id' => $task['id'], 'scoring' => $task['scoring'], 'source_index' => $task['source_index'],
            'interaction' => count($controls) > 1 ? 'compound' : $controls[0]['kind'], 'controls' => $controls];
    }
    return ['title' => 'Практика', 'm44_practice_ui_v1' => ['revision' => 1], 'author_practice' => $practice,
        'author_self_check' => ['section_title' => 'Практика', 'intro' => '', 'title' => 'Відповідь і пояснення', 'prompts' => $prompts, 'answers' => $answers],
        'cases' => $cases, 'linked_practice' => ['source' => 'theory_links', 'question_types' => ['4'], 'seeder_classes' => [$bank['seeder_class']],
            'title' => 'Побудуй речення', 'intro' => 'Склади англійське речення за українським.', 'footer' => 'Завдання з наявного власного тесту цієї сторінки.']];
}
function m44Target(array $original, array $lesson, array $bank, int $owner, array $mapping): array
{
    if (($bank['seeder_class'] ?? null) !== M44_OWN_BANKS[$owner] || (string) ($bank['question_type'] ?? '') !== '4' || ($bank['question_count'] ?? null) !== 72
        || $original['seeder']['class'] !== $lesson['identity'] || $original['page']['title'] !== $lesson['title'] || $original['page']['locale'] !== 'uk'
        || $original['page']['category']['slug'] !== end($lesson['category_path']) || $mapping['identity'] !== $lesson['identity']
        || $mapping['theory_path'] !== $lesson['theory_path'] || count($mapping['section_order']) !== count($lesson['sections'])) { throw new RuntimeException('M44 finite author/owner/bank mapping differs.'); }
    $blocks = $original['page']['blocks']; $oldCount = count($blocks); $nav = $mapping['navigation_slot'];
    if ($blocks[0]['type'] !== 'hero' || $blocks[$nav]['type'] !== 'navigation-chips') { throw new RuntimeException('M44 original hero/navigation differs.'); }
    $oldHero = json_decode($blocks[0]['body'], true, flags: JSON_THROW_ON_ERROR);
    $hero = ['level' => $oldHero['level'], 'intro' => m44Escape($lesson['subtitle']), 'rules' => [], 'author_hero' => $lesson['hero'],
        'm44_v1' => m44Marker($lesson, 'hero', 'm44-'.$lesson['key'].'-hero')];
    foreach ($lesson['hero'] as $i => $rule) { $hero['rules'][] = ['label' => $rule['label'], 'color' => ['blue', 'emerald', 'amber', 'violet'][$i % 4], 'text' => m44Escape($rule['text']), 'example' => $rule['formula']]; }
    $blocks[0]['body'] = m44Json($hero);
    $plans = [['slot' => 0, 'role' => 'hero', 'key' => $hero['m44_v1']['key'], 'points' => []]];
    foreach ($lesson['sections'] as $i => $section) {
        $map = $mapping['section_order'][$i]; $slot = $map['slot'];
        if ($map['section_index'] !== $i || $slot === 0 || $slot === $nav) { throw new RuntimeException('M44 source slot collision.'); }
        $data = m44Section($lesson, $section, $map);
        $config = $slot < $oldCount ? $original['page']['blocks'][$slot] : ['column' => 'right', 'heading' => null,
            'level' => $original['page']['blocks'][0]['level'], 'uuid_key' => 'm44-'.$section['id'], 'inherit_base_tags' => false, 'tags' => []];
        $config['type'] = $section['native_kind']; $config['body'] = m44Json($data); $blocks[$slot] = $config;
        $plans[] = ['slot' => $slot, 'role' => 'section', 'key' => $data['m44_v1']['key'], 'section_id' => $section['id'], 'section_index' => $i, 'points' => $data['point_plan']];
    }
    $navigation = json_decode($original['page']['blocks'][$nav]['body'], true, flags: JSON_THROW_ON_ERROR);
    if (count($navigation['items']) !== count($lesson['resolved_navigation'])) { throw new RuntimeException('M44 actual navigation count differs.'); }
    foreach ($navigation['items'] as $i => &$item) { $resolved = $lesson['resolved_navigation'][$i];
        if ($item['url'] !== $resolved['old_url'] || $item['label'] !== $resolved['label']) { throw new RuntimeException('M44 actual navigation differs from independently resolved BEFORE.'); }
        $item['url'] = $resolved['url'];
    } unset($item);
    $blocks[$nav]['body'] = m44Json($navigation);
    $practice = m44Practice($lesson['practice'], $bank); $practice['m44_v1'] = m44Marker($lesson, 'practice', 'm44-'.$lesson['key'].'-practice');
    $practiceSlot = $mapping['practice_slot']; if (isset($blocks[$practiceSlot])) { throw new RuntimeException('M44 practice replaces an old identity.'); }
    $blocks[$practiceSlot] = ['type' => 'practice-set', 'column' => 'footer', 'heading' => null, 'level' => $original['page']['blocks'][0]['level'],
        'uuid_key' => $practice['m44_v1']['key'], 'inherit_base_tags' => false, 'tags' => [], 'body' => m44Json($practice)];
    $plans[] = ['slot' => $practiceSlot, 'role' => 'practice', 'key' => $practice['m44_v1']['key'], 'points' => []];
    ksort($blocks); if (array_keys($blocks) !== range(0, $practiceSlot) || count($blocks) - $oldCount !== $mapping['expected_inserts']) { throw new RuntimeException('M44 projected block coverage differs.'); }
    $after = $original; $after['page']['blocks'] = array_values($blocks); $after['page']['subtitle_text'] = $lesson['subtitle'];
    $after['page']['subtitle_html'] = '<p><strong>'.m44Escape($original['page']['title']).'</strong> — '.m44Escape($lesson['subtitle']).'</p>';
    return ['path' => $lesson['definition_path'], 'identity' => $lesson['identity'], 'slug' => $original['slug'], 'ancestry' => $lesson['category_path'],
        'native_count' => $oldCount, 'navigation_slot' => $nav, 'navigation_changes' => $mapping['navigation_changes'],
        'section_slots' => array_column($mapping['section_order'], 'slot'), 'practice_slot' => $practiceSlot, 'bank' => $bank, 'plans' => $plans, 'after' => $after];
}
