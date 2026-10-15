<?php

namespace App\Support;

use App\Models\Page;

/** Finite M56 data policy. Unreviewed hashes deliberately cannot load a release. */
final class M56GerundSimplePassivesPackage
{
    public const BASE_SHA = 'fff71b7daa371497b3df100c2d97d108058dc175';
    public const MASTER = 'docs/content/m56-gerund-simple-passives.v1.0.1.json';
    public const MASTER_SHA = 'e0d9563ba73aa78140f5e1e473a41949985970a65ab70c9db60aecc2b516e3fb';
    public const BEFORE = 'database/content-patches/m56-gerund-simple-passives-before.v1.json';
    public const BEFORE_SHA = '6c88d15e88709da723fc65db0ac9bb31f09a57b5a4adb4c7a1981b7f6e30060a';
    public const SOURCE = 'database/content-patches/m56-gerund-simple-passives.v1.0.1.json';
    public const SOURCE_SHA = '3d2b4757255d9dde7ecb9826b1e37ef23e71cf3d985bed3a06479a3d978ef1ab';
    public const CONTRACT = 'docs/content/theory-sentence-builders/m56-gerund-simple-passives.v1.json';
    public const CONTRACT_SHA = 'dd724a1d5faf6d4f8e52b9986c7674e46813dbfcda0d7ada7c6d69e95e01b7fa';
    public const OWNERS = [
        'Database\\Seeders\\Page_V3\\PassiveVoice\\InfinitivesGerund\\PassiveVoicePassiveGerundTheorySeeder',
        'Database\\Seeders\\Page_V3\\PassiveVoice\\Tenses\\PassiveVoicePresentSimpleTheorySeeder',
        'Database\\Seeders\\Page_V3\\PassiveVoice\\Tenses\\PassiveVoicePastSimpleTheorySeeder',
    ];
    public const SLUGS = ['theory-passive-voice-passive-gerund', 'theory-passive-voice-present-simple', 'theory-passive-voice-past-simple'];
    public const PAGE_IDS = [102, 113, 110];
    public const CATEGORY_IDS = [17, 1, 1];
    public const ANCESTRIES = [['passive-voice', 'passive-voice-infinitives-gerund'], ['passive-voice'], ['passive-voice']];
    public const LEVELS = ['B2', 'A2', 'A2'];
    public const DISPLAY_LEVELS = ['B2–C1', 'A2', 'A2'];
    public const CONTINUING_COUNTS = [10, 9, 10];
    public const CONTINUING_LEVELS = [
        ['B2', 'B2', 'B2', 'C1', 'B2', 'B2', 'B2', 'B2', 'B2', null],
        ['A2', 'A2', 'A2', 'A2', 'A2', 'A2', 'A2', 'A2', 'A2'],
        ['A2', 'A2', 'A2', 'A2', 'A2', 'A2', 'A2', 'A2', 'A2', 'A2'],
    ];
    public const RENDERED_TITLES = ['Passive Gerund', 'Present Simple Passive', 'Past Simple Passive'];

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
        $bindings[0]['before_subtitle'] = ['mode' => 'captured-plain',
            'sha256' => 'fbc30cdfc88a1916b9763c4c53f132947f4341bde9b76e03f99d5eb51b42660e',
            'raw_page_title' => 'Passive Gerund', 'extracted_title' => 'Passive Gerund', 'h1' => 'Passive Gerund'];
        return ['label' => 'M56', 'base_sha' => self::BASE_SHA, 'version' => '1.0.1',
            'marker' => 'm56_native_v1', 'detail_marker' => 'm56_detail_key', 'uuid_prefix' => 'm56:', 'design' => 'm56-native-v1',
            'artifacts' => [self::BEFORE => self::BEFORE_SHA, self::SOURCE => self::SOURCE_SHA,
                self::MASTER => self::MASTER_SHA, self::CONTRACT => self::CONTRACT_SHA], 'bindings' => $bindings];
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
