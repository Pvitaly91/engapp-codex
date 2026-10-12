<?php

namespace App\Support;

use App\Models\Page;
use Illuminate\Database\Eloquent\Collection;
use RuntimeException;

/** Finite M48 data binding/projection into existing native components; no view or CSS selection. */
final class M48ConditionalsPackage
{
    public const BASE_SHA = '662c58482c8fc36b4044f7aef8cd260223c3b242';
    public const MASTER = 'docs/content/m48-conditionals.v1.0.0.json';
    public const MASTER_SHA = '6dfe8e8859894bc65fd1ecfcaae487331763a54eeedb0c0b685e680278e9819c';
    public const BEFORE = 'database/content-patches/m48-conditionals-before.v1.json';
    public const BEFORE_SHA = 'f5288fd9b2bee018417e40b08b2a9f4e63f58a6ccb9301b14a088d9c0a48c94a';
    public const SOURCE = 'database/content-patches/m48-conditionals.v1.json';
    public const SOURCE_SHA = '4208b6f6bf760f898a260bdb1395ca3c5a47a6085cb41473b6feeb5a47c20a7d';
    public const OWNERS = [
        'Database\\Seeders\\Page_V3\\Conditionals\\ConditionalsInCaseTheorySeeder',
        'Database\\Seeders\\Page_V3\\Conditionals\\ConditionalsWishIfOnlyTheorySeeder',
        'Database\\Seeders\\Page_V3\\Conditionals\\ConditionalsWouldRatherTheorySeeder',
    ];

    public static function load(?string $root = null): array
    {
        $root ??= base_path(); static $cache = [];
        $out = [];
        foreach ([self::BEFORE => self::BEFORE_SHA, self::SOURCE => self::SOURCE_SHA, self::MASTER => self::MASTER_SHA] as $path => $sha) {
            $bytes = file_get_contents($root.'/'.$path);
            if (!hash_equals($sha, hash('sha256', $bytes))) { throw new RuntimeException('M48 immutable source differs: '.$path); }
            $out[] = $cache[$sha] ??= json_decode($bytes, true, flags: JSON_THROW_ON_ERROR);
        }
        return $out;
    }

    public static function definition(array $lesson, array $before): array
    {
        $after = $before;
        $after['page']['subtitle_html'] = $lesson['subtitle_html'];
        $after['page']['subtitle_text'] = $lesson['subtitle_text'];
        $after['page']['blocks'] = [];
        foreach ($lesson['blocks'] as $block) {
            $data = $block['data'];
            $details = array_values(array_filter($lesson['details'], static fn ($d) => $d['owner_block_key'] === $block['key']));
            $data['m48_native_v1'] = ['key' => $block['key'], 'detail_keys' => array_column($details, 'key')];
            foreach ($details as $detail) {
                $pointer = explode('/', $detail['owner_pointer']);
                $field = ['forms-grid' => 'items', 'usage-panels' => 'sections'][$block['type']] ?? null;
                if (count($pointer) !== 3 || $pointer[1] !== $field || !ctype_digit($pointer[2])
                    || !isset($data[$field][(int) $pointer[2]])) {
                    throw new RuntimeException('M48 detail owner is not an existing native point.');
                }
                $i = (int) $pointer[2];
                foreach ($detail['fragment']['data']['sections'] as $value) {
                    $examples = implode('<br><br>', array_map(static fn ($e) => $e['en'].'<br><span>'.$e['ua'].'</span>', $value['examples']));
                    if ($field === 'items') {
                        $data['items'][$i]['rules'][] = ['label' => $detail['title'],
                            'text' => '<strong>'.$value['label'].'</strong><br>'.$value['description'].'<br>'.($value['note'] ?? ''), 'example' => $examples,
                            'm48_detail_key' => $detail['key']];
                    } else {
                        $basic = $data['sections'][$i]['note'] ?? '';
                        $data['sections'][$i]['note'] = ($basic === '' ? '' : $basic.'<br><br>')
                            .'<strong>'.$detail['title'].'</strong><br><strong>'.$value['label'].'</strong><br>'
                            .$value['description'].'<br><br>'.$examples.'<br>'.($value['note'] ?? '');
                        $data['sections'][$i]['m48_detail_key'] = $detail['key'];
                    }
                }
            }
            $slot = $block['existing_source_index'] ?? null;
            $config = is_int($slot) ? $before['page']['blocks'][$slot]
                : ['type' => $block['type'], 'column' => $block['column']]
                    + (array_key_exists('level', $block) ? ['level' => $block['level']] : [])
                    + ['uuid_key' => 'm48:'.$lesson['slug'].':'.$block['key']];
            if ($config['type'] !== $block['type'] || $config['column'] !== $block['column']
                || ($config['level'] ?? null) !== ($block['level'] ?? null)) { throw new RuntimeException('M48 continuing block metadata differs.'); }
            $config['body'] = M26DetailPackage::json($data);
            $after['page']['blocks'][] = $config;
        }
        return $after;
    }

    public static function validate(array $before, array $package, array $master): void
    {
        if (array_column($before['targets'], 'identity') !== self::OWNERS
            || array_column($package['targets'], 'identity') !== self::OWNERS
            || array_column($master['lessons'], 'identity') !== self::OWNERS
            || $master['version'] !== '1.0.0' || $master['base_sha'] !== self::BASE_SHA) {
            throw new RuntimeException('M48 owner/version/base scope differs.');
        }
        foreach ($master['lessons'] as $i => $lesson) {
            $target = $package['targets'][$i]; $original = $before['targets'][$i];
            if ($lesson['locale'] !== 'uk' || $original['before']['seeder']['class'] !== self::OWNERS[$i]
                || $original['before']['page']['category']['slug'] !== 'conditionals'
                || $target['path'] !== $original['path'] || $target['ancestry'] !== ['conditionals']
                || $target['after'] !== self::definition($lesson, $original['before'])
                || $target['block_keys'] !== array_column($lesson['blocks'], 'key')) { throw new RuntimeException('M48 master/projection fidelity differs.'); }
            $oldSlots = array_values(array_filter(array_column($lesson['blocks'], 'existing_source_index'), 'is_int'));
            if ($oldSlots !== range(0, count($original['before']['page']['blocks']) - 1)) { throw new RuntimeException('M48 old block slots/order changed.'); }
            $practice = array_values(array_filter($lesson['blocks'], static fn ($b) => $b['type'] === 'practice-set'));
            if (count($practice) !== 1) { throw new RuntimeException('M48 requires one existing native practice-set per lesson.'); }
            $tasks = [...($practice[0]['data']['selects'] ?? []), ...($practice[0]['data']['choices'] ?? [])];
            if (count($tasks) !== 6 || array_column($tasks, 'source_index') !== range(1, 6)) { throw new RuntimeException('M48 six-task identity differs.'); }
            foreach ($tasks as $task) {
                if (!in_array($task['answer'], $task['accepted'], true)) { throw new RuntimeException('M48 canonical answer missing.'); }
                foreach ($task['options'] ?? $practice[0]['data']['choice_options'] as $option) {
                    if (!is_string($task['feedback'][mb_strtolower(trim($option))] ?? null)) { throw new RuntimeException('M48 option feedback missing.'); }
                }
            }
            self::assertLearningData($lesson['blocks']);
            self::assertLearningData($lesson['details']);
            $owners = []; $detailKeys = [];
            foreach ($lesson['details'] as $detail) {
                $owner = $detail['owner_block_key'].':'.$detail['owner_pointer'];
                if (isset($owners[$owner]) || isset($detailKeys[$detail['key']])) { throw new RuntimeException('M48 duplicate detail owner/key.'); }
                $owners[$owner] = true; $detailKeys[$detail['key']] = true;
                if (($detail['fragment']['type'] ?? null) !== 'usage-panels' || empty($detail['fragment']['data']['sections'])) {
                    throw new RuntimeException('M48 detail must contain existing native fragments.');
                }
                foreach ($detail['fragment']['data']['sections'] as $section) {
                    if (empty($section['label']) || empty($section['description']) || empty($section['examples'])) {
                        throw new RuntimeException('M48 detail fragment is incomplete.');
                    }
                    foreach ($section['examples'] as $example) {
                        if (empty($example['en']) || empty($example['ua'])) { throw new RuntimeException('M48 detail translation missing.'); }
                    }
                }
            }
            foreach ($lesson['blocks'] as $b) {
                foreach ($b['data']['sections'] ?? [] as $s) {
                    if (empty($s['label']) || !in_array($s['color'], ['blue', 'emerald', 'sky', 'amber', 'rose', 'slate'], true)) {
                        throw new RuntimeException('M48 unnamed point or unsupported semantic role.');
                    }
                    foreach ($s['examples'] ?? [] as $e) { if (empty($e['en']) || empty($e['ua'])) { throw new RuntimeException('M48 bilingual example missing.'); } }
                }
            }
        }
    }

    /** Author learning data cannot select an executable view or introduce presentation classes/styles. */
    private static function assertLearningData(array $values): void
    {
        foreach ($values as $key => $value) {
            if (is_string($key) && in_array(strtolower($key), ['view', 'native_view', 'stylesheet', 'css_class', 'class', 'style'], true)) {
                throw new RuntimeException('M48 learning payload contains presentation selection.');
            }
            if (is_array($value)) { self::assertLearningData($value); }
            elseif (is_string($value) && preg_match('~<[^>]*\\s(?:class|style|on[a-z]+)\\s*=|<(?:script|style|iframe)\\b~i', $value)) {
                throw new RuntimeException('M48 learning payload contains executable/styled markup.');
            }
        }
    }

    private static function binding(object $block, array $data): ?array
    {
        if (($block->locale ?? null) !== 'uk' || !isset($data['m48_native_v1'])) { return null; }
        try {
            [$before, $package, $master] = self::load();
            self::validate($before, $package, $master);
            foreach ($package['targets'] as $i => $target) {
                if (($block->seeder ?? null) !== $target['identity']) { continue; }
                foreach ($target['after']['page']['blocks'] as $slot => $source) {
                    if (($block->uuid ?? null) !== M26DetailPackage::uuid($target['identity'], $source, $slot + 1)) { continue; }
                    foreach (['type', 'column', 'heading', 'level', 'css_class'] as $field) {
                        if (($block->$field ?? null) !== ($source[$field] ?? null)) { return null; }
                    }
                    if ((int) ($block->sort_order ?? -1) !== $slot + 1 || $data !== json_decode($source['body'], true)) { return null; }
                    return ['target' => $target, 'lesson' => $master['lessons'][$i], 'source' => $source, 'slot' => $slot];
                }
            }
        } catch (\Throwable) { /* Complete stored native fields remain the fallback. */ }
        return null;
    }

    private static function design(array $binding): array
    {
        return ['package' => 'm48-native-v1', 'identity' => $binding['target']['identity'], 'locale' => 'uk',
            'uuid' => M26DetailPackage::uuid($binding['target']['identity'], $binding['source'], $binding['slot'] + 1),
            'source_index' => $binding['slot'], 'body_sha256' => hash('sha256', $binding['source']['body']),
            'component' => $binding['source']['type']];
    }

    public static function presentation(object $block, array $data): ?array
    {
        $binding = self::binding($block, $data);
        if ($binding === null) { return null; }
        $points = []; $key = $data['m48_native_v1']['key'];
        foreach ($binding['lesson']['details'] as $detail) {
            if ($detail['owner_block_key'] !== $key) { continue; }
            $index = (int) explode('/', $detail['owner_pointer'])[2];
            $field = explode('/', $detail['owner_pointer'])[1];
            if ($field === 'items') {
                $rules = $data['items'][$index]['rules'];
                foreach ($detail['fragment']['data']['sections'] as $_) {
                    $last = array_pop($rules);
                    if (($last['m48_detail_key'] ?? null) !== $detail['key']) { return null; }
                }
                $data['items'][$index]['rules'] = $rules;
            } else {
                if (($data['sections'][$index]['m48_detail_key'] ?? null) !== $detail['key']) { return null; }
                $basic = $binding['lesson']['blocks'][$binding['slot']]['data']['sections'][$index];
                if (array_key_exists('note', $basic)) { $data['sections'][$index]['note'] = $basic['note']; }
                else { unset($data['sections'][$index]['note']); }
                unset($data['sections'][$index]['m48_detail_key']);
            }
            $points[$index] = ['key' => $detail['key'], 'title' => $detail['title'],
                'fragments' => array_map(static fn ($value, $n) => ['id' => $detail['key'].'-detail-'.$n,
                    'type' => 'usage-panels', 'value' => $value], $detail['fragment']['data']['sections'],
                    array_keys($detail['fragment']['data']['sections']))];
        }
        return ['data' => $data, 'points' => $points, 'legacy_section' => null, 'legacy_practice_id' => null,
            'm42_native_design' => self::design($binding)];
    }

    public static function validDesign(?array $design): bool
    {
        if (($design['package'] ?? null) !== 'm48-native-v1') { return false; }
        try {
            [, $package, $master] = self::load();
            foreach ($package['targets'] as $target) {
                foreach ($target['after']['page']['blocks'] as $slot => $source) {
                    if ($design === self::design(['target' => $target, 'source' => $source, 'slot' => $slot])) { return true; }
                }
            }
        } catch (\Throwable) {}
        return false;
    }

    /** Semantic role/whole-point labels fill the existing usage model, never frozen M42 maps. */
    public static function pointPanels(?array $design): ?array
    {
        if (!self::validDesign($design) || $design['component'] !== 'usage-panels') { return null; }
        [, $package] = self::load();
        foreach ($package['targets'] as $target) {
            if ($target['identity'] !== $design['identity']) { continue; }
            $data = json_decode($target['after']['page']['blocks'][$design['source_index']]['body'], true);
            $points = [];
            foreach ($data['sections'] as $i => $s) {
                $points[$i] = ['label' => $s['label'], 'accent' => $s['color'], 'description_sha256' => [hash('sha256', $s['description'])]];
            }
            return ['points_by_index' => $points];
        }
        return null;
    }

    /** The existing correction component already has distinct EN/UK fields. */
    public static function correction(array $item, ?array $design): ?array
    {
        if (!self::validDesign($design) || $design['component'] !== 'mistakes-grid') { return null; }
        [, $package] = self::load();
        $target = $package['targets'][array_search($design['identity'], self::OWNERS, true)];
        $expected = json_decode($target['after']['page']['blocks'][$design['source_index']]['body'], true)['items'];
        if (!in_array($item, $expected, true)) { return null; }
        return ['wrong_en' => TheoryInlineHtml::render($item['wrong_en']), 'wrong_uk' => TheoryInlineHtml::render($item['wrong_ua']),
            'right_en' => TheoryInlineHtml::render($item['right_en']), 'right_uk' => TheoryInlineHtml::render($item['right_ua'])];
    }

    /** Clone only a fully matching accepted UK owner; courses keep the exact previous lesson. */
    public static function preserveCoursePage(?Page $page): ?Page
    {
        if ($page === null || !in_array($page->getRawOriginal('seeder'), self::OWNERS, true)) { return $page; }
        try {
            [$before, $package] = self::load();
            $i = array_search($page->getRawOriginal('seeder'), self::OWNERS, true);
            $target = $package['targets'][$i]; $old = $before['targets'][$i]['before'];
            $blocks = $page->textBlocks;
            $uk = $blocks->where('locale', 'uk')->values();
            if (count($uk) !== count($target['after']['page']['blocks']) + 1 || $page->getRawOriginal('text') !== $target['after']['page']['subtitle_text']) { return $page; }
            foreach ($target['after']['page']['blocks'] as $slot => $source) {
                $model = $uk->firstWhere('uuid', M26DetailPackage::uuid($target['identity'], $source, $slot + 1));
                if ($model === null || self::binding($model, json_decode($model->body, true)) === null) { return $page; }
            }
            $copy = clone $page;
            $copy->setRawAttributes(array_replace($page->getAttributes(), ['text' => $old['page']['subtitle_text']]), true);
            $restored = $blocks->reject(fn ($b) => $b->locale === 'uk')->values();
            $subtitle = $uk->firstWhere('type', 'subtitle');
            if ($subtitle === null || $subtitle->body !== $target['after']['page']['subtitle_html']) { return $page; }
            $clone = clone $subtitle; $clone->setRawAttributes(array_replace($subtitle->getAttributes(), ['body' => $old['page']['subtitle_html']]), true); $restored->push($clone);
            foreach ($old['page']['blocks'] as $slot => $source) {
                $model = $uk->firstWhere('uuid', M26DetailPackage::uuid($target['identity'], $source, $slot + 1));
                $clone = clone $model; $clone->setRawAttributes(array_replace($model->getAttributes(), ['body' => $source['body']]), true); $restored->push($clone);
            }
            $copy->setRelation('textBlocks', new Collection($restored->sortBy('sort_order')->values()->all()));
            return $copy;
        } catch (\Throwable) { return $page; }
    }
}
