<?php

// Finite mechanical transfer. No Laravel bootstrap, database, HTTP or author edits.
require_once __DIR__.'/m43-practice-projection.php';
const M43_AUTHOR_PATH = 'docs/content/m43-authored-tense-usage.v1.0.0.json';
const M43_AUTHOR_SHA = 'd9afc130ec223202ea83781147f9966855227d3194ccd7707c9b3c037b62e2f5';
const M43_MAPPING_PATH = 'docs/content/m43-native-mapping.v1.0.0.json';
const M43_MAPPING_SHA = 'c66cd63305d39753b98c5aaa4c53a1814ab4a60006a9c923dbba2b1fbb02cd9f';
const M43_BASE_SHA = 'f1a552303cbc46713fa4a34e71fd6be01cc25a42';
const M43_BEFORE_PATH = 'database/content-patches/m43-authored-tense-usage-before.json';
const M43_SOURCE_PATH = 'database/content-patches/m43-authored-tense-usage.v1.0.0.json';
const M43_BASE_BLOBS = ['9dfe02f3a08197c89a0ec2d88c3e436def06c5f0', '0f29a9aef0c9e21f219e405d449fade1a0630c9a', '9b44c659f6e9d9e9de9387bec198bc4b7cf4344d'];
const M43_OWN_BANKS = [
    'Database\\Seeders\\V3\\Polyglot\\PolyglotPastPerfectVsPastPerfectContinuousAllLevelsLessonSeeder',
    'Database\\Seeders\\V3\\Polyglot\\PolyglotStativeVerbsAllLevelsLessonSeeder',
    'Database\\Seeders\\V3\\Polyglot\\PolyglotUsedToWouldAllLevelsLessonSeeder',
];

function m43Json(array $value, bool $pretty = false): string
{
    return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
        | ($pretty ? JSON_PRETTY_PRINT : 0)).($pretty ? "\n" : '');
}
function m43Escape(string $text): string { return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8'); }
function m43Paragraphs(array $items): string { return implode('', array_map(fn (string $text) => '<p lang="uk">'.m43Escape($text).'</p>', $items)); }
function m43Examples(array $examples): string
{
    $out = '';
    foreach ($examples as $example) {
        if (!is_string($example['en'] ?? null) || !is_string($example['uk'] ?? null)) {
            throw new RuntimeException('M43 author example/translation incomplete.');
        }
        $out .= '<div class="theory-example"><p lang="en">'.m43Escape($example['en']).'</p><p lang="uk" class="theory-translation">'
            .m43Escape($example['uk']).'</p>';
        if (isset($example['note_uk'])) { $out .= '<p lang="uk" class="theory-example-note">'.m43Escape($example['note_uk']).'</p>'; }
        $out .= '</div>';
    }
    return $out;
}
function m43ReadExact(string $root, string $path, string $sha): array
{
    $bytes = file_get_contents($root.'/'.$path);
    if (!is_string($bytes) || !hash_equals($sha, hash('sha256', $bytes))) { throw new RuntimeException('M43 exact frozen source differs: '.$path); }
    return json_decode($bytes, true, flags: JSON_THROW_ON_ERROR);
}
function m43AuthorMaster(string $root): array
{
    $master = m43ReadExact($root, M43_AUTHOR_PATH, M43_AUTHOR_SHA);
    if ($master['schema'] !== 'gramlyze.authored-layer-master.v1' || $master['package'] !== 'M43'
        || $master['version'] !== '1.0.0' || $master['status'] !== 'authored_editorially_reviewed_ready_for_local_implementation'
        || $master['base_commit'] !== M43_BASE_SHA || $master['locale'] !== 'uk'
        || $master['policy']['codex_authors_new_teaching_text'] !== true
        || $master['policy']['personally_sentence_approved_by_user'] !== false
        || $master['policy']['implementation_authorized_by_user'] !== true
        || $master['policy']['frozen_master_edit'] !== false || $master['policy']['production_access'] !== false
        || $master['policy']['bank_writes'] !== false || count($master['lessons']) !== 3) {
        throw new RuntimeException('M43 frozen authorship/identity/scope differs.');
    }
    $ids = [];
    foreach ($master['lessons'] as $lesson) {
        $points = array_merge(...array_column($lesson['sections'], 'points'));
        $controls = array_merge(...array_column($lesson['practice'], 'controls'));
        $details = array_column(array_filter($points, fn ($point) => isset($point['detail'])), 'detail');
        if (count($lesson['practice']) !== 6) { throw new RuntimeException('M43 requires six authored tasks per lesson.'); }
        foreach (array_merge($lesson['sections'], $points, $details, $lesson['practice'], $controls) as $node) {
            if (!is_string($node['id'] ?? null) || !preg_match('/^[a-z0-9-]+$/D', $node['id']) || isset($ids[$node['id']])) {
                throw new RuntimeException('M43 stable source ID absent/duplicated.');
            }
            $ids[$node['id']] = true;
        }
    }
    return $master;
}
function m43NativeMapping(string $root): array
{
    $mapping = m43ReadExact($root, M43_MAPPING_PATH, M43_MAPPING_SHA);
    if ($mapping['schema'] !== 'gramlyze.m43-native-map.v1' || $mapping['base'] !== M43_BASE_SHA || count($mapping['targets']) !== 3) {
        throw new RuntimeException('M43 native mapping scope differs.');
    }
    return $mapping;
}
function m43Marker(array $lesson, string $role, string $key): array
{
    return ['revision' => '1.0.0', 'master_sha256' => M43_AUTHOR_SHA, 'mapping_sha256' => M43_MAPPING_SHA,
        'lesson_key' => $lesson['key'], 'role' => $role, 'key' => $key];
}
function m43Section(array $lesson, array $section, array $mapping): array
{
    if ($mapping['section_id'] !== $section['id'] || $mapping['component'] !== $section['native_kind']
        || count($mapping['points']) !== count($section['points'])) { throw new RuntimeException('M43 semantic section mapping differs.'); }
    $plans = [];
    foreach ($section['points'] as $i => $point) {
        $mapped = $mapping['points'][$i];
        $decision = isset($point['detail']) ? 'point_detail' : 'visible_basic';
        if ($mapped['id'] !== $point['id'] || $mapped['point_index'] !== $i || $mapped['decision'] !== $decision
            || $mapped['detail_id'] !== ($point['detail']['id'] ?? null)) { throw new RuntimeException('M43 exact point ownership differs.'); }
        $plan = ['point_index' => $i, 'id' => $point['id'], 'title' => $point['title'], 'decision' => $decision,
            'reason' => $decision === 'point_detail' ? 'Explicit meaningful author detail; basic remains visible.' : 'Complete authored basic remains visible.'];
        if (isset($point['detail'])) {
            $detail = $point['detail'];
            $plan['detail'] = $detail;
            $plan['detail_html'] = '<h4 lang="uk">'.m43Escape($detail['title']).'</h4>'.m43Paragraphs($detail['paragraphs_uk'])
                .m43Examples($detail['examples']);
        }
        $plans[] = $plan;
    }
    // The complete original author section is authoritative for every native
    // field and the static fallback. No inferred HTML replacement of its prose.
    return ['title' => $section['title'], 'author_section' => $section, 'm43_native_design' => $mapping,
        'm43_v1' => m43Marker($lesson, 'section', $section['id']), 'point_plan' => $plans];
}
function m43Target(array $original, array $lesson, array $bank, int $owner, array $mapping): array
{
    if (!isset(M43_OWN_BANKS[$owner]) || ($bank['seeder_class'] ?? null) !== M43_OWN_BANKS[$owner]
        || ($bank['question_count'] ?? null) !== 72 || (string) ($bank['question_type'] ?? '') !== '4'
        || !preg_match('/^[a-f0-9]{64}$/D', (string) ($bank['question_ids_sha256'] ?? ''))) {
        throw new RuntimeException('M43 exact own-bank evidence differs.');
    }
    if ($original['seeder']['class'] !== $lesson['identity'] || $original['page']['title'] !== $lesson['title']
        || $original['page']['locale'] !== 'uk' || $original['page']['category']['slug'] !== 'tenses'
        || $mapping['identity'] !== $lesson['identity'] || $mapping['definition_path'] !== $lesson['definition_path']
        || $mapping['theory_path'] !== $lesson['theory_path'] || count($mapping['section_order']) !== count($lesson['sections'])) {
        throw new RuntimeException('M43 owner metadata differs.');
    }
    $blocks = $original['page']['blocks']; $nativeCount = count($blocks); $navSlot = $mapping['navigation_slot'];
    if ($blocks[0]['type'] !== 'hero' || $blocks[$navSlot]['type'] !== 'navigation-chips') { throw new RuntimeException('M43 original hero/navigation differs.'); }
    $oldHero = json_decode($blocks[0]['body'], true, flags: JSON_THROW_ON_ERROR);
    $hero = ['level' => $oldHero['level'], 'intro' => m43Escape($lesson['subtitle']), 'rules' => [],
        'author_hero' => $lesson['hero'], 'm43_v1' => m43Marker($lesson, 'hero', 'm43-'.$lesson['key'].'-hero')];
    foreach ($lesson['hero'] as $i => $rule) {
        $hero['rules'][] = ['label' => $rule['label'], 'color' => ['blue', 'emerald', 'violet'][$i],
            'text' => m43Escape($rule['text']), 'example' => $rule['formula']];
    }
    $blocks[0]['body'] = m43Json($hero);
    $plans = [['slot' => 0, 'role' => 'hero', 'key' => $hero['m43_v1']['key'], 'points' => []]];
    foreach ($lesson['sections'] as $i => $section) {
        $map = $mapping['section_order'][$i]; $slot = $map['slot'];
        if ($map['section_index'] !== $i || $slot === 0 || $slot === $navSlot) { throw new RuntimeException('M43 native slot collision.'); }
        $data = m43Section($lesson, $section, $map);
        $config = $slot < $nativeCount ? $original['page']['blocks'][$slot]
            : ['column' => 'right', 'heading' => null, 'level' => $original['page']['blocks'][0]['level'],
                'uuid_key' => 'm43-'.$section['id'], 'inherit_base_tags' => false, 'tags' => []];
        $config['type'] = $section['native_kind']; $config['body'] = m43Json($data); $blocks[$slot] = $config;
        $plans[] = ['slot' => $slot, 'role' => 'section', 'key' => $data['m43_v1']['key'], 'section_id' => $section['id'],
            'section_index' => $i, 'points' => $data['point_plan']];
    }
    $practice = m43Practice($lesson['practice'], $bank);
    $practice['author_practice'] = $lesson['practice'];
    $practice['m43_v1'] = m43Marker($lesson, 'practice', 'm43-'.$lesson['key'].'-practice');
    $practiceSlot = $mapping['practice_slot'];
    if (isset($blocks[$practiceSlot])) { throw new RuntimeException('M43 practice would replace an existing block.'); }
    $blocks[$practiceSlot] = ['type' => 'practice-set', 'column' => 'footer', 'heading' => null,
        'level' => $original['page']['blocks'][0]['level'], 'uuid_key' => $practice['m43_v1']['key'],
        'inherit_base_tags' => false, 'tags' => [], 'body' => m43Json($practice)];
    $plans[] = ['slot' => $practiceSlot, 'role' => 'practice', 'key' => $practice['m43_v1']['key'], 'points' => []];
    ksort($blocks);
    if (array_keys($blocks) !== range(0, $practiceSlot) || $blocks[$navSlot] !== $original['page']['blocks'][$navSlot]) {
        throw new RuntimeException('M43 changed original navigation or left a block-index gap.');
    }
    $after = $original; $after['page']['blocks'] = array_values($blocks);
    $after['page']['subtitle_text'] = $lesson['subtitle'];
    $after['page']['subtitle_html'] = '<p><strong>'.m43Escape($original['page']['title']).'</strong> — '.m43Escape($lesson['subtitle']).'</p>';
    return ['path' => $lesson['definition_path'], 'identity' => $lesson['identity'], 'slug' => $original['slug'],
        'ancestry' => $lesson['category_path'], 'native_count' => $nativeCount, 'navigation_slot' => $navSlot,
        'navigation_additions' => [], 'section_slots' => array_column($mapping['section_order'], 'slot'),
        'practice_slot' => $practiceSlot, 'bank' => $bank, 'after' => $after, 'plans' => $plans,
        'detail_quality_audit' => array_merge(...array_column(array_filter($plans, fn ($p) => $p['role'] === 'section'), 'points'))];
}
