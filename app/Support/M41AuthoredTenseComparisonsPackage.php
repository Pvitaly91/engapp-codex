<?php

namespace App\Support;

use RuntimeException;

/** Finite approved author revision: no prose editing, threshold or inferred owner. */
final class M41AuthoredTenseComparisonsPackage
{
    public const MASTER_PATH = 'docs/content/m41-authored-tense-comparisons.v1.0.1.json';
    public const MASTER_SHA = '9020cf977e903c76f93c7165bbe402567b5fbf40a98a8139dc6751b223b34553';
    public const ORIGINAL_SHA = 'a9569a61f37643951e7342de66dc7f3967951d4d7187c106a0f8703cf3653dcf';
    public const CORRECTION_PATH = 'docs/content/m41-author-correction.v1.0.1.json';
    public const CORRECTION_SHA = 'efe1d4ed3c691b8108e168d7dc182937fb9667ab96f192f2375d055c97137f89';
    public const APPROVAL_PATH = 'docs/content/m41-codex-approval-and-correction.md';
    public const APPROVAL_SHA = '1c74999e7f50ea3b7f1b3d363a8bd42d96f092c334d486893b78273072119e40';
    public const BASE_SHA = 'ab61310a81f2389353c264fe23266025fe49ffd6';
    public const BEFORE = 'database/content-patches/m41-authored-tense-comparisons-before.json';
    public const BEFORE_SHA = '5966e2b5e330030fc399b77a5a80880a57462daac4b1869f94b4027cdbcae808';
    public const SOURCE = 'database/content-patches/m41-authored-tense-comparisons.v1.0.1.json';
    public const SOURCE_SHA = '1324f0627dcd53b70b135b78c06204c3a77b6e02fe0a48056ac7750b650becfc';

    public static function load(?string $root = null): array
    {
        $root ??= base_path(); $out = [];
        foreach ([self::BEFORE => self::BEFORE_SHA, self::SOURCE => self::SOURCE_SHA] as $path => $sha) {
            $bytes = file_get_contents($root.'/'.$path);
            if (!hash_equals($sha, hash('sha256', $bytes))) { throw new RuntimeException('M41 immutable projection differs: '.$path); }
            $out[] = json_decode($bytes, true, flags: JSON_THROW_ON_ERROR);
        }
        self::authorMaster($out[0], $root);
        return $out;
    }

    /** Exact supplied bytes are bound independently of platform checkout EOL. */
    public static function authorMaster(array $before, ?string $root = null): array
    {
        $root ??= base_path(); $sources = [
            'author_master_source' => [self::MASTER_PATH, self::MASTER_SHA],
            'author_correction_source' => [self::CORRECTION_PATH, self::CORRECTION_SHA],
            'author_approval_source' => [self::APPROVAL_PATH, self::APPROVAL_SHA],
        ];
        foreach ($sources as $key => [$path, $sha]) {
            $record = $before[$key] ?? []; $bytes = $record['bytes'] ?? null;
            if (($record['path'] ?? null) !== $path || ($record['sha256'] ?? null) !== $sha
                || !is_string($bytes) || hash('sha256', $bytes) !== $sha) {
                throw new RuntimeException('M41 exact author handoff snapshot differs.');
            }
            if (is_file($root.'/'.$path) && file_get_contents($root.'/'.$path) !== $bytes) {
                throw new RuntimeException('M41 actual author handoff bytes differ.');
            }
        }
        $bytes = $before['author_master_source']['bytes'];
        $master = json_decode($bytes, true, flags: JSON_THROW_ON_ERROR);
        $correction = json_decode($before['author_correction_source']['bytes'], true, flags: JSON_THROW_ON_ERROR);
        if ($correction['original_sha256'] !== self::ORIGINAL_SHA || $correction['new_sha256'] !== self::MASTER_SHA
            || array_column($correction['changes'], 'json_pointer') !== ['/version', '/status', '/lessons/2/sections/2/points/1/paragraphs_uk/1']) {
            throw new RuntimeException('M41 author correction allowlist differs.');
        }
        $original = $bytes;
        foreach ($correction['changes'] as $change) {
            $after = json_encode($change['after'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
            $old = json_encode($change['before'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
            if (substr_count($original, $after) !== 1) { throw new RuntimeException('M41 author correction is ambiguous.'); }
            $original = str_replace($after, $old, $original);
        }
        if (hash('sha256', $original) !== self::ORIGINAL_SHA || $before['base_sha'] !== self::BASE_SHA
            || $master['version'] !== '1.0.1' || $master['status'] !== 'author_revision_ready_for_local_implementation'
            || $master['base_commit'] !== self::BASE_SHA || $master['locale'] !== 'uk' || count($master['lessons']) !== 3
            || $master['policy']['codex_authors_new_teaching_text'] !== false
            || $master['policy']['frozen_master_edit'] !== false || $master['policy']['production_access'] !== false
            || $master['policy']['bank_writes'] !== false) {
            throw new RuntimeException('M41 immutable history, identity or author policy differs.');
        }
        return $master;
    }

    public static function json(array $value): string { return M26DetailPackage::json($value); }
    private static function escape(string $value): string { return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8'); }
    private static function paragraphs(array $values): string { return implode('', array_map(fn ($value) => '<p>'.self::escape($value).'</p>', $values)); }

    public static function validate(array $before, array $package): void
    {
        [$expectedBefore, $expectedPackage] = self::load();
        if ($before !== $expectedBefore || $package !== $expectedPackage) {
            throw new RuntimeException('M41 differs from the accepted finite author projection.');
        }
        $master = self::authorMaster($before); $detailCounts = [];
        foreach ($package['targets'] as $i => $target) {
            $lesson = $master['lessons'][$i]; $old = $before['targets'][$i]['before']; $after = $target['after'];
            if ($target['identity'] !== $lesson['identity'] || $target['path'] !== $lesson['definition_path']
                || $before['targets'][$i]['source_git_blob'] !== $lesson['baseline_git_blob_sha1']
                || $target['ancestry'] !== $lesson['category_path'] || $after['page']['locale'] !== 'uk'
                || $after['page']['title'] !== $lesson['preserve']['page_title'] || count($after['page']['blocks']) !== 9
                || $after['page']['subtitle_text'] !== $lesson['subtitle']
                || $after['page']['subtitle_html'] !== '<p><strong>'.self::escape($old['page']['title']).'</strong> — '.self::escape($lesson['subtitle']).'</p>') {
                throw new RuntimeException('M41 protected owner metadata or approved subtitle differs.');
            }
            $restored = $after; $restored['page']['blocks'] = $old['page']['blocks'];
            $restored['page']['subtitle_text'] = $old['page']['subtitle_text']; $restored['page']['subtitle_html'] = $old['page']['subtitle_html'];
            if ($restored !== $old) { throw new RuntimeException('M41 changed protected definition metadata.'); }
            foreach ($old['page']['blocks'] as $slot => $original) {
                $config = $after['page']['blocks'][$slot]; $config['type'] = $original['type']; $config['body'] = $original['body'];
                if ($config !== $original || M26DetailPackage::uuid($target['identity'], $original, $slot + 1)
                    !== M26DetailPackage::uuid($target['identity'], $after['page']['blocks'][$slot], $slot + 1)) {
                    throw new RuntimeException('M41 changed original column/level/order/UUID/configuration.');
                }
            }
            $navOld = json_decode($old['page']['blocks'][$target['navigation_slot']]['body'], true, flags: JSON_THROW_ON_ERROR);
            $nav = json_decode($after['page']['blocks'][$target['navigation_slot']]['body'], true, flags: JSON_THROW_ON_ERROR);
            if ($nav['title'] !== $navOld['title'] || array_slice($nav['items'], 0, count($navOld['items'])) !== $navOld['items']) {
                throw new RuntimeException('M41 changed an existing related link.');
            }
            foreach (array_slice($nav['items'], count($navOld['items'])) as $item) {
                if (!in_array(['label' => $item['label'], 'path' => $item['url']], $lesson['related'], true)
                    || ($item['current'] ?? null) !== false) { throw new RuntimeException('M41 invented related navigation.'); }
            }
            $details = 0; $keys = [];
            foreach ($target['plans'] as $plan) {
                $source = $after['page']['blocks'][$plan['slot']]; $data = json_decode($source['body'], true, flags: JSON_THROW_ON_ERROR);
                if (isset($keys[$plan['key']]) || $data['m41_v1']['key'] !== $plan['key']
                    || $data['m41_v1']['master_sha256'] !== self::MASTER_SHA || $data['m41_v1']['role'] !== $plan['role']) {
                    throw new RuntimeException('M41 moved or duplicated a finite section.');
                }
                $keys[$plan['key']] = true;
                if ($plan['role'] === 'hero') {
                    $originalHero = json_decode($old['page']['blocks'][0]['body'], true, flags: JSON_THROW_ON_ERROR);
                    if ($data['level'] !== $originalHero['level'] || $data['author_hero'] !== $lesson['hero']
                        || $data['intro'] !== self::escape($lesson['subtitle'])) { throw new RuntimeException('M41 hero fidelity or technical level differs.'); }
                } elseif ($plan['role'] === 'section') {
                    $section = $lesson['sections'][$plan['section_index']];
                    if ($data['author_section'] !== $section || $data['title'] !== $section['title']
                        || count($data['sections']) !== count($section['points'])) { throw new RuntimeException('M41 complete author section differs.'); }
                    foreach ($section['points'] as $j => $point) {
                        $p = $plan['points'][$j]; $native = $data['sections'][$j];
                        if ($p['id'] !== $point['id'] || $native['label'] !== $point['title']
                            || $native['description'] !== self::paragraphs($point['paragraphs_uk'])
                            || $native['examples'] !== array_map(fn ($e) => ['en' => $e['en'], 'ua' => $e['uk']], $point['examples'])
                            || $p['decision'] !== (isset($point['detail']) ? 'point_detail' : 'visible_basic')) {
                            throw new RuntimeException('M41 basic/example/translation or explicit detail ownership differs.');
                        }
                        if (isset($point['detail'])) {
                            $details++;
                            if ($p['detail'] !== $point['detail'] || $native['note'] !== $p['detail_html']) {
                                throw new RuntimeException('M41 exact author detail differs.');
                            }
                        }
                    }
                } elseif ($plan['role'] === 'practice') {
                    if ($data['author_practice'] !== $lesson['practice'] || array_column($data['cases'], 'source_index') !== range(1, 6)) {
                        throw new RuntimeException('M41 reordered or rewrote an author task.');
                    }
                    foreach ($lesson['practice'] as $j => $task) {
                        $case = $data['cases'][$j];
                        if ($case['id'] !== $task['id'] || count($case['controls']) !== count($task['controls'])) {
                            throw new RuntimeException('M41 omitted a required compound part.');
                        }
                        foreach ($task['controls'] as $k => $control) {
                            $c = $case['controls'][$k];
                            if ($c['id'] !== $control['id'] || $c['kind'] !== $control['kind'] || $c['label'] !== $control['label_uk']
                                || $c['required'] !== true || ($c['stimulus_en'] ?? null) !== ($control['stimulus_en'] ?? null)) {
                                throw new RuntimeException('M41 control or stimulus fidelity differs.');
                            }
                            if ($control['kind'] === 'manual') {
                                if ($c['answer'] !== $control['canonical_answer'] || $c['accepted'] !== $control['accepted_answers']
                                    || $c['tokens'] !== $control['tokens'] || implode(' ', $c['tokens']) !== $c['answer']) {
                                    throw new RuntimeException('M41 invented a manual answer, token or alias.');
                                }
                            } elseif ($c['options'] !== $control['options'] || $c['answer'] !== $control['correct_value']) {
                                throw new RuntimeException('M41 invented an answer candidate.');
                            }
                        }
                    }
                } else { throw new RuntimeException('M41 unknown finite role.'); }
            }
            $detailCounts[] = $details;
        }
        if ($detailCounts !== [4, 5, 5]) { throw new RuntimeException('M41 source-defined disclosure ownership differs.'); }
    }

    /** Only a fixed, code-owned view can be selected after complete identity validation. */
    public static function presentation(object $block, array $data): ?array
    {
        if (!isset($data['m41_v1']) || ($block->locale ?? null) !== 'uk') { return null; }
        try {
            [$before, $package] = self::load(); self::validate($before, $package);
            foreach ($package['targets'] as $target) {
                if (($block->seeder ?? null) !== $target['identity']) { continue; }
                foreach ($target['plans'] as $plan) {
                    $slot = $plan['slot']; $source = $target['after']['page']['blocks'][$slot];
                    if (($block->uuid ?? null) !== M26DetailPackage::uuid($target['identity'], $source, $slot + 1)) { continue; }
                    if (($block->type ?? null) !== $source['type'] || (int) ($block->sort_order ?? -1) !== $slot + 1
                        || $data !== json_decode($source['body'], true, flags: JSON_THROW_ON_ERROR)) { return null; }
                    $basic = $data; $points = [];
                    foreach ($plan['points'] as $point) {
                        if ($point['decision'] !== 'point_detail') { continue; }
                        unset($basic['sections'][$point['point_index']]['note']);
                        $key = 'm41-'.$point['id'];
                        $points[$point['point_index']] = ['key' => $key, 'title' => $point['title'], 'fragments' => [
                            ['id' => 'block-'.$key.'-detail', 'type' => 'm41-author-html', 'value' => $point['detail_html']],
                        ]];
                    }
                    return ['data' => $basic, 'points' => $points, 'legacy_section' => null, 'legacy_practice_id' => null,
                        'native_view' => $plan['role'] === 'section' ? 'engram.theory.blocks-v3.m41-author-section' : null];
                }
            }
        } catch (\Throwable) { /* Full stored author basic, detail, tables and keys remain readable. */ }
        return null;
    }
}
