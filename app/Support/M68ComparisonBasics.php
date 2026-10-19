<?php

namespace App\Support;

use App\Models\Page;
use RuntimeException;

/** Finite M68 data policy; all presentation and preservation use the accepted native primitive. */
final class M68ComparisonBasics
{
    public const BASE_SHA = '2161cb0b03afd203933ad022ccb4e77e8e8ca363';
    public const MASTER = 'docs/content/m68-comparison-basics.v1.0.0.json';
    public const MASTER_SHA = '2bbc2d702435d2fd45cf6b53d0655b7efbc293c02a1c0b63676eddf44d16c5d1';
    public const BEFORE = 'docs/content/m68-before-source.v1.json';
    public const BEFORE_SHA = '6f95b0d6174417f29221636377911b3bd05c3a918e62a4cd20ff4b1987d61455';
    public const SOURCE = 'database/content-patches/m68-comparison-basics.v1.json';
    public const SOURCE_SHA = '2e3aa667469414d98ed258dea07004817487e3026cb7577fa1c13d6f0f05e794';
    public const CONTRACT = 'docs/content/theory-sentence-builders/m68-comparison-basics.v1.json';
    public const CONTRACT_SHA = 'd76d289f343c6f2a4bb588ba8e0909c8e852ab0affb0bb4c61788b9d4832b91a';
    public const CHECKSUMS = 'docs/content/m68-source-checksums.v1.json';
    public const CHECKSUMS_SHA = '55a1e154d0948720465f5d6a20fc6c4800ac0b1af3c9c4c0d9fbf1e2d5833453';
    public const OWNERS = ['Database\\Seeders\\Page_V3\\Adjectives\\AdjectivesDegreesOfComparisonTheorySeeder', 'Database\\Seeders\\Page_V3\\Adjectives\\AdjectivesComparativeVsSuperlativeTheorySeeder', 'Database\\Seeders\\Page_V3\\Adjectives\\AdjectivesEqualityComparisonAsAsNotAsAsTheorySeeder'];
    public const SLUGS = ['theory-degrees-of-comparison', 'comparative-vs-superlative', 'equality-comparison-asas-not-asas'];
    public const PAGE_IDS = [274, 273, 276];
    public const CATEGORY_IDS = [52, 52, 52];
    public const ANCESTRIES = [['prykmetniky-ta-pryslinknyky'], ['prykmetniky-ta-pryslinknyky'], ['prykmetniky-ta-pryslinknyky']];
    public const LEVELS = ['A2', 'A2', 'A2'];
    public const DISPLAY_LEVELS = ['A2', 'A2–B1', 'A2–B1'];
    public const CONTINUING_COUNTS = [9, 11, 12];
    public const CONTINUING_LEVELS = [[null, null, null, null, null, null, null, null, null], ['A2', 'A2', 'A2', 'A2', 'A2', 'B1', 'B1', 'B2', 'A2', 'B1', 'A1'], ['A2', 'A2', 'A2', 'A2', 'B1', 'B1', 'A2', 'B1', 'B2', 'A2', 'B1', 'A1']];
    public const CONTINUING_TYPES = [['hero', 'forms-grid', 'usage-panels', 'comparison-table', 'usage-panels', 'usage-panels', 'mistakes-grid', 'summary-list', 'navigation-chips'], ['hero', 'comparison-table', 'usage-panels', 'usage-panels', 'forms-grid', 'comparison-table', 'usage-panels', 'usage-panels', 'mistakes-grid', 'summary-list', 'navigation-chips'], ['hero', 'comparison-table', 'usage-panels', 'comparison-table', 'usage-panels', 'forms-grid', 'comparison-table', 'usage-panels', 'usage-panels', 'mistakes-grid', 'summary-list', 'navigation-chips']];
    public const RENDERED_TITLES = ['Degrees of Comparison', 'Comparative vs Superlative', 'As ... As / Not As ... As'];
    public const PRACTICE_KEYS = ['degrees-practice', 'comparative-practice', 'equality-practice'];

    public const BEFORE_SUBTITLES = [['mode' => 'captured-plain', 'sha256' => 'f74e9ccec77ac5a58a9a0caa8b241f3b9cd760845519426cb9f9219e8fb7a8a7', 'raw_page_title' => 'Degrees of Comparison', 'extracted_title' => 'Degrees of Comparison', 'h1' => 'Degrees of Comparison'], ['mode' => 'captured-plain', 'sha256' => 'd3acd0d5fe8da2fa86402672ea3388a79558935ad8fdcc95a2498e96459f6c0d', 'raw_page_title' => 'Comparative vs Superlative', 'extracted_title' => 'Comparative vs Superlative', 'h1' => 'Comparative vs Superlative'], ['mode' => 'captured-plain', 'sha256' => 'fd76e4b9ac277722a06b9f9c5dc5481f539b7f8ef2c7d7a85c5b6f6c9dabdba3', 'raw_page_title' => 'As ... As / Not As ... As', 'extracted_title' => 'As ... As / Not As ... As', 'h1' => 'As ... As / Not As ... As']];
    public const NEUTRAL_SLOT_UUID = '89698a29-377c-50d0-9cee-c4dfc56faad2';

    public static function profile(): array
    {
        $bindings = [];
        foreach (self::OWNERS as $i => $owner) {
            $bindings[] = ['identity' => $owner, 'slug' => self::SLUGS[$i],
                'page_id' => self::PAGE_IDS[$i], 'category_id' => self::CATEGORY_IDS[$i],
                'ancestry' => self::ANCESTRIES[$i],
                'local_url' => 'http://gramlyze.loc/theory/'.implode('/', self::ANCESTRIES[$i]).'/'.self::SLUGS[$i],
                'practice_level' => self::LEVELS[$i], 'display_level' => self::DISPLAY_LEVELS[$i],
                'rendered_title' => self::RENDERED_TITLES[$i], 'continuing_levels' => self::CONTINUING_LEVELS[$i],
                'before_subtitle' => self::BEFORE_SUBTITLES[$i],
                'component_overrides' => $i === 1 ? [8 => 'usage-panels'] : [],
                'appended_blocks' => [['key' => self::PRACTICE_KEYS[$i],
                    'type' => 'practice-set', 'column' => 'left', 'level' => self::LEVELS[$i]]]];
        }
        return ['label' => 'M68', 'base_sha' => self::BASE_SHA, 'version' => '1.0.0',
            'marker' => 'm68_native_v1', 'detail_marker' => 'm68_detail_key',
            'uuid_prefix' => 'm68:', 'design' => 'm68-native-v1',
            // The fifth artifact is a checked companion; the first four load indices stay unchanged.
            'artifacts' => [self::BEFORE => self::BEFORE_SHA, self::SOURCE => self::SOURCE_SHA,
                self::MASTER => self::MASTER_SHA, self::CONTRACT => self::CONTRACT_SHA,
                self::CHECKSUMS => self::CHECKSUMS_SHA], 'bindings' => $bindings];
    }

    /**
     * The exact historical checklist is neutral teaching, not wrong/right pairs.
     * Stale data remains fully readable; only the verified design grants accents/details.
     */
    public static function semanticComponent(object $block, array $data, ?array $design): ?string
    {
        $expected = ['seeder' => self::OWNERS[1], 'locale' => 'uk', 'page_id' => 273, 'page_category_id' => 52,
            'uuid' => self::NEUTRAL_SLOT_UUID, 'type' => 'mistakes-grid', 'column' => 'right',
            'heading' => null, 'css_class' => null, 'level' => 'A2'];
        foreach ($expected as $field => $value) { if (($block->$field ?? null) !== $value) { return null; } }
        if ((int) ($block->sort_order ?? -1) !== 9 || !is_array($data['sections'] ?? null) || $data['sections'] === []
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

    public static function load(?string $root = null): array
    {
        $data = self::release()->load($root);
        foreach ($data[2]['lessons'] ?? [] as $lesson) { self::plainFeedback($lesson); }
        return $data;
    }

    public static function definition(array $lesson, array $before): array
    {
        self::plainFeedback($lesson);
        return self::release()->definition($lesson, $before);
    }

    public static function validate(array $before, array $patch, array $master, ?array $contract = null): void
    {
        if (($before['content_type'] ?? null) !== 'M68_source_before'
            || ($patch['content_type'] ?? null) !== 'M68_Comparison_Basics') {
            throw new RuntimeException('M68 exact provenance differs.');
        }
        foreach ($master['lessons'] ?? [] as $i => $lesson) {
            self::plainFeedback($lesson);
            $types = self::CONTINUING_TYPES[$i] ?? null;
            if ($types === null
                || array_column($before['targets'][$i]['before']['page']['blocks'] ?? [], 'type') !== $types
                || array_column(array_slice($lesson['blocks'] ?? [], 0, self::CONTINUING_COUNTS[$i]), 'type') !== $types) {
                throw new RuntimeException('M68 actual per-owner native continuing types differ.');
            }
        }
        self::release()->validate($before, $patch, $master, $contract);
    }

    private static function plainFeedback(array $lesson): void
    {
        foreach ($lesson['blocks'] ?? [] as $block) {
            if (($block['type'] ?? null) !== 'practice-set') { continue; }
            foreach (['selects', 'choices', 'inputs'] as $group) {
                foreach ($block['data'][$group] ?? [] as $task) {
                    foreach ($task['feedback'] ?? [] as $text) {
                        if (!is_string($text) || preg_match('/<(?:\\/?[a-z]|[!?])|&(?:#[0-9]+|#x[0-9a-f]+|[a-z][a-z0-9]+);/iu', $text)) {
                            throw new RuntimeException('M68 x-text feedback must be plain text without tags/entities.');
                        }
                    }
                }
            }
        }
    }

    public static function presentation(object $block, array $data): ?array { return self::release()->presentation($block, $data); }
    public static function validDesign(?array $design): bool { return self::release()->validDesign($design); }
    public static function pointPanels(?array $design): ?array { return self::release()->pointPanels($design); }
    public static function correction(array $item, ?array $design): ?array { return self::release()->correction($item, $design); }
    public static function preserveExcludedTextBlock(object $block, string $caller): ?object { return self::release()->preserveExcludedTextBlock($block, $caller); }
    public static function preserveCoursePage(?Page $page): ?Page { return self::release()->preserveCoursePage($page); }
}
