<?php

namespace App\Support;

use App\Models\Page;
use App\Models\TextBlock;
use Illuminate\Database\Eloquent\Collection;
use RuntimeException;

/** Finite data binding only; native serialization and all rendering/answer engines are reused. */
final class M50ObligationAdvicePackage
{
    public const BASE_SHA = '4d85f890b8fe4fa0887d2f989581c18eb8cf4534';
    public const MASTER = 'docs/content/m50-obligation-advice.v1.0.0.json';
    public const MASTER_SHA = '5f169d760aff9ecadd283186311cdddd1f88e997c705bb65c32cd50751d73e01';
    public const BEFORE = 'database/content-patches/m50-obligation-advice-before.v1.json';
    public const BEFORE_SHA = '4ff6487a31da2fb3e41a42913d1f9db6ac4303ce928bea4dc6168c553b23baaf';
    public const SOURCE = 'database/content-patches/m50-obligation-advice.v1.json';
    public const SOURCE_SHA = 'a3d34a9f2ecd94dec9dc4ef64fffe172faa84a9940fa85cb5052416a233149b4';
    public const OWNERS = [
        'Database\\Seeders\\Page_V3\\ModalVerbs\\ModalVerbsMustHaveToTheorySeeder',
        'Database\\Seeders\\Page_V3\\ModalVerbs\\ModalVerbsShouldOughtToTheorySeeder',
        'Database\\Seeders\\Page_V3\\ModalVerbs\\ModalVerbsNeedNeedntDontHaveToTheorySeeder',
    ];
    public const SLUGS = ['must-have-to', 'should-ought-to', 'need-neednt-dont-have-to'];
    public const PAGE_IDS = [75, 79, 76];
    public const BANKS = [
        'Database\\Seeders\\V3\\Polyglot\\PolyglotMustHaveToLessonSeeder',
        'Database\\Seeders\\V3\\Polyglot\\PolyglotShouldOughtToLessonSeeder',
        'Database\\Seeders\\V3\\Polyglot\\PolyglotGerundVsInfinitiveLessonSeeder',
    ];
    public const BANK_SETS = [[self::BANKS[0]], [self::BANKS[1]], [self::BANKS[2], self::BANKS[0]]];

    public static function load(?string $root = null): array
    {
        $root ??= base_path(); $out = [];
        foreach ([self::BEFORE => self::BEFORE_SHA, self::SOURCE => self::SOURCE_SHA, self::MASTER => self::MASTER_SHA] as $path => $sha) {
            if (!preg_match('/^[a-f0-9]{64}$/D', $sha) || !is_file($root.'/'.$path) || is_link($root.'/'.$path)) {
                throw new RuntimeException('M50 source is not reviewed/frozen: '.$path);
            }
            $bytes = file_get_contents($root.'/'.$path);
            if (!hash_equals($sha, hash('sha256', $bytes))) { throw new RuntimeException('M50 immutable source differs: '.$path); }
            $out[] = json_decode($bytes, true, flags: JSON_THROW_ON_ERROR);
        }

        return $out;
    }

    /** Reuse the accepted native serializer, then change only code-owned provenance keys. */
    public static function definition(array $lesson, array $before): array
    {
        $after = M49AdviceModalsPackage::definition($lesson, $before);
        foreach ($after['page']['blocks'] as &$block) {
            $data = json_decode($block['body'], true, flags: JSON_THROW_ON_ERROR);
            $data['m50_native_v1'] = $data['m49_native_v1']; unset($data['m49_native_v1']);
            foreach ($data['sections'] ?? [] as $i => $section) {
                if (isset($section['m49_detail_key'])) {
                    $data['sections'][$i]['m50_detail_key'] = $section['m49_detail_key'];
                    unset($data['sections'][$i]['m49_detail_key']);
                }
            }
            foreach ($data['items'] ?? [] as $i => $item) {
                foreach ($item['rules'] ?? [] as $j => $rule) {
                    if (isset($rule['m49_detail_key'])) {
                        $data['items'][$i]['rules'][$j]['m50_detail_key'] = $rule['m49_detail_key'];
                        unset($data['items'][$i]['rules'][$j]['m49_detail_key']);
                    }
                }
            }
            if (isset($block['uuid_key']) && str_starts_with($block['uuid_key'], 'm49:')) {
                $block['uuid_key'] = 'm50:'.substr($block['uuid_key'], 4);
            }
            $block['body'] = M26DetailPackage::json($data);
        }
        unset($block);

        return $after;
    }

    public static function validate(array $before, array $patch, array $master): void
    {
        foreach ([$before, $patch, $master] as $artifact) {
            if (($artifact['schema_version'] ?? null) !== 1 || ($artifact['base_sha'] ?? null) !== self::BASE_SHA) {
                throw new RuntimeException('M50 schema/base differs.');
            }
        }
        if (($master['version'] ?? null) !== '1.0.0' || array_column($before['targets'] ?? [], 'identity') !== self::OWNERS
            || array_column($patch['targets'] ?? [], 'identity') !== self::OWNERS
            || array_column($master['lessons'] ?? [], 'identity') !== self::OWNERS) {
            throw new RuntimeException('M50 exact owner/version scope differs.');
        }
        $taskIds = [];
        foreach ($master['lessons'] as $i => $lesson) {
            $original = $before['targets'][$i]; $target = $patch['targets'][$i];
            $path = TheorySentenceBuilderAcceptance::definitionPath(self::OWNERS[$i]);
            if ($original['path'] !== $path || $original['before']['seeder']['class'] !== self::OWNERS[$i]
                || $original['before']['page']['locale'] !== 'uk' || $original['before']['type'] !== 'theory'
                || $original['before']['page']['category']['slug'] !== 'modal-verbs'
                || $lesson['locale'] !== 'uk' || $lesson['slug'] !== self::SLUGS[$i]
                || ($lesson['display_level'] ?? null) !== 'A2-B1' || ($lesson['technical_block_level'] ?? null) !== 'A2'
                || ($lesson['blocks'][0]['data']['level'] ?? null) !== 'A2-B1'
                || $target['path'] !== $path || $target['slug'] !== self::SLUGS[$i]
                || $target['ancestry'] !== ['modal-verbs']
                || ($target['page_binding'] ?? null) !== ['id' => self::PAGE_IDS[$i], 'page_category_id' => 15]
                || $target['local_url'] !== 'http://gramlyze.loc/theory/modal-verbs/'.self::SLUGS[$i]
                || $target['after'] !== self::definition($lesson, $original['before'])
                || $target['block_keys'] !== array_column($lesson['blocks'], 'key')) {
                throw new RuntimeException('M50 owner/projection/locale/level fidelity differs.');
            }
            $oldSlots = array_values(array_filter(array_column($lesson['blocks'], 'existing_source_index'), 'is_int'));
            if ($oldSlots !== range(0, count($original['before']['page']['blocks']) - 1)) {
                throw new RuntimeException('M50 continuing slots/order changed.');
            }
            $practices = array_values(array_filter($lesson['blocks'], static fn ($b) => $b['type'] === 'practice-set'));
            if (count($practices) !== 1) { throw new RuntimeException('M50 requires exactly one native practice.'); }
            $data = $practices[0]['data'];
            if (count($data['selects'] ?? []) !== 2 || count($data['choices'] ?? []) !== 2 || count($data['inputs'] ?? []) !== 2
                || array_column([...$data['selects'], ...$data['choices'], ...$data['inputs']], 'source_index') !== range(1, 6)
                || ($data['linked_practice']['source'] ?? null) !== 'theory_links'
                || ($data['linked_practice']['question_types'] ?? null) !== ['4']
                || ($data['linked_practice']['seeder_classes'] ?? null) !== self::BANK_SETS[$i]) {
                throw new RuntimeException('M50 own groups or exact linked-bank contract differs.');
            }
            foreach (['selects', 'choices', 'inputs'] as $group) {
                foreach ($data[$group] as $task) {
                    if (!is_string($task['id'] ?? null) || $task['id'] === '' || isset($taskIds[$task['id']])
                        || !in_array($task['answer'] ?? null, $task['accepted'] ?? [], true)) {
                        throw new RuntimeException('M50 task ID/canonical answer differs.');
                    }
                    $taskIds[$task['id']] = true;
                    $options = $task['options'] ?? ($group === 'choices' ? ($data['choice_options'] ?? ['a', 'b']) : $task['accepted']);
                    foreach ($options as $option) {
                        if (!is_string($task['feedback'][mb_strtolower(trim($option))] ?? null)) {
                            throw new RuntimeException('M50 visible option feedback is missing.');
                        }
                    }
                }
            }
            foreach ($data['inputs'] as $input) {
                if (!is_string($input['before'] ?? null) || !str_contains($input['before'], '/')
                    || !is_string($input['after'] ?? null) || preg_match('/[\x{0400}-\x{04FF}]/u', $input['after']) !== 1) {
                    throw new RuntimeException('M50 native input bank or visible UK condition is missing.');
                }
            }
            self::assertLearningData($lesson['blocks']); self::assertLearningData($lesson['details']);
            $owners = []; $keys = [];
            foreach ($lesson['details'] as $detail) {
                $owner = $detail['owner_block_key'].':'.$detail['owner_pointer'];
                if (isset($owners[$owner]) || isset($keys[$detail['key']])
                    || ($detail['fragment']['type'] ?? null) !== 'usage-panels' || empty($detail['fragment']['data']['sections'])) {
                    throw new RuntimeException('M50 duplicate/incomplete detail owner.');
                }
                $owners[$owner] = true; $keys[$detail['key']] = true;
                foreach ($detail['fragment']['data']['sections'] as $section) {
                    if (empty($section['label']) || empty($section['description']) || empty($section['examples'])) {
                        throw new RuntimeException('M50 detail native fragment is incomplete.');
                    }
                    foreach ($section['examples'] as $example) {
                        if (empty($example['en']) || empty($example['ua'])) { throw new RuntimeException('M50 detail translation missing.'); }
                    }
                }
            }
            foreach ($lesson['blocks'] as $block) {
                if ($block['type'] !== 'navigation-chips' && ($block['level'] ?? null) !== 'A2') {
                    throw new RuntimeException('M50 technical block level differs.');
                }
                foreach ($block['data']['sections'] ?? [] as $section) {
                    if (empty($section['label']) || !in_array($section['color'] ?? null, ['blue', 'emerald', 'sky', 'amber', 'rose', 'slate'], true)) {
                        throw new RuntimeException('M50 unnamed or unsupported native point.');
                    }
                    foreach ($section['examples'] ?? [] as $example) {
                        if (empty($example['en']) || empty($example['ua'])) { throw new RuntimeException('M50 bilingual example missing.'); }
                    }
                }
            }
        }
    }

    private static function assertLearningData(array $values): void
    {
        foreach ($values as $key => $value) {
            if (is_string($key) && in_array(strtolower($key), ['view', 'native_view', 'stylesheet', 'css_class', 'class', 'style'], true)) {
                throw new RuntimeException('M50 payload selects presentation.');
            }
            if (is_array($value)) { self::assertLearningData($value); }
            elseif (is_string($value) && preg_match('~<[^>]*\s(?:class|style|on[a-z]+)\s*=|<(?:script|style|iframe)\b~i', $value)) {
                throw new RuntimeException('M50 payload contains executable/styled markup.');
            }
        }
    }

    private static function binding(object $block, array $data): ?array
    {
        if (($block->locale ?? null) !== 'uk'
            || !in_array($block->seeder ?? null, self::OWNERS, true) || !isset($data['m50_native_v1'])) { return null; }
        try {
            [$before, $patch, $master] = self::load(); self::validate($before, $patch, $master);
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
        } catch (\Throwable) { /* The entire stored native text remains visible in fallback. */ }

        return null;
    }

    private static function design(array $binding): array
    {
        return ['package' => 'm50-native-v1', 'identity' => $binding['target']['identity'], 'locale' => 'uk',
            'uuid' => M26DetailPackage::uuid($binding['target']['identity'], $binding['source'], $binding['slot'] + 1),
            'source_index' => $binding['slot'], 'body_sha256' => hash('sha256', $binding['source']['body']), 'component' => $binding['source']['type']];
    }

    public static function presentation(object $block, array $data): ?array
    {
        if (app()->getLocale() !== 'uk') { return null; }
        $binding = self::binding($block, $data);
        if ($binding === null) { return null; }
        $points = []; $basic = $binding['lesson']['blocks'][$binding['slot']]['data'];
        $key = $data['m50_native_v1']['key'];
        foreach ($binding['lesson']['details'] as $detail) {
            if ($detail['owner_block_key'] !== $key) { continue; }
            $index = (int) explode('/', $detail['owner_pointer'])[2];
            $points[$index] = ['key' => $detail['key'], 'title' => $detail['title'],
                'fragments' => array_map(static fn ($section, $n) => ['id' => $detail['key'].'-detail-'.$n,
                    'type' => 'usage-panels', 'value' => $section], $detail['fragment']['data']['sections'], array_keys($detail['fragment']['data']['sections']))];
        }

        return ['data' => $basic + ['m50_native_v1' => $data['m50_native_v1']], 'points' => $points,
            'legacy_section' => null, 'legacy_practice_id' => null, 'm42_native_design' => self::design($binding)];
    }

    public static function validDesign(?array $design): bool
    {
        if (($design['package'] ?? null) !== 'm50-native-v1') { return false; }
        try {
            [, $patch] = self::load();
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
        [, $patch, $master] = self::load(); $i = array_search($design['identity'], self::OWNERS, true);
        $sections = $master['lessons'][$i]['blocks'][$design['source_index']]['data']['sections'];
        $points = [];
        foreach ($sections as $n => $section) {
            $points[$n] = ['label' => $section['label'], 'accent' => $section['color'],
                'description_sha256' => [hash('sha256', $section['description'])]];
        }

        return ['points_by_index' => $points];
    }

    public static function correction(array $item, ?array $design): ?array
    {
        if (!self::validDesign($design) || $design['component'] !== 'mistakes-grid') { return null; }
        [, $patch] = self::load(); $i = array_search($design['identity'], self::OWNERS, true);
        $expected = json_decode($patch['targets'][$i]['after']['page']['blocks'][$design['source_index']]['body'], true)['items'];
        if (!in_array($item, $expected, true)) { return null; }

        return ['wrong_en' => TheoryInlineHtml::render($item['wrong_en']), 'wrong_uk' => TheoryInlineHtml::render($item['wrong_ua']),
            'right_en' => TheoryInlineHtml::render($item['right_en']), 'right_uk' => TheoryInlineHtml::render($item['right_ua'])];
    }

    /** A frozen explanatory subtitle is not a new title; exact theory caller only, no query or model mutation. */
    public static function displayTitle(Page $page, string $locale, string $caller): ?string
    {
        if ($caller !== 'theory' || $locale !== 'uk' || app()->getLocale() !== 'uk'
            || !in_array($page->getRawOriginal('seeder'), self::OWNERS, true) || !$page->relationLoaded('textBlocks')) {
            return null;
        }
        try {
            [$before, $patch] = self::load();
            $i = array_search($page->getRawOriginal('seeder'), self::OWNERS, true); $target = $patch['targets'][$i];
            $old = $before['targets'][$i]['before']; $after = $target['after'];
            $fields = ['id' => $target['page_binding']['id'], 'page_category_id' => $target['page_binding']['page_category_id'],
                'seeder' => $target['identity'], 'type' => 'theory', 'slug' => $target['slug'], 'text' => $after['page']['subtitle_text']];
            foreach ($fields as $field => $value) {
                if ($page->getRawOriginal($field) !== $value || $page->getAttribute($field) !== $value) { return null; }
            }
            if ($page->getRawOriginal('title') !== $old['page']['title'] || $after['page']['title'] !== $old['page']['title']) { return null; }
            $subtitles = $page->getRelation('textBlocks')->filter(static fn ($block) => $block instanceof TextBlock
                && $block->locale === 'uk' && $block->type === 'subtitle')->values();
            if ($subtitles->count() !== 1) { return null; }
            $subtitle = $subtitles[0];
            $source = ['type' => 'subtitle', 'column' => 'header', 'heading' => null, 'css_class' => null,
                'level' => $old['page']['subtitle_level'] ?? null, 'uuid_key' => $old['page']['subtitle_uuid_key'] ?? 'subtitle'];
            $expected = ['uuid' => M26DetailPackage::uuid($target['identity'], $source, 0), 'page_id' => $fields['id'],
                'page_category_id' => $fields['page_category_id'], 'seeder' => $fields['seeder'], 'locale' => 'uk',
                'sort_order' => 0, 'type' => 'subtitle', 'column' => 'header', 'heading' => null, 'css_class' => null,
                'level' => $source['level'], 'body' => $after['page']['subtitle_html']];
            foreach ($expected as $field => $value) {
                if ($subtitle->getAttribute($field) !== $value) { return null; }
            }

            return $old['page']['title'];
        } catch (\Throwable) { return null; }
    }

    /** Caller-owned preservation of existing standalone theory help; never a global type filter. */
    public static function preserveExcludedTextBlock(object $block, string $caller): ?object
    {
        if ($caller !== 'standalone_test' || ($block->locale ?? null) !== 'uk'
            || !in_array($block->seeder ?? null, self::OWNERS, true)) { return $block; }
        try {
            [$before, $patch] = self::load(); $i = array_search($block->seeder, self::OWNERS, true);
            $target = $patch['targets'][$i]; $old = $before['targets'][$i]['before'];
            if (($block->page_id ?? null) !== $target['page_binding']['id']
                || ($block->page_category_id ?? null) !== $target['page_binding']['page_category_id']) { return $block; }
            if ($block->type === 'subtitle') {
                $source = ['type' => 'subtitle', 'column' => 'header', 'heading' => null, 'css_class' => null,
                    'level' => $old['page']['subtitle_level'] ?? null, 'uuid_key' => $old['page']['subtitle_uuid_key'] ?? 'subtitle'];
                if ($block->uuid !== M26DetailPackage::uuid($target['identity'], $source, 0)
                    || (int) ($block->sort_order ?? -1) !== 0 || $block->body !== $target['after']['page']['subtitle_html']) { return $block; }
                foreach (['type', 'column', 'heading', 'level', 'css_class'] as $field) {
                    if (($block->$field ?? null) !== ($source[$field] ?? null)) { return $block; }
                }
                $body = $old['page']['subtitle_html'];
            } else {
                $data = json_decode((string) $block->body, true, flags: JSON_THROW_ON_ERROR);
                $binding = self::binding($block, $data);
                if ($binding === null) { return $block; }
                if ($binding['slot'] === 6 && $binding['source']['type'] === 'practice-set') { return null; }
                if (!isset($old['page']['blocks'][$binding['slot']])) { return $block; }
                $body = $old['page']['blocks'][$binding['slot']]['body'];
            }
            $copy = clone $block;
            if (method_exists($copy, 'setRawAttributes') && method_exists($copy, 'getAttributes')) {
                $copy->setRawAttributes(array_replace($copy->getAttributes(), ['body' => $body]), true);
            } else { $copy->body = $body; }

            return $copy;
        } catch (\Throwable) { return $block; }
    }

    public static function preserveCoursePage(?Page $page): ?Page
    {
        if ($page === null || !in_array($page->getRawOriginal('seeder'), self::OWNERS, true) || !$page->relationLoaded('textBlocks')) { return $page; }
        try {
            [$before, $patch] = self::load(); $i = array_search($page->getRawOriginal('seeder'), self::OWNERS, true);
            $target = $patch['targets'][$i]; $old = $before['targets'][$i]['before']; $blocks = $page->textBlocks;
            if ($page->getKey() !== $target['page_binding']['id'] || $page->getRawOriginal('page_category_id') !== $target['page_binding']['page_category_id']
                || $page->getRawOriginal('slug') !== $target['slug'] || $page->getRawOriginal('title') !== $old['page']['title']
                || $page->getRawOriginal('type') !== 'theory' || $page->getRawOriginal('text') !== $target['after']['page']['subtitle_text']) { return $page; }
            foreach (['page_category_id' => $target['page_binding']['page_category_id'], 'seeder' => $target['identity'],
                'slug' => $target['slug'], 'title' => $old['page']['title'], 'type' => 'theory',
                'text' => $target['after']['page']['subtitle_text']] as $field => $value) {
                if ($page->getAttribute($field) !== $value) { return $page; }
            }
            $uk = $blocks->where('locale', 'uk')->values();
            if ($uk->count() !== count($target['after']['page']['blocks']) + 1) { return $page; }
            foreach ($target['after']['page']['blocks'] as $slot => $source) {
                $model = $uk->firstWhere('uuid', M26DetailPackage::uuid($target['identity'], $source, $slot + 1));
                if ($model === null || self::binding($model, json_decode($model->body, true)) === null) { return $page; }
            }
            $subtitle = $uk->firstWhere('type', 'subtitle');
            if (!$subtitle instanceof TextBlock) { return $page; }
            $source = ['type' => 'subtitle', 'column' => 'header', 'heading' => null, 'css_class' => null,
                'level' => $old['page']['subtitle_level'] ?? null, 'uuid_key' => $old['page']['subtitle_uuid_key'] ?? 'subtitle'];
            $expected = ['uuid' => M26DetailPackage::uuid($target['identity'], $source, 0), 'page_id' => $target['page_binding']['id'],
                'page_category_id' => $target['page_binding']['page_category_id'], 'seeder' => $target['identity'], 'locale' => 'uk',
                'sort_order' => 0, 'type' => 'subtitle', 'column' => 'header', 'heading' => null, 'css_class' => null,
                'level' => $source['level'], 'body' => $target['after']['page']['subtitle_html']];
            foreach ($expected as $field => $value) {
                if ($subtitle->getAttribute($field) !== $value) { return $page; }
            }
            $copy = clone $page; $copy->setRawAttributes(array_replace($page->getAttributes(), ['text' => $old['page']['subtitle_text']]), true);
            $restored = $blocks->reject(static fn ($b) => $b->locale === 'uk')->values();
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
