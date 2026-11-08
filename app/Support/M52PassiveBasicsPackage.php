<?php

namespace App\Support;

use App\Models\Page;
use App\Models\TextBlock;
use Illuminate\Database\Eloquent\Collection;
use RuntimeException;

/**
 * Finite M52 identity/basic-detail boundary around the accepted M49 serializer.
 * No renderer, design, answer engine, query, seeder or automatic apply mechanism.
 */
final class M52PassiveBasicsPackage
{
    public const BASE_SHA = '0e2ed40c438400e2f2a3992314220fdf206db9a7';
    public const MASTER = 'docs/content/m52-passive-basics.v1.0.0.json';
    public const MASTER_SHA = 'e1a020633c8e45869da213be82310885f717ab90b6e685f7c13d83a007dcf904';
    public const BEFORE = 'database/content-patches/m52-passive-basics-before.v1.json';
    public const BEFORE_SHA = '5d8e3853a83630ca924436e940b771adcbca47d4de0498b492748c792ec5cbb1';
    public const SOURCE = 'database/content-patches/m52-passive-basics.v1.json';
    public const SOURCE_SHA = '2b4c2a052bfb52a9888b74eb105546bda31e8f75e780ba89cf8fcce21aa5fd26';
    public const CONTRACT = 'docs/content/theory-sentence-builders/m52-passive-basics.v1.json';
    public const CONTRACT_SHA = '2f473bd621ff48e9aac1ac13e2a56d03f5f604c0ef47c186fde1e608940f1f8b';
    public const OWNERS = [
        'Database\\Seeders\\Page_V3\\PassiveVoice\\Basics\\PassiveVoiceFormationRulesTheorySeeder',
        'Database\\Seeders\\Page_V3\\PassiveVoice\\Basics\\PassiveVoiceNegativesQuestionsTheorySeeder',
        'Database\\Seeders\\Page_V3\\PassiveVoice\\Basics\\PassiveVoiceModalVerbsTheorySeeder',
    ];
    public const SLUGS = [
        'theory-passive-voice-formation-rules', 'theory-passive-voice-negatives-questions', 'theory-passive-voice-modal-verbs',
    ];
    public const PAGE_IDS = [1, 98, 97]; // Independent local BEFORE, not guessed from source order.
    public const CATEGORY_IDS = [1, 1, 1];
    public const LEVELS = ['A2', 'B1', 'B1'];
    public const CONTINUING_COUNTS = [9, 11, 11];
    public const RENDERED_TITLES = [
        'Пасивний стан (Passive Voice)', 'Negatives & Questions in Passive', 'Пасив з модальними дієсловами',
    ];

    /** Draft sentinels, missing files, symlinks and raw-byte drift always fail closed. */
    public static function load(?string $root = null): array
    {
        $root ??= base_path(); $out = [];
        foreach ([self::BEFORE => self::BEFORE_SHA, self::SOURCE => self::SOURCE_SHA,
            self::MASTER => self::MASTER_SHA, self::CONTRACT => self::CONTRACT_SHA] as $path => $sha) {
            if (preg_match('/^[a-f0-9]{64}$/D', $sha) !== 1 || !is_file($root.'/'.$path) || is_link($root.'/'.$path)) {
                throw new RuntimeException('M52 release is not reviewed/frozen: '.$path);
            }
            $bytes = file_get_contents($root.'/'.$path);
            if (!is_string($bytes) || !hash_equals($sha, hash('sha256', $bytes))) {
                throw new RuntimeException('M52 immutable source differs: '.$path);
            }
            $out[] = json_decode($bytes, true, flags: JSON_THROW_ON_ERROR);
        }

        return $out;
    }

    /** Pure draft/frozen projection: reuse M49's serializer, not its fixed identity/slot policy. */
    public static function definition(array $lesson, array $before): array
    {
        $after = M49AdviceModalsPackage::definition($lesson, $before);
        foreach ($after['page']['blocks'] as &$block) {
            $data = json_decode($block['body'], true, flags: JSON_THROW_ON_ERROR);
            $data['m52_native_v1'] = $data['m49_native_v1']; unset($data['m49_native_v1']);
            foreach ($data['sections'] ?? [] as $i => $section) {
                if (isset($section['m49_detail_key'])) {
                    $data['sections'][$i]['m52_detail_key'] = $section['m49_detail_key'];
                    unset($data['sections'][$i]['m49_detail_key']);
                }
            }
            foreach ($data['items'] ?? [] as $i => $item) {
                foreach ($item['rules'] ?? [] as $n => $rule) {
                    if (isset($rule['m49_detail_key'])) {
                        $data['items'][$i]['rules'][$n]['m52_detail_key'] = $rule['m49_detail_key'];
                        unset($data['items'][$i]['rules'][$n]['m49_detail_key']);
                    }
                }
            }
            if (isset($block['uuid_key']) && str_starts_with($block['uuid_key'], 'm49:')) {
                $block['uuid_key'] = 'm52:'.substr($block['uuid_key'], 4);
            }
            $block['body'] = M26DetailPackage::json($data);
        }
        unset($block);

        return $after;
    }

    public static function validate(array $before, array $patch, array $master, ?array $contract = null): void
    {
        $contract ??= self::load()[3];
        foreach ([$before, $patch, $master] as $artifact) {
            if (($artifact['schema_version'] ?? null) !== 1 || ($artifact['base_sha'] ?? null) !== self::BASE_SHA) {
                throw new RuntimeException('M52 schema/base differs.');
            }
        }
        if (($master['version'] ?? null) !== '1.0.0' || ($contract['schema_version'] ?? null) !== 1
            || array_column($before['targets'] ?? [], 'identity') !== self::OWNERS
            || array_column($patch['targets'] ?? [], 'identity') !== self::OWNERS
            || array_column($master['lessons'] ?? [], 'identity') !== self::OWNERS
            || array_column($contract['targets'] ?? [], 'identity') !== self::OWNERS) {
            throw new RuntimeException('M52 exact owner/version/independent bank scope differs.');
        }
        $taskIds = []; $detailKeys = [];
        foreach ($master['lessons'] as $i => $lesson) {
            $old = $before['targets'][$i]; $target = $patch['targets'][$i]; $declaration = $contract['targets'][$i];
            $path = TheorySentenceBuilderAcceptance::definitionPath(self::OWNERS[$i]);
            $url = 'http://gramlyze.loc/theory/passive-voice/'.self::SLUGS[$i];
            if (self::PAGE_IDS[$i] < 1 || self::CATEGORY_IDS[$i] < 1
                || ($old['path'] ?? null) !== $path || ($old['before']['seeder']['class'] ?? null) !== self::OWNERS[$i]
                || ($old['before']['slug'] ?? null) !== self::SLUGS[$i] || ($old['before']['type'] ?? null) !== 'theory'
                || ($old['before']['page']['locale'] ?? null) !== 'uk'
                || ($old['before']['page']['category']['slug'] ?? null) !== 'passive-voice'
                || ($lesson['locale'] ?? null) !== 'uk' || ($lesson['slug'] ?? null) !== self::SLUGS[$i]
                || ($lesson['technical_block_level'] ?? null) !== self::LEVELS[$i]
                || ($lesson['display_level'] ?? null) !== self::LEVELS[$i]
                || ($lesson['blocks'][0]['data']['level'] ?? null) !== self::LEVELS[$i]
                || ($target['path'] ?? null) !== $path || ($target['slug'] ?? null) !== self::SLUGS[$i]
                || ($target['ancestry'] ?? null) !== ['passive-voice']
                || ($target['page_binding'] ?? null) !== ['id' => self::PAGE_IDS[$i], 'page_category_id' => self::CATEGORY_IDS[$i]]
                || ($target['local_url'] ?? null) !== $url || ($declaration['definition'] ?? null) !== $path
                || ($declaration['local_url'] ?? null) !== $url || ($declaration['level'] ?? null) !== self::LEVELS[$i]
                || ($target['after'] ?? null) !== self::definition($lesson, $old['before'])
                || ($target['block_keys'] ?? null) !== array_column($lesson['blocks'], 'key')) {
                throw new RuntimeException('M52 exact owner/category/locale/level/projection differs.');
            }
            foreach ([$old['before']['page']['subtitle_html'], $lesson['subtitle_html'] ?? ''] as $subtitle) {
                if (preg_match('/^\s*<p>\s*<strong>(.*?)<\/strong>/su', $subtitle, $title) !== 1
                    || html_entity_decode(strip_tags($title[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8') !== self::RENDERED_TITLES[$i]) {
                    throw new RuntimeException('M52 supported subtitle must preserve the independently captured display title.');
                }
            }
            $count = count($old['before']['page']['blocks']);
            if ($count !== self::CONTINUING_COUNTS[$i] || count($lesson['blocks']) !== $count + 1
                || array_column($lesson['blocks'], 'existing_source_index') !== [...range(0, $count - 1), null]
                || count(array_unique(array_column($lesson['blocks'], 'key'))) !== $count + 1) {
                throw new RuntimeException('M52 actual continuing slots/order or appended practice differs.');
            }
            $protected = $target['after']; $protected['page']['blocks'] = $old['before']['page']['blocks'];
            foreach (['subtitle_html', 'subtitle_text'] as $field) { $protected['page'][$field] = $old['before']['page'][$field]; }
            if ($protected !== $old['before']) { throw new RuntimeException('M52 protected title/slug/category/tags/source metadata changed.'); }
            foreach ($lesson['blocks'] as $slot => $block) {
                if ($slot < $count) {
                    $beforeConfig = $old['before']['page']['blocks'][$slot]; $afterConfig = $target['after']['page']['blocks'][$slot];
                    unset($beforeConfig['body'], $afterConfig['body']);
                    if ($beforeConfig !== $afterConfig) { throw new RuntimeException('M52 continuing block metadata/UUID/order changed.'); }
                }
                if ($block['type'] !== 'navigation-chips' && ($block['level'] ?? null) !== self::LEVELS[$i]) {
                    throw new RuntimeException('M52 per-owner technical block level differs.');
                }
                self::assertSections($block['data']['sections'] ?? []);
                if ($block['type'] === 'navigation-chips') {
                    foreach ($block['data']['items'] ?? [] as $item) {
                        if (empty($item['current']) && (!is_string($item['url'] ?? null)
                            || preg_match('~^/theory/[a-z0-9-]+(?:/[a-z0-9-]+)*$~D', $item['url']) !== 1)) {
                            throw new RuntimeException('M52 navigation needs an explicit supported local theory destination.');
                        }
                    }
                }
                if ($block['type'] === 'mistakes-grid') {
                    foreach ($block['data']['items'] ?? [] as $item) {
                        foreach (['wrong_en', 'wrong_ua', 'right_en', 'right_ua'] as $field) {
                            if (!is_string($item[$field] ?? null) || trim($item[$field]) === '') {
                                throw new RuntimeException('M52 paired mistake/correction translation is missing.');
                            }
                        }
                    }
                }
            }
            $practiceBlock = $lesson['blocks'][$count];
            if ($practiceBlock['type'] !== 'practice-set' || $practiceBlock['column'] !== 'left') {
                throw new RuntimeException('M52 requires one appended standard practice-set.');
            }
            self::assertPractice($practiceBlock['data'], $declaration['bank_seeders'] ?? [], $taskIds);
            $owners = [];
            foreach ($lesson['details'] as $detail) {
                $owner = ($detail['owner_block_key'] ?? '').':'.($detail['owner_pointer'] ?? '');
                $blocksByKey = array_column($lesson['blocks'], null, 'key');
                $ownerBlock = $blocksByKey[$detail['owner_block_key'] ?? ''] ?? null;
                $field = ['forms-grid' => 'items', 'usage-panels' => 'sections'][$ownerBlock['type'] ?? ''] ?? null;
                $pointer = explode('/', $detail['owner_pointer'] ?? '');
                if (!is_string($detail['key'] ?? null) || $detail['key'] === '' || isset($detailKeys[$detail['key']])
                    || isset($owners[$owner]) || count($pointer) !== 3 || $pointer[1] !== $field || !ctype_digit($pointer[2])
                    || !isset($ownerBlock['data'][$field][(int) $pointer[2]])
                    || ($detail['fragment']['type'] ?? null) !== 'usage-panels' || empty($detail['fragment']['data']['sections'])) {
                    throw new RuntimeException('M52 duplicate/incomplete semantic detail owner.');
                }
                $detailKeys[$detail['key']] = true; $owners[$owner] = true;
                self::assertSections($detail['fragment']['data']['sections']);
                foreach ($detail['fragment']['data']['sections'] as $section) {
                    if (empty($section['description']) || empty($section['examples'])) {
                        throw new RuntimeException('M52 meaningful detail fragment is incomplete.');
                    }
                }
            }
            self::assertLearningData($lesson['blocks']); self::assertLearningData($lesson['details']);
        }
    }

    private static function assertPractice(array $data, array $banks, array &$taskIds): void
    {
        if ($banks === [] || count(array_unique($banks)) !== count($banks)
            || count($data['selects'] ?? []) !== 2 || count($data['choices'] ?? []) !== 2 || count($data['inputs'] ?? []) !== 2
            || array_column([...$data['selects'], ...$data['choices'], ...$data['inputs']], 'source_index') !== range(1, 6)
            || ($data['linked_practice']['source'] ?? null) !== 'theory_links'
            || ($data['linked_practice']['question_types'] ?? null) !== ['4']
            || ($data['linked_practice']['seeder_classes'] ?? null) !== $banks) {
            throw new RuntimeException('M52 own 2/2/2 groups or independently declared thematic banks differ.');
        }
        foreach ($banks as $bank) {
            if (!is_string($bank) || preg_match('/^Database\\\\Seeders\\\\V3\\\\[A-Za-z0-9_]+\\\\[A-Za-z0-9_]+$/D', $bank) !== 1) {
                throw new RuntimeException('M52 declared bank identity is invalid.');
            }
        }
        foreach (['selects', 'choices', 'inputs'] as $group) {
            foreach ($data[$group] as $task) {
                if (!is_string($task['id'] ?? null) || $task['id'] === '' || isset($taskIds[$task['id']])
                    || !in_array($task['answer'] ?? null, $task['accepted'] ?? [], true)) {
                    throw new RuntimeException('M52 task ID/canonical answer differs.');
                }
                $taskIds[$task['id']] = true;
                foreach ($task['options'] ?? ($group === 'choices' ? ($data['choice_options'] ?? ['a', 'b']) : $task['accepted']) as $option) {
                    if (!is_string($option) || !is_string($task['feedback'][mb_strtolower(trim($option))] ?? null)) {
                        throw new RuntimeException('M52 supported per-option feedback is missing.');
                    }
                }
            }
        }
        foreach ($data['inputs'] as $input) {
            if (!is_string($input['before'] ?? null) || !str_contains($input['before'], '/')
                || !is_string($input['after'] ?? null) || preg_match('/[\x{0400}-\x{04FF}]/u', $input['after']) !== 1) {
                throw new RuntimeException('M52 visible Ukrainian input condition or native token bank is missing.');
            }
        }
    }

    private static function assertSections(array $sections): void
    {
        foreach ($sections as $section) {
            if (empty($section['label']) || !in_array($section['color'] ?? null, ['blue', 'emerald', 'sky', 'amber', 'rose', 'slate'], true)) {
                throw new RuntimeException('M52 unnamed native point or unsupported semantic accent.');
            }
            foreach ($section['examples'] ?? [] as $example) {
                if (empty($example['en']) || empty($example['ua'])) { throw new RuntimeException('M52 bilingual example is missing.'); }
            }
        }
    }

    private static function assertLearningData(array $values): void
    {
        foreach ($values as $key => $value) {
            if (is_string($key) && in_array(strtolower($key), ['view', 'native_view', 'stylesheet', 'css_class', 'class', 'style'], true)) {
                throw new RuntimeException('M52 learning data cannot select presentation.');
            }
            if (is_array($value)) { self::assertLearningData($value); }
            elseif (is_string($value) && preg_match('~<[^>]*\s(?:class|style|on[a-z]+)\s*=|<(?:script|style|iframe)\b~i', $value)) {
                throw new RuntimeException('M52 learning data contains executable/styled markup.');
            }
        }
    }

    private static function binding(object $block, array $data): ?array
    {
        if (($block->locale ?? null) !== 'uk' || !in_array($block->seeder ?? null, self::OWNERS, true) || !isset($data['m52_native_v1'])) { return null; }
        try {
            [$before, $patch, $master, $contract] = self::load(); self::validate($before, $patch, $master, $contract);
            $i = array_search($block->seeder, self::OWNERS, true); $target = $patch['targets'][$i];
            if (($block->page_id ?? null) !== $target['page_binding']['id']
                || ($block->page_category_id ?? null) !== $target['page_binding']['page_category_id']) { return null; }
            foreach ($target['after']['page']['blocks'] as $slot => $source) {
                if (($block->uuid ?? null) !== M26DetailPackage::uuid($target['identity'], $source, $slot + 1)) { continue; }
                foreach (['type', 'column', 'heading', 'level', 'css_class'] as $field) {
                    if (($block->$field ?? null) !== ($source[$field] ?? null)) { return null; }
                }
                if ((int) ($block->sort_order ?? -1) !== $slot + 1 || ($block->body ?? null) !== $source['body']
                    || $data !== json_decode($source['body'], true, flags: JSON_THROW_ON_ERROR)) { return null; }

                return ['target' => $target, 'lesson' => $master['lessons'][$i], 'source' => $source, 'slot' => $slot];
            }
        } catch (\Throwable) { /* Invalid bindings retain the complete stored native fallback, including details. */ }

        return null;
    }

    private static function design(array $binding): array
    {
        return ['package' => 'm52-native-v1', 'identity' => $binding['target']['identity'], 'locale' => 'uk',
            'uuid' => M26DetailPackage::uuid($binding['target']['identity'], $binding['source'], $binding['slot'] + 1),
            'source_index' => $binding['slot'], 'body_sha256' => hash('sha256', $binding['source']['body']),
            'component' => $binding['source']['type']];
    }

    public static function presentation(object $block, array $data): ?array
    {
        if (app()->getLocale() !== 'uk') { return null; }
        $binding = self::binding($block, $data);
        if ($binding === null) { return null; }
        $points = [];
        foreach ($binding['lesson']['details'] as $detail) {
            if ($detail['owner_block_key'] !== $data['m52_native_v1']['key']) { continue; }
            $index = (int) explode('/', $detail['owner_pointer'])[2];
            $points[$index] = ['key' => $detail['key'], 'title' => $detail['title'], 'fragments' => array_map(
                static fn ($section, $n) => ['id' => $detail['key'].'-detail-'.$n, 'type' => 'usage-panels', 'value' => $section],
                $detail['fragment']['data']['sections'], array_keys($detail['fragment']['data']['sections']))];
        }

        return ['data' => $binding['lesson']['blocks'][$binding['slot']]['data'] + ['m52_native_v1' => $data['m52_native_v1']],
            'points' => $points, 'legacy_section' => null, 'legacy_practice_id' => null, 'm42_native_design' => self::design($binding)];
    }

    public static function validDesign(?array $design): bool
    {
        if (($design['package'] ?? null) !== 'm52-native-v1') { return false; }
        try {
            [$before, $patch, $master, $contract] = self::load(); self::validate($before, $patch, $master, $contract);
            foreach ($patch['targets'] as $target) {
                foreach ($target['after']['page']['blocks'] as $slot => $source) {
                    if ($design === self::design(compact('target', 'source', 'slot'))) { return true; }
                }
            }
        } catch (\Throwable) {}

        return false;
    }

    public static function pointPanels(?array $design): ?array
    {
        if (!self::validDesign($design) || $design['component'] !== 'usage-panels') { return null; }
        [, , $master] = self::load(); $i = array_search($design['identity'], self::OWNERS, true); $points = [];
        foreach ($master['lessons'][$i]['blocks'][$design['source_index']]['data']['sections'] as $n => $section) {
            $points[$n] = ['label' => $section['label'], 'accent' => $section['color'],
                'description_sha256' => [hash('sha256', $section['description'])]];
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

    private static function exactSubtitle(object $block, array $target, array $old): bool
    {
        $source = ['type' => 'subtitle', 'column' => 'header', 'heading' => null, 'css_class' => null,
            'level' => $old['page']['subtitle_level'] ?? null, 'uuid_key' => $old['page']['subtitle_uuid_key'] ?? 'subtitle'];
        $expected = ['uuid' => M26DetailPackage::uuid($target['identity'], $source, 0), 'page_id' => $target['page_binding']['id'],
            'page_category_id' => $target['page_binding']['page_category_id'], 'seeder' => $target['identity'], 'locale' => 'uk',
            'sort_order' => 0, 'type' => 'subtitle', 'column' => 'header', 'heading' => null, 'css_class' => null,
            'level' => $source['level'], 'body' => $target['after']['page']['subtitle_html']];
        foreach ($expected as $field => $value) { if (($block->$field ?? null) !== $value) { return false; } }

        return true;
    }

    private static function cloneBody(object $block, string $body): object
    {
        $copy = clone $block;
        if (method_exists($copy, 'setRawAttributes') && method_exists($copy, 'getAttributes')) {
            $copy->setRawAttributes(array_replace($copy->getAttributes(), ['body' => $body]), true);
        } else { $copy->body = $body; }

        return $copy;
    }

    /** Only the explicit standalone caller gets a clone of the full pre-modernisation material. */
    public static function preserveExcludedTextBlock(object $block, string $caller): ?object
    {
        if ($caller !== 'standalone_test' || ($block->locale ?? null) !== 'uk'
            || !in_array($block->seeder ?? null, self::OWNERS, true)) { return $block; }
        try {
            [$before, $patch, $master, $contract] = self::load(); self::validate($before, $patch, $master, $contract);
            $i = array_search($block->seeder, self::OWNERS, true); $target = $patch['targets'][$i]; $old = $before['targets'][$i]['before'];
            if (($block->page_id ?? null) !== $target['page_binding']['id']
                || ($block->page_category_id ?? null) !== $target['page_binding']['page_category_id']) { return $block; }
            if ($block->type === 'subtitle') {
                if (!self::exactSubtitle($block, $target, $old)) { return $block; }

                return self::cloneBody($block, $old['page']['subtitle_html']);
            }
            $binding = self::binding($block, json_decode((string) $block->body, true, flags: JSON_THROW_ON_ERROR));
            if ($binding === null) { return $block; }
            if ($binding['slot'] === count($old['page']['blocks'])) {
                return $binding['source']['type'] === 'practice-set' ? null : $block;
            }

            return self::cloneBody($block, $old['page']['blocks'][$binding['slot']]['body']);
        } catch (\Throwable) { return $block; }
    }

    /** All UK slots must match accepted AFTER; restore cloned BEFORE and keep non-UK models untouched. */
    public static function preserveCoursePage(?Page $page): ?Page
    {
        if ($page === null || !in_array($page->getRawOriginal('seeder'), self::OWNERS, true)
            || !$page->relationLoaded('textBlocks')) { return $page; }
        try {
            [$before, $patch, $master, $contract] = self::load(); self::validate($before, $patch, $master, $contract);
            $i = array_search($page->getRawOriginal('seeder'), self::OWNERS, true); $target = $patch['targets'][$i];
            $old = $before['targets'][$i]['before']; $blocks = $page->getRelation('textBlocks');
            $expected = ['id' => $target['page_binding']['id'], 'page_category_id' => $target['page_binding']['page_category_id'],
                'seeder' => $target['identity'], 'slug' => $target['slug'], 'title' => $old['page']['title'], 'type' => 'theory',
                'text' => $target['after']['page']['subtitle_text']];
            foreach ($expected as $field => $value) {
                if ($page->getRawOriginal($field) !== $value || $page->getAttribute($field) !== $value) { return $page; }
            }
            $uk = $blocks->where('locale', 'uk')->values();
            if ($uk->count() !== count($target['after']['page']['blocks']) + 1) { return $page; }
            foreach ($target['after']['page']['blocks'] as $slot => $source) {
                $matches = $uk->where('uuid', M26DetailPackage::uuid($target['identity'], $source, $slot + 1))->values();
                if ($matches->count() !== 1 || !$matches[0] instanceof TextBlock
                    || self::binding($matches[0], json_decode($matches[0]->body, true, flags: JSON_THROW_ON_ERROR)) === null) { return $page; }
            }
            $subtitles = $uk->where('type', 'subtitle')->values();
            if ($subtitles->count() !== 1 || !self::exactSubtitle($subtitles[0], $target, $old)) { return $page; }
            $copy = clone $page;
            $copy->setRawAttributes(array_replace($page->getAttributes(), ['text' => $old['page']['subtitle_text']]), true);
            $restored = $blocks->reject(static fn ($block) => $block->locale === 'uk')->values();
            $restored->push(self::cloneBody($subtitles[0], $old['page']['subtitle_html']));
            foreach ($old['page']['blocks'] as $slot => $source) {
                $model = $uk->firstWhere('uuid', M26DetailPackage::uuid($target['identity'], $source, $slot + 1));
                $restored->push(self::cloneBody($model, $source['body']));
            }
            $copy->setRelation('textBlocks', new Collection($restored->sortBy('sort_order')->values()->all()));

            return $copy;
        } catch (\Throwable) { return $page; }
    }
}
