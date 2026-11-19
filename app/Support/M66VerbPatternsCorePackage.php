<?php

namespace App\Support;

use App\Models\Page;
use RuntimeException;

/** Finite M66 native data policy; no new renderer or practice engine. */
final class M66VerbPatternsCorePackage
{
    public const BASE_SHA = 'c96e6b4e0c042f98fb0c105f61225f72d85a4850';
    public const MASTER = 'docs/content/m66-verb-patterns-core.v1.0.2.json';
    public const MASTER_SHA = '39f5a58004cd14dcb17818851ebd3442e1c84ba5535a8d748c1aeb51ab030e2c';
    public const BEFORE = 'database/content-patches/m66-verb-patterns-core-before.v1.json';
    public const BEFORE_SHA = 'bf7a164a4748c0c1d576ac85c11532f6fb1fd25a0b17dfb5ef5a2b1ad4b6b9c6';
    public const SOURCE = 'database/content-patches/m66-verb-patterns-core.v3.json';
    public const SOURCE_SHA = '7339ef045e403c2473ed5e8a034d54bbe1639d179ba4782a84b9ce64088135a7';
    public const CONTRACT = 'docs/content/theory-sentence-builders/m66-verb-patterns-core.v1.json';
    public const CONTRACT_SHA = '702cb32d4dceb208baf7efb4512dd0443f0138b27f222aed1db3c60449d7d641';
    public const OWNERS = [
        'Database\\Seeders\\Page_V3\\VerbPatterns\\VerbPatternsBareInfinitiveTheorySeeder',
        'Database\\Seeders\\Page_V3\\VerbPatterns\\VerbPatternsGerundVsInfinitiveTheorySeeder',
        'Database\\Seeders\\Page_V3\\VerbPatterns\\VerbPatternsVerbsPlusGerundTheorySeeder',
    ];
    public const SLUGS = ['bare-infinitive', 'gerund-vs-infinitive', 'verbs-plus-gerund'];
    public const PAGE_IDS = [193, 196, 199];
    public const CATEGORY_IDS = [33, 33, 33];
    public const ANCESTRIES = [['verb-patterns'], ['verb-patterns'], ['verb-patterns']];
    public const LEVELS = ['A2', 'B1', 'B1'];
    public const DISPLAY_LEVELS = ['A2-B1', 'B1', 'B1'];
    public const CONTINUING_COUNTS = [6, 6, 6];
    public const CONTINUING_LEVELS = [
        ['A2', 'A2', 'A2', 'A2', 'A2', null],
        ['B1', 'B1', 'B1', 'B1', 'B1', null],
        ['B1', 'B1', 'B1', 'B1', 'B1', null],
    ];
    public const RENDERED_TITLES = ['Bare Infinitive', 'Gerund vs Infinitive', 'Verbs + Gerund'];

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
                'appended_blocks' => [['key' => ['bare','comparison','verbs-gerund'][$i].'-practice',
                    'type' => 'practice-set', 'column' => 'left', 'level' => self::LEVELS[$i]]]];
        }
        return ['label' => 'M66', 'base_sha' => self::BASE_SHA, 'version' => '1.0.2',
            'marker' => 'm66_native_v1', 'detail_marker' => 'm66_detail_key',
            'uuid_prefix' => 'm66:', 'design' => 'm66-native-v1',
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
        if (($before['content_type'] ?? null) !== 'M66_source_before'
            || ($patch['content_type'] ?? null) !== 'M66_Verb_Patterns_Core') {
            throw new RuntimeException('M66 exact provenance differs.');
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
                        if (!is_string($text) || preg_match('/<(?:\\/?[a-z]|[!?])|&(?:#[0-9]+|#x[0-9a-f]+|[a-z][a-z0-9]+);/iu', $text)) {
                            throw new RuntimeException('M66 x-text feedback must be plain text without tags/entities.');
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
