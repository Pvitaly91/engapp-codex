<?php

namespace App\Support;

use App\Models\Page;
use RuntimeException;

/** Finite M64 data policy. Pending hashes cannot activate a draft release. */
final class M64ReasonResultContrastPackage
{
    public const BASE_SHA = 'b5f521d9886a1a9b1c5298f84f581bf7b54e2713';
    public const MASTER = 'docs/content/m64-reason-result-contrast.v1.0.0.json';
    public const MASTER_SHA = '889ea0b30f3b573cbdfd7a09e80178a0131c42eba01da3945d57b79c2d91e318';
    public const BEFORE = 'database/content-patches/m64-reason-result-contrast-before.v1.json';
    public const BEFORE_SHA = 'd558fc75c96eef8d301dffad140dff2752200b9eaab55b6b942041db77fb7296';
    public const SOURCE = 'database/content-patches/m64-reason-result-contrast.v1.json';
    public const SOURCE_SHA = '8b70892aab72a801d9bff5d8df82575b48b68a4a43604bf0f16569d7e8878ee2';
    public const CONTRACT = 'docs/content/theory-sentence-builders/m64-reason-result-contrast.v1.json';
    public const CONTRACT_SHA = 'ebb20b28361b6bdb10c2d731706a6286845fa8bd4b90b101ea8ced6964d7d274';
    public const OWNERS = [
        'Database\\Seeders\\Page_V3\\ClausesAndLinkingWords\\ClausesAndLinkingWordsReasonTheorySeeder',
        'Database\\Seeders\\Page_V3\\ClausesAndLinkingWords\\ClausesAndLinkingWordsResultTheorySeeder',
        'Database\\Seeders\\Page_V3\\ClausesAndLinkingWords\\ClausesAndLinkingWordsContrastTheorySeeder',
    ];
    public const SLUGS = ['reason-because-since-as', 'result-so-therefore-as-a-result', 'contrast-although-though-however'];
    public const PAGE_IDS = [36, 37, 30];
    public const CATEGORY_IDS = [34, 34, 34];
    public const ANCESTRIES = [['clauses-and-linking-words'], ['clauses-and-linking-words'], ['clauses-and-linking-words']];
    public const LEVELS = ['B1', 'B1', 'B1'];
    public const DISPLAY_LEVELS = ['B1-B2', 'B1-B2', 'B1-B2'];
    public const CONTINUING_COUNTS = [7, 7, 7];
    public const CONTINUING_LEVELS = [
        ['B1', null, null, null, null, null, null],
        ['B1', null, null, null, null, null, null],
        ['B1', null, null, null, null, null, null],
    ];
    public const RENDERED_TITLES = ['Reason: because / since / as', 'Result: so / therefore / as a result', 'Contrast: although / though / however'];

    public static function profile(): array
    {
        $bindings = [];
        $slots = [
            [
                1 => ['left', '1. Основне значення', 'usage-panels'],
                2 => ['left', '2. Типовий pattern', 'forms-grid'],
                3 => ['right', '3. Приклади і відтінки', 'comparison-table'],
                4 => ['right', '4. Типові помилки', 'mistakes-grid'],
                5 => ['right', '5. Коротке правило', 'summary-list'],
            ],
            [
                1 => ['left', '1. Як працює so', 'usage-panels'],
                2 => ['left', '2. Як працюють therefore і as a result', 'forms-grid'],
                3 => ['right', '3. Пунктуація і pattern', 'comparison-table'],
                4 => ['right', '4. Типові помилки', 'mistakes-grid'],
                5 => ['right', '5. Коротке правило', 'summary-list'],
            ],
            [
                1 => ['left', '1. Як працюють although і though', 'usage-panels'],
                2 => ['left', '2. Як працює however', 'forms-grid'],
                3 => ['right', '3. Порівняй pattern', 'comparison-table'],
                4 => ['right', '4. Типові помилки', 'mistakes-grid'],
                5 => ['right', '5. Коротке правило', 'summary-list'],
            ],
        ];
        foreach (self::OWNERS as $i => $owner) {
            $binding = ['identity' => $owner, 'slug' => self::SLUGS[$i], 'page_id' => self::PAGE_IDS[$i],
                'category_id' => self::CATEGORY_IDS[$i], 'ancestry' => self::ANCESTRIES[$i],
                'local_url' => 'http://gramlyze.loc/theory/'.implode('/', self::ANCESTRIES[$i]).'/'.self::SLUGS[$i],
                'practice_level' => self::LEVELS[$i], 'display_level' => self::DISPLAY_LEVELS[$i],
                'rendered_title' => self::RENDERED_TITLES[$i], 'continuing_levels' => self::CONTINUING_LEVELS[$i]];
            foreach ($slots[$i] as $slot => [$column, $heading, $type]) {
                $binding['continuing_metadata'][$slot] = ['column' => $column,
                    'before' => ['type' => 'box', 'heading' => $heading, 'level' => null],
                    'after' => ['type' => $type, 'heading' => null, 'level' => null]];
                $binding['preserved_native_metadata'][$slot] = ['column' => $column, 'level' => null];
            }
            $binding['appended_blocks'] = [['key' => ['reason', 'result', 'contrast'][$i].'-practice',
                'type' => 'practice-set', 'column' => 'left', 'level' => 'B1']];
            $bindings[] = $binding;
        }
        return ['label' => 'M64', 'base_sha' => self::BASE_SHA, 'version' => '1.0.0',
            'marker' => 'm64_native_v1', 'detail_marker' => 'm64_detail_key', 'uuid_prefix' => 'm64:', 'design' => 'm64-native-v1',
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
        if (($before['content_type'] ?? null) !== 'M64_source_before'
            || ($patch['content_type'] ?? null) !== 'M64_Reason_Result_Contrast') {
            throw new RuntimeException('M64 new provenance/content_type must name this exact release.');
        }
        foreach ($master['lessons'] ?? [] as $lesson) { self::plainFeedback($lesson); }
        self::release()->validate($before, $patch, $master, $contract);
    }

    /** Author-source rejection only: no tag stripping, HTML decoding or runtime data mutation. */
    private static function plainFeedback(array $lesson): void
    {
        foreach ($lesson['blocks'] ?? [] as $block) {
            if (($block['type'] ?? null) !== 'practice-set') { continue; }
            foreach (['selects', 'choices', 'inputs'] as $group) {
                foreach ($block['data'][$group] ?? [] as $task) {
                    foreach ($task['feedback'] ?? [] as $text) {
                        if (!is_string($text)
                            || preg_match('/<(?:\/?[a-z]|[!?])|&(?:#[0-9]+|#x[0-9a-f]+|[a-z][a-z0-9]+);/iu', $text)) {
                            throw new RuntimeException('M64 native x-text task.feedback must be plain text, without tags or entities.');
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
