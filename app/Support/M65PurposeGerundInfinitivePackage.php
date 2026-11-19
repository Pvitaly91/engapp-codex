<?php

namespace App\Support;

use App\Models\Page;
use RuntimeException;

/** M65 finite mixed-category data policy; existing native serializer/components only. */
final class M65PurposeGerundInfinitivePackage
{
    public const BASE_SHA = '8371a96ad8b0254d5e88d2265dff57d91cb87190';
    public const MASTER = 'docs/content/m65-purpose-gerund-infinitive.v1.0.0.json';
    public const MASTER_SHA = 'c42c6a24735731b1a958a5aaa173b92a32e2b9f7a883274e8d4110d165706c98';
    public const BEFORE = 'database/content-patches/m65-purpose-gerund-infinitive-before.v1.json';
    public const BEFORE_SHA = '87e158a8c9a850db1160686603e11f902b3673c45ebb51d5bbf4138332980b97';
    public const SOURCE = 'database/content-patches/m65-purpose-gerund-infinitive.v1.json';
    public const SOURCE_SHA = '1bd5439ce9729c0d4e1003002d3484b8b3c9aae31a6ce11ef2d9a3ffa6b274d7';
    public const CONTRACT = 'docs/content/theory-sentence-builders/m65-purpose-gerund-infinitive.v1.json';
    public const CONTRACT_SHA = '252aa15bfe5d405f3b2f0d0b7464722ae20f8c193edc52b328c9bed3f9bcbab5';
    public const OWNERS = [
        'Database\\Seeders\\Page_V3\\ClausesAndLinkingWords\\ClausesAndLinkingWordsPurposeTheorySeeder',
        'Database\\Seeders\\Page_V3\\VerbPatterns\\VerbPatternsGerundTheorySeeder',
        'Database\\Seeders\\Page_V3\\VerbPatterns\\VerbPatternsToInfinitiveTheorySeeder',
    ];
    public const SLUGS = ['purpose-to-in-order-to-so-that', 'gerund', 'to-infinitive'];
    public const PAGE_IDS = [35, 195, 198];
    public const CATEGORY_IDS = [34, 33, 33];
    public const ANCESTRIES = [['clauses-and-linking-words'], ['verb-patterns'], ['verb-patterns']];
    public const LEVELS = ['B1', 'A2', 'A2'];
    public const DISPLAY_LEVELS = ['B1-B2', 'A2-B1', 'A2-B1'];
    public const CONTINUING_COUNTS = [7, 6, 6];
    public const CONTINUING_LEVELS = [
        ['B1', null, null, null, null, null, null],
        ['A2', 'A2', 'A2', 'A2', 'A2', null],
        ['A2', 'A2', 'A2', 'A2', 'A2', null],
    ];
    public const RENDERED_TITLES = ['Purpose: to / in order to / so that', 'Gerund', 'To-Infinitive'];

    public static function profile(): array
    {
        $bindings = [];
        $purposeTransitions = [
            1 => ['left', '1. to / in order to', 'forms-grid'],
            2 => ['left', '2. so that + clause', 'usage-panels'],
            3 => ['right', '3. Вибір pattern', 'comparison-table'],
            4 => ['right', '4. Типові помилки', 'mistakes-grid'],
            5 => ['right', '5. Коротке правило', 'summary-list'],
        ];
        foreach (self::OWNERS as $i => $owner) {
            $binding = ['identity' => $owner, 'slug' => self::SLUGS[$i],
                'page_id' => self::PAGE_IDS[$i], 'category_id' => self::CATEGORY_IDS[$i],
                'ancestry' => self::ANCESTRIES[$i],
                'local_url' => 'http://gramlyze.loc/theory/'.implode('/', self::ANCESTRIES[$i]).'/'.self::SLUGS[$i],
                'practice_level' => self::LEVELS[$i], 'display_level' => self::DISPLAY_LEVELS[$i],
                'rendered_title' => self::RENDERED_TITLES[$i], 'continuing_levels' => self::CONTINUING_LEVELS[$i]];
            // Only Purpose has HTML boxes. Native A2 owners keep all metadata unchanged.
            if ($i === 0) {
                foreach ($purposeTransitions as $slot => [$column, $heading, $type]) {
                    $binding['continuing_metadata'][$slot] = ['column' => $column,
                        'before' => ['type' => 'box', 'heading' => $heading, 'level' => null],
                        'after' => ['type' => $type, 'heading' => null, 'level' => null]];
                    $binding['preserved_native_metadata'][$slot] = ['column' => $column, 'level' => null];
                }
            }
            $binding['appended_blocks'] = [['key' => ['purpose','gerund','infinitive'][$i].'-practice',
                'type' => 'practice-set', 'column' => 'left', 'level' => self::LEVELS[$i]]];
            $bindings[] = $binding;
        }
        return ['label' => 'M65', 'base_sha' => self::BASE_SHA, 'version' => '1.0.0',
            'marker' => 'm65_native_v1', 'detail_marker' => 'm65_detail_key',
            'uuid_prefix' => 'm65:', 'design' => 'm65-native-v1',
            'artifacts' => [self::BEFORE => self::BEFORE_SHA, self::SOURCE => self::SOURCE_SHA,
                self::MASTER => self::MASTER_SHA, self::CONTRACT => self::CONTRACT_SHA], 'bindings' => $bindings];
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
        if (($before['content_type'] ?? null) !== 'M65_source_before'
            || ($patch['content_type'] ?? null) !== 'M65_Purpose_Gerund_Infinitive') {
            throw new RuntimeException('M65 exact provenance differs.');
        }
        foreach ($master['lessons'] ?? [] as $lesson) { self::plainFeedback($lesson); }
        self::release()->validate($before, $patch, $master, $contract);
    }
    private static function plainFeedback(array $lesson): void
    {
        foreach ($lesson['blocks'] ?? [] as $block) {
            if (($block['type'] ?? null) !== 'practice-set') { continue; }
            foreach (['selects','choices','inputs'] as $group) {
                foreach ($block['data'][$group] ?? [] as $task) {
                    foreach ($task['feedback'] ?? [] as $text) {
                        if (!is_string($text) || preg_match('/<(?:\/?[a-z]|[!?])|&(?:#[0-9]+|#x[0-9a-f]+|[a-z][a-z0-9]+);/iu', $text)) {
                            throw new RuntimeException('M65 x-text feedback must be plain text without tags/entities.');
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
