<?php

namespace App\Support;

use App\Models\Page;

/** Finite frozen M54 descriptor; the shared release owns rendering and guards. */
final class M54PassivePatternsPackage
{
    public const BASE_SHA = 'f4e1c0d94a590d630582e2bb613cbdc93654fc4a';
    public const MASTER = 'docs/content/m54-passive-patterns.v1.0.0.json';
    public const MASTER_SHA = 'd34d35d9ff0882160c0fd91fbfddda32294c73282eccc87fb9040d1fe8a1e9fb';
    public const BEFORE = 'database/content-patches/m54-passive-patterns-before.v1.json';
    public const BEFORE_SHA = '66268c26cfea551c925a4374ec35f17dc31cc79147a8336977c66ef6671478eb';
    public const SOURCE = 'database/content-patches/m54-passive-patterns.v1.json';
    public const SOURCE_SHA = '0523683ff2c5b4621831d7fa10ae9675941069c7206ac44fa56ea7df45d6f9f9';
    public const CONTRACT = 'docs/content/theory-sentence-builders/m54-passive-patterns.v1.json';
    public const CONTRACT_SHA = 'f91a4366af510eeeac0482517529675683038036f377628ece5ca704e95a45d8';
    public const OWNERS = [
        'Database\\Seeders\\Page_V3\\PassiveVoice\\Basics\\PassiveVoicePhrasalVerbsTheorySeeder',
        'Database\\Seeders\\Page_V3\\PassiveVoice\\Basics\\PassiveVoiceRestrictionsTheorySeeder',
        'Database\\Seeders\\Page_V3\\PassiveVoice\\Basics\\PassiveVoiceTwoObjectVerbsTheorySeeder',
    ];
    public const SLUGS = ['theory-passive-voice-phrasal-verbs', 'theory-passive-voice-restrictions', 'theory-passive-voice-two-object-verbs'];
    public const PAGE_IDS = [99, 100, 101]; // Fresh independent local M54 BEFORE, not source-order guesses.
    public const CATEGORY_IDS = [1, 1, 1];
    public const LEVELS = ['B1', 'B1', 'B1'];
    public const DISPLAY_LEVELS = ['B1–B2', 'B1–B2', 'B1'];
    public const CONTINUING_COUNTS = [9, 10, 9];
    public const CONTINUING_LEVELS = [
        ['B1', 'B1', 'B1', 'B1', 'B1', 'B2', 'B1', 'B1', null],
        ['B1', 'B1', 'B1', 'B1', 'B2', 'B2', 'B2', 'B1', 'B1', null],
        ['B1', 'B1', 'B1', 'B1', 'B1', 'B2', 'B1', 'B1', null],
    ];
    public const RENDERED_TITLES = ['Пасив фразових дієслів (Phrasal Verbs in Passive)', 'Обмеження пасиву', 'Two-object verbs'];

    public static function profile(): array
    {
        $bindings = [];
        foreach (self::OWNERS as $i => $owner) {
            $bindings[] = ['identity' => $owner, 'slug' => self::SLUGS[$i], 'page_id' => self::PAGE_IDS[$i],
                'category_id' => self::CATEGORY_IDS[$i], 'ancestry' => ['passive-voice'],
                'local_url' => 'http://gramlyze.loc/theory/passive-voice/'.self::SLUGS[$i],
                'practice_level' => self::LEVELS[$i], 'display_level' => self::DISPLAY_LEVELS[$i],
                'rendered_title' => self::RENDERED_TITLES[$i], 'continuing_levels' => self::CONTINUING_LEVELS[$i]];
        }
        return ['label' => 'M54', 'base_sha' => self::BASE_SHA, 'version' => '1.0.0',
            'marker' => 'm54_native_v1', 'detail_marker' => 'm54_detail_key', 'uuid_prefix' => 'm54:', 'design' => 'm54-native-v1',
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
