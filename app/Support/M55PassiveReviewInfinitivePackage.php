<?php

namespace App\Support;

use App\Models\Page;

/** Finite M55 data policy. Unreviewed hashes deliberately cannot load a release. */
final class M55PassiveReviewInfinitivePackage
{
    public const BASE_SHA = '0cf633036f4c91f2376976651ec0519d80ec6191';
    public const MASTER = 'docs/content/m55-passive-review-infinitive.v1.0.0.json';
    public const MASTER_SHA = 'd99299587c3876973869c1b9593bb158cf3904cae2df1c972d07866ce573cd1d';
    public const BEFORE = 'database/content-patches/m55-passive-review-infinitive-before.v1.json';
    public const BEFORE_SHA = '31779edfe50d7a1c00059ffea591eced2e068ca89976707e833a38a477f4ac51';
    public const SOURCE = 'database/content-patches/m55-passive-review-infinitive.v1.json';
    public const SOURCE_SHA = 'af0cf5fcc06550d0b8c71e6dfc1ec208f9d18d636d070a3ddc02bd6df006fa0c';
    public const CONTRACT = 'docs/content/theory-sentence-builders/m55-passive-review-infinitive.v1.json';
    public const CONTRACT_SHA = '4811578f527df86ee5c7835bab6913fa7d57629e3bfab1c49ae075234c70c612';
    public const OWNERS = [
        'Database\\Seeders\\Page_V3\\PassiveVoice\\Basics\\PassiveVoiceCommonMistakesTheorySeeder',
        'Database\\Seeders\\Page_V3\\PassiveVoice\\Basics\\PassiveVoiceFormalityStyleTheorySeeder',
        'Database\\Seeders\\Page_V3\\PassiveVoice\\InfinitivesGerund\\PassiveVoicePassiveInfinitiveTheorySeeder',
    ];
    public const SLUGS = ['theory-passive-voice-common-mistakes', 'theory-passive-voice-formality-style', 'theory-passive-voice-passive-infinitive'];
    public const PAGE_IDS = [93, 94, 103];
    public const CATEGORY_IDS = [1, 1, 17];
    public const ANCESTRIES = [['passive-voice'], ['passive-voice'], ['passive-voice', 'passive-voice-infinitives-gerund']];
    public const LEVELS = ['A2', 'B2', 'B2'];
    public const DISPLAY_LEVELS = ['A2–B2', 'B2–C1', 'B2–C1'];
    public const CONTINUING_COUNTS = [11, 10, 9];
    public const CONTINUING_LEVELS = [
        ['A2', 'A2', 'A2', 'A2', 'A2', 'B1', 'B1', 'A2', 'A2', 'A2', null],
        ['B2', 'B2', 'B2', 'B2', 'B2', 'C1', 'C1', 'B2', 'B2', null],
        ['B2', 'B2', 'B2', 'C1', 'B2', 'B2', 'B2', 'B2', null],
    ];
    public const RENDERED_TITLES = ['Приклади вживання та типові помилки', 'Формальність та стиль пасиву', 'Passive Infinitive'];
    public const NEUTRAL_SLOT_UUID = '70e88baa-d5b1-5007-b7ea-5b08af8a3b51';

    public static function profile(): array
    {
        $bindings = [];
        foreach (self::OWNERS as $i => $owner) {
            $bindings[] = ['identity' => $owner, 'slug' => self::SLUGS[$i], 'page_id' => self::PAGE_IDS[$i],
                'category_id' => self::CATEGORY_IDS[$i], 'ancestry' => self::ANCESTRIES[$i],
                'local_url' => 'http://gramlyze.loc/theory/'.implode('/', self::ANCESTRIES[$i]).'/'.self::SLUGS[$i],
                'practice_level' => self::LEVELS[$i], 'display_level' => self::DISPLAY_LEVELS[$i],
                'rendered_title' => self::RENDERED_TITLES[$i], 'continuing_levels' => self::CONTINUING_LEVELS[$i]];
        }
        $bindings[1]['component_overrides'] = [7 => 'usage-panels'];
        $bindings[2]['before_subtitle'] = ['mode' => 'captured-plain',
            'sha256' => '0fa42a349d06ce69204e4531678883d4a5cbe3c15fdd9f5e1f91d8bbc7b4fb4e',
            'raw_page_title' => 'Passive Infinitive', 'extracted_title' => 'Passive Infinitive', 'h1' => 'Passive Infinitive'];
        return ['label' => 'M55', 'base_sha' => self::BASE_SHA, 'version' => '1.0.0',
            'marker' => 'm55_native_v1', 'detail_marker' => 'm55_detail_key', 'uuid_prefix' => 'm55:', 'design' => 'm55-native-v1',
            'artifacts' => [self::BEFORE => self::BEFORE_SHA, self::SOURCE => self::SOURCE_SHA,
                self::MASTER => self::MASTER_SHA, self::CONTRACT => self::CONTRACT_SHA], 'bindings' => $bindings];
    }

    /**
     * This one historical stored slot is a neutral comparison, not a correction.
     * On source drift it remains fully readable, but verified design/body guards
     * in the caller alone may grant native accents or disclosure authority.
     */
    public static function semanticComponent(object $block, array $data, ?array $design): ?string
    {
        $expected = ['seeder' => self::OWNERS[1], 'locale' => 'uk', 'page_id' => 94, 'page_category_id' => 1,
            'uuid' => self::NEUTRAL_SLOT_UUID, 'type' => 'mistakes-grid', 'column' => 'left',
            'heading' => null, 'css_class' => null, 'level' => 'B2'];
        foreach ($expected as $field => $value) { if (($block->$field ?? null) !== $value) { return null; } }
        if ((int) ($block->sort_order ?? -1) !== 8 || !is_array($data['sections'] ?? null) || $data['sections'] === []
            || array_key_exists('items', $data) || json_decode((string) ($block->body ?? ''), true) !== $data) { return null; }
        $correction = static function (array $values) use (&$correction): bool {
            foreach ($values as $key => $value) {
                if (in_array($key, ['wrong', 'right', 'wrong_en', 'wrong_ua', 'right_en', 'right_ua'], true)
                    || (is_array($value) && $correction($value))) { return true; }
            }
            return false;
        };
        if ($correction($data)) { return null; }
        foreach ($data['sections'] as $section) {
            if (!is_array($section) || !is_string($section['label'] ?? null) || trim($section['label']) === ''
                || !is_string($section['description'] ?? null)
                || !in_array($section['color'] ?? null, ['blue', 'emerald', 'sky', 'amber', 'rose', 'slate'], true)
                || !is_array($section['examples'] ?? null)) { return null; }
            foreach ($section['examples'] as $example) {
                if (!is_array($example) || !is_string($example['en'] ?? null) || !is_string($example['ua'] ?? null)) { return null; }
            }
        }
        return 'usage-panels';
    }

    private static function release(): TheoryNativeContentRelease { return new TheoryNativeContentRelease(self::profile()); }
    public static function load(?string $root = null): array { return self::release()->load($root); }
    public static function definition(array $lesson, array $before): array { return self::release()->definition($lesson, $before); }
    public static function validate(array $before, array $patch, array $master, ?array $contract = null): void { self::release()->validate($before, $patch, $master, $contract); }
    public static function presentation(object $block, array $data): ?array { return self::release()->presentation($block, $data); }
    public static function validDesign(?array $design): bool { return self::release()->validDesign($design); }
    public static function pointPanels(?array $design): ?array { return self::release()->pointPanels($design); }
    public static function correction(array $item, ?array $design): ?array { return self::release()->correction($item, $design); }
    public static function preserveExcludedTextBlock(object $block, string $caller): ?object { return self::release()->preserveExcludedTextBlock($block, $caller); }
    public static function preserveCoursePage(?Page $page): ?Page { return self::release()->preserveCoursePage($page); }
}
