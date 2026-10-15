<?php

namespace App\Support;

use App\Models\Page;

/** Finite M58 data policy. Unreviewed hashes deliberately cannot load a release. */
final class M58PastFuturePassivesPackage
{
    public const BASE_SHA = '7c4bac183d93227fbb5544b197538b75c3d579dc';
    public const MASTER = 'docs/content/m58-past-future-passives.v1.0.0.json';
    public const MASTER_SHA = '3e82d91b64eb123fdbdbb343ebedbb8fb30b7bbf79183efeac46d81ca32a52bd';
    public const BEFORE = 'database/content-patches/m58-past-future-passives-before.v1.json';
    public const BEFORE_SHA = 'ab62337aea493548229910e4b908cbdeb15c444ae8638b69242b61a4f0147097';
    public const SOURCE = 'database/content-patches/m58-past-future-passives.v1.json';
    public const SOURCE_SHA = '863b2a6eeb826892d399d8e5de28a99d94f5e9ff76a6516a1b5604fc0e69df70';
    public const CONTRACT = 'docs/content/theory-sentence-builders/m58-past-future-passives.v1.json';
    public const CONTRACT_SHA = 'c6c45bffdb8313360c2b0d3e1944d4574fa2cc4a1a9a401e7b6f16e86edc5cc1';
    public const OWNERS = [
        'Database\\Seeders\\Page_V3\\PassiveVoice\\Tenses\\PassiveVoicePastPerfectTheorySeeder',
        'Database\\Seeders\\Page_V3\\PassiveVoice\\Tenses\\PassiveVoiceFutureSimpleTheorySeeder',
        'Database\\Seeders\\Page_V3\\PassiveVoice\\Tenses\\PassiveVoiceFuturePerfectTheorySeeder',
    ];
    public const SLUGS = ['theory-passive-voice-past-perfect', 'theory-passive-voice-future-simple', 'theory-passive-voice-future-perfect'];
    public const PAGE_IDS = [109, 107, 106];
    public const CATEGORY_IDS = [18, 18, 18];
    public const ANCESTRIES = [['passive-voice', 'passive-voice-tenses'], ['passive-voice', 'passive-voice-tenses'], ['passive-voice', 'passive-voice-tenses']];
    public const LEVELS = ['B1', 'B1', 'B2'];
    public const DISPLAY_LEVELS = ['B1', 'B1', 'B2'];
    public const CONTINUING_COUNTS = [12, 11, 12];
    public const CONTINUING_LEVELS = [
        ['B1', 'B1', 'B1', 'B1', 'B1', 'B1', 'B1', 'B1', 'B1', 'B1', 'B1', null],
        ['B1', 'B1', 'B1', 'B1', 'B1', 'B1', 'B1', 'B1', 'B1', 'B1', null],
        ['B2', 'B2', 'B2', 'B2', 'B2', 'B2', 'B2', 'B2', 'B2', 'B2', 'B2', null],
    ];
    public const RENDERED_TITLES = ['Past Perfect Passive', 'Future Simple Passive', 'Future Perfect Passive'];

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
        return ['label' => 'M58', 'base_sha' => self::BASE_SHA, 'version' => '1.0.0',
            'marker' => 'm58_native_v1', 'detail_marker' => 'm58_detail_key', 'uuid_prefix' => 'm58:', 'design' => 'm58-native-v1',
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
