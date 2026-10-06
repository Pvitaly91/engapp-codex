<?php

// Deterministic technical transfer of the approved author payload. No Laravel,
// database, Git, HTTP, or startup hooks; no authoring and no semantic grading.
require_once __DIR__.'/m41-practice-projection.php';
const M41_AUTHOR_PATH = 'docs/content/m41-authored-tense-comparisons.v1.0.1.json';
const M41_AUTHOR_SHA = '9020cf977e903c76f93c7165bbe402567b5fbf40a98a8139dc6751b223b34553';
const M41_ORIGINAL_AUTHOR_SHA = 'a9569a61f37643951e7342de66dc7f3967951d4d7187c106a0f8703cf3653dcf';
const M41_CORRECTION_PATH = 'docs/content/m41-author-correction.v1.0.1.json';
const M41_CORRECTION_SHA = 'efe1d4ed3c691b8108e168d7dc182937fb9667ab96f192f2375d055c97137f89';
const M41_APPROVAL_PATH = 'docs/content/m41-codex-approval-and-correction.md';
const M41_APPROVAL_SHA = '1c74999e7f50ea3b7f1b3d363a8bd42d96f092c334d486893b78273072119e40';
const M41_BASE_SHA = 'ab61310a81f2389353c264fe23266025fe49ffd6';
const M41_BEFORE_PATH = 'database/content-patches/m41-authored-tense-comparisons-before.json';
const M41_SOURCE_PATH = 'database/content-patches/m41-authored-tense-comparisons.v1.0.1.json';

function m41Json(array $value, bool $pretty = false): string
{
    return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
        | ($pretty ? JSON_PRETTY_PRINT : 0)).($pretty ? "\n" : '');
}

function m41Escape(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
}

function m41Paragraphs(array $paragraphs): string
{
    return implode('', array_map(fn (string $text): string => '<p>'.m41Escape($text).'</p>', $paragraphs));
}

function m41Examples(array $examples): string
{
    $out = '';
    foreach ($examples as $example) {
        if (!is_string($example['en'] ?? null) || !is_string($example['uk'] ?? null)) {
            throw new RuntimeException('M41 refuses an example without its exact author translation.');
        }
        $out .= '<div class="theory-example"><p lang="en">'.m41Escape($example['en'])
            .'</p><p lang="uk" class="theory-translation">'.m41Escape($example['uk']).'</p></div>';
    }
    return $out;
}

function m41Table(array $table, string $label): string
{
    $out = '<div class="theory-table-scroll" tabindex="0" role="region" aria-label="'.m41Escape($label).'"><table><thead><tr>';
    foreach ($table['columns'] as $column) { $out .= '<th scope="col">'.m41Escape($column).'</th>'; }
    $out .= '</tr></thead><tbody>';
    foreach ($table['rows'] as $row) {
        if (count($row) !== count($table['columns'])) { throw new RuntimeException('M41 table arity differs.'); }
        $out .= '<tr>';
        foreach ($row as $i => $cell) {
            $out .= ($i === 0 ? '<th scope="row">' : '<td>').implode('<br>', array_map(m41Escape(...), explode("\n", $cell)))
                .($i === 0 ? '</th>' : '</td>');
        }
        $out .= '</tr>';
    }
    return $out.'</tbody></table></div>';
}

function m41Marker(array $lesson, string $role, string $key): array
{
    return ['revision' => '1.0.1', 'master_sha256' => M41_AUTHOR_SHA,
        'lesson_key' => $lesson['key'], 'role' => $role, 'key' => $key];
}

function m41AuthorMaster(string $root): array
{
    $bytes = file_get_contents($root.'/'.M41_AUTHOR_PATH);
    // This approval binds exact supplied UTF-8/LF bytes, not a repaired checksum.
    if (!hash_equals(M41_AUTHOR_SHA, hash('sha256', $bytes))) {
        throw new RuntimeException('M41 exact approved author bytes differ; stop without writes.');
    }
    foreach ([M41_CORRECTION_PATH => M41_CORRECTION_SHA, M41_APPROVAL_PATH => M41_APPROVAL_SHA] as $path => $sha) {
        if (!hash_equals($sha, hash_file('sha256', $root.'/'.$path))) {
            throw new RuntimeException('M41 exact author correction/approval provenance differs.');
        }
    }
    $correction = json_decode(file_get_contents($root.'/'.M41_CORRECTION_PATH), true, flags: JSON_THROW_ON_ERROR);
    if ($correction['original_sha256'] !== M41_ORIGINAL_AUTHOR_SHA || $correction['new_sha256'] !== M41_AUTHOR_SHA
        || array_column($correction['changes'], 'json_pointer') !== ['/version', '/status', '/lessons/2/sections/2/points/1/paragraphs_uk/1']) {
        throw new RuntimeException('M41 exact three-change author correction allowlist differs.');
    }
    $originalBytes = $bytes;
    foreach ($correction['changes'] as $change) {
        $after = json_encode($change['after'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        $before = json_encode($change['before'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        if (substr_count($originalBytes, $after) !== 1) { throw new RuntimeException('M41 author correction is absent or ambiguous.'); }
        $originalBytes = str_replace($after, $before, $originalBytes);
    }
    if (!hash_equals(M41_ORIGINAL_AUTHOR_SHA, hash('sha256', $originalBytes))) {
        throw new RuntimeException('M41 reverse correction does not reproduce exact frozen original v1 bytes.');
    }
    $master = json_decode($bytes, true, flags: JSON_THROW_ON_ERROR);
    if ($master['schema'] !== 'gramlyze.authored-layer-preview.v1' || $master['package'] !== 'M41'
        || $master['version'] !== '1.0.1' || $master['status'] !== 'author_revision_ready_for_local_implementation'
        || $master['base_commit'] !== M41_BASE_SHA || $master['locale'] !== 'uk'
        || $master['policy']['production_access'] !== false || $master['policy']['bank_writes'] !== false
        || $master['policy']['codex_authors_new_teaching_text'] !== false || count($master['lessons']) !== 3) {
        throw new RuntimeException('M41 approved identity or author policy differs.');
    }
    $expected = [[12, 4, 11], [13, 5, 10], [10, 5, 11]];
    $ids = [];
    foreach ($master['lessons'] as $i => $lesson) {
        $points = array_merge(...array_column($lesson['sections'], 'points'));
        $controls = array_merge(...array_column($lesson['practice'], 'controls'));
        if (count($lesson['sections']) !== 6 || count($lesson['practice']) !== 6
            || [count($points), count(array_filter($points, fn ($point) => isset($point['detail']))), count($controls)] !== $expected[$i]) {
            throw new RuntimeException('M41 finite point/detail/task/control scope differs.');
        }
        foreach (array_merge($lesson['sections'], $points, $lesson['practice'], $controls) as $node) {
            if (!is_string($node['id'] ?? null) || isset($ids[$node['id']])) { throw new RuntimeException('M41 duplicate/missing author ID.'); }
            $ids[$node['id']] = true;
        }
        foreach ($lesson['practice'] as $task) {
            if ($task['scoring'] !== 'all_required_controls') { throw new RuntimeException('M41 compound scoring policy differs.'); }
            foreach ($task['controls'] as $control) {
                if (($control['required'] ?? null) !== true || !in_array($control['kind'], ['select', 'choice', 'manual'], true)) {
                    throw new RuntimeException('M41 omitted required control or unknown interaction.');
                }
                if ($control['kind'] === 'manual') {
                    if (!in_array($control['canonical_answer'], $control['accepted_answers'], true)
                        || implode(' ', $control['tokens']) !== $control['canonical_answer']) {
                        throw new RuntimeException('M41 finite answer/token fidelity differs.');
                    }
                } elseif (count(array_filter($control['options'], fn ($option) => $option['value'] === $control['correct_value'])) !== 1) {
                    throw new RuntimeException('M41 correct option identity differs.');
                }
            }
        }
    }
    return $master;
}

function m41Section(array $lesson, array $section): array
{
    $points = []; $audit = [];
    foreach ($section['points'] as $i => $point) {
        $native = ['label' => $point['title'], 'color' => $point['kind'] === 'warning' ? 'amber' : 'blue',
            'description' => m41Paragraphs($point['paragraphs_uk']),
            'examples' => array_map(fn ($example) => ['en' => $example['en'], 'ua' => $example['uk']], $point['examples'])];
        $plan = ['point_index' => $i, 'id' => $point['id'], 'title' => $point['title'],
            'decision' => 'visible_basic', 'reason' => 'The complete authored basic point, including short caveats, stays visible.'];
        if (isset($point['detail'])) {
            $detail = $point['detail'];
            // Full detail remains stored/readable in native fallback; only the
            // finite approved presentation moves it into its own disclosure.
            $native['note'] = '<h4>'.m41Escape($detail['title']).'</h4>'.m41Paragraphs($detail['paragraphs_uk'])
                .m41Examples($detail['examples']);
            $plan['decision'] = 'point_detail';
            $plan['reason'] = 'Explicit author detail: independent explanatory depth with contextual examples; not a runtime word threshold.';
            $plan['detail'] = $detail;
            $plan['detail_html'] = $native['note'];
        }
        $points[] = $native; $audit[] = $plan;
    }
    return ['title' => $section['title'], 'intro' => isset($section['table']) ? m41Table($section['table'], $section['title']) : '',
        'sections' => $points, 'author_section' => $section,
        'm41_v1' => m41Marker($lesson, 'section', 'm41-'.$section['id']), 'point_plan' => $audit];
}

function m41Target(array $original, array $lesson, array $bank, int $owner, array $verifiedRelated = []): array
{
    $bankClasses = [
        'Database\\Seeders\\V3\\Polyglot\\PolyglotPastSimpleVsPastContinuousAllLevelsLessonSeeder',
        'Database\\Seeders\\V3\\Polyglot\\PolyglotPresentSimpleVsPresentContinuousAllLevelsLessonSeeder',
        'Database\\Seeders\\V3\\Polyglot\\PolyglotPresentPerfectVsPastSimpleLessonSeeder',
    ];
    if (!in_array($owner, [0, 1, 2], true) || ($bank['seeder_class'] ?? null) !== $bankClasses[$owner]
        || ($bank['question_count'] ?? null) !== [72, 72, 24][$owner]
        || (string) ($bank['question_type'] ?? '') !== '4'
        || count(array_unique($bank['question_ids'] ?? [])) !== ($bank['question_count'] ?? null)) {
        throw new RuntimeException('M41 actual approved own-bank owner/count/type scope differs.');
    }
    $blocks = $original['page']['blocks']; $nativeCount = count($blocks);
    $sectionSlots = $owner === 2 ? [1, 2, 3, 4, 6, 7] : [1, 2, 3, 4, 5, 6];
    $navSlot = $owner === 2 ? 5 : 7;
    if ($nativeCount !== ($owner === 2 ? 6 : 8) || $blocks[0]['type'] !== 'hero' || $blocks[$navSlot]['type'] !== 'navigation-chips'
        || $original['seeder']['class'] !== $lesson['identity'] || $original['page']['title'] !== $lesson['title']
        || $original['page']['locale'] !== 'uk' || $original['page']['category']['slug'] !== 'tenses'
        || $original['slug'] !== $lesson['preserve']['slug']) {
        throw new RuntimeException('M41 accepted native owner/metadata/slot mapping differs.');
    }
    $oldHero = json_decode($blocks[0]['body'], true, flags: JSON_THROW_ON_ERROR);
    $hero = ['level' => $oldHero['level'], 'intro' => m41Escape($lesson['subtitle']), 'rules' => [],
        'author_hero' => $lesson['hero'], 'm41_v1' => m41Marker($lesson, 'hero', 'm41-'.$lesson['key'].'-hero')];
    foreach ($lesson['hero'] as $i => $rule) {
        $hero['rules'][] = ['label' => $rule['label'], 'color' => ['blue', 'emerald', 'violet'][$i],
            'text' => m41Escape($rule['text']), 'example' => $rule['formula']];
    }
    $blocks[0]['body'] = m41Json($hero); $plans = [['slot' => 0, 'role' => 'hero', 'key' => $hero['m41_v1']['key'], 'points' => []]];
    foreach ($lesson['sections'] as $i => $section) {
        $slot = $sectionSlots[$i]; $data = m41Section($lesson, $section);
        $config = $slot < $nativeCount ? $original['page']['blocks'][$slot]
            : ['column' => 'right', 'heading' => null, 'level' => $original['page']['blocks'][0]['level'],
                'uuid_key' => 'm41-'.$section['id'], 'inherit_base_tags' => false, 'tags' => []];
        $config['type'] = 'usage-panels'; $config['body'] = m41Json($data); $blocks[$slot] = $config;
        $plans[] = ['slot' => $slot, 'role' => 'section', 'key' => $data['m41_v1']['key'], 'section_id' => $section['id'],
            'section_index' => $i, 'points' => $data['point_plan']];
    }
    $practice = m41Practice($lesson['practice'], $bank);
    $practice['author_practice'] = $lesson['practice'];
    $practice['m41_v1'] = m41Marker($lesson, 'practice', 'm41-'.$lesson['key'].'-practice');
    $blocks[8] = ['type' => 'practice-set', 'column' => 'footer', 'heading' => null,
        'level' => $original['page']['blocks'][0]['level'], 'uuid_key' => $practice['m41_v1']['key'],
        'inherit_base_tags' => false, 'tags' => [], 'body' => m41Json($practice)];
    $plans[] = ['slot' => 8, 'role' => 'practice', 'key' => $practice['m41_v1']['key'], 'points' => []];
    ksort($blocks); $blocks = array_values($blocks);
    if ($blocks[$navSlot] !== $original['page']['blocks'][$navSlot] || count($blocks) !== 9) {
        throw new RuntimeException('M41 preserved native navigation/configuration differs.');
    }
    $navigation = json_decode($blocks[$navSlot]['body'], true, flags: JSON_THROW_ON_ERROR); $additions = [];
    foreach ($lesson['related'] as $related) {
        if (in_array($related['path'], array_column($navigation['items'], 'url'), true)) { continue; }
        if (!isset($verifiedRelated[$related['path']]) || $verifiedRelated[$related['path']]['http_status'] !== 200
            || $verifiedRelated[$related['path']]['redirects'] !== 0) {
            throw new RuntimeException('M41 refuses an unresolved additional author-related route.');
        }
        $item = ['label' => $related['label'], 'url' => $related['path'], 'current' => false];
        $navigation['items'][] = $item; $additions[] = $item;
    }
    if ($additions !== []) { $blocks[$navSlot]['body'] = m41Json($navigation); }
    $after = $original; $after['page']['blocks'] = $blocks;
    $after['page']['subtitle_text'] = $lesson['subtitle'];
    // The established controller extracts localized Page.title from the first
    // strong subtitle phrase. Preserve approved H1/title identity in this
    // technical wrapper; do not let the author's opening em dash rename it.
    $after['page']['subtitle_html'] = '<p><strong>'.m41Escape($original['page']['title']).'</strong> — '.m41Escape($lesson['subtitle']).'</p>';
    // Raw local row IDs/test URLs remain in private inventory, never committed.
    $publicBank = array_intersect_key($bank, array_flip(['seeder_class', 'question_type', 'level', 'levels', 'question_count']));
    $publicBank['question_ids_sha256'] = hash('sha256', m41Json(array_values($bank['question_ids'])));
    return ['path' => $lesson['definition_path'], 'identity' => $lesson['identity'], 'slug' => $original['slug'],
        'ancestry' => $lesson['category_path'], 'native_count' => $nativeCount, 'navigation_slot' => $navSlot,
        'navigation_additions' => $additions, 'section_slots' => $sectionSlots, 'practice_slot' => 8, 'bank' => $publicBank, 'after' => $after, 'plans' => $plans,
        'detail_quality_audit' => array_merge(...array_map(fn ($plan) => $plan['points'], array_filter($plans, fn ($plan) => $plan['role'] === 'section')))];
}
