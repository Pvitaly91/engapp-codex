<?php

namespace App\Support;

use App\Models\Page;
use App\Models\TextBlock;
use Illuminate\Database\Eloquent\Collection;
use RuntimeException;

/** Finite M51 learning-data binding only. Existing native serializer, components and answer engines are unchanged. */
final class M51ModalMeaningsPackage
{
    public const BASE_SHA = '7b0ca49b3cbfd7135b80f6931cdd42834eb24c53';
    public const MASTER = 'docs/content/m51-modal-meanings.v1.0.0.json';
    public const MASTER_SHA = 'e127bc998980367ab363af09636c02cfc4b7a9ba6436e19a9428416f5a168d67';
    public const BEFORE = 'database/content-patches/m51-modal-meanings-before.v1.json';
    public const BEFORE_SHA = 'abd557eb8b5d51da0a3eda3b0cd23de2010c3dbd5aaf00717f750952db1556f0';
    public const SOURCE = 'database/content-patches/m51-modal-meanings.v1.json';
    public const SOURCE_SHA = '8efd1224abc2424405060ae52374777b755741e13d9314374cd2599701974f6d';
    public const OWNERS = M51QuestionSupplement::OWNERS;
    public const BANKS = M51QuestionSupplement::BANKS;
    public const SLUGS = M51QuestionSupplement::SLUGS;
    public const PAGE_IDS = [73, 77, 78];
    public const TECHNICAL_LEVELS = ['B1', 'B1', 'A2'];
    public const DISPLAY_LEVELS = ['B1-B2', 'B1-B2', 'A2-B1'];

    public static function load(?string $root = null): array
    {
        $root ??= base_path(); $out = [];
        foreach ([self::BEFORE => self::BEFORE_SHA, self::SOURCE => self::SOURCE_SHA, self::MASTER => self::MASTER_SHA] as $path => $sha) {
            if (preg_match('/^[a-f0-9]{64}$/D', $sha) !== 1 || !is_file($root.'/'.$path) || is_link($root.'/'.$path)) {
                throw new RuntimeException('M51 release is not reviewed/frozen: '.$path);
            }
            $bytes = file_get_contents($root.'/'.$path);
            if (!is_string($bytes) || !hash_equals($sha, hash('sha256', $bytes))) { throw new RuntimeException('M51 immutable source differs: '.$path); }
            $out[] = json_decode($bytes, true, flags: JSON_THROW_ON_ERROR);
        }

        return $out;
    }

    /** Reuse the accepted serializer, remapping only code-owned provenance/detail/new-UUID keys. */
    public static function definition(array $lesson, array $before): array
    {
        $after = M49AdviceModalsPackage::definition($lesson, $before);
        foreach ($after['page']['blocks'] as &$block) {
            $data = json_decode($block['body'], true, flags: JSON_THROW_ON_ERROR);
            $data['m51_native_v1'] = $data['m49_native_v1']; unset($data['m49_native_v1']);
            foreach ($data['sections'] ?? [] as $i => $section) {
                if (isset($section['m49_detail_key'])) {
                    $data['sections'][$i]['m51_detail_key'] = $section['m49_detail_key']; unset($data['sections'][$i]['m49_detail_key']);
                }
            }
            foreach ($data['items'] ?? [] as $i => $item) {
                foreach ($item['rules'] ?? [] as $n => $rule) {
                    if (isset($rule['m49_detail_key'])) {
                        $data['items'][$i]['rules'][$n]['m51_detail_key'] = $rule['m49_detail_key']; unset($data['items'][$i]['rules'][$n]['m49_detail_key']);
                    }
                }
            }
            if (isset($block['uuid_key']) && str_starts_with($block['uuid_key'], 'm49:')) { $block['uuid_key'] = 'm51:'.substr($block['uuid_key'], 4); }
            $block['body'] = M26DetailPackage::json($data);
        }
        unset($block);

        return $after;
    }

    public static function validate(array $before, array $patch, array $master): void
    {
        foreach ([$before, $patch, $master] as $artifact) {
            if (($artifact['schema_version'] ?? null) !== 1 || ($artifact['base_sha'] ?? null) !== self::BASE_SHA) {
                throw new RuntimeException('M51 schema/base differs.');
            }
        }
        if (($master['version'] ?? null) !== '1.0.0' || array_column($before['targets'] ?? [], 'identity') !== self::OWNERS
            || array_column($patch['targets'] ?? [], 'identity') !== self::OWNERS || array_column($master['lessons'] ?? [], 'identity') !== self::OWNERS) {
            throw new RuntimeException('M51 exact owner/version scope differs.');
        }
        $taskIds = [];
        foreach ($master['lessons'] as $i => $lesson) {
            $old = $before['targets'][$i]; $target = $patch['targets'][$i]; $path = TheorySentenceBuilderAcceptance::definitionPath(self::OWNERS[$i]);
            if (($old['path'] ?? null) !== $path || ($old['before']['seeder']['class'] ?? null) !== self::OWNERS[$i]
                || ($old['before']['page']['locale'] ?? null) !== 'uk' || ($old['before']['type'] ?? null) !== 'theory'
                || ($old['before']['page']['category']['slug'] ?? null) !== 'modal-verbs'
                || ($lesson['locale'] ?? null) !== 'uk' || ($lesson['slug'] ?? null) !== self::SLUGS[$i]
                || ($lesson['technical_block_level'] ?? null) !== self::TECHNICAL_LEVELS[$i]
                || ($lesson['display_level'] ?? null) !== self::DISPLAY_LEVELS[$i]
                || ($lesson['blocks'][0]['data']['level'] ?? null) !== self::DISPLAY_LEVELS[$i]
                || ($target['path'] ?? null) !== $path || ($target['slug'] ?? null) !== self::SLUGS[$i]
                || ($target['ancestry'] ?? null) !== ['modal-verbs']
                || ($target['page_binding'] ?? null) !== ['id' => self::PAGE_IDS[$i], 'page_category_id' => 15]
                || ($target['local_url'] ?? null) !== 'http://gramlyze.loc/theory/modal-verbs/'.self::SLUGS[$i]
                || ($target['after'] ?? null) !== self::definition($lesson, $old['before'])
                || ($target['block_keys'] ?? null) !== array_column($lesson['blocks'], 'key')) {
                throw new RuntimeException('M51 exact owner/projection/locale/level fidelity differs.');
            }
            if (preg_match('/^\s*<p>\s*<strong>(.*?)<\/strong>/su', $lesson['subtitle_html'] ?? '', $title) !== 1
                || html_entity_decode(strip_tags($title[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8') !== $old['before']['page']['title']) {
                throw new RuntimeException('M51 supported subtitle must retain the exact leading existing title.');
            }
            if (count($old['before']['page']['blocks']) !== 6 || count($lesson['blocks']) !== 7
                || array_column($lesson['blocks'], 'existing_source_index') !== [0, 1, 2, 3, 4, 5, null]
                || array_column($lesson['blocks'], 'type') !== ['hero', 'forms-grid', 'usage-panels', 'mistakes-grid', 'summary-list', 'navigation-chips', 'practice-set']) {
                throw new RuntimeException('M51 six continuing slots and one appended native practice differ.');
            }
            foreach ($lesson['blocks'] as $block) {
                if ($block['type'] !== 'navigation-chips' && ($block['level'] ?? null) !== self::TECHNICAL_LEVELS[$i]) {
                    throw new RuntimeException('M51 per-owner technical block level differs.');
                }
                self::assertSections($block['data']['sections'] ?? []);
            }
            $practice = $lesson['blocks'][6]['data'];
            if (count($practice['selects'] ?? []) !== 2 || count($practice['choices'] ?? []) !== 2 || count($practice['inputs'] ?? []) !== 2
                || array_column([...$practice['selects'], ...$practice['choices'], ...$practice['inputs']], 'source_index') !== range(1, 6)
                || ($practice['linked_practice']['source'] ?? null) !== 'theory_links'
                || ($practice['linked_practice']['question_types'] ?? null) !== ['4']
                || ($practice['linked_practice']['seeder_classes'] ?? null) !== [self::BANKS[$i]]) {
                throw new RuntimeException('M51 complete own practice or exact thematic bank differs.');
            }
            foreach (['selects', 'choices', 'inputs'] as $group) {
                foreach ($practice[$group] as $task) {
                    if (!is_string($task['id'] ?? null) || $task['id'] === '' || isset($taskIds[$task['id']])
                        || !in_array($task['answer'] ?? null, $task['accepted'] ?? [], true)) { throw new RuntimeException('M51 task key/canonical answer differs.'); }
                    $taskIds[$task['id']] = true;
                    foreach ($task['options'] ?? ($group === 'choices' ? ($practice['choice_options'] ?? ['a', 'b']) : $task['accepted']) as $option) {
                        if (!is_string($task['feedback'][mb_strtolower(trim($option))] ?? null)) { throw new RuntimeException('M51 explicit visible option feedback missing.'); }
                    }
                }
            }
            foreach ($practice['inputs'] as $input) {
                if (!is_string($input['before'] ?? null) || !str_contains($input['before'], '/')
                    || !is_string($input['after'] ?? null) || preg_match('/[\x{0400}-\x{04FF}]/u', $input['after']) !== 1) {
                    throw new RuntimeException('M51 full native token bank or visible Ukrainian input condition missing.');
                }
            }
            $details = []; $owners = [];
            foreach ($lesson['details'] as $detail) {
                $owner = $detail['owner_block_key'].':'.$detail['owner_pointer'];
                if (isset($details[$detail['key']]) || isset($owners[$owner]) || ($detail['fragment']['type'] ?? null) !== 'usage-panels'
                    || empty($detail['fragment']['data']['sections'])) { throw new RuntimeException('M51 duplicate/incomplete semantic detail owner.'); }
                $details[$detail['key']] = true; $owners[$owner] = true;
                self::assertSections($detail['fragment']['data']['sections']);
                foreach ($detail['fragment']['data']['sections'] as $section) {
                    if (empty($section['description']) || empty($section['examples'])) { throw new RuntimeException('M51 meaningful detail fragment missing.'); }
                }
            }
            self::assertLearningData($lesson['blocks']); self::assertLearningData($lesson['details']);
        }
    }

    private static function assertSections(array $sections): void
    {
        foreach ($sections as $section) {
            if (empty($section['label']) || !in_array($section['color'] ?? null, ['blue', 'emerald', 'sky', 'amber', 'rose', 'slate'], true)) {
                throw new RuntimeException('M51 unnamed native point or unsupported semantic accent.');
            }
            foreach ($section['examples'] ?? [] as $example) {
                if (empty($example['en']) || empty($example['ua'])) { throw new RuntimeException('M51 exact bilingual example missing.'); }
            }
        }
    }

    private static function assertLearningData(array $values): void
    {
        foreach ($values as $key => $value) {
            if (is_string($key) && in_array(strtolower($key), ['view', 'native_view', 'stylesheet', 'css_class', 'class', 'style'], true)) {
                throw new RuntimeException('M51 learning payload selects presentation.');
            }
            if (is_array($value)) { self::assertLearningData($value); }
            elseif (is_string($value) && preg_match('~<[^>]*\s(?:class|style|on[a-z]+)\s*=|<(?:script|style|iframe)\b~i', $value)) {
                throw new RuntimeException('M51 learning payload contains executable/styled markup.');
            }
        }
    }

    private static function binding(object $block, array $data): ?array
    {
        if (($block->locale ?? null) !== 'uk' || !in_array($block->seeder ?? null, self::OWNERS, true) || !isset($data['m51_native_v1'])) { return null; }
        try {
            [$before, $patch, $master] = self::load(); self::validate($before, $patch, $master);
            $i = array_search($block->seeder, self::OWNERS, true); $target = $patch['targets'][$i];
            if (($block->page_id ?? null) !== $target['page_binding']['id'] || ($block->page_category_id ?? null) !== $target['page_binding']['page_category_id']) { return null; }
            foreach ($target['after']['page']['blocks'] as $slot => $source) {
                if (($block->uuid ?? null) !== M26DetailPackage::uuid($target['identity'], $source, $slot + 1)) { continue; }
                foreach (['type', 'column', 'heading', 'level', 'css_class'] as $field) { if (($block->$field ?? null) !== ($source[$field] ?? null)) { return null; } }
                if ((int) ($block->sort_order ?? -1) !== $slot + 1 || ($block->body ?? null) !== $source['body']
                    || $data !== json_decode($source['body'], true, flags: JSON_THROW_ON_ERROR)) { return null; }

                return ['target' => $target, 'lesson' => $master['lessons'][$i], 'source' => $source, 'slot' => $slot];
            }
        } catch (\Throwable) { /* Rejected bindings keep all stored author text in the native fallback. */ }

        return null;
    }

    private static function design(array $binding): array
    {
        return ['package' => 'm51-native-v1', 'identity' => $binding['target']['identity'], 'locale' => 'uk',
            'uuid' => M26DetailPackage::uuid($binding['target']['identity'], $binding['source'], $binding['slot'] + 1),
            'source_index' => $binding['slot'], 'body_sha256' => hash('sha256', $binding['source']['body']), 'component' => $binding['source']['type']];
    }

    public static function presentation(object $block, array $data): ?array
    {
        if (app()->getLocale() !== 'uk') { return null; }
        $binding = self::binding($block, $data);
        if ($binding === null) { return null; }
        $points = [];
        foreach ($binding['lesson']['details'] as $detail) {
            if ($detail['owner_block_key'] !== $data['m51_native_v1']['key']) { continue; }
            $index = (int) explode('/', $detail['owner_pointer'])[2];
            $points[$index] = ['key' => $detail['key'], 'title' => $detail['title'],
                'fragments' => array_map(static fn ($section, $n) => ['id' => $detail['key'].'-detail-'.$n, 'type' => 'usage-panels', 'value' => $section],
                    $detail['fragment']['data']['sections'], array_keys($detail['fragment']['data']['sections']))];
        }

        return ['data' => $binding['lesson']['blocks'][$binding['slot']]['data'] + ['m51_native_v1' => $data['m51_native_v1']],
            'points' => $points, 'legacy_section' => null, 'legacy_practice_id' => null, 'm42_native_design' => self::design($binding)];
    }

    public static function validDesign(?array $design): bool
    {
        if (($design['package'] ?? null) !== 'm51-native-v1') { return false; }
        try {
            [, $patch] = self::load();
            foreach ($patch['targets'] as $target) {
                foreach ($target['after']['page']['blocks'] as $slot => $source) { if ($design === self::design(compact('target', 'source', 'slot'))) { return true; } }
            }
        } catch (\Throwable) {}

        return false;
    }

    public static function pointPanels(?array $design): ?array
    {
        if (!self::validDesign($design) || $design['component'] !== 'usage-panels') { return null; }
        [, , $master] = self::load(); $i = array_search($design['identity'], self::OWNERS, true); $points = [];
        foreach ($master['lessons'][$i]['blocks'][$design['source_index']]['data']['sections'] as $n => $section) {
            $points[$n] = ['label' => $section['label'], 'accent' => $section['color'], 'description_sha256' => [hash('sha256', $section['description'])]];
        }

        return ['points_by_index' => $points];
    }

    public static function correction(array $item, ?array $design): ?array
    {
        if (!self::validDesign($design) || $design['component'] !== 'mistakes-grid') { return null; }
        [, $patch] = self::load(); $i = array_search($design['identity'], self::OWNERS, true);
        $items = json_decode($patch['targets'][$i]['after']['page']['blocks'][$design['source_index']]['body'], true)['items'];
        if (!in_array($item, $items, true)) { return null; }

        return ['wrong_en' => TheoryInlineHtml::render($item['wrong_en']), 'wrong_uk' => TheoryInlineHtml::render($item['wrong_ua']),
            'right_en' => TheoryInlineHtml::render($item['right_en']), 'right_uk' => TheoryInlineHtml::render($item['right_ua'])];
    }

    private static function subtitleSource(array $old): array
    {
        return ['type' => 'subtitle', 'column' => 'header', 'heading' => null, 'css_class' => null,
            'level' => $old['page']['subtitle_level'] ?? null, 'uuid_key' => $old['page']['subtitle_uuid_key'] ?? 'subtitle'];
    }

    private static function exactSubtitle(object $block, array $target, array $old): bool
    {
        $source = self::subtitleSource($old);
        $expected = ['uuid' => M26DetailPackage::uuid($target['identity'], $source, 0), 'page_id' => $target['page_binding']['id'],
            'page_category_id' => $target['page_binding']['page_category_id'], 'seeder' => $target['identity'], 'locale' => 'uk',
            'sort_order' => 0, 'type' => 'subtitle', 'column' => 'header', 'heading' => null, 'css_class' => null,
            'level' => $source['level'], 'body' => $target['after']['page']['subtitle_html']];
        foreach ($expected as $field => $value) { if (($block->$field ?? null) !== $value) { return false; } }

        return true;
    }

    /** Exact standalone help compatibility: clone accepted existing bodies, exclude only the exact appended practice. */
    public static function preserveExcludedTextBlock(object $block, string $caller): ?object
    {
        if ($caller !== 'standalone_test' || ($block->locale ?? null) !== 'uk' || !in_array($block->seeder ?? null, self::OWNERS, true)) { return $block; }
        try {
            [$before, $patch] = self::load(); $i = array_search($block->seeder, self::OWNERS, true);
            $target = $patch['targets'][$i]; $old = $before['targets'][$i]['before'];
            if (($block->page_id ?? null) !== $target['page_binding']['id'] || ($block->page_category_id ?? null) !== $target['page_binding']['page_category_id']) { return $block; }
            if ($block->type === 'subtitle') {
                if (!self::exactSubtitle($block, $target, $old)) { return $block; }
                $body = $old['page']['subtitle_html'];
            } else {
                $binding = self::binding($block, json_decode((string) $block->body, true, flags: JSON_THROW_ON_ERROR));
                if ($binding === null) { return $block; }
                if (!isset($old['page']['blocks'][$binding['slot']])) { return $binding['source']['type'] === 'practice-set' ? null : $block; }
                $body = $old['page']['blocks'][$binding['slot']]['body'];
            }
            $copy = clone $block;
            if (method_exists($copy, 'setRawAttributes') && method_exists($copy, 'getAttributes')) {
                $copy->setRawAttributes(array_replace($copy->getAttributes(), ['body' => $body]), true);
            } else { $copy->body = $body; }

            return $copy;
        } catch (\Throwable) { return $block; }
    }

    /** Courses keep complete independently captured BEFORE text and all non-UK rows without model mutation. */
    public static function preserveCoursePage(?Page $page): ?Page
    {
        if ($page === null || !in_array($page->getRawOriginal('seeder'), self::OWNERS, true) || !$page->relationLoaded('textBlocks')) { return $page; }
        try {
            [$before, $patch] = self::load(); $i = array_search($page->getRawOriginal('seeder'), self::OWNERS, true);
            $target = $patch['targets'][$i]; $old = $before['targets'][$i]['before']; $blocks = $page->getRelation('textBlocks');
            $expected = ['id' => $target['page_binding']['id'], 'page_category_id' => $target['page_binding']['page_category_id'],
                'seeder' => $target['identity'], 'slug' => $target['slug'], 'title' => $old['page']['title'], 'type' => 'theory',
                'text' => $target['after']['page']['subtitle_text']];
            foreach ($expected as $field => $value) { if ($page->getRawOriginal($field) !== $value || $page->getAttribute($field) !== $value) { return $page; } }
            $uk = $blocks->where('locale', 'uk')->values();
            if ($uk->count() !== count($target['after']['page']['blocks']) + 1) { return $page; }
            foreach ($target['after']['page']['blocks'] as $slot => $source) {
                $model = $uk->firstWhere('uuid', M26DetailPackage::uuid($target['identity'], $source, $slot + 1));
                if (!$model instanceof TextBlock || self::binding($model, json_decode($model->body, true, flags: JSON_THROW_ON_ERROR)) === null) { return $page; }
            }
            $subtitles = $uk->where('type', 'subtitle')->values();
            if ($subtitles->count() !== 1 || !self::exactSubtitle($subtitles[0], $target, $old)) { return $page; }
            $copy = clone $page; $copy->setRawAttributes(array_replace($page->getAttributes(), ['text' => $old['page']['subtitle_text']]), true);
            $restored = $blocks->reject(static fn ($block) => $block->locale === 'uk')->values();
            $subtitle = clone $subtitles[0]; $subtitle->setRawAttributes(array_replace($subtitle->getAttributes(), ['body' => $old['page']['subtitle_html']]), true); $restored->push($subtitle);
            foreach ($old['page']['blocks'] as $slot => $source) {
                $model = $uk->firstWhere('uuid', M26DetailPackage::uuid($target['identity'], $source, $slot + 1));
                $clone = clone $model; $clone->setRawAttributes(array_replace($model->getAttributes(), ['body' => $source['body']]), true); $restored->push($clone);
            }
            $copy->setRelation('textBlocks', new Collection($restored->sortBy('sort_order')->values()->all()));

            return $copy;
        } catch (\Throwable) { return $page; }
    }
}
