<?php

namespace App\Support;

use App\Models\Page;
use RuntimeException;

/** Finite M67 data policy; all presentation and preservation use the accepted native primitive. */
final class M67VerbPatternsCompletion
{
    public const BASE_SHA = 'a8db1d50a01005b9a519ee33a655cf65c20d13aa';
    public const MASTER = 'docs/content/m67-verb-patterns-completion.v1.0.0.json';
    public const MASTER_SHA = '864a6d33f0b9d40e20acb0725ac0d6ce9f886b1ea876c7562325c10e1b2cda39';
    public const BEFORE = 'docs/content/m67-before-source.v1.json';
    public const BEFORE_SHA = '36be0c65197da8f28cccc1032c1b736f29105d256cb79685afa8a4276b785764';
    public const SOURCE = 'database/content-patches/m67-verb-patterns-completion.v1.json';
    public const SOURCE_SHA = 'de64f598892a693bbd6292c31ff484fd8bbe7662d305a5c4217ff7ff794568ae';
    public const CONTRACT = 'docs/content/theory-sentence-builders/m67-verb-patterns-completion.v1.json';
    public const CONTRACT_SHA = '2d964cd1ecf4d4ea4e2c29297994bdca1806e54395369825c24a0e106535fe5d';
    public const CHECKSUMS = 'docs/content/m67-source-checksums.v1.json';
    public const CHECKSUMS_SHA = 'a1ec7f331d1ea457f94b158ff5f1420056a82ef91029d286007cfe2b2a7151fd';
    public const OWNERS = [
        'Database\\Seeders\\Page_V3\\VerbPatterns\\VerbPatternsVerbsPlusInfinitiveTheorySeeder',
        'Database\\Seeders\\Page_V3\\VerbPatterns\\VerbPatternsStopRememberForgetTryRegretTheorySeeder',
        'Database\\Seeders\\Page_V3\\VerbPatterns\\VerbPatternsBeUsedToGetUsedToUsedToTheorySeeder',
    ];
    public const SLUGS = ['verbs-plus-infinitive', 'stop-remember-forget-try-regret', 'be-used-to-get-used-to-used-to'];
    public const PAGE_IDS = [200, 197, 194];
    public const CATEGORY_IDS = [33, 33, 33];
    public const ANCESTRIES = [['verb-patterns'], ['verb-patterns'], ['verb-patterns']];
    public const LEVELS = ['B1', 'B1', 'B1'];
    public const DISPLAY_LEVELS = ['B1', 'B1-B2', 'B1-B2'];
    public const CONTINUING_COUNTS = [6, 6, 6];
    public const CONTINUING_LEVELS = [
        ['B1', 'B1', 'B1', 'B1', 'B1', null],
        ['B1', 'B1', 'B1', 'B1', 'B1', null],
        ['B1', 'B1', 'B1', 'B1', 'B1', null],
    ];
    public const CONTINUING_TYPES = [
        ['hero', 'forms-grid', 'usage-panels', 'mistakes-grid', 'summary-list', 'navigation-chips'],
        ['hero', 'comparison-table', 'usage-panels', 'mistakes-grid', 'summary-list', 'navigation-chips'],
        ['hero', 'comparison-table', 'usage-panels', 'mistakes-grid', 'summary-list', 'navigation-chips'],
    ];
    public const RENDERED_TITLES = [
        'Verbs + Infinitive',
        'Stop / Remember / Forget / Try / Regret',
        'Be used to / Get used to / Used to',
    ];
    public const PRACTICE_KEYS = ['infinitive-practice', 'meaning-practice', 'usedto-practice'];

    public static function profile(): array
    {
        $bindings = [];
        foreach (self::OWNERS as $i => $owner) {
            $bindings[] = ['identity' => $owner, 'slug' => self::SLUGS[$i],
                'page_id' => self::PAGE_IDS[$i], 'category_id' => self::CATEGORY_IDS[$i],
                'ancestry' => self::ANCESTRIES[$i],
                'local_url' => 'http://gramlyze.loc/theory/verb-patterns/'.self::SLUGS[$i],
                'practice_level' => self::LEVELS[$i], 'display_level' => self::DISPLAY_LEVELS[$i],
                'rendered_title' => self::RENDERED_TITLES[$i], 'continuing_levels' => self::CONTINUING_LEVELS[$i],
                'appended_blocks' => [['key' => self::PRACTICE_KEYS[$i],
                    'type' => 'practice-set', 'column' => 'left', 'level' => self::LEVELS[$i]]]];
        }
        return ['label' => 'M67', 'base_sha' => self::BASE_SHA, 'version' => '1.0.0',
            'marker' => 'm67_native_v1', 'detail_marker' => 'm67_detail_key',
            'uuid_prefix' => 'm67:', 'design' => 'm67-native-v1',
            // The fifth artifact is a checked companion; the first four load indices stay unchanged.
            'artifacts' => [self::BEFORE => self::BEFORE_SHA, self::SOURCE => self::SOURCE_SHA,
                self::MASTER => self::MASTER_SHA, self::CONTRACT => self::CONTRACT_SHA,
                self::CHECKSUMS => self::CHECKSUMS_SHA], 'bindings' => $bindings];
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
        if (($before['content_type'] ?? null) !== 'M67_source_before'
            || ($patch['content_type'] ?? null) !== 'M67_Verb_Patterns_Completion') {
            throw new RuntimeException('M67 exact provenance differs.');
        }
        foreach ($master['lessons'] ?? [] as $i => $lesson) {
            self::plainFeedback($lesson);
            $types = self::CONTINUING_TYPES[$i] ?? null;
            if ($types === null
                || array_column($before['targets'][$i]['before']['page']['blocks'] ?? [], 'type') !== $types
                || array_column(array_slice($lesson['blocks'] ?? [], 0, 6), 'type') !== $types) {
                throw new RuntimeException('M67 actual six native continuing types differ.');
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
                            throw new RuntimeException('M67 x-text feedback must be plain text without tags/entities.');
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
